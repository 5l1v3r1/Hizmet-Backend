<?php

/*
Created by osman/uk to operate custom functions
*/
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Helper
{

	public static function create_menu($operations, $active_page,$get_first=false){
		$menus = DB::table('menus')->orderBy('dis_order','asc')->where('status',1)->get();
		$the_menus = array();

        //$user_operations = self::get_operations_name(Auth::user()->operations);

		foreach($menus as $menu){

            if($menu->parent=="0"){
                $the_menus[$menu->id] =  array("name"=>$menu->name, "id"=>$menu->id, "icon"=>$menu->icon,"parent"=>$menu->parent);
                $the_menus[$menu->id]["children"] = array();
                $the_menus[$menu->id]["is_active"] = false;
                $the_menus[$menu->id]["display"] = false;

                if($menu->name == $active_page)
                    $the_menus[$menu->id]["is_active"] = true;

                if(self::has_right(Auth::user()->operations,"view_".$menu->name)){
                    $the_menus[$menu->id]["display"] = true;
                }
            }
            else{

                $child_display = false;
                if(self::has_right(Auth::user()->operations,"view_".$menu->name)){
                    $the_menus[$menu->parent]["display"] = true;
                    $child_display = true;
                }
                $the_menus[$menu->parent]["children"][] = array("name"=>$menu->name, "id"=>$menu->id, "icon"=>$menu->icon,"parent"=>$menu->parent,"display"=>$child_display);

                if($menu->name == $active_page)
                    $the_menus[$menu->parent]["is_active"] = true;

            }


		}

		//to get first visible menu item (may be used for determination of active page according to user's rights)
		if($get_first==true){

            foreach($the_menus as $one_menu){
                if($one_menu["display"]==true){
                    if(count($one_menu["children"])>0){

                        foreach($one_menu["children"] as $one_child){
                            if($one_child["display"]==true)
                                return $one_child["name"];
                        }
                    }
                    else{
                        return $one_menu["name"];
                    }
                }
            }

            return "404";
        }

        foreach($the_menus as $one_menu){

            if($one_menu["display"] == false)
                continue;

            echo '<li '.($one_menu["is_active"]==true?'class="active"':'').'>';

            echo '<a href="'.((!(count($one_menu["children"])>0))?'/'.$one_menu["name"].'':'javascript:void(1);').'"><i class="fa '.$one_menu["icon"].' fa-lg"></i> <span class="nav-label">'.trans("menu.".$one_menu["name"]).'</span>'.((count($one_menu["children"])>0)?'<span class="fa arrow">':'').'</a>';

            if(count($one_menu["children"])>0){
                echo '<ul class="nav nav-second-level">';
                foreach($one_menu["children"] as $one_child){
                    if($one_child["display"] == false)
                        continue;

                    echo '<li '.($one_child["name"]==$active_page?'class="active"':'').'><a href="/'.$one_child["name"].'">'.trans("menu.".$one_child["name"]).'</a></li>';
                }
                echo '</ul>';
            }
            echo '</li>';
        }
	}

	public static function has_right($user_operations,$operation){

        $user_operations = self::get_operations_name($user_operations);

        if($user_operations[0]=="all")
            return true;
        else if($user_operations[0]=="none")
            return false;

	    foreach($user_operations as $one_operation){
	        if($one_operation->name == $operation)
	            return true;
        }
    }

    public static function get_operations_name($operations){

        if(is_null($operations) || !isset($operations) || trim($operations)=="")
            return ["none"];

        $operations_array = json_decode($operations);
        if($operations_array[0]=="all"){
            return ["all"];
        }
        if($operations_array[0]=="none"){
            return ["none"];
        }

        return DB::select('SELECT name FROM operations WHERE JSON_CONTAINS(?,JSON_ARRAY(id))',[$operations]);

    }

    public static function user_type_virtual_table(){
        return "
           (
                SELECT 1 as id, '".trans('global.superadmin')."' as type
                UNION
                SELECT 2 as id, '".trans('global.admin')."' as type 
                UNION
                SELECT 3 as id, '".trans('global.distributor')."' as type
                UNION
                SELECT 4 as id, '".trans('global.client')."' as type )
         ";
    }

    public static function device_type_virtual_table(){
        return "
            (
                SELECT 'meter' as type,'".trans('global.meter')."' as device_type
                UNION
                SELECT 'relay' as type, '".trans('global.relay')."' as device_type 
                UNION
                SELECT 'analyzer' as type, '".trans('global.analyzer')."' as device_type
                
            )
        ";
    }

    public static function alert_type_virtual_table(){
        return "
           (
                SELECT 'reactive' as type, '".trans('alerts.reactive')."' as alert_type
                UNION
                SELECT 'current' as type, '".trans('alerts.current')."' as alert_type 
                UNION
                SELECT 'voltage' as type, '".trans('alerts.voltage')."' as alert_type
                UNION
                SELECT 'connection' as type, '".trans('alerts.connection')."' as alert_type )
         ";
    }

    /**
     * @param $fee_scales: json string
     * @return string
     */
    public static function device_fee_scale_virtual_table($fee_scales){

        $fee_scales_array = json_decode($fee_scales);

        $return_text = "";
        foreach ($fee_scales_array as $one_scale){
            $return_text .= "SELECT ".$one_scale->id." as id, '".$one_scale->date."' as date, '".$one_scale->type."' as fee_scale_type UNION ";
        }

        $return_text = trim($return_text,'UNION ');

        return "( ".$return_text." )";

    }

    public static function clear_unused_img($path){
        $files = glob($path.'/*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)){
                if (strpos($file, '__') !== false) {
                    unlink($file); // delete file
                }
            }
        }
    }

    public static function city_list($id){
        return '
        <select class="form-control" name="'.$id.'" id="'.$id.'" style="width:100%;" required>
            <option value="Adana">Adana</option>
            <option value="Adıyaman">Adıyaman</option>
            <option value="Afyon">Afyon</option>
            <option value="Ağrı">Ağrı</option>
            <option value="Aksaray">Aksaray</option>
            <option value="Amasya">Amasya</option>
            <option value="Ankara">Ankara</option>
            <option value="Antalya">Antalya</option>
            <option value="Ardahan">Ardahan</option>
            <option value="Artvin">Artvin</option>
            <option value="Aydın">Aydın</option>
            <option value="Balıkesir">Balıkesir</option>
            <option value="Bartın">Bartın</option>
            <option value="Batman">Batman</option>
            <option value="Bayburt">Bayburt</option>
            <option value="Bilecik">Bilecik</option>
            <option value="Bingöl">Bingöl</option>
            <option value="Bitlis">Bitlis</option>
            <option value="Bolu">Bolu</option>
            <option value="Burdur">Burdur</option>
            <option value="Bursa">Bursa</option>
            <option value="Çanakkale">Çanakkale</option>
            <option value="Çankırı">Çankırı</option>
            <option value="Çorum">Çorum</option>
            <option value="Denizli">Denizli</option>
            <option value="Diyarbakır">Diyarbakır</option>
            <option value="Düzce">Düzce</option>
            <option value="Edirne">Edirne</option>
            <option value="Elazığ">Elazığ</option>
            <option value="Erzincan">Erzincan</option>
            <option value="Erzurum">Erzurum</option>
            <option value="Eskişehir">Eskişehir</option>
            <option value="Gaziantep">Gaziantep</option>
            <option value="Giresun">Giresun</option>
            <option value="Gümüşhane">Gümüşhane</option>
            <option value="Hakkari">Hakkari</option>
            <option value="Hatay">Hatay</option>
            <option value="Iğdır">Iğdır</option>
            <option value="Isparta">Isparta</option>
            <option value="İçel">İçel</option>
            <option value="İstanbul">İstanbul</option>
            <option value="İzmir">İzmir</option>
            <option value="Kahramanmaraş">Kahramanmaraş</option>
            <option value="Karabük">Karabük</option>
            <option value="Karaman">Karaman</option>
            <option value="Kars">Kars</option>
            <option value="Kastamonu">Kastamonu</option>
            <option value="Kayseri">Kayseri</option>
            <option value="Kırıkkale">Kırıkkale</option>
            <option value="Kırklareli">Kırklareli</option>
            <option value="Kırşehir">Kırşehir</option>
            <option value="Kilis">Kilis</option>
            <option value="Kocaeli">Kocaeli</option>
            <option value="Konya">Konya</option>
            <option value="Kütahya">Kütahya</option>
            <option value="Malatya">Malatya</option>
            <option value="Manisa">Manisa</option>
            <option value="Mardin">Mardin</option>
            <option value="Muğla">Muğla</option>
            <option value="Muş">Muş</option>
            <option value="Nevşehir">Nevşehir</option>
            <option value="Niğde">Niğde</option>
            <option value="Ordu">Ordu</option>
            <option value="Osmaniye">Osmaniye</option>
            <option value="Rize">Rize</option>
            <option value="Sakarya">Sakarya</option>
            <option value="Samsun">Samsun</option>
            <option value="Siirt">Siirt</option>
            <option value="Sinop">Sinop</option>
            <option value="Sivas">Sivas</option>
            <option value="Şanlıurfa">Şanlıurfa</option>
            <option value="Şırnak">Şırnak</option>
            <option value="Tekirdağ">Tekirdağ</option>
            <option value="Tokat">Tokat</option>
            <option value="Trabzon">Trabzon</option>
            <option value="Tunceli">Tunceli</option>
            <option value="Uşak">Uşak</option>
            <option value="Van">Van</option>
            <option value="Yalova">Yalova</option>
            <option value="Yozgat">Yozgat</option>
            <option value="Zonguldak">Zonguldak</option>
        </select>';
    }

    public static function get_distributors_select($id, $is_hidden=false){
        $return_value = '
            <div class="form-group" style="'.($is_hidden==true?"display:none;":"").'">
                <label class="col-sm-3 control-label">'.trans('global.distributor').' 
                    <span style="color:red;">*</span>
                </label>
                <div class="col-sm-6">';

        $return_value .= '<select  class="form-control" name="'.$id.'" id="'.$id.'" style="width:100%;">';

        $user_type = Auth::user()->user_type;

        if( $user_type == 1 || $user_type == 2 ){
            $return_value .= '<option value="0">'.trans('global.main_distributor').'</option>';

            $result = DB::table('distributors')->where('status','<>',0)->orderBy('name')->get();

            if( count($result) > 0){
                foreach ($result as $one_dist){
                    $return_value .= '<option value="'.$one_dist->id.'">'.$one_dist->name.'</option>';
                }
            }
        }
        else{
            return "<input id='hdn_".$id."' type='hidden' value='".Auth::user()->org_id."'></input>";
        }


        $return_value .= '</select></div></div>';



        return $return_value;
    }

    public static function get_clients_select($id, $is_hidden=false){
        $return_value = '
            <div class="form-group" style="'.($is_hidden==true?"display:none;":"").'">
                <label class="col-sm-3 control-label">'.trans('global.client').' <span style="color:red;">*</span></label>
                <div class="col-sm-6">';

        $return_value .= '<select class="form-control" name="'.$id.'" id="'.$id.'" style="width:100%;">';

        $user_type = Auth::user()->user_type;
        $result = array();

        if( $user_type == 1 || $user_type == 2 ){
            $result = DB::table('clients')
                    ->where("status",'<>', 0)
                    ->orderBy('name')->get();
        }
        else if( $user_type == 3 ){
            $result = DB::table('clients')
                        ->where("status",'<>', 0)
                        ->where('distributor_id', Auth::user()->org_id)
                        ->orderBy('name')
                        ->get();
        }
        else{
            abort(404);
        }

        if( count($result) > 0){
            foreach ($result as $one_dist){
                $return_value .= '<option value="'.$one_dist->id.'">'.$one_dist->name.'</option>';
            }
        }

        $return_value .= '
                    </select>
                </div> <!-- .col-sm-6 -->
            </div> <!-- .form-group -->';

        return $return_value;
    }

    public static function get_user_type_select($id){
        $return_value = '<div class="form-group"><label class="col-sm-3 control-label">'.trans('user_management.type').' <span style="color:red;">*</span></label><div class="col-sm-6"><select name="'.$id.'" id="'.$id.'" class="form-control" style="width:100%;"><option value="4">'.trans('global.client').'</option>';

        if( Auth::user()->user_type == 2 || Auth::user()->user_type == 1 ){
            $return_value .= '<option value="3">'.trans('global.distributor').'</option>';
        }

        if( Auth::user()->user_type == 1 ){
            $return_value .= '<option value="2">'.trans('global.admin').'</option>';
        }


        $return_value .= '</select></div></div>';

        return $return_value;
    }

    public static function authorization_edit_form($the_user){
        $default_operations = DB::table('user_type')
                                    ->where('id',$the_user->user_type)
                                    ->first();

        $all_operations = DB::select('SELECT O.id as id, O.name as name, M.name as menu, (CASE WHEN MM.name != "" THEN MM.name ELSE M.name END) as parent, (CASE WHEN MM.dis_order != "" THEN MM.dis_order ELSE M.dis_order END) as dis_order FROM operations O
                                      LEFT JOIN menus M ON M.id=O.menu_id
                                      LEFT JOIN menus MM ON M.parent = MM.id
                                      WHERE (CASE WHEN \'["all"]\' <> \''.$default_operations->default_operations.'\' THEN JSON_CONTAINS(?,JSON_ARRAY(O.id)) ELSE 1=1 END) AND M.status<>0 ORDER BY dis_order, M.dis_order ASC, O.id ASC',[$default_operations->default_operations]);


        //temp data as indicator for the for loop below to determine the checked operations successfully
        $all_operations [] = (object) array('id' => -1,'name'=>'', 'menu'=>'','parent'=>'tmp');

        $return_value = '';
        $menu_array = array();

        $user_operations = json_decode($the_user->operations);

        $cursor = array("current_parent"=>"", "current_menu"=>"", "counter"=> 0, "cursor_pos"=>0, "parent_counter"=>0);

        foreach ($all_operations as $one_operation){

            if($cursor["cursor_pos"] == 0){
                $cursor["current_parent"] = $one_operation->parent;
                $cursor["current_menu"] = $one_operation->menu;
            }

            if($cursor["current_menu"] != $one_operation->menu){
                if($cursor["counter"] == 0){
                    $menu_array[$cursor["current_parent"]][$cursor["current_menu"]]["checked"] = "none";
                }
                else if($cursor["counter"] == COUNT($menu_array[$cursor["current_parent"]][$cursor["current_menu"]])){
                    $menu_array[$cursor["current_parent"]][$cursor["current_menu"]]["checked"] = "all";
                }
                else{
                    $menu_array[$cursor["current_parent"]][$cursor["current_menu"]]["checked"] = "some";
                }

                $cursor["current_menu"] = $one_operation->menu;
                $cursor["counter"] = 0;
            }

            if($cursor["current_parent"] != $one_operation->parent){
                /*if($cursor["parent_counter"] == 0){
                    $menu_array[$cursor["current_parent"]]["checked"] = "none";
                }
                else if($cursor["parent_counter"] == COUNT($menu_array[$cursor["current_parent"]])){
                    $menu_array[$cursor["current_parent"]]["checked"] = "all";
                }
                else{
                    $menu_array[$cursor["current_parent"]]["checked"] = "some";
                }*/

                $cursor["current_parent"] = $one_operation->parent;
                $cursor["parent_counter"] = 0;
            }

            $menu_array[$one_operation->parent][$one_operation->menu][$one_operation->id] = array("id"=>$one_operation->id,"name"=>$one_operation->name,"checked"=>"");
            if($user_operations[0]=="all" || in_array($one_operation->id,$user_operations)){
                $menu_array[$one_operation->parent][$one_operation->menu][$one_operation->id]["checked"] = "checked";
                $cursor["counter"]++;
            }
            else{
                $menu_array[$one_operation->parent][$one_operation->menu][$one_operation->id]["checked"] = "false";
            }


            $cursor["cursor_pos"]++;

        }

        array_pop($menu_array);

        //check status for parent objects
        foreach ($menu_array as &$parent) {
            $total_operation_count = 0;
            $checked_operation_count = 0;

            foreach ($parent as $menu){
                foreach ($menu as $operation){


                    if(isset($operation["checked"])){
                        if( $operation["checked"]=="checked")
                            $checked_operation_count++;

                        $total_operation_count++;
                    }

                }
            }

            if($total_operation_count == $checked_operation_count)
                $parent["checked"] = "all";
            else if($checked_operation_count == 0){
                $parent["checked"] = "none";
            }
            else
                $parent["checked"] = "some";


        }


        foreach ($menu_array as $key=>$one_menu) {
            $parent_check_icon = "square-o";
            $parent_color = "#676a6c";

            if($one_menu["checked"] == "all"){
                $parent_color = "#23B613";
                $parent_check_icon = "check-square-o";
            }else if($one_menu["checked"] == "some"){
                $parent_check_icon = "square";
                $parent_color = "#4E5698";
            }

            $return_value .= '<div class="row the_parent" id="nestable2" ><div class="col-lg-12" style="margin-top: 20px;">
                        <div class="ibox collapsed">
                            <a class="collapse-link">
                                <div class="ibox-title" style="border-top-width: 4px;">
                                    <h5 style="color:'.$parent_color.'"><span class="fa fa-'.$parent_check_icon.' fa-lg the_parent_icon"></span> '.trans('menu.'.$key).'</h5>
                                    <div class="ibox-tools">        
                                        <i class="fa fa-chevron-up"></i>
                                    </div>
                                </div>
                            </a>
                            <div class="ibox-content"><div class="dd" id="'.$key.'_menu">
                                <ol class="dd-list">';

            foreach ($one_menu as $sub_key => $sub_menu){
                if($sub_key == "checked")
                    continue;

                $menu_check_icon = "square-o";
                if($sub_menu["checked"] == "all"){
                    $menu_check_icon = "check-square-o";
                }else if($sub_menu["checked"] == "some"){
                    $menu_check_icon = "square";
                }

                $return_value .='<li class="dd-item the_sub_menu" data-id="'.$sub_key.'_sub" id="'.$sub_key.'_sub" ><button style="display:none;margin-top:-1px;" data-action="collapse" type="button" onclick="collapse_item($(this))" ></button><button data-action="expand" style="margin-top:-1px;" type="button"  onclick="expand_item($(this))"></button>
                                        <div class="dd-handle">
                                            <i onclick="adjust_checkbox(\'sub\',$(this));" class="fa fa-'.$menu_check_icon.' the_sub_menu_icon"></i> '.trans("menu.".$sub_key).'
                                        </div>
                                        <ol class="dd-list" style="display:none;">';

                foreach ($sub_menu as $op_key => $op_menu){
                    if($op_key == "checked")
                        continue;

                    $op_check_icon = "square-o";
                    if($op_menu["checked"] == "checked"){
                        $op_check_icon = "check-square-o";
                    }
                    $return_value .='<li class="dd-item the_operation" data-id="'.$op_menu["id"].'_op" id="'.$op_menu["id"].'_op">
                                                <div class="dd-handle">
                                                    <i onclick="adjust_checkbox(\'op\',$(this));" class="fa fa-'.$op_check_icon.' the_operation_icon"></i> '.trans("global.".$op_menu["name"]).'
                                                </div>
                                            </li>';
                }

                $return_value .='</ol></li>';
            }

            $return_value.='</ol></div></div>
                        </div>
                    </div></div>';
        }

        if(self::has_right(Auth::user()->operations,'add_new_user')){
            $return_value .= '<div class="row form-horizontal" style="margin:20px;"><div class="form-group">
                                        <div class="col-lg-12 text-center">
                                            <button type="button" class="btn btn-primary btn-lg" id="save_authorizations" name="save_authorizations" onclick="save_authorizations();">
                                                <i class="fa fa-plus"></i> '.trans('user_management.save') .'
                                            </button>
                                        </div>
                                    </div></div>';
        }
        return $return_value;
    }

    /*public static function get_modem_type($id){
        $return_value = '<div class="form-group"><label class="col-sm-3 control-label">'.trans('modem_management.modem_type').' <span style="color:red;">*</span></label><div class="col-sm-6"><select name="'.$id.'" id="'.$id.'" class="form-control" style="width:100%;">';

        $result = DB::table("modem_type")->get();
        foreach ($result as $one_result){
            $return_value .= '<option value="'.$one_result->id.'">'.$one_result->type.' ('.$one_result->trademark.' / '.$one_result->model.')</option> ';
        }


        $return_value .= '</select></div></div>';

        return $return_value;
    } */

    public static function fire_event($event_type,$the_user,$table_name="",$affected_id=0){

        if($event_type == "login" || $event_type == "logout"){

            $table_name = "users";
            $affected_id = $the_user->id;
        }

        $result = DB::table('event_logs')->insert(
            [
                'user_id' => $the_user->id,
                'table_name' => $table_name,
                'event_type' => $event_type,
                'affected_id' => $affected_id
            ]
        );

    }

    /**
     * This function is deprecated and still uncomplete
     * @param $events
     * @return string
     */
    public static function prepare_event_html($events){

        $return_html = "";

        $title = "";
        $icon = "";
        $content = "";
        $icon_bg = "";

        foreach($events as $one_event){

            if($one_event->type == "session"){

                if($one_event->event_type == "login"){
                    $title = trans("event_logs.login_to_system_title");
                    $icon = "fa-unlock";
                    $icon_bg = "navy-bg";

                    $result = DB::select("SELECT name, email FROM users WHERE id=".$one_event->user_id);
                    $content = trans("event_logs.login_to_system_content",['name'=>$result[0]->name, 'email'=>$result[0]->email]);

                }
                else if($one_event->event_type == "logout"){

                    $title = trans("event_logs.logout_from_system_title");
                    $icon = "fa-lock";
                    $icon_bg = "yellow-bg";

                    $result = DB::select("SELECT name, email FROM users WHERE id=".$one_event->user_id);
                    $content = trans("event_logs.logout_from_system_content",['name'=>$result[0]->name, 'email'=>$result[0]->email]);

                }

            }
            else if($one_event->type == "event"){

                if($one_event->table_name == "modems"){

                    //get modem specific info
                    $result = DB::select("SELECT M.serial_no as serial_no, C.name as client FROM modems M, clients C WHERE M.client_id=C.id AND M.id=".$one_event->affected_id);

                    $modem_serial_no = $result[0]->serial_no;
                    $client_name = $result[0]->client;

                    //get user specific info
                    $result = DB::select("SELECT name FROM users WHERE id=".$one_event->user_id);
                    $performer_name = $result[0]->name;

                    $icon_bg = "lazur-bg";
                    $icon = "fa-podcast";

                    if($one_event->event_type == "create"){

                        $title = trans("event_logs.modem_created_title");
                        $content = trans("event_logs.modem_created_content",["modem_serial_no"=>$modem_serial_no,"client_name"=>$client_name,"performer_name"=>$performer_name]);

                    }
                    else if($one_event->event_type == "update"){

                        $title = trans("event_logs.modem_updated_title");
                        $content = trans("event_logs.modem_updated_content",["modem_serial_no"=>$modem_serial_no,"client_name"=>$client_name,"performer_name"=>$performer_name]);
                    }
                    else if($one_event->event_type == "delete"){

                        $title = trans("event_logs.modem_deleted_title");
                        $content = trans("event_logs.modem_deleted_content",["modem_serial_no"=>$modem_serial_no,"client_name"=>$client_name,"performer_name"=>$performer_name]);
                    }

                }
                else if($one_event->table_name == "devices"){
                    //get modem specific info
                    $result = DB::select("SELECT D.device_no as device_serial_no,DT.type as device_type FROM devices D, device_type DT  WHERE DT.id=D.device_type_id AND D.id=".$one_event->affected_id);

                    $device_serial_no = $result[0]->device_serial_no;
                    $device_type = trans("global.".$result[0]->device_type);

                    //get user specific info
                    $result = DB::select("SELECT name FROM users WHERE id=".$one_event->user_id);
                    $performer_name = $result[0]->name;

                    $icon_bg = "gray-bg";
                    $icon = "fa-cogs";

                    if($one_event->event_type == "create"){

                        $title = trans("event_logs.device_created_title");
                        $content = trans("event_logs.device_created_content",["device_serial_no"=>$device_serial_no,"device_type"=>$device_type,"performer_name"=>$performer_name]);

                    }
                    else if($one_event->event_type == "update"){

                        $title = trans("event_logs.device_updated_title");
                        $content = trans("event_logs.device_updated_content",["device_serial_no"=>$device_serial_no,"device_type"=>$device_type,"performer_name"=>$performer_name]);
                    }
                    else if($one_event->event_type == "delete"){

                        $title = trans("event_logs.device_deleted_title");
                        $content = trans("event_logs.device_deleted_content",["device_serial_no"=>$device_serial_no,"device_type"=>$device_type,"performer_name"=>$performer_name]);
                    }

                }
                else if($one_event->table_name == "users"){



                    $icon_bg = "blue-bg";
                    $icon = "fa-user";

                    if($one_event->event_type =="create"){

                        $title = trans("event_logs.user_created_title");
                    }
                    else if($one_event->event_type =="update"){

                        $title = trans("event_logs.user_updated_title");

                    }
                    else if($one_event->event_type =="delete"){

                        $title = trans("event_logs.user_deleted_title");

                    }
                    else if($one_event->event_type =="user_status_activated"){

                        $title = trans("event_logs.user_status_activated_title");

                    }
                    else if($one_event->event_type =="user_status_deactivated"){

                        $title = trans("event_logs.user_status_deactivated_title");

                    }
                    else if($one_event->event_type =="user_change_authorization"){

                        $title = trans("event_logs.user_change_authorization_title");

                    }
                    else if($one_event->event_type =="profile_update"){

                        $title = trans("event_logs.profile_update_title");

                    }
                }
                else if($one_event->table_name == "clients"){


                }
                else if($one_event->table_name == "distributors"){

                }
            }


            $return_html .= '<div class="vertical-timeline-block">
                                <div class="vertical-timeline-icon '.$icon_bg.'">
                                    <i class="fa '.$icon.'"></i>
                                </div>

                                <div class="vertical-timeline-content" style="padding-top: 3px;">
                                    <h2>'.$title.'</h2>
                                    <p> '.$content.'
                                    </p>
                                    <span class="vertical-date">
                                        <small>'.date('d/m/Y H:i:s',strtotime($one_event->date)).'</small>
                                    </span>
                                </div>
                            </div>';
        }
        return $return_html;
    }

    public static function secondsToTime($inputSeconds) {

        $secondsInAMinute = 60;
        $secondsInAnHour  = 60 * $secondsInAMinute;
        $secondsInADay    = 24 * $secondsInAnHour;

        // extract days
        $days = floor($inputSeconds / $secondsInADay);

        // extract hours
        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        // return the final array
        $obj = array(
            'd' => (int) $days,
            'h' => (int) $hours,
            'm' => (int) $minutes,
            's' => (int) $seconds,
        );


        $return_string = "";
        if($obj["d"]>0)
            $return_string.=$obj["d"]." ".trans("global.day")." ";

        if($obj["h"]>0)
            $return_string.=$obj["h"]." ".trans("global.hour")." ";


        if($return_string == ""){

            if($obj["m"]>0){
                $return_string.=$obj["m"]." ".trans("global.minute")." ";
            }
            else{
                $return_string = $inputSeconds;
            }



        }

        return $return_string;
    }

    /**
     * Send SMS
     *
     * @param $body: Message content
     * @param $phones: Telephone numbers to send SMS (If more than one, it should be sent separated by a comma)
     *
     * @return mixed|string
     */
    public static function sendSMS($body=false, $phones=false){
        if( $body == false || $phones == false ){
            abort(404);
        }

        $url="http://processor.smsorigin.com/xml/process.aspx";
        $data ="
            <MainmsgBody>
                <Command>0</Command>
                <PlatformID>1</PlatformID>
                <UserName>".env("SMS_USERNAME")."</UserName>
                <PassWord>".env("SMS_PASSWORD")."</PassWord>
                <ChannelCode>".env("SMS_CHANNEL")."</ChannelCode>
                <Mesgbody>".trim($body)."</Mesgbody>
                <Numbers>".$phones."</Numbers>
                <Type>2</Type>
                <Originator></Originator>
                <SDate></SDate>
                <EDate></EDate>
                <Concat>1</Concat>			
            </MainmsgBody>";

        /*
            Type 1: The message should not contain Turkish characters!
            Type 2: It may contain Turkish characters, but the maximum message length is 268 characters.
            SDate: Message sending time. If it is empty, it will be sent immediately. Format: ddMMyyyyhhmm
        */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        $result = curl_exec ($ch);
        curl_close ($ch);

        //echo $result;
    }

    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * This user-land implementation follows the implementation quite strictly;
     * it does not attempt to improve the code or algorithm in any way. It will
     * raise a warning if you have fewer than 2 values in your array, just like
     * the extension does (although as an E_USER_WARNING, not E_WARNING).
     *
     * @param array $a
     * @param bool $sample [optional] Defaults to false
     * @return float|bool The standard deviation or false on error.
     */
    public static function stats_standard_deviation(array $a, $sample = false) {
        $n = count($a);
        if ($n === 0) {
            //trigger_error("The array has zero elements", E_USER_WARNING);
            return false;
        }
        if ($sample && $n === 1) {
            //trigger_error("The array has only 1 element", E_USER_WARNING);
            return false;
        }

        $mean = array_sum($a) / $n;
        $carry = 0.0;

        foreach ($a as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        };

        if ($sample) {
            --$n;
        }

        return sqrt($carry / $n);
    }


    /**
     * This function calculates the distance between two given coordinates
     * @param $latitudeFrom
     * @param $longitudeFrom
     * @param $latitudeTo
     * @param $longitudeTo
     * @param int $earthRadius
     */
    public static function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371){

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}
?>
