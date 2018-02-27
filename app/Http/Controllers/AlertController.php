<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;

class AlertController extends Controller
{
    private $columns;
    private $definition_columns;

    public function __construct()
    {
        $this->columns = array(
            "icon" => array("orderable" => false, "name" => false),
            "type"=>array(),
            "device_no" => array("name" => "device_no_type"),
            "notification_method" => array("orderable" => false),
            "client" => array("name" => "client_distributor"),
            "created_at"=>array(),
            "buttons"=>array("orderable" => false, "name" => "operations", "nowrap" => true)
        );

        $this->definition_columns = array(
            "name" => array(),
            "type" => array(),
            "created_by" => array(),
            "created_at" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true)
        );
    }

    public function showTable(Request $request){
        //show all occurred alerts
        $prefix = "al";
        $url = "al_get_data";
        $default_order = '[5, "desc"]';

        if( Auth::user()->user_type == 4 ){
            $this->columns["client"]["visible"] = false;
        }
        else if( Auth::user()->user_type == 3 ){
            $this->columns["client"]["name"] = "client";
        }

        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        $data_table->set_add_right(false);

        //show alert definitions
        $prefix = "ald";
        $url = "ald_get_data";
        $default_order = '[3, "desc"]';
        $definition_table = new DataTable($prefix, $url, $this->definition_columns, $default_order, $request);
        return view('pages.alerts')->with(array("DataTableObj"=> $data_table,"DefinitionDataTableObj"=>$definition_table));
    }

    public function getData(Request $request, $type="", $id=""){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();
        $order_column = "A.created_at";
        $order_dir = "DESC";
        $where_clause = " A.status = 1 ";

        // if the request comes from one of the detail page (modem|meter|relay|analyzer|client|distributor)
        if( $type != "" && $id != "" && is_numeric($id) ){
            // Auth user has rigth to view the detail page of that device
            if( $type == "modem" ){
                if( !Helper::has_right(Auth::user()->operations, 'view_modem_detail') ){
                    abort(404);
                }

                $param_array[] = $id;
                $where_clause .= " AND M.id = ? ";
            }
            else if( $type == "meter" || $type == "relay" || $type == "analyzer" ){
                if( !Helper::has_right(Auth::user()->operations, 'view_device_detail') ){
                    abort(404);
                }

                $param_array[] = $id;
                $where_clause .= " AND D.id = ? ";
            }
            else if( $type == "client_management" ){
                if( !Helper::has_right(Auth::user()->operations, 'view_client_detail') ){
                    abort(404);
                }

                $param_array[] = $id;
                $where_clause .= " AND C.id = ? ";
            }
            else if( $type == "distributor_management" ){
                if( !Helper::has_right(Auth::user()->operations, 'view_distributor_detail') ){
                    abort(404);
                }

                $param_array[] = $id;
                $where_clause .= " AND DD.id = ? ";
            }
            else{
                abort(404);
            }
        }

        //Add filter according to user type
        if(Auth::user()->user_type == 4){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.id = ? ";
        }
        else if(Auth::user()->user_type == 3){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND DD.id = ? ";
        }

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;

            if( $order_column == "type" ){
                $order_column = "AVT.alert_type";
            }
            else if( $order_column == "device_no" ){
                $order_column = "D.device_no";
            }
            else if( $order_column == "client" ){
                $order_column = "C.name";
            }
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(A.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "D.device_no LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR DT.type LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR M.serial_no LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR DD.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR C.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR AVT.alert_type LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR VDT.device_type LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        // $total_count = DB::select('SELECT count(*) as total_count FROM alerts A LEFT JOIN distributors D ON A.distributor_id = D.id LEFT JOIN clients C ON A.client_id = C.id '.$where_clause, $param_array);

        $total_count = DB::table('alerts as A')
            ->leftJoin('devices as D', 'D.id', 'A.device_id')
            ->leftJoin('device_type as DT', 'D.device_type_id', 'DT.type')
            ->leftJoin('modems as M', 'D.modem_id', 'M.id')
            ->leftJoin('clients as C', 'M.client_id', 'C.id')
            ->leftJoin('distributors as DD', 'C.distributor_id', 'DD.id')
            ->leftJoin(DB::raw( Helper::alert_type_virtual_table().' as AVT'), 'AVT.type', 'A.type' )
            ->leftJoin(DB::raw( Helper::device_type_virtual_table().' as VDT'), 'VDT.type', 'DT.type' )
            ->whereRaw($where_clause, $param_array)
            ->count();

        //$total_count = $total_count[0];
        //$total_count = $total_count->total_count;

        //$param_array[] = $length;
        //$param_array[] = $start;
        // $result = DB::select('SELECT A.*, D.name as distributor_name, D.id as distributor_id, C.name as client_name, C.id as client_id FROM alerts A LEFT JOIN distributors D ON A.distributor_id=D.id LEFT JOIN clients C ON A.client_id=C.id '.$where_clause.' ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?',$param_array);

        $result = DB::table('alerts as A')
            ->select('A.*', 'D.id as device_id','D.device_no as device_name','M.id as modem_id','M.serial_no as modem_name','AD.name as definition_name','AD.policy as policy', 'DD.name as distributor_name', 'DD.id as distributor_id', 'C.name as client_name', 'C.id as client_id', 'DT.type as device_type')
            ->leftJoin('devices as D', 'D.id', 'A.device_id')
            ->leftJoin('device_type as DT', 'D.device_type_id', 'DT.id')
            ->leftJoin('modems as M', 'D.modem_id', 'M.id')
            ->leftJoin('clients as C', 'M.client_id', 'C.id')
            ->leftJoin('distributors as DD', 'C.distributor_id', 'DD.id')
            ->leftJoin('alert_definitions as AD', 'AD.id', DB::raw('JSON_EXTRACT(A.detail,"$.definition_id")'))
            ->leftJoin(DB::raw( Helper::alert_type_virtual_table().' as AVT'), 'AVT.type', 'A.type' )
            ->leftJoin(DB::raw( Helper::device_type_virtual_table().' as VDT'), 'VDT.type', 'DT.type' )
            ->where('D.status', '<>', 0)
            ->where('M.status', '<>', 0)
            ->whereRaw($where_clause, $param_array)
            ->orderBy($order_column, $order_dir)
            ->limit($length)
            ->offset($start)
            ->get();

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                // prepare client info column
                if( Auth::user()->user_type == 4 ){
                    $client_link = "";
                }
                else if( Auth::user()->user_type == 3 ){
                    $client_link = "<a href='/client_management/detail/" . $one_row->client_id . "' target='_blank' title='" . trans('devices.go_client_detail') . "'>" . $one_row->client_name . "</a>";
                }
                else{
                    $client_link = "<a href='/client_management/detail/" . $one_row->client_id . "' target='_blank' title='" . trans('devices.go_client_detail') . "'>" . $one_row->client_name . "</a> / " . ($one_row->distributor_name == "" ? trans("global.main_distributor") : "<a href='/distributor_management/detail/" . $one_row->distributor_id . "' target='_blank' title='" . trans('devices.go_distributor_detail') . "'>" . $one_row->distributor_name . "</a>");
                }

                // Set icon according to alarm type
                if( $one_row->type == "reactive" ){
                    $icon = "<i class='fa fa-bolt fa-2x' style='color: #ff0000;'></i>";
                }
                else if( $one_row->type == "connection" ){
                    $icon = "<i class='fa fa-chain-broken fa-2x' style='color: #000099;'></i>";
                }
                else if( $one_row->type == "voltage" ){
                    $icon = "<i class='fa fa-exclamation-triangle fa-2x' style='color: #ff9900;'></i>";
                }
                else if( $one_row->type == "current" ){
                    $icon = "<i class='fa fa-random fa-2x' style='color: #666633;'></i>";
                }

                // prepare notification_method column
                $nm_icon = "";
                $nm_icon_array = array();
                $notifications = json_decode($one_row->detail);
                $notifications = $notifications->action;
                foreach ($notifications as $one_notification){

                    if( $one_notification == "email" ){
                        if( !in_array("email", $nm_icon_array) ){
                            $nm_icon_array[] = "email";
                            $nm_icon .= "<i title='".trans('alerts.email')."' class='fa fa-envelope-o fa-lg' style='color: #267326;margin-right: 5px;cursor:pointer;'></i> ";
                        }
                    }
                    else if( $one_notification == "sms" ){
                        if( !in_array("sms", $nm_icon_array) ){
                            $nm_icon_array[] = "sms";
                            $nm_icon .= "<i title='".trans('alerts.sms')."' class='fa fa-phone fa-lg' style='color: #267326;margin-right: 5px;cursor:pointer;'></i> ";
                        }
                    }
                    else if( $one_notification == "notification" ){
                        if( !in_array("notification", $nm_icon_array) ){
                            $nm_icon_array[] = "notification";
                            $nm_icon .= "<i title='".trans('alerts.notification')."' class='fa fa-bell-o fa-lg' style='color: #267326;margin-right: 5px;cursor:pointer;'></i> ";
                        }
                    }
                }

                $the_device_no = "<a href='/".$one_row->device_type."/detail/" . $one_row->device_id . "' target='_blank' title='" . trans('alerts.go_device_detail') . "'>" . $one_row->device_name . " (".trans('global.'.$one_row->device_type).")" . "</a>";

                if($one_row->sub_type == "modem"){
                    $the_device_no = "<a href='/modem_management/detail/" . $one_row->modem_id . "' target='_blank' title='" . trans('alerts.go_device_detail') . "'>" . $one_row->modem_name . " (".trans('global.modem').")" . "</a>";
                }

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "icon" => $icon,
                    "type" => trans('alerts.'.$one_row->type),
                    "device_no" => $the_device_no,
                    "notification_method" => $nm_icon,
                    "client" => $client_link,
                    "created_at" => date('d/m/Y H:i:s',strtotime($one_row->created_at)),
                    "buttons" => self::prepare_buttons($one_row->id),
                    "detail" => self::detail_html($one_row->type, $one_row->sub_type, $one_row->detail, $one_row->device_type,$one_row->definition_name, $one_row->policy),
                    "status" => $one_row->status

                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function prepare_buttons($alert_id)
    {
        $return_value = "";

        // show alert detail button
        $return_value .= '
            <a href="javascript:void(0);" title="'.trans('alerts.detail').'" class="btn btn-info btn-sm detail_button">
                <i class="fa fa-info-circle fa-lg"></i>
            </a>
        ';

        if( Helper::has_right(Auth::user()->operations, "change_alert_status") ){
            $return_value .= '
                <a href="javascript:void(0);" onclick="delete_alert('.$alert_id.')" title="'.trans('alerts.delete').'" class="btn btn-warning btn-sm">
                    <i class="fa fa-trash-o fa-lg"></i>
                </a>
            ';

        }

        return $return_value;
    }

    public function detail_html($type, $sub_type, $detail, $device_type, $definition_name, $policy){
        $return_text = "";
        $detail = json_decode($detail,true);
        $table_text = "";
        $policy = json_decode($policy);
        $sms_limit = 0;
        $email_limit = 0;
        $notification_limit = 0;
        $has_reactive = false;
        $has_voltage = false;
        $has_current = false;

        if($type == "reactive"){
            $return_text .= "<h4 style='color:darkred;'>".trans("alerts.detail_reactive_exp", array("sub_type"=>trans("alerts.".$sub_type)))."</h4>";

            $table_text .="<table>";
            $table_text .="<thead>
                                <tr>
                                    <th></th>
                                    <th style='text-align: center;'>".trans('alerts.'.$sub_type.'_ratio')."</th>
                                    <th style='text-align: center;'>".trans("alerts.limit")."</th>
                                    <th style='text-align: center;'>".trans("alerts.data_interval")."</th>
                                </tr>
                           </thead><tbody>";

            if( isset($detail["notification"]) ){
                $has_reactive = true;
                $table_text .="<tr>
                                   <td style='text-align: center;'>".trans("alerts.notification")."</td>
                                   <td style='text-align: center;color:red;font-weight: bold;'>% ".number_format($detail["notification"]["ratio"],2)."</td>
                                   <td style='text-align: center;'>% ".number_format($detail["notification"]["limit"],2)."</td>
                                   <td style='text-align: center;'>".date('d/m/Y H:i', strtotime($detail["notification"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["notification"]['end_date']))."</td>
                               </tr>";
            }

            if(isset($detail["email"])) {
                $has_reactive = true;
                $table_text .="<tr>
                                   <td style='text-align: center;'>".trans("alerts.email")."</td>
                                   <td style='text-align: center;color:red;font-weight: bold;'>% ".number_format($detail["email"]["ratio"],2)."</td>
                                   <td style='text-align: center;'>% ".number_format($detail["email"]["limit"],2)."</td>
                                   <td style='text-align: center;'>".date('d/m/Y H:i', strtotime($detail["email"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["email"]['end_date']))."</td>
                               </tr>";

            }

            if(isset($detail["sms"])) {
                $has_reactive = true;
                $table_text .="<tr>
                                   <td style='text-align: center;'>".trans("alerts.sms")."</td>
                                   <td style='text-align: center;color:red;font-weight: bold;'>% ".number_format($detail["sms"]["ratio"],2)."</td>
                                   <td style='text-align: center;'>% ".number_format($detail["sms"]["limit"],2)."</td>
                                   <td style='text-align: center;'>".date('d/m/Y H:i', strtotime($detail["sms"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["sms"]['end_date']))."</td>
                               </tr>";
            }

            $table_text .="</tbody></table>";

            if($has_reactive)
                $return_text .=$table_text;

        }
        else if($type == "connection"){
            if($sub_type == "modem"){
                $return_text .= "<h4 style='color:darkred;'>".trans("alerts.detail_connection_modem")."</h4>";
            }
            else if($sub_type == "device"){
                $return_text .= "<h4 style='color:darkred;'>".trans("alerts.detail_connection_device",array("device_type"=>trans("global.".$device_type)))."</h4>";
            }

            $return_text .= "<b>".trans("alerts.last_connection_at").": </b>".($detail["last_connection_date"]?date('d/m/Y H:i:s',strtotime($detail["last_connection_date"])):trans("alerts.no_connection_yet"));
        }
        else if($type == "voltage"){
            if($sub_type == "lower"){
                $return_text .= "<h4 style='color:darkred;'>".trans("alerts.detail_voltage_lower")."</h4>";

                if(isset($policy->sms->voltage_lower_limit))
                    $sms_limit = $policy->sms->voltage_lower_limit;
                else
                    $sms_limit = "N/A";
                $email_limit = $policy->email->voltage_lower_limit;
                $notification_limit = $policy->notification->voltage_lower_limit;
            }
            else if($sub_type == "upper"){
                $return_text .= "<h4 style='color:darkred;'>".trans("alerts.detail_voltage_upper")."</h4>";
                $sms_limit = $policy->sms->voltage_upper_limit;
                $email_limit = $policy->email->voltage_upper_limit;
                $notification_limit = $policy->notification->voltage_upper_limit;
            }

            $table_text .="<table>";
            $table_text .="<thead>
                                <tr>
                                    <th></th>
                                    <th style='text-align: center;'>L1</th>
                                    <th style='text-align: center;'>L2</th>
                                    <th style='text-align: center;'>L3</th>
                                    <th style='text-align: center;'>".trans("alerts.limit")."</th>
                                    <th style='text-align: center;'>".trans("alerts.data_interval")."</th>
                                </tr>
                           </thead><tbody>";

            if(isset($detail["notification"])){
                $has_voltage = true;
                $table_text .="<tr>
                               <td>".trans("alerts.notification")."</td>
                               <td>".(isset($detail["notification"]["l1"])?$detail["notification"]["l1"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["notification"]["l2"])?$detail["notification"]["l2"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["notification"]["l3"])?$detail["notification"]["l3"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".$notification_limit."</td>
                               <td>".date('d/m/Y H:i', strtotime($detail["notification"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["notification"]['end_date']))."</td>
                               </tr>";
            }

            if(isset($detail["email"])){
                $has_voltage = true;
                $table_text .="<tr>
                               <td>".trans("alerts.email")."</td>
                               <td>".(isset($detail["email"]["l1"])?$detail["email"]["l1"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["email"]["l2"])?$detail["email"]["l2"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["email"]["l3"])?$detail["email"]["l3"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".$email_limit."</td>
                               <td>".date('d/m/Y H:i', strtotime($detail["email"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["email"]['end_date']))."</td>
                               </tr>";
            }

            if(isset($detail["sms"])){
                $has_voltage = true;
                $table_text .="<tr>
                               <td>".trans("alerts.sms")."</td>
                               <td>".(isset($detail["sms"]["l1"])?$detail["sms"]["l1"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["sms"]["l2"])?$detail["sms"]["l2"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["sms"]["l3"])?$detail["sms"]["l3"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".$sms_limit."</td>
                               <td>".date('d/m/Y H:i', strtotime($detail["sms"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["sms"]['end_date']))."</td>
                               </tr>";
            }

            $table_text .="</tbody></table>";

            if($has_voltage)
                $return_text .=$table_text;
        }
        else if($type == "current"){
            if($sub_type == "5A"){
                $return_text .= "<h4 style='color:darkred;'>".trans("alerts.detail_current_5A")."</h4>";
            }
            else if($sub_type == "unbalanced"){
                $return_text .= "<h4 style='color:darkred;'>".trans("alerts.detail_current_unbalanced")."</h4>";
            }

            $table_text .="<table>";
            $table_text .="<thead><tr>
                                <th></th>
                                <th style='text-align: center;'>L1</th>
                                <th style='text-align: center;'>L2</th>
                                <th style='text-align: center;'>L3</th>
                                <th style='text-align: center;'>".trans('alerts.data_interval')."</th>
                            </tr></thead><tbody>";
            if(isset($detail["notification"])){
                $has_current = true;
                $table_text .="<tr>
                               <td>".trans("alerts.notification")."</td>
                               <td>".(isset($detail["notification"]["l1"])?$detail["notification"]["l1"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["notification"]["l2"])?$detail["notification"]["l2"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["notification"]["l3"])?$detail["notification"]["l3"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".date('d/m/Y H:i', strtotime($detail["notification"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["notification"]['end_date']))."</td>
                               </tr>";
            }

            if(isset($detail["email"])){
                $has_current = true;
                $table_text .="<tr>
                               <td>".trans("alerts.email")."</td>
                               <td>".(isset($detail["email"]["l1"])?$detail["email"]["l1"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["email"]["l2"])?$detail["email"]["l2"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["email"]["l3"])?$detail["email"]["l3"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".date('d/m/Y H:i', strtotime($detail["email"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["email"]['end_date']))."</td>
                               </tr>";
            }

            if(isset($detail["sms"])){
                $has_current = true;
                $table_text .="<tr>
                               <td>".trans("alerts.sms")."</td>
                               <td>".(isset($detail["sms"]["l1"])?$detail["sms"]["l1"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["sms"]["l2"])?$detail["sms"]["l2"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".(isset($detail["sms"]["l3"])?$detail["sms"]["l3"]:"<i class='fa fa-check'></i>")."</td>
                               <td>".date('d/m/Y H:i', strtotime($detail["sms"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["sms"]['end_date']))."</td>
                               </tr>";
            }

            $table_text .="</tbody></table>";

            if($has_current)
                $return_text .=$table_text;
        }

        $return_text .= "<br/><b>".trans("alerts.alert_definition").":</b> ".$definition_name;

        /* if( $type == "reactive" ){
            if(isset($detail["notification"]))
                $return_text .= "<br/><b>".trans("alerts.notification")." ".trans("alerts.data_interval").":</b> " .
                    date('d/m/Y H:i',
                        strtotime
                    ($detail["notification"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["notification"]['end_date']));

            if(isset($detail["email"]))
                $return_text .= "<br/><b>".trans("alerts.email")." ".trans("alerts.data_interval").":</b> " .
                    date('d/m/Y H:i',
                        strtotime
                        ($detail["email"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["email"]['end_date']));

            if(isset($detail["sms"]))
                $return_text .= "<br/><b>".trans("alerts.sms")." ".trans("alerts.data_interval").":</b> " .
                    date('d/m/Y H:i',
                        strtotime
                        ($detail["sms"]['start_date'])) . " - " . date('d/m/Y H:i', strtotime($detail["sms"]['end_date']));


        } */

        return $return_text;
    }

    public function getDefinitionData(){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();
        $order_column = "A.created_at";
        $order_dir = "DESC";
        $where_clause = " A.status = 1 ";

        //Add filter according to user type
        if(Auth::user()->user_type == 3){ // @TODO: DB yapısına göre düzenlenecek!!!
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND (A.org_id = ? OR A.org_id=0)";
        }

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->definition_columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;

            if( $order_column == "type" ){
                $order_column = "AVT.alert_type";
            }
            else if( $order_column == "created_by" ){
                $order_column = "U.name";
            }
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(A.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "A.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR U.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR AVT.alert_type LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::table('alert_definitions as A')
            ->select('A.*', 'U.name as creator')
            ->leftJoin('users as U', 'U.id', 'A.created_by')
            ->leftJoin(DB::raw( Helper::alert_type_virtual_table().' as AVT'), 'AVT.type', 'A.type' )
            ->whereRaw($where_clause, $param_array)
            ->count();

        $result = DB::table('alert_definitions as A')
            ->select('A.*', 'U.name as creator')
            ->leftJoin('users as U', 'U.id', 'A.created_by')
            ->leftJoin(DB::raw( Helper::alert_type_virtual_table().' as AVT'), 'AVT.type', 'A.type' )
            ->whereRaw($where_clause, $param_array)
            ->orderBy($order_column, $order_dir)
            ->limit($length)
            ->offset($start)
            ->get();

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "name" => $one_row->name,
                    "type" => trans('alerts.'.$one_row->type),
                    "created_by" => $one_row->creator,
                    "created_at" => date('d/m/Y H:i:s',strtotime($one_row->created_at)),
                    "buttons" => self::prepare_definition_buttons($one_row->id, $one_row->org_id)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function prepare_definition_buttons( $item_id, $org_id )
    {
        $return_value = "";

        if( Helper::has_right(Auth::user()->operations, "add_new_alert_definition") ){
            if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) || (Auth::user()->user_type == 3 && Auth::user()->org_id == $org_id) ){
                $return_value .= '<a href="javascript:void(1);" title="'.trans('alerts.edit').'" onclick="edit_alert_definition('.$item_id.');" class="btn btn-warning btn-sm"><i class="fa fa-edit fa-lg"></i></a> ';
            }
        }


        //check if this fee is used already
        $devices = DB::select("SELECT GROUP_CONCAT(D.device_no SEPARATOR ', ') as used_devices FROM devices D, alert_definitions AD WHERE D.status<>0 AND FIND_IN_SET(AD.id, D.alert_definitions)>0 AND AD.id=?",[$item_id]);

        $devices = $devices[0]->used_devices;

        if( Helper::has_right(Auth::user()->operations, "delete_alert_definition") ){
            if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) || (Auth::user()->user_type == 3 && Auth::user()->org_id == $org_id) ){
                $return_value .= '<a '.($devices != ""?"style=\"opacity:0.4;\"":"").' href="javascript:void(1);" title="'.trans('alerts.delete').'" onclick="'.($devices!=""?"alertBox('','".trans("alerts.not_deletable",["devices"=>$devices])."','info');":"delete_alert_definition(".$item_id.");").'" class="btn btn-danger btn-sm"><i class="fa fa-trash-o fa-lg"></i></a> ';
            }
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    public function create(Request $request){
        if( !$request->has('new_definition_type') ){
            abort(404);
        }

        if( !$request->has('new_definition_name') ){
            abort(404);
        }

        $type = $request->input('new_definition_type');
        $name = $request->input('new_definition_name');
        $policy_array = array();
        $op_type = "new";
        $created_by = Auth::user()->id;
        $org_id = 0;
        $edit_info = false;
        $name_validator = 'bail|required|min:3|max:255|unique:alert_definitions,name';


        if( $request->has('definition_op_type') && trim($request->input('definition_op_type')) == "edit" ) {
            $op_type = "edit";

            // Auth user has right to edit
            $edit_info = DB::table('alert_definitions')->where('id',$request->input('definition_edit_id'))->first();
            if( isset($edit_info->id)){
                if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){ }
                else if( Auth::user()->user_type == 3 ){
                    if( Auth::user()->org_id != $edit_info->org_id ){
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

            $name_validator = 'bail|required|min:3|max:255|unique:alert_definitions,name,'.$edit_info->id.',id';
        }

        if( Auth::user()->user_type == 3 ){
            $org_id = Auth::user()->org_id;
        }

        $validation_array = array(
            "new_definition_name" => $name_validator
        );

        $n_checked = false;
        $e_checked = false;
        $s_checked = false;

        if( $request->has('hdn_notification_checkbox') && $request->input('hdn_notification_checkbox') == 1 ){
            $n_checked = true;

            //$validation_array['n_notification_period'] = 'bail|required|digits_between:1,2';
            $policy_array["notification"]["notification_period"]["type"] = $request->input('n_notification_period');
            $policy_array["notification"]["notification_period"]["period"] = $request->input('n_notification_period_'.$request->input('n_notification_period'));

        }

        if( $request->has('hdn_email_checkbox') && $request->input('hdn_email_checkbox') == 1 ){
            $e_checked = true;

            //$validation_array['e_notification_period'] = 'bail|required|digits_between:1,2';
            $policy_array["email"]["notification_period"]["type"] = $request->input('e_notification_period');
            $policy_array["email"]["notification_period"]["period"] = $request->input('e_notification_period_'.$request->input('e_notification_period'));
        }

        if( $request->has('hdn_sms_checkbox') && $request->input('hdn_sms_checkbox') == 1 ){
            $s_checked = true;

            //$validation_array['s_notification_period'] = 'bail|required|digits_between:1,2';
            $policy_array["sms"]["notification_period"]["type"] = $request->input('s_notification_period');
            $policy_array["sms"]["notification_period"]["period"] = $request->input('s_notification_period_'.$request->input('s_notification_period'));
        }

        if( $n_checked == false && $e_checked == false && $s_checked == false  ){
            abort(404);
        }


        if( $type == "reactive" ){
            if( $n_checked ){
                $validation_array["n_inductive_limit"] = 'bail|required|digits_between:1,3';
                $validation_array["n_capacitive_limit"] = 'bail|required|digits_between:1,3';
                $validation_array["n_consumption_limit"] = 'bail|required|digits_between:1,10';
                $validation_array["n_calculation_period"] = 'bail|required|digits_between:1,2';

                $policy_array["notification"] += array(
                    "inductive_limit" => $request->input('n_inductive_limit'),
                    "capacitive_limit" => $request->input('n_capacitive_limit'),
                    "consumption_limit" => $request->input('n_consumption_limit'),
                    "calculation_period" => $request->input('n_calculation_period')
                );
            }

            if( $e_checked ){
                $validation_array["e_inductive_limit"] = 'bail|required|digits_between:1,3';
                $validation_array["e_capacitive_limit"] = 'bail|required|digits_between:1,3';
                $validation_array["e_consumption_limit"] = 'bail|required|digits_between:1,10';
                $validation_array["e_calculation_period"] = 'bail|required|digits_between:1,2';

                $policy_array["email"] += array(
                    "inductive_limit" => $request->input('e_inductive_limit'),
                    "capacitive_limit" => $request->input('e_capacitive_limit'),
                    "consumption_limit" => $request->input('e_consumption_limit'),
                    "calculation_period" => $request->input('e_calculation_period')
                );
            }

            if( $s_checked ){
                $validation_array["s_inductive_limit"] = 'bail|required|digits_between:1,3';
                $validation_array["s_capacitive_limit"] = 'bail|required|digits_between:1,3';
                $validation_array["s_consumption_limit"] = 'bail|required|digits_between:1,10';
                $validation_array["s_calculation_period"] = 'bail|required|digits_between:1,2';

                $policy_array["sms"] += array(
                    "inductive_limit" => $request->input('s_inductive_limit'),
                    "capacitive_limit" => $request->input('s_capacitive_limit'),
                    "consumption_limit" => $request->input('s_consumption_limit'),
                    "calculation_period" => $request->input('s_calculation_period')
                );
            }
        }
        else if( $type == "current" ){
            if( $n_checked ){
                $unbalanced_current_status = 0;
                $higher_than_5A_status = 0;

                if( $request->has('n_unbalanced_current_status') && $request->input('n_unbalanced_current_status') == "on"  ){
                    $unbalanced_current_status = 1;
                }

                if( $request->has('n_5A_current_status') && $request->input('n_5A_current_status') == "on"  ){
                    $higher_than_5A_status = 1;
                }

                $policy_array["notification"] += array(
                    "unbalanced_current_status" => $unbalanced_current_status,
                    "5A_current_status" => $higher_than_5A_status
                );
            }

            if( $e_checked ){
                $unbalanced_current_status = 0;
                $higher_than_5A_status = 0;

                if( $request->has('e_unbalanced_current_status') && $request->input('e_unbalanced_current_status') == "on"  ){
                    $unbalanced_current_status = 1;
                }

                if( $request->has('e_5A_current_status') && $request->input('e_5A_current_status') == "on"  ){
                    $higher_than_5A_status = 1;
                }

                $policy_array["email"] += array(
                    "unbalanced_current_status" => $unbalanced_current_status,
                    "5A_current_status" => $higher_than_5A_status
                );
            }

            if( $s_checked ){
                $unbalanced_current_status = 0;
                $higher_than_5A_status = 0;

                if( $request->has('s_unbalanced_current_status') && $request->input('s_unbalanced_current_status') == "on"  ){
                    $unbalanced_current_status = 1;
                }

                if( $request->has('s_5A_current_status') && $request->input('s_5A_current_status') == "on"  ){
                    $higher_than_5A_status = 1;
                }

                $policy_array["sms"] += array(
                    "unbalanced_current_status" => $unbalanced_current_status,
                    "5A_current_status" => $higher_than_5A_status
                );
            }
        }
        else if( $type == "voltage" ){
            if( $n_checked ){
                $validation_array["n_voltage_lower_limit"] = 'bail|required|digits_between:1,5';
                $validation_array["n_voltage_upper_limit"] = 'bail|required|digits_between:1,5';

                $policy_array["notification"] += array(
                    "voltage_lower_limit" => $request->input('n_voltage_lower_limit'),
                    "voltage_upper_limit" => $request->input('n_voltage_upper_limit')
                );
            }

            if( $e_checked ){
                $validation_array["e_voltage_lower_limit"] = 'bail|required|digits_between:1,5';
                $validation_array["e_voltage_upper_limit"] = 'bail|required|digits_between:1,5';

                $policy_array["email"] += array(
                    "voltage_lower_limit" => $request->input('e_voltage_lower_limit'),
                    "voltage_upper_limit" => $request->input('e_voltage_upper_limit')
                );
            }

            if( $s_checked ){
                $validation_array["s_voltage_lower_limit"] = 'bail|required|digits_between:1,5';
                $validation_array["s_voltage_upper_limit"] = 'bail|required|digits_between:1,5';

                $policy_array["sms"] += array(
                    "voltage_lower_limit" => $request->input('s_voltage_lower_limit'),
                    "voltage_upper_limit" => $request->input('s_voltage_upper_limit')
                );
            }
        }
        else if( $type == "connection" ){
            if( $n_checked ){
                $device_connection_status = 0;
                $modem_connection_status = 0;

                if( $request->has('n_device_connection') && $request->input('n_device_connection') == "on"  ){
                    $device_connection_status = 1;
                }

                if( $request->has('n_modem_connection') && $request->input('n_modem_connection') == "on"  ){
                    $modem_connection_status = 1;
                }

                $validation_array["n_duration"] = 'bail|required|digits_between:1,2';

                $policy_array["notification"] += array(
                    "device_connection" => $device_connection_status,
                    "modem_connection" => $modem_connection_status,
                    "duration" => $request->input('n_duration')
                );
            }

            if( $e_checked ){
                $device_connection_status = 0;
                $modem_connection_status = 0;

                if( $request->has('e_device_connection') && $request->input('e_device_connection') == "on"  ){
                    $device_connection_status = 1;
                }

                if( $request->has('e_modem_connection') && $request->input('e_modem_connection') == "on"  ){
                    $modem_connection_status = 1;
                }

                $validation_array["e_duration"] = 'bail|required|digits_between:1,2';

                $policy_array["email"] += array(
                    "device_connection" => $device_connection_status,
                    "modem_connection" => $modem_connection_status,
                    "duration" => $request->input('e_duration')
                );
            }

            if( $s_checked ){
                $device_connection_status = 0;
                $modem_connection_status = 0;

                if( $request->has('s_device_connection') && $request->input('s_device_connection') == "on"  ){
                    $device_connection_status = 1;
                }

                if( $request->has('s_modem_connection') && $request->input('s_modem_connection') == "on"  ){
                    $modem_connection_status = 1;
                }

                $validation_array["s_duration"] = 'bail|required|digits_between:1,2';

                $policy_array["sms"] += array(
                    "device_connection" => $device_connection_status,
                    "modem_connection" => $modem_connection_status,
                    "duration" => $request->input('s_duration')
                );
            }


        }
        else{
            abort(404);
        }

        $this->validate($request, $validation_array);


        //save the data to DB
        if( $op_type == "new" ){ // insert new modem
            $last_insert_id = DB::table('alert_definitions')->insertGetId(
                [
                    'name' => $name,
                    'type' => $type,
                    'policy' => json_encode($policy_array),
                    'org_id' => $org_id,
                    'created_by' => $created_by
                ]
            );

            //fire event
            Helper::fire_event("create", Auth::user(), "alert_definitions", $last_insert_id);

            //return insert operation result via global session object
            session(['new_alert_definition_insert_success' => true]);
        }
        else if( $op_type == "edit" ){ // update user's info

            DB::table('alert_definitions')->where('id', $request->input("definition_edit_id"))
                ->update(
                    [
                        'name' => $name,
                        'type' => $type,
                        'policy' => json_encode($policy_array)
                    ]
                );

            //fire event
            Helper::fire_event("update",Auth::user(),"alert_definitions",$request->input("definition_edit_id"));

            //return update operation result via global session object
            session(['alert_definition_update_success' => true]);
        }

        return redirect()->back();
    }

    public function delete(Request $request){
        if(!($request->has("id") && is_numeric($request->input("id")))) {
            abort(404);
        }

        if( $request->has('type') && $request->input('type') == "one_alert" ){


            $the_alert = DB::table('alerts as A')
                ->select('A.id as id', 'C.id as client_id','DD.id as distributor_id')
                ->leftJoin('devices as D', 'D.id', 'A.device_id')
                ->leftJoin('modems as M', 'D.modem_id', 'M.id')
                ->leftJoin('clients as C', 'M.client_id', 'C.id')
                ->leftJoin('distributors as DD', 'C.distributor_id', 'DD.id')
                ->where('A.id', $request->input('id'))
                ->first();

            if($the_alert && COUNT($the_alert)>0 && isset($the_alert->id)){

                if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) || (Auth::user()->user_type == 3 && Auth::user()->org_id == $the_alert->distributor_id) ){
                    DB::table('alerts')
                        ->where('id', $request->input('id'))
                        ->update(
                            [
                                'status' => 0
                            ]
                        );

                    return "SUCCESS";
                }
            }
        }
        else{
            $definition_info = DB::table("alert_definitions")
                ->where('id', $request->input("id"))
                ->first();

            if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) || (Auth::user()->user_type == 3 && Auth::user()->org_id == $definition_info->org_id) ){
                $devices = DB::select("SELECT id FROM devices WHERE status<>0 AND FIND_IN_SET(?, alert_definitions)>0",[$definition_info->id]);

                if(count($devices)>0 && isset($devices[0]->id)){
                    return "ERROR";
                }
                else{
                    DB::table('alert_definitions')->where('id', $definition_info->id)
                        ->update(
                            [
                                'status' => 0
                            ]
                        );

                    //fire event
                    Helper::fire_event("delete", Auth::user(), "alert_definitions", $request->input("id"));

                    session(['alert_definition_delete_success' => true]);

                    return "SUCCESS";
                }
            }
            else{
                return "ERROR";
            }
        }
    }

    public function getDefinitionInfo(Request $request){
        if($request->has("id") && is_numeric($request->input("id"))){

            // Auth user has right to edit
            $result = DB::table('alert_definitions')
                ->where('id', $request->input('id'))
                ->where('status', '<>', 0)
                ->first();

            if( isset($result->id)){
                if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){ }
                else if( Auth::user()->user_type == 3 ){
                    if( Auth::user()->org_id != $result->org_id ){
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

            echo json_encode($result);
        }
        else{
            echo "NEXIST";
        }
    }

    public function updateUserRead(Request $request){

        if(!($request->has("id") && is_numeric($request->input("id")))) {
            abort(404);
        }


        DB::table('users')
            ->where('id', Auth::user()->id)
            ->update(
                [
                        'last_read_alert' => $request->input("id")
                ]
            );

        return "SUCCESS";

    }
}
