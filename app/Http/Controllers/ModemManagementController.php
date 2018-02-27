<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;

class ModemManagementController extends Controller
{
    private $columns;
    private $device_columns;
    private $alerts_columns;

    public function __construct()
    {
        $this->columns = array(
            "status"=>array("orderable"=>false),
            "serial_no"=>array(),
            "modem_type"=>array(),
            "model"=>array("name"=>"trademark_model"),
            "client"=>array(),
            "location"=>array(),
            "last_connection_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );

        $this->device_columns = array(
            "status"=>array("orderable"=>false),
            "device_no" => array(),
            "device_type" =>array("name"=>"type"),
            "modem_no" => array("visible"=>false),
            "client" => array("name" => "client_distributor","visible"=>false),
            "inductive" => array(),
            "capacitive" => array(),
            "data_period" => array(),
            "last_data_at" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );

        $this->alerts_columns = array(
            "icon" => array("orderable" => false, "name" => false),
            "type" => array(),
            "device_no" => array("visible" => false, "name" => "device_no_type"),
            "notification_method" => array("orderable" => false),
            "client" => array("visible" => false, "name" => "client_distributor"),
            "created_at"=>array(),
            "buttons"=>array( "orderable" => false, "name" => "operations", "nowrap" => true)
        );
    }

    public function showTable(Request $request){
        $prefix = "mm";
        $url = "mm_get_data";
        $default_order = '[6,"desc"]';

        if(Auth::user()->user_type==4)
            $this->columns["client"]["visible"] = false;

        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);

        return view('pages.modem_management')->with("DataTableObj",$data_table);
    }

    public function getData($detail_type="", $detail_org_id=""){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $order_column = "M.last_connection_at";
        $order_dir = "DESC";

        $param_array = array();
        $where_clause = " M.status<>0 ";

        if($detail_type == "client"){
            if( !is_numeric($detail_org_id) ){
                abort(404);
            }

            $param_array[] = $detail_org_id;
            $where_clause .= " AND M.client_id=? ";
        }
        else if($detail_type == "distributor"){
            if( !is_numeric($detail_org_id) ){
                abort(404);
            }

            $param_array[] = $detail_org_id;
            $where_clause .= " AND C.distributor_id = ? ";
        }

        if( Auth::user()->user_type == 3 ){
            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }
        else if( Auth::user()->user_type == 4 ){
            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND C.id=? ";
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

            if($order_column == "client"){
                $order_column = " C.name ";
            }
            else if($order_column =="location"){
                $order_column = "JSON_UNQUOTE(json_extract(M.location, '$.verbal'))";
            }
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(M.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "M.serial_no LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR MT.type LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR MT.trademark LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR MT.model LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR M.distinctive_identifier LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR C.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR D .name LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";
                $where_clause .= " OR lcase(JSON_UNQUOTE(json_extract(M.location,'$.verbal'))) LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";
                $where_clause .= " OR lcase(JSON_UNQUOTE(json_extract(M.location,'$.text'))) LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('
                                    SELECT 
                                      count(*) as total_count 
                                    FROM 
                                      modems M, 
                                      modem_type MT,
                                      clients C
                                    LEFT JOIN distributors D ON D.id=C.distributor_id
                                    WHERE '.$where_clause.' AND 
                                        M.modem_type_id=MT.id AND 
                                        C.id=M.client_id 
                                   ',
                                   $param_array
                                );
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        /* OLD VERSION
        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT M.*, JSON_UNQUOTE(json_extract(M.location,\'$.verbal\')) as location_verbal,JSON_UNQUOTE(json_extract(M.location,\'$.text\')) as location_text, MT.type as modem_type, C.name as client_name, C.id as client_id, MT.trademark as trademark, MT.model as model, D.name as distributor, D.id as distributor_id, DS.device_name as devices, DS.data_period as data_period FROM clients C, modem_type MT, distributors D, modems M LEFT JOIN (SELECT D.modem_id as modem_id, MAX(D.data_period) as data_period, GROUP_CONCAT(D.device_no ORDER BY D.device_no SEPARATOR \', \') as device_name FROM devices D WHERE status<>0 GROUP BY modem_id) DS ON DS.modem_id = M.id '.$where_clause.' AND M.modem_type_id=MT.id AND C.id=M.client_id AND D.id=C.distributor_id ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?',$param_array);
        */

        $result = DB::table('modems as M')
            ->select(
                'M.*',
                DB::raw("JSON_UNQUOTE(json_extract(M.location, '$.verbal')) as location_verbal"),
                DB::raw("JSON_UNQUOTE(json_extract(M.location, '$.text')) as location_text"),
                'MT.type as modem_type',
                'C.name as client_name',
                'C.id as client_id',
                'MT.trademark as trademark',
                'MT.model as model',
                DB::raw('(CASE WHEN C.distributor_id=0 THEN "'. trans('global.main_distributor').'" ELSE D.name END) as distributor'),
                'D.id as distributor_id',
                'DS.device_name as devices',
                'DS.data_period as data_period'
            )
            ->leftJoin(DB::raw("(SELECT 
                                  D.modem_id as modem_id, 
                                  MAX(D.data_period) as data_period, 
                                  GROUP_CONCAT(D.device_no ORDER BY D.device_no SEPARATOR ', ') as device_name 
                                FROM 
                                  devices D 
                                WHERE D.status<>0 
                                GROUP BY modem_id) as DS"), 'DS.modem_id', 'M.id')
            ->leftJoin('modem_type as MT', 'MT.id', 'M.modem_type_id')
            ->leftJoin('clients as C', 'C.id', 'M.client_id')
            ->leftJoin('distributors as D', 'D.id', 'C.distributor_id')
            ->whereRaw($where_clause, $param_array)
            ->orderBy($order_column, $order_dir)
            ->offset($start)
            ->limit($length)
            ->get();

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        $status_never_connection = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.no_connection_yet').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:#ff9900;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-chain-broken" style="position: absolute;color:red;"></i></span></span>';

        $status_no_connection = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.connection_failed').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:#ff9900;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-chain-broken" style="position: absolute;color:red;"></i></span></span>';

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                $status = '<i class="fa fa-check-square-o fa-2x" aria-hidden="true" style="color: green;"></i>';

                $last_connection_at = ($one_row->last_connection_at!=null?"<span data-toggle='tooltip' data-placement='bottom' title='". trans('modem_management.first_connection_at') . ": " . date('d/m/Y H:i',strtotime($one_row->first_connection_at)) . "'>" . date('H:i',strtotime($one_row->last_connection_at)) . "</span>":"<span style='color: #FF0000;'>".trans("modem_management.no_connection")."</span>");

                if($one_row->last_connection_at!=null) {
                    $last_data_diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($one_row->last_connection_at));
                    $minutes = round($last_data_diff / 60);
                    $minutes_verbal = Helper::secondsToTime($last_data_diff);

                    if ($minutes > $one_row->data_period) {
                        $last_connection_at = "<i class='fa fa-info-circle ' style='font-size:20px;color:#cc0000;float:left;margin-right:3px;'></i> <div style='float:left;color:#cc0000;font-weight: bold;' data-toggle='tooltip' data-placement='bottom' title='" . trans("devices.last_connection_verbal", array("verbal" => $minutes_verbal)) . "'> " . date('d/m/Y', strtotime($one_row->last_connection_at)) . " </div>";

                        $status = $status_no_connection;
                    }
                }
                else{
                    $status = $status_never_connection;
                }

                $client_link = '<a title="'.trans('modem_management.go_client_detail').'" href="/client_management/detail/'.$one_row->client_id.'" target="_blank"> '.$one_row->client_name.'</a>';

                if( Auth::user()->user_type == 1 || Auth::user()->user_type==2 ){
                    if( $one_row->distributor_id != 0 ){
                        $client_link .= " / " . '<a title="'.trans('modem_management.go_distributor_detail').'" href="/distributor_management/detail/'.$one_row->distributor_id.'" target="_blank">'.$one_row->distributor.'</a>';
                    }else{
                        $client_link .= " / " . $one_row->distributor;
                    }
                }

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "status" => $status,
                    "serial_no" => $one_row->serial_no . "<br/>" . $one_row->distinctive_identifier,
                    "modem_type" => trans('global.'.$one_row->modem_type),
                    "model" => $one_row->trademark."/".$one_row->model,
                    "client" => $client_link,
                    "location" => "<span data-toggle='tooltip' data-placement='bottom' title='".$one_row->location_text."'>".$one_row->location_verbal."</span>",
                    "last_connection_at" => $last_connection_at,
                    "buttons" => self::create_buttons($one_row->id,$one_row->devices, $detail_type)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function modemDetail(Request $request, $id){
        //prepare modem specific info
        /* OLD VERSION
        $the_modem = DB::select('SELECT M.*,JSON_UNQUOTE(json_extract(M.location,\'$.text\')) as location_text, MT.type as modem_type, C.name as client_name,C.logo as client_avatar,  MT.trademark as trademark, MT.model as model, D.name as distributor, U.name as created_by, C.distributor_id as distributor_id FROM clients C,modem_type MT, modems M, distributors D, users U WHERE U.id=M.created_by AND M.id=? AND M.status<>0 AND M.modem_type_id=MT.id AND C.id=M.client_id AND D.id=C.distributor_id',[$id]);
        $the_modem = $the_modem[0];
        */

        $the_modem = DB::table('modems as M')
                    ->select(
                        'M.*',
                        DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.text')) as location_text"),
                        'MT.type as modem_type',
                        'MT.trademark as trademark',
                        'MT.model as model',
                        'C.name as client_name',
                        'C.logo as client_avatar',
                        DB::raw("(CASE WHEN C.distributor_id=0 THEN '". trans('global.main_distributor') ."' ELSE D.name END) as distributor"),
                        'C.distributor_id as distributor_id',
                        'U.name as created_by'
                    )
                    ->leftJoin('modem_type as MT', 'MT.id', 'M.modem_type_id')
                    ->leftJoin('users as U', 'U.id', 'M.created_by')
                    ->leftJoin('clients as C', 'C.id', 'M.client_id')
                    ->leftJoin('distributors as D', 'D.id', 'C.distributor_id')
                    ->where('M.status', '<>', 0)
                    ->where('M.id', $id)
                    ->first();

        if(Auth::user()->user_type == 3){
            if(Auth::user()->org_id != $the_modem->distributor_id)
                abort(404);
        }
        else if(Auth::user()->user_type == 4){
            if(Auth::user()->org_id != $the_modem->client_id)
                abort(404);
        }

        //prepare devices table obj which belongs to this modem
        $prefix = "md";
        $url = "md_get_data/all_devices/modem/".$id;
        $default_order = '[8,"desc"]';
        $data_table = new DataTable($prefix, $url, $this->device_columns, $default_order,$request);
        $data_table->set_add_right(false);
        $data_table->set_lang_page("devices");

        //prepare alerts table obj which belongs to this modem
        $prefix = "mdal";
        $url = "al_get_data/modem/".$id;
        $default_order = '[5,"desc"]';
        $alert_table = new DataTable($prefix, $url, $this->alerts_columns, $default_order, $request);
        $alert_table->set_add_right(false);
        $alert_table->set_lang_page("alerts");

        return view(
            'pages.modem_detail',
            [
                'the_modem' => json_encode($the_modem),
                'DevicesTableObj' => $data_table,
                'AlertsTableObj' => $alert_table
            ]
        );
    }

    public function getDeviceData(Request $request,$id){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $where_clause = "WHERE D.status<>0 ";
        $order_column = "D.last_data_at";
        $order_dir = "DESC";

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->device_columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;

            if($order_column == "device_type")
                $order_column = "VDT.device_type";
            else if($order_column =="model"){
                $order_column = "DT.trademark ".$order_dir." ,DT.model";
            }
        }

        $param_array = array();
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(D.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "D.device_no LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR DT.trademark LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR DT.model LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR VDT.device_type LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        if( Auth::user()->user_type == 3 ){
            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }
        else if( Auth::user()->user_type == 4 ){
            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND C.id=? ";
        }


        //filter by modem_id
        $param_array[]= $id;
        $where_clause .= " AND M.id=? ";

        $total_count = DB::select('SELECT count(*) as total_count FROM clients C,device_type DT, devices D,modems M,'.Helper::device_type_virtual_table().' as VDT  '.$where_clause.' AND M.id=D.modem_id AND D.device_type_id=DT.id AND C.id=M.client_id AND VDT.type=DT.type ',$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT D.*, DT.type as device_type, DT.trademark as trademark, DT.model as model FROM clients C,device_type DT, modems M, devices D, '.Helper::device_type_virtual_table().' as VDT '.$where_clause.' AND D.device_type_id=DT.id AND C.id=M.client_id AND M.id=D.modem_id AND VDT.type=DT.type ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?',$param_array);

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                if( true ){
                    $status = '<i class="fa fa-check-circle fa-2x" aria-hidden="true" style="color: green;"></i>';
                }
                else{
                    $status = '<i class="fa fa-exclamation-triangle fa-2x" aria-hidden="true" style="color: orange;"></i>';
                }

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "device_no" => $one_row->device_no,
                    "device_type" => trans("global." . $one_row->device_type),
                    "model" => $one_row->trademark . "/" . $one_row->model,
                    "status" => $status,
                    "inductive" => $one_row->inductive,
                    "capacitive" => $one_row->capacitive,
                    "last_data_at" => ($one_row->last_data_at != null ? date('d/m/Y H:i:s', strtotime($one_row->last_data_at)) : trans("modem_detail.no_first_data")),
                    "buttons" => self::create_device_buttons($one_row->id, $one_row->device_type)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    /**
     * Return the information of related modem to edit operations
     *
     * @param Request $request
     * @return string
     */
    public function getInfo(Request $request){
        if($request->has("id") && is_numeric($request->input("id"))){

            // Auth user has right to edit
            $result = DB::table('modems as M')
                    ->join('clients as C', 'C.id', '=', 'M.client_id')
                    ->where('M.id',$request->input('id'))
                    ->where("M.status",'<>', 0)
                    ->first();

            if( isset($result->id)){
                if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){ }
                else if( Auth::user()->user_type == 3 ){
                    if( Auth::user()->org_id != $result->distributor_id ){
                        return "ERROR_1";
                    }
                }
                else{
                    return "ERROR_2";
                }
            }
            else{
                return "ERROR_3";
            }

            $result = DB::table("modems")
                ->where("id", $request->input("id"))
                ->where('status','<>',0)
                ->first();

            if(isset($result->id)){
                echo json_encode($result);
            }
            else{
                echo "ERROR";
            }
        }
        else{
            echo "NEXIST";
        }
    }

    public function create(Request $request){
        $op_type = "new";
        $created_by = Auth::user()->id;
        //$serial_no_validation = 'bail|required|alpha_num|min:3|max:10|unique:modems,serial_no';
        $serial_no_validation = 'bail|required|min:3|max:255|unique:modems,serial_no';
        $additional_info_array = array();

        if( $request->has('modem_op_type') && trim($request->input('modem_op_type')) == "edit" ) {
            $op_type = "edit";

            // Auth user has right to edit
            $result = DB::table('modems as M')
                ->join('clients as C', 'C.id', 'M.client_id')
                ->where('M.id', $request->input('modem_edit_id'))
                ->first();

            if( isset($result->id) ){
                if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){ }
                else if( Auth::user()->user_type == 3 ){
                    if( Auth::user()->org_id != $result->distributor_id ){
                        return "ERROR_1";
                    }
                }
                else{
                    return "ERROR_2";
                }
            }
            else{
                return "ERROR_3";
            }

            // $serial_no_validation = 'bail|required|alpha_num|min:3|max:10|unique:modems,serial_no,'.$request->input("modem_edit_id").',id';
            $serial_no_validation = 'bail|required|min:3|max:255|unique:modems,serial_no,'.$request->input("modem_edit_id").',id';
        }

        $this->validate($request, [
            'new_modem_serial_no' => $serial_no_validation,
            'new_modem_distinctive_identifier' => 'bail|required|min:3|max:255',
            'new_modem_type' => 'bail|required|digits_between:1,11',
            'new_modem_client' => 'bail|required|digits_between:1,11',
            'new_modem_location_text' => 'bail|required|min:3|max:255',
            'new_modem_location_latitude' => 'bail|required|min:2|max:30',
            'new_modem_location_longitude' => 'bail|required|min:2|max:30',
            'airports' => 'bail|required|digits_between:1,100',
            'new_modem_explanation' => 'bail|min:3|max:500'
        ]);

        $location_verbal = preg_split("/[0-9]{5}/", $request->input("new_modem_location_text"));
        $location_verbal = explode(',',$location_verbal[1]);
        $location_verbal = trim($location_verbal[0]);

        $location_json = ["text"=>$request->input("new_modem_location_text"),"latitude"=>$request->input("new_modem_location_latitude"),"longitude"=>$request->input("new_modem_location_longitude"),"verbal"=>$location_verbal];
        $location_json = json_encode($location_json);

        //handle additional informations
        $client = DB::table('clients')
            ->where('id',$request->input("new_modem_client"))
            ->where('status','<>',0)
            ->first();

        if(!(COUNT($client)>0 && is_numeric($client->id)))
            abort(404);

        if($client->distributor_id != 0){
            $add_infos = DB::table('additional_infos')
                ->where("distributor_id",$client->distributor_id)
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
                                $additional_info_array[] = array("id"=>$one_info->id,"value"=>$the_value);
                            }
                        }
                        else{
                            $additional_info_array[] = array("id"=>$one_info->id,"value"=>$request->input("ainfo_".$one_info->id));
                        }
                    }
                }
            }
        }


        //save the data to DB
        if( $op_type == "new" ){ // insert new modem
            $last_insert_id = DB::table('modems')->insertGetId(
                [
                    'serial_no' => $request->input("new_modem_serial_no"),
                    'distinctive_identifier' => $request->input("new_modem_distinctive_identifier"),
                    'modem_type_id' => $request->input("new_modem_type"),
                    'client_id' => $request->input("new_modem_client"),
                    'location' => $location_json,
                    'airport_id' => $request->input('airports'),
                    'explanation' => $request->input("new_modem_explanation"),
                    'created_by' => $created_by,
                    'additional_info' => json_encode($additional_info_array)
                ]
            );

            //fire event
            Helper::fire_event("create",Auth::user(),"modems",$last_insert_id);

            //return insert operation result via global session object
            session(['new_modem_insert_success' => true]);
        }
        else if( $op_type == "edit" ){ // update user's info
            DB::table('modems')->where('id', $request->input("modem_edit_id"))
                ->update(
                    [
                        'serial_no' => $request->input("new_modem_serial_no"),
                        'distinctive_identifier' => $request->input("new_modem_distinctive_identifier"),
                        'modem_type_id' => $request->input("new_modem_type"),
                        'client_id' => $request->input("new_modem_client"),
                        'location' => $location_json,
                        'airport_id' => $request->input('airports'),
                        'explanation' => $request->input("new_modem_explanation"),
                        'additional_info' => json_encode($additional_info_array)
                    ]
                );

            //fire event
            Helper::fire_event("update",Auth::user(),"modems",$request->input("modem_edit_id"));

            //return update operation result via global session object
            session(['modem_update_success' => true]);
        }

        return redirect()->back();
    }

    public function create_buttons($item_id, $devices, $detail_type){
        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_modem_detail")){
            $return_value .= '<a href="/modem_management/detail/'.$item_id.'" title="'.trans('modem_management.detail_modem').'" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
        }

        if($detail_type == ""){
            if(Helper::has_right(Auth::user()->operations, "add_new_modem")){
                $return_value .= '<a href="javascript:void(1);" title="'.trans('modem_management.edit_modem').'" onclick="edit_modem('.$item_id.');" class="btn btn-warning btn-sm"><i class="fa fa-edit fa-lg"></i></a> ';
            }

            if(Helper::has_right(Auth::user()->operations, "delete_modem")){
                $return_value .= '<a '.($devices!=""?"style=\"opacity:0.4;\"":"").' href="javascript:void(1);" title="'.trans('modem_management.delete_modem').'" onclick="'.($devices!=""?"alertBox('','<b>[".$devices."]</b> ".trans("modem_management.not_deletable")."','info');":"delete_modem(".$item_id.");").'" class="btn btn-danger btn-sm"><i class="fa fa-trash-o fa-lg"></i></a> ';
            }
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    public function create_device_buttons($item_id, $item_type){
        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_modem_detail")){
            $return_value .= '<a href="/'.$item_type.'/detail/'.$item_id.'" title="'.trans('modem_management.detail').'" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    public function delete(Request $request){

        if(!($request->has("id") && is_numeric($request->input("id"))))
            return "ERROR";

        $result = DB::select('SELECT C.distributor_id as distributor, DS.device_name as device_name FROM clients C,modems M LEFT JOIN (SELECT D.modem_id as modem_id, GROUP_CONCAT(D.device_no ORDER BY D.device_no SEPARATOR \', \') as device_name FROM devices D WHERE status<>0 GROUP BY modem_id) DS ON DS.modem_id = M.id WHERE C.id=M.client_id AND M.id=?',array($request->input("id")));

        if(trim($result[0]->device_name) == ""){
            if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || (Auth::user()->user_type==3 && Auth::user()->org_id == $result[0]->distributor)){

                DB::table('modems')->where('id', $request->input("id"))
                    ->update(
                        [
                            'status' => 0
                        ]
                    );

                //fire event
                Helper::fire_event("delete",Auth::user(),"modems",$request->input("id"));

                session(['modem_delete_success' => true]);
                return "SUCCESS";
            }
        }
        else
            return "ERROR";
    }

    public function getClients(Request $request){
        $param_array = array();
        $where_clause = " C.status <> 0 ";

        if(Auth::user()->user_type == 4)
            abort(404);
        else if(Auth::user()->user_type == 3){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }

        $result = DB::table('clients as C')
            ->select(
                'C.id as id',
                'C.logo as logo',
                'C.name as text',
                DB::raw("(CASE WHEN C.distributor_id=0 THEN '". trans('global.main_distributor') ."' ELSE D.name END) as distributor"),
                DB::raw("JSON_UNQUOTE(json_extract(C.location, '$.verbal')) as location_verbal")
            )
            ->leftJoin('distributors as D', 'D.id', 'C.distributor_id')
            ->whereRaw($where_clause, $param_array)
            ->orderBy('C.name', 'ASC')
            ->get();

        if( isset($result[0]->id) && $result[0]->id != "" ){
            return json_encode($result);
        }
        else{
            return "NEXIST";
        }
    }

    public function getAddInfo(Request $request){
        if($request->has("client_id") && is_numeric($request->input("client_id"))){

            $client = DB::table('clients as C')
                ->where('id', $request->input('client_id'))
                ->where('status', '<>', 0)
                ->first();

            //echo $client->distributor_id; exit;

            if( COUNT($client)>0 && is_numeric($client->id) ){
                if(Auth::user()->user_type == 3 && Auth::user()->org_id != $client->distributor_id)
                    abort(404);
            }
            else{
                abort(404);
            }

            $modem_ainfo = false;

            if($request->has("op_type") && $request->input("op_type") == "edit"){
                if($request->has("modem_id") && is_numeric($request->input("modem_id"))){

                    $modem = DB::table("modems")
                        ->select(
                            "id",
                            "additional_info"
                        )
                        ->where("status", "<>", 0)
                        ->where("client_id", $client->id)
                        ->where("id", $request->input("modem_id"))
                        ->first();

                    if(!(COUNT($modem)>0 && is_numeric($modem->id))){
                        abort(404);
                    }
                    else{
                        $tmp_modem_ainfo = array();

                        if($modem->additional_info != ""){
                            $tmp_modem_ainfo = json_decode($modem->additional_info);
                        }

                        foreach ($tmp_modem_ainfo as $one_info){
                            $modem_ainfo[$one_info->id] = $one_info;
                        }
                    }
                }
            }

            $return_text = "";
            $result = DB::table("additional_infos")
                ->where("distributor_id", $client->distributor_id)
                ->where("status", "<>",0)
                ->where("parent_id", 0)
                ->get();

            if(COUNT($result)>0 && is_numeric($result[0]->id)){
                foreach($result as $one_result){
                    if($one_result->is_category){
                        $element_result =  DB::table("additional_infos")
                            ->where("distributor_id",$client->distributor_id)
                            ->where("status","<>",0)
                            ->where("parent_id",$one_result->id)
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
                            if(isset($modem_ainfo[$one_result->id])){
                                $input_value = $modem_ainfo[$one_result->id]->value;
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
                        if(isset($modem_ainfo[$one_result->id])){
                            $input_value = $modem_ainfo[$one_result->id]->value;
                        }

                        $return_text .='
                            <div class="form-group">
                                <label class="col-sm-3 control-label">'.$one_result->name.'</label>
                                <div class="col-sm-6">
                                   <input type="text" placeholder="" class="form-control" id="ainfo_'.$one_result->id.'" name="ainfo_'.$one_result->id.'" maxlength="255" value="'.$input_value.'" >
    
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

    public function getAirportsByDistance(Request $request){
        if($request->has("data_obj")){
            $data_obj = $request->input("data_obj");

            $data_obj = json_decode($data_obj,true);

            $latitude_from = $data_obj["latitude"];
            $longitude_from = $data_obj["longitude"];

            $the_airports = DB::table('airports')
                ->select(
                    'id',
                    'ICAO',
                    'location',
                    'name'
                )
                ->get();


            $return_array = array();

            foreach ($the_airports as $one_airport){
                $the_location = json_decode($one_airport->location,true);
                $dist = Helper::vincentyGreatCircleDistance((double)$latitude_from,(double)$longitude_from,
                    (double)$the_location["latitude"],
                    (double)$the_location["longitude"]);

                $return_array[] = array(
                        "text" => "(".$one_airport->ICAO.") " . $one_airport->name . " (" . number_format($dist, 2) . " km)",
                        "id" => $one_airport->id,
                        "dist" => $dist
                    );
            }


            //sort the airports by their distances to the current position
            usort($return_array,function($a,$b){

                if($a["dist"]<$b["dist"]){
                    return -1;
                }
                else{
                    return 1;
                }
            });

            return json_encode($return_array);

        }
        else{
            abort(404);
        }
    }
}
