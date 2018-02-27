<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\Helper;
use App\Helpers\DataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventlogsController extends Controller
{

    private $columns;

    public function __construct()
    {
        $this->columns = array(
            "icon"=>array("name"=>false,"orderable"=>false),
            "event_type"=>array("orderable"=>false),
            "actor"=>array("orderable"=>false),
            "affected"=>array("orderable"=>false),
            "date"=>array("nowrap"=>true)
        );
    }

    public function prepareEventTableObject($request,$type,$id){
        $prefix = "el";
        $url = "el_get_data/".$type."/".$id;
        $default_order = '[4,"desc"]';

        $data_table = new DataTable($prefix,$url,$this->columns,$default_order,$request);
        $data_table->set_date_range(date('d/m/Y',strtotime("-1 week")),date('d/m/Y'));
        $data_table->set_init_fnct('
            //change placeholder of search filter of datatable
            $("#div_'.$prefix.'_search_custom").find("input").attr("placeholder","'.trans("global.search_user").'");
            $("#el_table").find("th").first().next().remove();
            $("#el_table").find("th").first().attr("colspan","2");
            
            $("#el_table").find("th").first().html("\
                <div class=\"row\" style=\"margin-top: -5px;\"> \
                    <div class=\"col-lg-6\" style=\"padding:0 8px 10px;\"> \
                        <select id=\"el_select_table\" style=\"width:100%;\"> \
                            <option value=\"all\">'.trans("event_logs.all_events").'</option> \
                            '.(Auth::user()->user_type != 4?' \
                                <option value=\"modems\">'.trans("event_logs.modem_events").'</option> \
                                <option value=\"meters\">'.trans("event_logs.meter_events").'</option> \
                                <option value=\"relays\">'.trans("event_logs.relay_events").'</option> \
                                <option value=\"analyzers\">'.trans("event_logs.analyzer_events").'</option> \
                            ':"").' \
                            <option value=\"users\">'.trans("event_logs.user_events").'</option> \
                            <option value=\"clients\">'.trans("event_logs.client_events").'</option> \
                            '.((Auth::user()->user_type != 4)?' \
                                <option value=\"distributors\">'.trans("event_logs.distributor_events").'</option> \
                                <option value=\"fee_scales\">'.trans("event_logs.fee_scale_events").'</option> \
                                <option value=\"alert_definitions\">'.trans("event_logs.alert_definition_events").'</option> \
                                <option value=\"reports\">'.trans("event_logs.report_events").'</option> \
                                <option value=\"organization_schema\">'.trans("event_logs.org_schema_events").'</option> \
                                <option value=\"additional_infos\">'.trans("event_logs.ainfo_events").'</option> \
                            ':"").' \
                        </select> \
                    </div> \
                    <div class=\"col-lg-6\" style=\"padding:0 8px 10px;\"> \
                        <select id=\"el_select_type\" style=\"width:100%;\"> \
                            '.((Auth::user()->user_type != 4)?' \
                                <option value=\"all\">'.trans("event_logs.all_records").'</option> \
                                <option value=\"create\">'.trans("event_logs.add_new").'</option> \
                                <option value=\"delete\">'.trans("event_logs.delete_device").'</option> \
                                <option value=\"update\">'.trans("event_logs.update_device").'</option> \
                            ':' \
                                <option value=\"all\">'.trans("event_logs.all_records").'</option> \
                                <option value=\"sessions\">'.trans("event_logs.session_events").'</option> \
                                <option value=\"update\">'.trans("event_logs.update_device").'</option> \ ').' \
                        </select> \
                    </div> \
                </div> \
            ");
            
            $("#el_select_table").select2({
                minimumResultsForSearch: Infinity
            });
            
            $("#el_select_type").select2({
                minimumResultsForSearch: Infinity
            });
            
            $("#el_select_table").change(function(){
                the_val = $(this).val();        
                $("#el_select_type").empty();
        
               if(the_val == "modems"){
                      $("#el_select_type").append($(\'<option>\', {value: \'all\', text: \''.trans('event_logs.all_records').'\'}));
                      $("#el_select_type").append($(\'<option>\', {value: \'create\', text: \''.trans('event_logs.new_modem').'\'}));
                      $("#el_select_type").append($(\'<option>\', {value: \'delete\', text: \''.trans('event_logs.delete_device').'\'}));
                      $("#el_select_type").append($(\'<option>\', {value: \'update\', text: \''.trans('event_logs.update_device').'\'}));
                }
                else if(the_val == "meters"){
                    $("#el_select_type").append($(\'<option>\', {value: \'all\', text: \''.trans('event_logs.all_records').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'create\', text: \''.trans('event_logs.new_meter').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'delete\', text: \''.trans('event_logs.delete_device').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'update\', text: \''.trans('event_logs.update_device').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'corrupted_data\', text: \''.trans('event_logs.incorrect_data').'\'}));
                }
                else if(the_val == "relays"){
                    $("#el_select_type").append($(\'<option>\', {value: \'all\', text: \''.trans('event_logs.all_records').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'create\', text: \''.trans('event_logs.new_relay').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'delete\', text: \''.trans('event_logs.delete_device').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'update\', text: \''.trans('event_logs.update_device').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'corrupted_data\', text: \''.trans('event_logs.incorrect_data').'\'}));
                }
                else if(the_val == "analyzers"){
                    $("#el_select_type").append($(\'<option>\', {value: \'all\', text: \''.trans('event_logs.all_records').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'create\', text: \''.trans('event_logs.new_analyzer').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'delete\', text: \''.trans('event_logs.delete_device').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'update\', text: \''.trans('event_logs.update_device').'\'}));
                    $("#el_select_type").append($(\'<option>\', {value: \'corrupted_data\', text: \''.trans('event_logs.incorrect_data').'\'}));
                }
                else if( the_val == "users" ){
                    '.(Auth::user()->user_type!=4 ?'' .
                        '$("#el_select_type").append($("<option>", {value: "all", text: "'.trans('event_logs.all_records').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "sessions", text: "'.trans('event_logs.session_events').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "activation", text: "'.trans('event_logs.account_activation').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "user_change_authorization", text: "'.trans('event_logs.users_user_change_authorization').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "create", text: "'.trans('event_logs.new_user').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "delete", text: "'.trans('event_logs.delete_user').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_user').'"}));' .
                    '':'' .
                        '$("#el_select_type").append($("<option>", {value: "all", text: "'.trans('event_logs.all_records').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "sessions", text: "'.trans('event_logs.session_events').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_device').'"}));').'' .
                '}
                else if( the_val == "clients" ){
                    '.(Auth::user()->user_type!=4 ? '
                    $("#el_select_type").append($("<option>", {value: "all", text: "'.trans('event_logs.all_records').'"}));
                    $("#el_select_type").append($("<option>", {value: "create", text: "'.trans('event_logs.new_client').'"}));
                    $("#el_select_type").append($("<option>", {value: "delete", text: "'.trans('event_logs.delete_client').'"}));
                    $("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_client').'"}));
                    ':'
                        $("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_client').'"}));').'
                }
                else if( the_val == "distributors" ){
                    '.(Auth::user()->user_type!=3 ? '
                    $("#el_select_type").append($("<option>", {value: "all", text: "'.trans('event_logs.all_records').'"}));
                    $("#el_select_type").append($("<option>", {value: "create", text: "'.trans('event_logs.new_distributor').'"}));
                    $("#el_select_type").append($("<option>", {value: "delete", text: "'.trans('event_logs.delete_distributor').'"}));
                    $("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_distributor').'"}));
                    ':'$("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_distributor').'"}));').'
                }
                else if( the_val == "fee_scales" ){
                    $("#el_select_type").append($("<option>", {value: "all", text: "'.trans('event_logs.all_records').'"}));
                    $("#el_select_type").append($("<option>", {value: "create", text: "'.trans('event_logs.new_fee_scale').'"}));
                    $("#el_select_type").append($("<option>", {value: "delete", text: "'.trans('event_logs.delete_fee_scale').'"}));
                    $("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_fee_scale').'"}));
                }
                else{
                    '.(Auth::user()->user_type!=4?'' .
                        '$("#el_select_type").append($("<option>", {value: "all", text: "'.trans('event_logs.all_records').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "create", text: "'.trans('event_logs.add_new').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "delete", text: "'.trans('event_logs.delete_device').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_device').'"}));'
                    :'' .
                        '$("#el_select_type").append($("<option>", {value: "all", text: "'.trans('event_logs.all_records').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "sessions", text: "'.trans('event_logs.session_events').'"}));' .
                        '$("#el_select_type").append($("<option>", {value: "update", text: "'.trans('event_logs.update_device').'"}));').'' .
                '}
                
                el_filter_obj.category = the_val;
                el_filter_obj.type = "all";                
                el_dt.ajax.reload();
            });
            
            
            $("#el_select_type").change(function(){
                the_val = $(this).val();
                
                el_filter_obj.category = $("#el_select_table").val();
                el_filter_obj.type = the_val;                
                el_dt.ajax.reload();                
            });
        ');

        $data_table->set_add_right(false);
        return $data_table;
    }
    public function showTable(Request $request){
        $data_table = self::prepareEventTableObject($request,"all","all");
        return view('pages.event_logs')->with("DataTableObj",$data_table);
    }

    public function getData($type,$id){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $where_clause = "WHERE 1=1 ";
        $order_column = "EL.date";
        $order_dir = "DESC";

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];
            $column_item = array_keys(array_slice($this->columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;
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

        $param_array = array();
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(EL.date) BETWEEN ? AND ? ";


        //filter data according to displayed page
        if($type != "all"){
            if($type == "user"){
                $param_array[]= $id;
                $param_array[]= $id;
                $where_clause .= " AND (EL.user_id = ? OR (EL.table_name='users' AND EL.affected_id=?))";
            }
            else if($type == "client"){
                $client_users = DB::select("SELECT GROUP_CONCAT(id) as users_id FROM users WHERE user_type=4 AND org_id=?",[$id]);
                $client_users = $client_users[0]->users_id;

                $client_modems = DB::select("SELECT GROUP_CONCAT(id) as modems_id FROM modems WHERE client_id=?",[$id]);
                $client_modems = $client_modems[0]->modems_id;

                $client_devices = DB::select("SELECT GROUP_CONCAT(id) as devices_id FROM devices WHERE FIND_IN_SET(modem_id,?)>0",[$client_modems]);
                $client_devices = $client_devices[0]->devices_id;

                $param_array[]= $client_users;
                $param_array[]= $client_devices;
                $param_array[]= $client_modems;
                $param_array[]= $client_users;
                $param_array[]= $id;
                $where_clause .= " AND (FIND_IN_SET(EL.user_id,?)>0 OR ((EL.table_name='meters' OR EL.table_name='relays' OR EL.table_name='analyzers') AND FIND_IN_SET(EL.affected_id,?)>0 ) OR (EL.table_name='modems' AND FIND_IN_SET(EL.affected_id,?)>0) OR (EL.table_name='users' AND FIND_IN_SET(EL.affected_id,?)>0) OR (EL.table_name='clients' AND EL.affected_id=?))";

            }
            else if($type == "distributor"){
                $dist_users = DB::select("SELECT GROUP_CONCAT(id) as users_id FROM users WHERE (user_type=3 AND org_id=?) OR (user_type=4 AND porg_id=?)",[$id,$id]);
                $dist_users = $dist_users[0]->users_id;

                $dist_clients = DB::select("SELECT GROUP_CONCAT(id) as clients_id FROM clients WHERE distributor_id=?",[$id]);
                $dist_clients = $dist_clients[0]->clients_id;

                $client_modems = DB::select("SELECT GROUP_CONCAT(id) as modems_id FROM modems WHERE FIND_IN_SET(client_id,?)>0",[$dist_clients]);
                $client_modems = $client_modems[0]->modems_id;

                $client_devices = DB::select("SELECT GROUP_CONCAT(id) as devices_id FROM devices WHERE FIND_IN_SET(modem_id,?)>0",[$client_modems]);
                $client_devices = $client_devices[0]->devices_id;

                $param_array[]= $dist_users;
                $param_array[]= $dist_users;
                $param_array[]= $id;
                $param_array[]= $dist_clients;
                $param_array[]= $client_modems;
                $param_array[]= $client_devices;

                $where_clause .= "AND (FIND_IN_SET(EL.user_id,?)>0 OR (EL.table_name='users' AND FIND_IN_SET(EL.affected_id,?)>0) OR (EL.table_name='distributors' AND EL.affected_id=?) OR (EL.table_name='clients' AND FIND_IN_SET(EL.affected_id,?)>0) OR (EL.table_name='modems' AND FIND_IN_SET(EL.affected_id,?)>0) OR ((EL.table_name='meters' OR EL.table_name='relays' OR EL.table_name='analyzers') AND FIND_IN_SET(EL.affected_id,?)>0 ))";
            }
        }

        // get custom filters(event category and type) if exist
        if( isset($filter_obj["category"]) && $filter_obj["category"] != "" ){
            if( $filter_obj["category"] != "all" ){
                $param_array[]= $filter_obj["category"];
                $where_clause .= " AND EL.table_name = ? ";
            }
        }

        if( isset($filter_obj["type"]) && $filter_obj["type"] != "" ){
            if( $filter_obj["type"] != "all" ){
                if($filter_obj["type"] == "activation"){
                    $param_array[]= "user_status_deactivated";
                    $param_array[]= "user_status_activated";
                    $where_clause .= " AND (EL.event_type = ? OR EL.event_type =? )";
                }
                else if($filter_obj["type"] == "sessions"){
                    $param_array[]= "login";
                    $param_array[]= "logout";
                    $where_clause .= " AND (EL.event_type = ? OR EL.event_type =? )";
                }
                else if($filter_obj["type"] == "update"){
                    $param_array[]= "update";
                    $param_array[]= "profile_update";
                    $where_clause .= " AND (EL.event_type = ? OR EL.event_type =? )";
                }
                else{
                    $param_array[]= $filter_obj["type"];
                    $where_clause .= " AND EL.event_type = ? ";
                }
            }
        }

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "AU.name LIKE ? ";
                $where_clause .= "AND EL.event_type NOT LIKE 'corrupted_data' ";
                //$param_array[]="%".$search_value."%";
                //$where_clause .= " OR FS.active_unit_price LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        if( Auth::user()->user_type == 3 ){
            $param_array[]=Auth::user()->org_id;
            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND ((AU.user_type=3 AND AU.org_id=?) OR (AU.user_type=4 AND AU.porg_id=?)) ";
        }
        else if(Auth::user()->user_type == 4){

            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND (AU.user_type=4 AND AU.org_id=?) ";
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM event_logs EL LEFT JOIN users AU ON AU.id=EL.user_id '.$where_clause,$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT EL.*, AU.name as actor FROM event_logs EL LEFT JOIN users AU ON AU.id=EL.user_id '.$where_clause.' ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?',$param_array);

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                $affected_detail = self::createAffected($one_row->table_name,$one_row->affected_id,$one_row->event_type);

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "icon" => $affected_detail["icon"],
                    "event_type" => $affected_detail["event_type"],
                    "actor"=> "<a href='/user_management/detail/".$one_row->user_id."' title='".trans('event_logs.go_actor')."' target='_blank'>".$one_row->actor."</a>",
                    "affected"=> $affected_detail["affected_value"],
                    "date" => date('d/m/Y H:i:s',strtotime($one_row->date))
                );

                if($one_row->event_type == "corrupted_data"){
                    $tmp_array["actor"] = trans("global.system");
                }

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    private function createAffected($table_name, $affected_id, $event_type){
        $return_array = array();
        $affected_value = "";
        $event_value = trans("event_logs.".$table_name."_".$event_type);
        $icon ='';
        $icon_color = "red";
        $small_icon = "times";

        if($event_type=="create"){
            $icon_color = "green";
            $small_icon = "plus";
        }
        else if($event_type == "update"){
            $icon_color = "#6699ff";
            $small_icon = "refresh";
        }
        else if($event_type == "delete"){
            $icon_color = "red";
            $small_icon = "times";
        }
        else if($event_type == "profile_update"){
            $icon_color = "#3232bd";
            $small_icon = "refresh";
        }
        else if($event_type == "user_change_authorization"){
            $icon_color = "orange";
            $small_icon = "star";
        }
        else if($event_type == "user_status_activated"){
            $icon_color = "green";
            $small_icon = "check";
        }
        else if($event_type == "user_status_deactivated"){
            $icon_color = "darkRed";
            $small_icon = "circle";
        }
        else if($event_type == "login"){
            $icon_color = "green";
            $small_icon = "unlock";
        }
        else if($event_type == "logout"){
            $icon_color = "orange";
            $small_icon = "lock";
        }
        else if($event_type == "corrupted_data"){
            $icon_color = "darkRed";
            $small_icon = "exclamation-circle";
        }

        if($table_name == "users"){
            $result = DB::select("SELECT name FROM users WHERE id=".$affected_id);
            if( isset($result[0]->name) ) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/user_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->name . "</a>";
            }
            $icon.='<i class="fa fa-user-o fa-2x" style="color:'.$icon_color.';"></i>';
        }
        else if($table_name == "modems"){
            $result = DB::select("SELECT serial_no as name FROM modems WHERE id=".$affected_id);
            if( isset($result[0]->name) ) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/modem_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->name . "</a>";
            }
            $icon.='<i class="fa fa-podcast fa-2x" style="color:'.$icon_color.';"></i>';
        }
        else if($table_name == "meters" || $table_name == "relays" || $table_name == "analyzers"){
            $result = DB::select("SELECT D.device_no as name, DT.type as device_type FROM devices D, device_type DT WHERE DT.id=D.device_type_id AND D.id=".$affected_id);
            if( isset($result[0]->name) ){
                if($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/".$result[0]->device_type."/detail/".$affected_id."' target='_blank'>".$result[0]->name."</a>";

                $event_value = trans("event_logs.".$table_name."_".$event_type,["device_type"=>trans("global.".$result[0]->device_type)]);

                if( $result[0]->device_type == "meter" ){
                    $icon .= '<i class="fa fa-tachometer fa-2x" style="color:' . $icon_color . ';"></i>';
                }
                else if( $result[0]->device_type == "relay" ){
                    $icon .= '<i class="fa fa-sliders fa-2x" style="color:' . $icon_color . ';"></i>';
                }
                else if( $result[0]->device_type == "analyzer" ){
                    $icon .= '<i class="fa fa-desktop fa-2x" style="color:' . $icon_color . ';"></i>';
                }
                else {
                    $icon .= '<i class="fa fa-cog fa-2x" style="color:' . $icon_color . ';"></i>';
                }
            }
            else {
                $icon .= '<i class="fa fa-cog fa-2x" style="color:' . $icon_color . ';"></i>';
            }
        }
        else if($table_name == "clients"){
            $result = DB::select("SELECT name FROM clients WHERE id=".$affected_id);
            if( isset($result[0]->name) ) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/client_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->name . "</a>";
            }
            $icon.='<i class="fa fa-handshake-o fa-2x" style="color:'.$icon_color.';"></i>';
        }
        else if($table_name == "distributors"){
            $result = DB::select("SELECT name FROM distributors WHERE id=".$affected_id);
            if( isset($result[0]->name) ) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/distributors_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->name . "</a>";
            }
                $icon.='<i class="fa fa-sitemap fa-2x" style="color:'.$icon_color.';"></i>';
        }
        else if($table_name == "fee_scales"){
            $result = DB::select("SELECT name FROM fee_scales WHERE id=".$affected_id);
            if( isset($result[0]->name) ) {
                $affected_value = "<a href='/fee_scale' target='_blank'>" . $result[0]->name . "</a>";
            }

            $icon .= '<i class="fa fa-file-text-o fa-2x" style="color:' . $icon_color . ';"></i>';
        }
        else if( $table_name == "alert_definitions" ){
            $result = DB::select("SELECT name FROM alert_definitions WHERE id=".$affected_id);
            if( isset($result[0]->name) ) {
                $affected_value = "<a href='/alerts' target='_blank'>" . $result[0]->name . "</a>";
            }

            $icon .= '<i class="fa fa-bell-o fa-2x" style="color:' . $icon_color . ';"></i>';
        }
        else if( $table_name == "reports" ){
            $result = DB::select("SELECT (CASE WHEN is_report=0 THEN template_name ELSE report_name END) as name FROM reports WHERE id=".$affected_id);
            if( isset($result[0]->name) ) {
                $affected_value = "<a href='/reporting' target='_blank'>" . $result[0]->name . "</a>";
            }

            $icon .= '<i class="fa fa-book fa-2x" style="color:' . $icon_color . ';"></i>';
        }
        else if( $table_name == "additional_infos" ){
            $result = DB::select("SELECT name, distributor_id FROM additional_infos WHERE id=".$affected_id);
            if( isset($result[0]->name) != "" ) {
                if( is_numeric($result[0]->distributor_id) ){
                    $affected_value = "<a href='/distributor_management/detail/".$result[0]->distributor_id."/#tab-9' target='_blank'>" . $result[0]->name . "</a>";
                }
                else{
                    $affected_value = $result[0]->name;
                }
            }
            else{
                $affected_value = trans('event_logs.not_found');
            }

            $icon .= '<i class="fa fa-puzzle-piece fa-2x" style="color:' . $icon_color . ';"></i>';
        }
        else if( $table_name == "organization_schema" ){
            $result = DB::select("SELECT name, distributor_id FROM organization_schema WHERE id=".$affected_id);
            if( isset($result[0]->name) != "" ) {
                if( is_numeric($result[0]->distributor_id) ){
                    $affected_value = "<a href='/distributor_management/detail/".$result[0]->distributor_id."/#tab-8' target='_blank'>" . $result[0]->name . "</a>";
                }
                else{
                    $affected_value = $result[0]->name;
                }
            }
            else{
                $affected_value = trans('event_logs.not_found');
            }

            $icon .= '<i class="fa fa-sitemap fa-2x" style="color:' . $icon_color . ';"></i>';
        }

        $icon .= '&nbsp;' .
                 //'<span style="position:relative;">' .
                 '<span style="position:relative;">' .
                     '<i class="fa fa-'.$small_icon.'" style="position: absolute;color:'.$icon_color.';"></i>' .
                 '</span>';
        //$icon .= '</span>';

        $return_array["event_type"] = $event_value;
        $return_array["affected_value"] = $affected_value;
        $return_array["icon"] = $icon;
        return $return_array;

    }

}
