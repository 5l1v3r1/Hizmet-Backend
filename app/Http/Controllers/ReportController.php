<?php

namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Helpers\Helper;
use App\Jobs\CreateReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class ReportController extends Controller
{
    private $columns;
    private $template_columns;

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->columns = array(
            "status" => array("visible" => false, "orderable" => false, "name" => false),
            "name" => array("name" => "report_name"),
            "type" => array(),
            "distributor" => array(),
            "created_by" => array(),
            "created_at" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true, "text_right" => true),
        );
    }

    public function showTable(Request $request){
        $prefix = "r";
        $url = "r_get_data/report";
        $default_order = '[5,"desc"]';
        $report_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        $report_table->set_add_right(false);

        // prepare
        $prefix = "rt";
        $url = "r_get_data/template";
        $default_order = '[5,"desc"]';
        $this->columns['status']['visible'] = true;
        $this->columns['name']['name'] = "template_name";
        $template_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        $template_table->set_add_right(false);

        return view(
            'pages.reporting',
            [
                'ReportsTableObj' => $report_table,
                'TemplatesTableObj' => $template_table
            ]
        );
    }

    public function getData(Request $request, $type){
        $is_report = 1;
        if( $type == "template" ){
            $is_report = 0;
        }

        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $order_column = "R.created_at";
        $order_dir = "DESC";
        $param_array = array();


        $param_array[] = $is_report;
        $where_clause = " R.status <> 0 AND R.is_report = ? ";

        // filters according to user type
        if( Auth::user()->user_type == 3){
            $param_array[]= Auth::user()->org_id;
            $where_clause .= " AND R.distributor_id = ? ";
        }

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;

            //special condition for column user_type

            if($order_column == "name"){
                if($is_report == 1){
                    $order_column = "R.report_name";
                }
                else{
                    $order_column = "R.template_name";
                }
            }
            else if($order_column == "type"){
                $order_column = "report_type";
            }
            else if($order_column == "distributor"){
                $order_column = "D.name";
            }
            else if($order_column == "created_by"){
                $order_column = "U.name";
            }

        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(R.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(trim($search_value) == ""){

            }
            else{
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "U.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR R.report_name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR R.template_name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR D.name LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::table('reports as R')
            ->leftJoin('users as U', 'U.id', 'R.created_by')
            ->leftJoin('distributors as D', 'D.id', 'R.distributor_id')
            ->whereRaw($where_clause, $param_array)
            ->count();

        $result = DB::table('reports as R')
            ->select(
                'R.*',
                DB::raw('(CASE WHEN R.template_id IS NULL THEN "'.trans('reporting.not_used_template').'" ELSE RR.template_name END) as report_template_name'),
                DB::raw('(CASE WHEN R.created_by=0 THEN "'.trans('global.system').'" ELSE U.name END) as created_by'),
                DB::raw('(CASE WHEN R.distributor_id=0 THEN "'. trans('global.main_distributor').'" ELSE D.name END) as distributor_name'),
                'D.id as distributor_id',
                'U.id as user_id',
                DB::raw("JSON_UNQUOTE(json_extract(R.detail, '$.report_type')) as report_type"),
                DB::raw("JSON_UNQUOTE(json_extract(R.detail, '$.working_type')) as working_type")
            )
            ->leftJoin('users as U', 'U.id', 'R.created_by')
            ->leftJoin('distributors as D', 'D.id', 'R.distributor_id')
            ->leftJoin('reports as RR', 'RR.id', 'R.template_id')
            ->whereRaw($where_clause, $param_array)
            ->orderBy($order_column,$order_dir)
            ->offset($start)
            ->limit($length)
            ->get();

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                // set name and status
                $status = "NA";
                $name = $one_row->report_name;
                $create_at = date('d/m/Y H:i', strtotime($one_row->created_at));

                if( $type == "template" ){
                    $name = $one_row->template_name;

                    if( $one_row->status == 3 ){ // periodic and active
                        $status = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('reporting.active_report').'"><i class="fa fa-calendar fa-2x" style="color:#33cc33;"></i></span>';
                    }
                    else if( $one_row->status == 4 ){ // periodic and passive
                        $status = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('reporting.passive_report').'"><i class="fa fa-pause-circle-o fa-2x" style="color:#ff9900;"></i></span>';
                    }
                    else if( $one_row->status == 1 ){ // instant
                        $status = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('reporting.instant_report').'"><i class="fa fa-clock-o fa-2x" style="color:#6600cc;"></i></span>';
                    }

                    // handle create_at
                    $first_run_time = date('d/m/Y H:i', strtotime($one_row->first_run_time));
                    $last_run_time = date('d/m/Y H:i', strtotime($one_row->last_run_time));

                    if( $first_run_time == "" || $first_run_time == NULL ){
                        $first_run_time = trans('reporting.not_worked_yet');
                    }

                    if( $last_run_time == "" || $last_run_time == NULL ){
                        $last_run_time = trans('reporting.not_worked_yet');
                    }

                    $create_at = '
                        <span 
                            data-toggle="tooltip" 
                            data-placement="bottom" 
                            title="
                                <div style=\'text-align: left;\'>
                                    <b>- '.trans('reporting.first_run').':</b> '.$first_run_time.' <br /> 
                                    <b>- '.trans('reporting.last_run').':</b> '.$last_run_time.' <br/> 
                                </div>"> '. $create_at . '
                        </span>
                    ';
                }

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "status" => $status,
                    "name" => $name,
                    "template" => $one_row->template_name,
                    "distributor" => $one_row->distributor_name,
                    "type" => trans('reporting.'.$one_row->report_type.'_report') . " (" . trans('reporting.'.$one_row->working_type) . ")",
                    "detail" => self::detail_html($one_row),
                    "created_by" => $one_row->created_by,
                    "created_at" => $create_at,
                    "buttons" => self::create_buttons($one_row->id, Auth::user()->operations, $type, $one_row->working_type, $one_row->status)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function create_buttons($id, $user_ops, $type, $working_type, $status){
        $return_value = "";

        if( $type == "report" ){
            if( $status == 2 ){ // preparing
                $return_value .= '<span style="color:#cb8508;font-weight: bold;"><i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i> '.trans('global.preparing').'</span>';
            }
            else{
                if( Helper::has_right(Auth::user()->operations, "view_reporting") ) {
                    $return_value .= '<a href="javascript:void(1);" title="' . trans('reporting.report_detail') . '" onclick="" class="btn btn-info btn-sm detail_button"><i class="fa fa-info-circle fa-lg"></i></a> ';

                    $return_value .= '<a href="javascript:void(1);" title="'.trans('reporting.download_report').'" onclick="download_report_file('.$id.');" class="btn btn-success btn-sm"><i class="fa fa-download fa-lg"></i></a> ';
                }

                if( Helper::has_right(Auth::user()->operations, "delete_report") ){
                    $return_value .= '<a href="javascript:void(1);" title="'.trans('reporting.delete_report').'" onclick="delete_report('.$id.', \''.$type.'\');" class="btn btn-danger btn-sm"><i class="fa fa-trash-o fa-lg"></i></a> ';
                }
            }
        }
        else if( $type == "template" ){
            if( Helper::has_right(Auth::user()->operations, "create_new_report") ) {
                if( $working_type == "periodic" ){
                    $i_class = "fa-pause";
                    $b_title = trans('reporting.deactivate_period');
                    $m_type = trans('reporting.stop_warning');

                    if( $status == 4 ){
                        $i_class = "fa-refresh";
                        $b_title = trans('reporting.activate_period');
                        $m_type = trans('reporting.start_warning');
                    }

                    $return_value .= '<a href="javascript:void(1);" title="'.$b_title.'" onclick="start_stop('.$id.', \''.$m_type.'\', '.$status.')" class="btn btn-info btn-sm"><i class="fa '.$i_class.' fa-lg"></i></a> ';
                }

                $return_value .= '<a href="javascript:void(1);" title="'.trans('reporting.run_now').'" onclick="rerun('.$id.')" class="btn btn-success btn-sm"><i class="fa fa-play-circle fa-lg"></i></a> ';

                $return_value .= '<a href="javascript:void(1);" title="'.trans('reporting.edit_report_template').'" onclick="edit_template('.$id.');" class="btn btn-warning btn-sm"><i class="fa fa-edit fa-lg"></i></a> ';
            }

            if( Helper::has_right(Auth::user()->operations, "delete_report") ) {
                $return_value .= '<a href="javascript:void(1);" title="'.trans('reporting.delete_report_template').'" onclick="delete_report('.$id.', \''.$type.'\');" class="btn btn-danger btn-sm"><i class="fa fa-trash-o fa-lg"></i></a> ';
            }
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    public function detail_html($data){
        $return_text = "";

        $return_text .= "
            <p>
                <span style='color:darkred;font-weight: bold;'>
                    ".trans("reporting.explanation").": 
                </span>
                ".($data->explanation!=""?$data->explanation:trans('reporting.no_explanation'))." 
            </p>
            <p>
                <span style='color:darkred;font-weight: bold;'>
                    ".trans("reporting.template_used").": 
                </span>
                ".$data->report_template_name." 
            </p>            
        ";

        $report_detail = json_decode($data->detail);
        $org_schema_detail = json_decode($data->org_schema_detail);
        $ainfo_detail = json_decode($data->additional_info);
        $email = json_decode($data->email);

        if( $report_detail->report_type == "stats" ){
            // uncompleted code
            //@TODO: Burada anlık mı periyodik mi ayrımına gidilmesi gerekiyor sanki!!!

            if( $report_detail->working_type == "instant" ){

            }
            else{

            }

            $content_types = array();
            foreach ($report_detail->content_types as $one){
                $content_types[] = trans('reporting.'.$one);
            }

            // calculate data_range
            $data_range = trans("alerts.since_day", array("day" => $report_detail->data_range));
            $data_range .= " (" . date('d/m/Y', strtotime('-'.$report_detail->data_range.' days', strtotime($data->created_at)));
            $data_range .= " - " . date('d/m/Y', strtotime($data->created_at)) . ")";

            $return_text .= "
                <p>
                    <span style='color:darkred;font-weight: bold;'>
                        ".trans("reporting.data_type").": 
                    </span>
                    ".implode(', ', $content_types)." (" . trans('reporting.'.$org_schema_detail->filter) . ")
                </p>
                <p>
                    <span style='color:darkred;font-weight: bold;'>
                        ".trans("reporting.data_range").": 
                    </span>
                    ".$data_range." 
                </p>            
            ";
        }
        else{ // consumption comparison
            $data_range = "N/A";

            if( isset($report_detail->comparison_start) && $report_detail->comparison_start != "" )
                $data_range = $report_detail->comparison_start . " - ";
            else
                $data_range = "N/A - ";

            if( isset($report_detail->comparison_end) && $report_detail->comparison_end != "" )
                $data_range .= $report_detail->comparison_end;
            else
                $data_range .= "N/A";

            $return_text .= "
                <p>
                    <span style='color:darkred;font-weight: bold;'>
                        ".trans("reporting.comparison_type").": 
                    </span>
                    ".trans('reporting.' . $report_detail->comparison_type . '_comparison') ." (" . trans('reporting.'.$org_schema_detail->filter) . ")
                </p>
                <p>
                    <span style='color:darkred;font-weight: bold;'>
                        ".trans("reporting.compared_dates", [ "type" => trans('reporting.'.$report_detail->comparison_type.'s') ]).": 
                    </span>
                    ".$data_range." 
                </p>            
            ";
        }

        if( $org_schema_detail->filter == "modem_based" ){
            $modem_ids = $org_schema_detail->values;

            $modem_list = trans('reporting.client_list_failed');
            if( COUNT($modem_ids)>0 ){
                $modems = DB::table('modems as M')
                    ->select(
                        'M.id as id',
                        'M.serial_no as serial_no',
                        'C.name as client_name',
                        DB::raw("JSON_UNQUOTE(json_extract(M.location, '$.verbal')) as location_verbal")
                    )
                    ->leftJoin('clients as C', 'C.id', 'M.client_id')
                    ->whereIn('M.id', $modem_ids)
                    ->get();

                if(COUNT($modems)>0 && is_numeric($modems[0]->id)){
                    $modem_list = "";

                    foreach ($modems as $one_modem){
                        $modem_list .= "
                            <span 
                                data-toggle='tooltip' 
                                data-placement='bottom' 
                                title='
                                    <div style=\"text-align: left;\">
                                        <b>- ". trans('reporting.client_name') . ":</b> " . $one_modem->client_name . "<br />
                                        <b>- ". trans('reporting.location') . ":</b> " . $one_modem->location_verbal . "
                                    </div>
                                '                              
                            >                            
                                <a href='/modem_management/detail/".$one_modem->id."' target='_blank'>".$one_modem->serial_no."</a>
                            </span>, ";
                    }
                }
            }

            $return_text .= "
                <p>
                    <span style='color:darkred;font-weight: bold;'>
                        ".trans("reporting.modems").": 
                    </span>
                    ". rtrim($modem_list, ', ') ." 
                </p>           
            ";
        }
        else{ // break based
            $break_ids = $org_schema_detail->values;
            foreach ($org_schema_detail->values as $key=>$one){
                if (strpos($one, 'client') !== false) {
                    unset($break_ids[$key]);
                }
            }

            if( COUNT($break_ids) == 1 && $break_ids[0] == 0){
                $breaks = trans('global.main_distributor');
            }
            else{
                $breaks = trans('reporting.break_list_failed');
                if( COUNT($break_ids)>0 ){
                    $result = DB::table('organization_schema')
                        ->select(
                            DB::raw('MAX(id) as id'),
                            DB::raw('GROUP_CONCAT(name SEPARATOR ", ") as name')
                        )
                        ->whereIn('id', $break_ids)
                        ->first();

                    if( COUNT($result)>0 && is_numeric($result->id) ){
                        $breaks = $result->name;
                    }
                }
            }

            $return_text .= "
                <p>
                    <span style='color:darkred;font-weight: bold;'>
                        ".trans("reporting.breaks").": 
                    </span>
                    ". $breaks ." 
                </p>           
            ";
        }

        // handle additional info
        if( COUNT($ainfo_detail)>0 ){
            $ainfo = DB::table('additional_infos')
                ->where('distributor_id', $data->distributor_id)
                ->get();

            $ainfos = array();
            if( COUNT($ainfo)>0 && is_numeric($ainfo[0]->id) ){
                foreach ($ainfo as $one_info){
                    $ainfos[$one_info->id] = $one_info;
                }

                $return_text .= "
                    <p>
                        <span style='color:darkred;font-weight: bold;'>
                            ".trans("reporting.additional_infos")."
                        </span>
                    </p>           
                ";

                $ainfo_categories = "";
                $ainfo_filters = "";
                $ainfo_infos = "";

                foreach ($ainfo_detail as $one){
                    if( $one->type == "category" ){
                        $ainfo_categories .= $ainfos[$one->id]->name . ", ";
                    }
                    else if( $one->type == "filter" ){
                        $ainfo_filters .= $ainfos[$one->id]->name . " (" . $ainfos[$one->value]->name . ")" . ", ";
                    }
                    else {
                        $ainfo_infos .= $ainfos[$one->id]->name . ", ";
                    }
                }

                $return_text .= "
                    <p>
                        <span style='color:darkred;font-weight: bold;'>
                            &nbsp;&nbsp;&nbsp; - ".trans("reporting.as_a_category").":
                        </span>
                        ". ($ainfo_categories == "" ? "-----":rtrim($ainfo_categories, ', ')) ."
                    </p>
                    <p>
                        <span style='color:darkred;font-weight: bold;'>
                            &nbsp;&nbsp;&nbsp; - ".trans("reporting.as_a_filter").":
                        </span>
                        ". ($ainfo_filters == "" ? "-----":rtrim($ainfo_filters, ', ')) ."
                    </p>
                    <p>
                        <span style='color:darkred;font-weight: bold;'>
                            &nbsp;&nbsp;&nbsp; - ".trans("reporting.as_an_info").":
                        </span>
                        ". ($ainfo_infos == "" ? "-----":rtrim($ainfo_infos, ', '))."
                    </p>
                ";
            }
        }

        // emails
        if( COUNT($email)>0 ){
            $return_text .= "
                <p>
                    <span style='color:darkred;font-weight: bold;'>
                        ".trans("reporting.email").": 
                    </span>
                    ". implode(', ', $email) ." 
                </p>           
            ";
        }

        return $return_text;
    }

    public function create(Request $request){
        $op_type = "";
        $edit_id = -1;

        if($request->has('report_op_type') && ( $request->input('report_op_type') == "new" || $request->input('report_op_type') == "edit")){
            $op_type = $request->input('report_op_type');
        }
        else{
            abort(404);
        }

        $purpose = "template";
        $report_type = "";
        $template_name = "";
        $report_name = "";
        $distributor_id = 0;
        $explanation = "";
        $is_report = 0;
        $last_insert_tid = NULL;
        $status = 1;
        $validation_array = array();
        $data_array = array();
        $org_schema_detail = array();
        $additional_info_array = array();
        $email_array = array();

        // report or template must be chosen
        if($request->has("purpose") && $request->input('purpose') == "on")
            $purpose = "report";

        // handle report type options (statistics or comparison)
        if(!$request->has("report_type_options"))
            abort(404);

        if($request->input("report_type_options") == 0){
            $report_type = "stats";
        }
        else if($request->input("report_type_options") == 1){
            $report_type = "comparison";
        }
        else{
            abort(404);
        }

        // common fields
        $data_array["report_type"] = $report_type;

        if($purpose == "report" && $op_type == "new"){
            $is_report = 1;

            $data_array["working_type"] = "instant";

            $validation_array["report_name"] = 'bail|required|max:255|min:3';
            $report_name = $request->input("report_name");

            if($report_type == "stats"){
                $validation_array["data_range"] = "bail|required|in:daily,weekly,yearly,monthly";
                $data_array["data_range"] = $request->input("data_range");

                $validation_array["stats_start"] = 'bail|required|regex:/(^[0-9\/]{4,10}$)/';
                $data_array["stats_start"] = $request->input("stats_start");

                $validation_array["stats_end"] = 'bail|required|regex:/(^[0-9\/]{4,10}$)/';
                $data_array["stats_end"] = $request->input("stats_end");

                $validation_array["content_types.*"] = 'bail|required|in:consumption,reactive_rates,current,voltage,cosfi';
                $data_array["content_types"] = $request->input("content_types");
            }
            else{
                $validation_array["comparison_type"] = 'bail|required|in:daily,weekly,monthly,yearly';
                $data_array["comparison_type"] = $request->input("comparison_type");

                $validation_array["comparison_start"] = 'bail|required|regex:/(^[0-9\/]{4,10}$)/';
                $data_array["comparison_start"] = $request->input("comparison_start");

                $validation_array["comparison_end"] = 'bail|required|regex:/(^[0-9\/]{4,10}$)/';
                $data_array["comparison_end"] = $request->input("comparison_end");
            }
        }
        else{ // template
            if( $op_type == "edit" ){
                if( $request->has('report_edit_id') && is_numeric($request->input('report_edit_id')) ){
                    $edit_id = $request->input('report_edit_id');

                    $result = DB::table('reports')
                        ->where('id', $edit_id)
                        ->where('is_report', 0)
                        ->where('status', '<>', 0)
                        ->first();

                    if( COUNT($result)>0 && is_numeric($result->id) ){
                        if( Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id ){
                            abort(404);
                        }

                        if(self::uniqueTemplateName($edit_id, $result->template_name) == false){
                            //return insert operation result via global session object
                            session(['same_template_name' => true]);
                            return redirect()->back();
                        }
                    }
                    else{
                        abort(404);
                    }
                }
                else{
                    abort(404);
                }
            }
            else{
                if(!($request->has("template_name") && trim($request->input("template_name"))!=""))
                    abort(404);

                //$validation_array["template_name"] = 'bail|required|unique:reports,template_name|max:255|min:3';
                if(self::uniqueTemplateName(0, $request->input("template_name")) == false){
                    //return insert operation result via global session object
                    session(['same_template_name' => true]);
                    return redirect()->back();
                }

            }

            $template_name = $request->input("template_name");

            if(!$request->has("working_type"))
                abort(404);

            $data_array["working_type"] = $request->input("working_type");

            if($request->input("working_type") == "instant"){
                if($report_type == "stats"){
                    $validation_array["data_range"] = "bail|required|in:daily,weekly,yearly,monthly";
                    $data_array["data_range"] = $request->input("data_range");

                    $validation_array["stats_start"] = 'bail|required|regex:/(^[0-9\/]{4,10}$)/';
                    $data_array["stats_start"] = $request->input("stats_start");

                    $validation_array["stats_end"] = 'bail|required|regex:/(^[0-9\/]{4,10}$)/';
                    $data_array["stats_end"] = $request->input("stats_end");

                    $validation_array["content_types.*"] = 'bail|required|in:consumption,reactive_rates,current,voltage,cosfi';
                    $data_array["content_types"] = $request->input("content_types");
                }
                else{
                    $validation_array["comparison_type"] = 'bail|required|in:daily,weekly,monthly,yearly';
                    $data_array["comparison_type"] = $request->input("comparison_type");

                    $validation_array["comparison_start"] = 'bail|required|regex:/(^[0-9\/]{4,10}$)/';
                    $data_array["comparison_start"] = $request->input("comparison_start");

                    $validation_array["comparison_end"] = 'bail|required|regex:/(^[0-9\/]{4,10}$)/';
                    $data_array["comparison_end"] = $request->input("comparison_end");
                }
            }
            else if($request->input("working_type") == "periodic"){
                $status = 3;

                if($report_type == "stats"){
                    if(!$request->has("working_period"))
                        abort(404);

                    $data_array["working_period"] = $request->input("working_period");

                    $validation_array["content_types.*"] = 'bail|required|in:consumption,reactive_rates,current,voltage,cosfi';
                    $data_array["content_types"] = $request->input("content_types");


                    if($request->input("working_period") == "daily"){
                        $data_array["data_range"] = "daily";
                    }
                    else if($request->input("working_period") == "weekly"){
                        $data_array["data_range"] = "weekly";
                        $validation_array["working_period_weekly"] = 'bail|required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday';
                        $data_array["working_period_weekly"] = $request->input("working_period_weekly");
                    }
                    else if($request->input("working_period") == "monthly"){
                        $data_array["data_range"] = "monthly";

                        $validation_array["working_period_monthly"] = 'bail|required|numeric|max:28|min:1';
                        $data_array["working_period_monthly"] = $request->input("working_period_monthly");

                    }
                    else if($request->input("working_period") == "yearly"){
                        $data_array["data_range"] = "yearly";
                    }
                    else{
                        abort(404);
                    }
                }
                else{
                    $data_array["comparison_type"] = $request->input("comparison_type");

                    if($request->input("comparison_type") == "daily"){
                        $validation_array["comparison_dates_pd"] = 'bail|required|in:ypdd,ypwd,ypmd,ypyd';
                        $data_array["comparison_dates_pd"] = $request->input("comparison_dates_pd");
                    }
                    else if($request->input("comparison_type") == "weekly"){
                        $validation_array["comparison_dates_pw"] = 'bail|required|in:lwpw,lwpm,lwpy';
                        $data_array["comparison_dates_pw"] = $request->input("comparison_dates_pw");
                    }
                    else if($request->input("comparison_type") == "monthly"){
                        $validation_array["comparison_dates_pm"] = 'bail|required|in:lmpm,lmpy';
                        $data_array["comparison_dates_pm"] = $request->input("comparison_dates_pm");
                    }
                    else if($request->input("comparison_type") == "yearly"){
                        $validation_array["comparison_dates_py"] = 'bail|required|in:lypy';
                        $data_array["comparison_dates_py"] = $request->input("comparison_dates_py");
                    }
                    else{
                        abort(404);
                    }
                }
            }
            else{
                abort(404);
            }
        }

        //handle distributor info
        if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
            if($request->input("report_distributor") != 0)
                $validation_array["report_distributor"] = 'bail|required|digits_between:1,11|exists:distributors,id';

            $distributor_id = $request->input("report_distributor");
        }
        else if(Auth::user()->user_type == 3){
            if($request->input("hdn_report_distributor") != Auth::user()->org_id)
                abort(404);

            $distributor_id = $request->input("hdn_report_distributor");
        }

        $validation_array["org_schema_filter"] = 'bail|required|numeric|in:0,1';
        $validation_array["hdn_org_schema_values"] = 'bail|required|min:1';

        $org_schema_detail["values"] = array();
        $selected_elements = explode(',', $request->input("hdn_org_schema_values"));

        if($request->input("org_schema_filter") == 0) {
            $org_schema_detail["filter"] = "modem_based";

            foreach ($selected_elements as $one_value){
                if( Helper::endsWith($one_value, 'modem') ){
                    $modem = explode('_', $one_value);
                    $modem_id = $modem[0];

                    $org_schema_detail["values"][] = $modem_id;
                }
            }
        }
        else {
            $org_schema_detail["filter"] = "break_based";

            foreach ($selected_elements as $one_value){

                if (strpos($one_value, 'client') === false) {
                    $org_schema_detail["values"][] = $one_value;
                }
            }

        }

        //handle additional info related to distributor_id
        if($distributor_id != 0){
            $add_infos = DB::table('additional_infos')
                ->where("distributor_id", $distributor_id)
                ->where("parent_id",0)
                ->where("status",1)
                ->get();

            if(COUNT($add_infos) && is_numeric($add_infos[0]->id)){
                foreach ($add_infos as $one_info){
                    if($request->has("ainfo_".$one_info->id)){
                        if($one_info->is_category){
                            $the_value = $request->input("ainfo_".$one_info->id);
                            if($the_value != ""){
                                $the_value = explode("_",$the_value);
                                $the_value = $the_value[2];
                                $additional_info_array[] = array(
                                    "type" => ($the_value == "category"?"category":"filter"),
                                    "id" => $one_info->id,
                                    "value" => $the_value
                                );
                            }
                        }
                        else{
                            $additional_info_array[] = array("type"=>"ainfo","id"=>$one_info->id,"value"=>"display");
                        }
                    }
                }
            }
        }

        //handle email
        if($request->input("report_emails") != ""){
            foreach ($request->input("report_emails") as $one_email){
                if (!filter_var($one_email, FILTER_VALIDATE_EMAIL) === false) {
                    $email_array[] = $one_email;
                }
            }
        }

        //handle explanation
        $validation_array["report_explanation"] = 'bail|min:3|max:500';
        $explanation = $request->input('report_explanation');

        //validation the form fields
        $this->validate($request, $validation_array);

        //save the data to DB
        if( $op_type == "new" ){
            $run_time = date("Y-m-d H:i:s");

            // Save it as a template
            if( $purpose == "template" ){
                if($request->input("working_type") != "instant"){
                    $run_time = NULL;
                }
                else{
                    $report_name = $template_name;
                }

                $last_insert_tid = DB::table('reports')->insertGetId(
                    [
                        'template_name' => $template_name,
                        'detail' => json_encode($data_array),
                        'distributor_id' => $distributor_id,
                        'org_schema_detail' => json_encode($org_schema_detail),
                        'additional_info' => json_encode($additional_info_array),
                        'email' => json_encode($email_array),
                        'explanation' => $explanation,
                        'status' => $status,
                        'is_report' => 0,
                        'first_run_time' => $run_time,
                        'last_run_time' => $run_time,
                        'created_by' => Auth::user()->id
                    ]
                );

                //fire event
                Helper::fire_event("create", Auth::user(), "reports", $last_insert_tid);

                //return insert operation result via global session object
                session(['create_template_success' => true]);
            }

            if( $purpose == "report"  || $request->input("working_type") == "instant"){
                $last_insert_id = DB::table('reports')->insertGetId(
                    [
                        'report_name' => $report_name,
                        'template_name' => $template_name,
                        'template_id' => $last_insert_tid,
                        'detail' => json_encode($data_array),
                        'distributor_id' => $distributor_id,
                        'org_schema_detail' => json_encode($org_schema_detail),
                        'additional_info' => json_encode($additional_info_array),
                        'email' => json_encode($email_array),
                        'explanation' => $explanation,
                        'is_report' => 1,
                        'status' => 2,
                        'created_by' => Auth::user()->id
                    ]
                );

                // Create ReportFile
                $this->dispatch(new CreateReport($last_insert_id));


                //fire event
                Helper::fire_event("create", Auth::user(), "reports", $last_insert_id);

                //return insert operation result via global session object
                session(['preparing_report_success' => true]);
            }
        }
        else if( $op_type == "edit" && $purpose == "template"){ // update template's info
            DB::table('reports')
                ->where('id', $edit_id)
                ->update(
                    [
                        'template_name' => $template_name,
                        'detail' => json_encode($data_array),
                        'distributor_id' => $distributor_id,
                        'org_schema_detail' => json_encode($org_schema_detail),
                        'additional_info' => json_encode($additional_info_array),
                        'email' => json_encode($email_array),
                        'explanation' => $explanation,
                        'status' => $status
                    ]
                );

            //fire event
            Helper::fire_event("update", Auth::user(), "reports", $edit_id);

            //return insert operation result via global session object
            session(['update_template_success' => true]);
        }

        return redirect()->back();
    }

    public function uniqueTemplateName($id, $name){
        if( is_numeric($id) && strlen($name)>3 ){
            $result = DB::table('reports')
                ->where('id', '<>', $id)
                ->where('template_name', $name)
                ->where('is_report', 0)
                ->where('status', '<>', 0)
                ->first();

            if( COUNT($result)>0 && is_numeric($result->id) ){
                return false;
            }
            else{
                return true;
            }
        }
        else{
            abort(404);
        }
    }

    public function deleteReport(Request $request){
        if( $request->has('id') && is_numeric($request->input('id')) && $request->has('type') ){
            $is_report = 1;

            if( $request->input('type') == "report" ){
                $is_report = 1;
            }
            else if( $request->input('type') == "template" ){
                $is_report = 0;
            }
            else{
                abort(404);
            }

            $result = DB::table('reports as R')
                ->where('R.id', $request->input('id'))
                ->where('R.status', '<>', 0)
                ->first();

            if( COUNT($result)>0 && is_numeric($result->id) ){
                if( Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id ){
                    abort(404);
                }

                DB::table('reports')
                    ->where('id', $request->input("id"))
                    ->update(
                        [
                            'status' => 0
                        ]
                    );

                if( $is_report == 1 && $result->document_name != "" ){
                    if(is_file(storage_path()."/app/public/".$result->document_name)){
                        unlink(storage_path()."/app/public/".$result->document_name); // delete file
                    }
                }

                //fire event
                Helper::fire_event("delete", Auth::user(), "reports", $request->input("id"));

                //return update operation result via global session object
                session([$request->input('type').'_delete_success' => true]);

                return "SUCCESS";
            }
            else{
                abort(404);
            }
        }
        else{
            abort(404);
        }
    }

    public function startStop(Request $request){
        if( $request->has('id') && is_numeric($request->input('id')) && $request->has('status') && is_numeric($request->input('status')) ){
            $result = DB::table('reports as R')
                ->where('R.id', $request->input('id'))
                ->where('R.status', $request->input('status'))
                ->first();

            if( COUNT($result)>0 && is_numeric($result->id) ){
                if( Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id ){
                    abort(404);
                }

                $status = 4;
                if( $result->status == 4 ){
                    $status = 3;
                }

                DB::table('reports')
                    ->where('id', $request->input("id"))
                    ->update(
                        [
                            'status' => $status
                        ]
                    );

                //fire event
                Helper::fire_event("update", Auth::user(), "reports", $request->input("id"));

                //return update operation result via global session object
                session(['template_update_success' => true]);

                return "SUCCESS";
            }
            else{
                abort(404);
            }
        }
        else{
            abort(404);
        }
    }

    public function getAddInfo(Request $request){

        if($request->has("distributor_id") && is_numeric($request->input("distributor_id"))){

            if(Auth::user()->user_type == 3 && Auth::user()->org_id != $request->input("distributor_id"))
                abort(404);



            $op_type = "new";
            // if the type is equal "new" than return only additional info input fields otherwise fill in with their value
            if( $request->has('op_type') && $request->input('op_type') == "edit" ){
                $op_type = "edit";
            }

            $report_ainfo = array();
            if( $op_type == "edit" ){
                if( $request->has('template_id') && is_numeric($request->input('template_id')) ){

                    $tmp_values = DB::table('reports')
                        ->select(
                            'id',
                            'additional_info'
                        )
                        ->where('id', $request->input("template_id"))
                        ->where("status", "<>", 0)
                        ->where('is_report', 0)
                        ->first();

                    if( COUNT($tmp_values)>0 && is_numeric($tmp_values->id) ){
                        if( $tmp_values->additional_info != "" && $tmp_values->additional_info != NULL ){
                            $tmp_values = json_decode($tmp_values->additional_info, true);

                            foreach ($tmp_values as $one_value){
                                $report_ainfo[$one_value["id"]] = $one_value;
                            }
                        }

                    }
                    else{
                        abort(404);
                    }
                }
                else{
                    abort(404);
                }
            }

            $return_text = "";
            $result = DB::table("additional_infos")
                ->where("distributor_id", $request->input("distributor_id"))
                ->where("parent_id", 0)
                ->where("status", "<>", 0)
                ->get();

            if(COUNT($result)>0 && is_numeric($result[0]->id)){
                foreach($result as $one_result){
                    if($one_result->is_category){
                        $element_result =  DB::table("additional_infos")
                                ->where("distributor_id", $request->input("distributor_id"))
                                ->where("parent_id",$one_result->id)
                                ->where("status","<>",0)
                                ->get();

                        if(COUNT($element_result)>0 && is_numeric($element_result[0]->id)){
                            $return_text .='
                            <div class="form-group">
                                <label class="col-sm-3 control-label">'.$one_result->name.'</label>
                                <div class="col-sm-6">
                                    <select name="ainfo_'.$one_result->id.'" id="ainfo_'.$one_result->id.'" class="form-control" style="width:100%;">
                                        <option></option>                                        
                                ';

                            $input_value = "";
                            if(isset($report_ainfo[$one_result->id])){
                                $input_value = $report_ainfo[$one_result->id]["value"];
                            }

                            if( $input_value == "category" ){
                                $return_text .= '<option value="ainfo_opt_category" selected> '.trans('reporting.use_as_group').' </option>';
                            }
                            else{
                                $return_text .= '<option value="ainfo_opt_category"> '.trans('reporting.use_as_group').' </option>';
                            }

                            foreach($element_result as $one_element){
                                $selected = "";
                                if($input_value == $one_element->id)
                                    $selected = "selected";

                                $return_text.='<option value="ainfo_opt_'.$one_element->id.'" '.$selected.'>'.$one_element->name.'</option>';
                            }

                            $return_text .='
                                    </select>
                                    
                                    <script>
                                        $("#ainfo_'.$one_result->id.'").select2({
                                            placeholder:"'.trans("client_management.select_one").'",
                                            minimumResultsForSearch: Infinity,
                                            allowClear: true
                                        });
                                    </script>
                                </div>
                            </div>';
                        }
                    }
                    else{
                        $input_value = "";
                        $checked = "";

                        if(isset($report_ainfo[$one_result->id])){
                            $input_value = $report_ainfo[$one_result->id]["value"];
                        }

                        if( $input_value == "display" ){
                            $checked = "checked";
                        }

                        $return_text .='
                            <div class="form-group">
                                <label class="col-sm-3 control-label">'.$one_result->name.'</label>
                                <div class="col-sm-6" style="padding-top: 6px;">
                                    <div id="div_ainfo_'.$one_result->id.'">
                                        <input type="checkbox" id="ainfo_'.$one_result->id.'" name="ainfo_'.$one_result->id.'" '.$checked.'>
                                        <label for="ainfo_'.$one_result->id.'" title="'.trans('reporting.ainfo_analyze_exp').'"> '.trans("reporting.analyze_according_this").' </label>
                                        <script>
                                            $("#div_ainfo_'.$one_result->id.'").iCheck({
                                                checkboxClass: "icheckbox_flat-orange",
                                                radioClass: "iradio_flat-orange",
                                                cursor: true
                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>';
                    }
                }

                return $return_text;
            }
            else{
                return "EMPTY";
            }
        }
        else{

            abort(404);
        }
    }

    public function getInfo(Request $request){
        if( $request->has('id') && is_numeric($request->input('id')) ){
            $result = DB::table('reports as R')
                ->where('R.id', $request->input('id'))
                ->where('R.status', '<>', 0)
                ->where('is_report', 0)
                ->first();

            if( COUNT($result)>0 && is_numeric($result->id) ){
                if( Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id ){
                    abort(404);
                }

                return json_encode($result);
            }
            else{
                //return "ERROR_2";
                abort(404);
            }
        }
        else{
            //return "ERROR_1";
            abort(404);
        }
    }

    public function reRunTemplate(Request $request){
        if( $request->has('id') && is_numeric($request->input('id')) ){
            $result = DB::table('reports as R')
                ->where('R.id', $request->input('id'))
                ->where('R.status', '<>', 0)
                ->where('R.is_report', 0)
                ->first();

            if( COUNT($result)>0 && is_numeric($result->id) ){
                if( Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id ){
                    abort(404);
                }

                $last_insert_id = DB::table('reports')->insertGetId(
                    [
                        'report_name' => $result->template_name,
                        'template_name' => $result->template_name,
                        'template_id' => $result->id,
                        'detail' => $result->detail,
                        'distributor_id' => $result->distributor_id,
                        'org_schema_detail' => $result->org_schema_detail,
                        'additional_info' => $result->additional_info,
                        'email' => $result->email,
                        'explanation' => $result->explanation,
                        'is_report' => 1,
                        'status' => 2,
                        'created_by' => $result->created_by
                    ]
                );

                $run_time = date('Y-m-d H:i:s');
                $first_run_time = $result->first_run_time;

                if( $first_run_time == "" || $first_run_time == NULL ){
                    $first_run_time = $run_time;
                }

                DB::table('reports')
                    ->where('id', $result->id)
                    ->update(
                        [
                            'first_run_time' => $first_run_time,
                            'last_run_time' => $run_time
                        ]
                    );

                // Create ReportFile
                $this->dispatch(new CreateReport($last_insert_id));

                //fire event
                Helper::fire_event("create", Auth::user(), "reports", $last_insert_id);

                //return insert operation result via global session object
                //session(['preparing_report_success' => true]);

                return "SUCCESS";
            }
            else{
                abort(404);
            }
        }
        else{
            abort(404);
        }
    }

    public function downloadReportFile(Request $request){
        if( $request->has('report_id') && is_numeric($request->input('report_id')) ){

            $download_token = $request->input("download_token");

            setcookie(
                "DownloadToken",
                $download_token,
                2147483647,            // expires January 1, 2038
                "/",                   // your path
                $_SERVER["HTTP_HOST"], // your domain
                false,               // Use true over HTTPS
                false              // Set true for $AUTH_COOKIE_NAME
            );

            $result = DB::table('reports as R')
                ->where('R.id', $request->input('report_id'))
                ->where('R.status', '<>', 0)
                ->where('R.is_report', 1)
                ->first();

            if( COUNT($result)>0 && is_numeric($result->id) ){
                if( Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id ){
                    abort(404);
                }

                if( $result->document_name == "" || $result->document_name == NULL || strlen($result->document_name) < 5 ){
                    echo "NOT_CREATED_YET";
                    return;
                }

                $report_detail = json_decode($result->detail);

                if( $report_detail->report_type == "stats" ){
                    $suffix = ".docx";
                    $word_header = 'Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                }
                else if( $report_detail->report_type == "comparison"){
                    $suffix = ".xlsx";
                    $excel_header = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                }
                else{
                    return "ERROR";
                }

                $file_path = storage_path()."/app/public/" . $result->document_name;
                $file_name = $result->document_name;

                if( file_exists($file_path) ){
                    // Prepare the report file to download
                    header('Content-Description: File Transfer');

                    // header('Content-Type: application/force-download');
                    if( $report_detail->report_type == "stats" ){
                        header($word_header);
                    }
                    else{
                        header($excel_header);
                    }

                    header('Content-Disposition: attachment; filename='.$file_name);
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header('Content-Length: ' . filesize($file_path));
                    header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: no-cache');

                    // clean the output buffer
                    ob_clean();
                    flush();

                    readfile($file_path);
                }
                else{
                    echo "FILE_NEXIST";
                    return;
                }

            }
            else{
                abort(404);
            }
        }
        else{
            abort(404);
        }
    }

    public static function performPeriodicReport(){

        //check all report templates to check if they are ready to be sent
        $templates = DB::table('reports')
            ->where('is_report',0)
            ->where('status',3)
            ->get();

        if(COUNT($templates) && is_numeric($templates[0]->id)){
            foreach ($templates as $one_template){

                $report_detail = json_decode($one_template->detail,true);
                if($report_detail["working_type"]=="periodic"){


                    //check if the time is ready for new report
                    $comparison_type = $report_detail["comparison_type"];
                    $is_reportable = false;

                    if($comparison_type == "daily"){

                        $is_reportable = true;
                    }
                    else if($comparison_type == "weekly"){

                        if(date('D') === 'Mon')
                            $is_reportable = true;
                    }
                    else if($comparison_type == "monthly"){
                        if(date('j') === '1')
                            $is_reportable = true;
                    }
                    else if($comparison_type == "yearly"){
                        if(date('z') === '0')
                            $is_reportable = true;
                    }

                    if($is_reportable == true){
                        $last_insert_id = DB::table('reports')->insertGetId(
                            [
                                'report_name' => $one_template->template_name,
                                'template_name' => $one_template->template_name,
                                'template_id' => $one_template->id,
                                'detail' => $one_template->detail,
                                'distributor_id' => $one_template->distributor_id,
                                'org_schema_detail' => $one_template->org_schema_detail,
                                'additional_info' => $one_template->additional_info,
                                'email' => $one_template->email,
                                'explanation' => $one_template->explanation,
                                'is_report' => 1,
                                'status' => 2,
                                'created_by' => 0
                            ]
                        );

                        $run_time = date('Y-m-d H:i:s');
                        $first_run_time = $one_template->first_run_time;

                        if( $first_run_time == "" || $first_run_time == NULL ){
                            $first_run_time = $run_time;
                        }

                        DB::table('reports')
                            ->where('id', $one_template->id)
                            ->update(
                                [
                                    'first_run_time' => $first_run_time,
                                    'last_run_time' => $run_time
                                ]
                            );

                        dispatch(new CreateReport($last_insert_id));
                    }
                    else{
                        continue;
                    }


                }
                else{
                    continue;
                }

            }
        }

    }

}
