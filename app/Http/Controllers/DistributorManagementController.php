<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;

class DistributorManagementController extends Controller
{
    private $columns;
    private $user_columns;
    private $client_columns;
    private $modem_columns;
    private $device_columns;
    private $alerts_columns;
    private $add_info_columns;

    public function __construct()
    {
        $this->columns = array(
            "logo"=>array("orderable"=>false,"name"=>false),
            "name"=>array(),
            "authorized_name"=>array(),
            "email"=>array(),
            "gsm_phone"=>array(),
            "location"=>array("name" => "address"),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );

        $this->user_columns = array(
            "avatar"=>array("orderable"=>false,"name"=>false),
            "name"=>array("name"=>"username"),
            "user_type"=>array("visible"=>false),
            "org_name"=>array("visible"=>false),
            "email"=>array(),
            "status"=>array("orderable"=>false),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );

        $this->client_columns = array(
            "logo"=>array("orderable"=>false,"name"=>false),
            "name"=>array("name" => "client_name"),
            "authorized_name"=>array(),
            "distributor"=>array( "visible" =>false ), // add visible options according to user type
            "email"=>array(),
            "gsm_phone"=>array(),
            "location"=>array(),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );

        $this->modem_columns = array(
            "status"=>array("orderable"=>false),
            "serial_no"=>array(),
            "modem_type"=>array(),
            "model"=>array("name"=>"trademark_model"),
            "client"=>array("visible"=>false),
            "location"=>array(),
            "last_connection_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );

        $this->device_columns = array(
            "status"=>array("orderable"=>false),
            "device_no" => array(),
            "modem_no" => array(),
            "client" => array("visible" => false),
            "inductive" => array(),
            "capacitive" => array(),
            "data_period" => array(),
            "last_data_at" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );

        $this->alerts_columns = array(
            "icon" => array("orderable" => false, "name" => false),
            "type" => array(),
            "device_no" => array("name" => "device_no_type"),
            "notification_method" => array("orderable" => false),
            "client" => array("visible" => false, "name" => "client_distributor"),
            "created_at"=>array(),
            "buttons"=>array( "orderable" => false, "name" => "operations", "nowrap" => true)
        );

        $this->add_info_columns = array(
            "category" => array("orderable" => false),
            "name" => array("name"=>"info_name"),
            "options" => array("orderable" => false),
            "created_by" => array(),
            "created_at"=>array(),
            "buttons"=>array( "orderable" => false, "name" => "operations", "nowrap" => true, "text_right" => true)
        );
    }

    public function showTable(Request $request){
        $prefix = "dm";
        $url = "dm_get_data";
        $default_order = '[6,"desc"]';
        $data_table = new DataTable($prefix,$url,$this->columns,$default_order,$request);

        return view('pages.distributor_management')->with("DataTableObj",$data_table);
    }

    public function uploadImage(Request $request){
        $uploadOk = 1;
        $message ="";

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["new_distributor_logo"]["tmp_name"]);
            if($check === false) {
                $message = "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Allow certain file formats
        $imageFileType = strtolower(pathinfo($_FILES["new_distributor_logo"]["name"],PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if file already exists
        /*if ( file_exists( "img/avatar/distributor/".md5_file($_FILES["new_distributor_logo"]["tmp_name"]).".".$imageFileType ) ) {
            $message = "Sorry, file already exists.";
            $uploadOk = 0;
        }*/

        // Check file size
        if ($_FILES["new_distributor_logo"]["size"] > 2048000) { //can't be larger than 2 MB
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo $message;
        }
        else {
            if ( move_uploaded_file( $_FILES['new_distributor_logo']['tmp_name'], "img/avatar/distributor/".Auth::user()->id . "__" . $_FILES["new_distributor_logo"]["name"]) ) {
                echo "{}";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    public function getData(){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $where_clause = "WHERE D.status<>0 ";
        $order_column = "D.created_at";
        $order_dir = "DESC";
        $param_array = array();

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

            if($order_column =="location"){
                $order_column = "JSON_UNQUOTE(json_extract(D.location,'$.verbal'))";
            }
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        if( Auth::user()->user_type == 3 ){
            $param_array[] =  Auth::user()->org_id;
            $where_clause .= " AND D.id = ? ";
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(D.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "D.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR D.email LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR authorized_name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR D.gsm_phone LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";
                $where_clause .= " OR lcase(JSON_UNQUOTE(json_extract(D.location,'$.verbal'))) LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";
                $where_clause .= " OR lcase(JSON_UNQUOTE(json_extract(D.location,'$.text'))) LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM distributors D '.$where_clause,$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT D.*, UT.modem_name as modem_name, (CASE WHEN D.created_by=0 THEN "'.trans("global.system").'" ELSE U.name END) as created_by_name, JSON_UNQUOTE(json_extract(D.location,\'$.verbal\')) as location_verbal,JSON_UNQUOTE(json_extract(D.location,\'$.text\')) as location_text FROM distributors D LEFT JOIN (SELECT C.distributor_id as distributor_id, GROUP_CONCAT(M.serial_no order by M.serial_no SEPARATOR \', \') as modem_name FROM clients C, modems M WHERE C.id = M.client_id AND M.status<>0 AND C.status=1 GROUP BY C.distributor_id) UT ON UT.distributor_id=D.id LEFT JOIN users U ON U.id=D.created_by ' . $where_clause . ' ORDER BY ' . $order_column . ' ' . $order_dir . ' LIMIT ? OFFSET ?', $param_array);

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                $logo = "<img  style='border-radius:50%;height:50px;max-width:50px;' src='/img/avatar/distributor/".$one_row->logo."' />";
                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "logo" => $logo,
                    "name" => $one_row->name,
                    "authorized_name" => $one_row->authorized_name,
                    "email" => $one_row->email,
                    "gsm_phone" => $one_row->gsm_phone,
                    "phone" => $one_row->phone,
                    "fax" => $one_row->fax,
                    "location" => "<span data-toggle='tooltip' data-placement='bottom' title='" . $one_row->location_text . "'>" . $one_row->location_verbal . "</span>",
                    "created_at" => "<span data-toggle='tooltip' data-placement='bottom' title='". trans('distributor_management.created_by') . ": " . $one_row->created_by_name . "'>" . date('d/m/Y H:i',strtotime($one_row->created_at)) . "</span>",
                    "buttons" => self::create_buttons($one_row->id, $one_row->modem_name)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function create_buttons($item_id, $modems){
        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_distributor_detail")){
            $return_value .= '<a href="/distributor_management/detail/'.$item_id.'" title="'.trans('distributor_management.detail').'" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
        }

        if(Helper::has_right(Auth::user()->operations, "add_new_distributor")){
            $return_value .= '<a href="javascript:void(1);" title="'.trans('distributor_management.edit').'" onclick="edit_distributor('.$item_id.');" class="btn btn-warning btn-sm"><i class="fa fa-edit fa-lg"></i></a> ';
        }


        if(Helper::has_right(Auth::user()->operations, "delete_distributor")){
            $return_value .= '<a '.($modems!=""?"style=\"opacity:0.4;\"":"").' href="javascript:void(1);" title="'.trans('distributor_management.delete_distributor').'" onclick="'.($modems!=""?"alertBox('','<b>[".$modems."]</b> ".trans("distributor_management.not_deletable")."','info');":"delete_distributor(".$item_id.");").'" class="btn btn-danger btn-sm"><i class="fa fa-trash-o fa-lg"></i></a> ';
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    public function getInfo(Request $request){
        if($request->has("id") && is_numeric($request->input("id"))){

            $result = DB::table("distributors")->where('status','<>',0)->where("id",$request->input("id"))->first();

            if(isset($result->id)){
                echo json_encode($result);
            }

        }
        else{
            echo "NEXIST";
        }
    }

    public function delete(Request $request){

        if(!($request->has("id") && is_numeric($request->input("id"))))
            return "ERROR";


        //no check if the user has right to delete, because this function is only be accessible by admins
        $result = DB::select('SELECT UT.modem_name as modem_name FROM distributors D LEFT JOIN (SELECT C.distributor_id as distributor_id, GROUP_CONCAT(M.serial_no order by M.serial_no SEPARATOR \', \') as modem_name FROM clients C, modems M WHERE C.id = M.client_id AND M.status<>0 AND C.status=1 GROUP BY C.distributor_id) UT ON UT.distributor_id=D.id WHERE D.id=?',array($request->input("id")));
        if(trim($result[0]->modem_name) == ""){
            DB::table('distributors')->where('id', $request->input("id"))
                ->update(
                    [
                        'status' => 0
                    ]
                );

            //fire event
            Helper::fire_event("delete",Auth::user(),"distributors",$request->input("id"));

            session(['distributor_delete_success' => true]);
            return "SUCCESS";

        }
        else
            return "ERROR";
    }

    public function distributorDetail(Request $request, $id){
        $the_distributor = DB::table("distributors as D")
            ->select(
                "D.*",
                DB::raw("JSON_UNQUOTE(json_extract(D.location,'$.text')) as location_text"),
                DB::raw("(CASE WHEN D.created_by=0 THEN '".trans('global.system')."' ELSE U.name END) as created_by")
            )
            ->leftJoin('users as U', 'U.id', 'D.created_by')
            ->where('D.id',$id)
            ->where('D.status','<>',0)
            ->first();

        //prepare user table obj which belongs to this distributor
        $prefix = "ddu";
        $url = "ddu_get_data/distributor/".$id;
        $default_order = '[5,"desc"]';
        $user_data_table = new DataTable($prefix,$url,$this->user_columns,$default_order,$request);
        $user_data_table->set_add_right(false);

        //prepare client table obj which belongs to this distributor
        $prefix = "ddc";
        $url = "ddc_get_data/distributor/".$id;
        $default_order = '[6,"desc"]';
        $client_data_table = new DataTable($prefix,$url,$this->client_columns,$default_order,$request);
        $client_data_table->set_add_right(false);

        //prepare modem table obj which belongs to this distributor
        $prefix = "ddm";
        $url = "ddm_get_data/distributor/".$id;
        $default_order = '[6,"desc"]';
        $modem_data_table = new DataTable($prefix,$url,$this->modem_columns,$default_order,$request);
        $modem_data_table->set_add_right(false);

        //prepare device table obj which belongs to this distributor
        $prefix = "ddd";
        $url = "ddd_get_data/all_devices/distributor/".$id;
        $default_order = '[7,"desc"]';
        $device_data_table = new DataTable($prefix,$url,$this->device_columns,$default_order,$request);
        $device_data_table->set_add_right(false);

        //prepare alerts table obj which belongs to this distributor
        $prefix = "ddal";
        $url = "al_get_data/".$request->segment(1)."/".$id;
        $default_order = '[5,"desc"]';
        $alert_table = new DataTable($prefix, $url, $this->alerts_columns, $default_order, $request);
        $alert_table->set_add_right(false);
        $alert_table->set_lang_page("alerts");

        //prepare add_info table obj which belongs to this distributor
        $prefix = "ddai";
        $url = "ai_get_data/".$id;
        $default_order = '[4,"desc"]';
        $add_info_table = new DataTable($prefix, $url, $this->add_info_columns, $default_order, $request);
        $add_info_table->set_add_right(true);

        //get event logs table from eventlogController
        $eventsTable = new EventlogsController();
        $eventsTable = $eventsTable->prepareEventTableObject($request,"distributor",$id);

        return view('pages.distributor_detail', [
                'the_distributor' => json_encode($the_distributor),
                'UserDataTableObj' => $user_data_table,
                'ClientDataTableObj' => $client_data_table,
                'ModemDataTableObj' => $modem_data_table,
                'DeviceDataTableObj' => $device_data_table,
                'EventDataTableObj' => $eventsTable,
                'AlertsDataTableObj' =>$alert_table,
                'AddInfoDataTableObj' =>$add_info_table
            ]
        );
    }

    public function create(Request $request){
        $op_type = "new";
        $logo = "no_avatar.png";
        $old_logo = "";
        $created_by = Auth::user()->id;
        $name_validator = 'bail|required|unique:distributors,name|max:255|min:3';

        if( $request->has('distributor_op_type') && trim($request->input('distributor_op_type')) == "edit") {
            $op_type = "edit";

            if( $request->has('distributor_edit_id') && is_numeric($request->input("distributor_edit_id")) ){
                $result = DB::table("distributors")
                    ->where("id", $request->input("distributor_edit_id"))
                    ->where('status', '<>', 0)
                    ->first();

                if( isset($result->id) && is_numeric($result->id) ){
                    // If the request is to update the profile information
                    // The distributor is Auth user's own distributor
                    if( Auth::user()->user_type == 3){
                        if( $request->has('distributor_edit_profile') && $request->input("distributor_edit_profile") == "yes" ){
                            if( Auth::user()->org_id != $result->id ) {
                                abort(404);
                            }
                        }
                        else{
                            abort(404);
                        }
                    }

                    if( isset($result->logo) && $result->logo != "" && $result->logo != NULL){
                        $old_logo = $result->logo;
                    }
                }
                else{
                    abort(404);
                }
            }
            else{
                abort(404);
            }

            $name_validator = 'bail|required|unique:distributors,name,'.$request->input("distributor_edit_id").',id|max:255|min:3';
        }

        $this->validate($request, [
            'new_distributor_name' => $name_validator,
            'new_distributor_authorized_name' => 'bail|required|min:3|max:255',
            'new_distributor_email' => 'bail|required|email|min:3|max:255',
            'new_distributor_gsm_phone' => 'bail|required|min:7|max:20',
            'new_distributor_phone' => 'bail|min:7|max:20',
            'new_distributor_fax' => 'bail|min:7|max:20',
            'new_distributor_tax_administration' => 'bail|min:3|max:255',
            'new_distributor_tax_no' => 'bail|min:3|max:30',
            'new_distributor_location_text' => 'bail|required|min:3|max:255',
            'new_distributor_location_latitude' => 'bail|required|min:2|max:30',
            'new_distributor_location_longitude' => 'bail|required|min:2|max:30'
        ]);

        if( $request->has('hidden_distributor_logo') && trim($request->input('hidden_distributor_logo')) == "changed"){
            if( $request->has('uploaded_distributor_image_name') ){
                $logo = $request->input('uploaded_distributor_image_name');
                $logo_name = Auth::user()->id . "__" . $logo;

                $imageFileType = pathinfo("img/avatar/distributor/".$logo_name,PATHINFO_EXTENSION);
                $logo = md5(uniqid()).".".$imageFileType;
                rename("img/avatar/distributor/".$logo_name, "img/avatar/distributor/".$logo);

            }
        }
        else if( $op_type == "edit" && trim($request->input('hidden_distributor_logo')) == "not_changed" && $old_logo != "no_avatar.png"){
            $logo = $old_logo;
        }

        $location_verbal = preg_split("/[0-9]{5}/", $request->input("new_distributor_location_text"));
        $location_verbal = explode(',',$location_verbal[1]);
        $location_verbal = trim($location_verbal[0]);

        $location_json = [
            "text" => $request->input("new_distributor_location_text"),
            "latitude" => $request->input("new_distributor_location_latitude"),
            "longitude" => $request->input("new_distributor_location_longitude"),
            "verbal" => $location_verbal
        ];
        $location_json = json_encode($location_json);

        //save the data
        if( $op_type == "new" ){ // insert new distributor
            $last_insert_id = DB::table('distributors')->insertGetId(
                [
                    'name' => $request->input("new_distributor_name"),
                    'authorized_name' => $request->input("new_distributor_authorized_name"),
                    'email' => $request->input("new_distributor_email"),
                    'gsm_phone' => $request->input("new_distributor_gsm_phone"),
                    'phone' => $request->input("new_distributor_phone"),
                    'fax' => $request->input("new_distributor_fax"),
                    'tax_administration' => $request->input("new_distributor_tax_administration"),
                    'tax_no' => $request->input("new_distributor_tax_no"),
                    'location' => $location_json,
                    'logo' => $logo,
                    'created_by' => $created_by
                ]
            );

            //fire event
            Helper::fire_event("create",Auth::user(),"distributors",$last_insert_id);

            //return insert operation result via global session object
            session(['new_distributor_insert_success' => true]);
        }
        else if( $op_type == "edit" ){ // update distributor's info
            DB::table('distributors')->where('id', $request->input("distributor_edit_id"))
                ->update(
                    [
                        'name' => $request->input("new_distributor_name"),
                        'authorized_name' => $request->input("new_distributor_authorized_name"),
                        'email' => $request->input("new_distributor_email"),
                        'gsm_phone' => $request->input("new_distributor_gsm_phone"),
                        'phone' => $request->input("new_distributor_phone"),
                        'fax' => $request->input("new_distributor_fax"),
                        'tax_administration' => $request->input("new_distributor_tax_administration"),
                        'tax_no' => $request->input("new_distributor_tax_no"),
                        'location' => $location_json,
                        'logo' => $logo
                    ]
                );

            if($old_logo != "no_avatar.png" && $old_logo != "" && trim($request->input('hidden_distributor_logo')) == "changed"){
                unlink("img/avatar/distributor/".$old_logo);
            }

            Helper::clear_unused_img("img/avatar/distributor");

            //fire event
            Helper::fire_event("update",Auth::user(),"distributors",$request->input("distributor_edit_id"));

            //return update operation result via global session object
            session(['distributor_update_success' => true]);
        }

        return redirect()->back();

    }

    public function getAddInfoList($id){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $where_clause = "WHERE A.status<>0 AND A.parent_id=0 ";
        $order_column = "A.created_at";
        $order_dir = "DESC";
        $param_array = array();

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->add_info_columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;

            if( $order_column == "created_by" ){
                $order_column = "U.name";
            }

        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        if( Auth::user()->user_type == 3 && Auth::user()->org_id != $id)
            abort(404);

        $param_array[] =  $id;
        $where_clause .= " AND A.distributor_id = ? ";

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
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM additional_infos A LEFT JOIN users U ON U.id=A.created_by '.$where_clause,$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT A.*, U.name as created_by FROM additional_infos A LEFT JOIN users U ON U.id=A.created_by ' . $where_clause . ' ORDER BY ' . $order_column . ' ' . $order_dir . ' LIMIT ? OFFSET ?', $param_array);

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                $icon = '<i class="fa fa-font fa-2x"></i>';
                $options = "N/A";

                if( $one_row->is_category == 1 ){
                    $icon = '<i class="fa fa-object-group fa-2x"></i>';

                    $option_result = DB::table('additional_infos')
                        ->select(DB::raw("GROUP_CONCAT(name ORDER BY name ASC SEPARATOR ', ') as options"))
                        ->where('parent_id',$one_row->id)
                        ->where('status',1)
                        ->first();
                    $options = $option_result->options;
                }

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "category" => $icon,
                    "name" => $one_row->name,
                    "options" => $options,
                    "created_by" => $one_row->created_by,
                    "created_at" => date('d/m/Y H:i',strtotime($one_row->created_at)),
                    "buttons" => self::createAddInfoButtons($one_row->id)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    private function createAddInfoButtons($id){
        if( !is_numeric($id) ){
            return '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        $ainfo = DB::table('additional_infos')
            ->where('id', $id)
            ->first();

        if( !(COUNT($ainfo)>0 && is_numeric($ainfo->id)) ){
            return '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        $return_text = "";
        $deletable = true;

        if( $ainfo->is_category == 0 ){
            if( Helper::has_right(Auth::user()->operations, "view_distributor_detail") ){
                $return_text .= '
                    <a href="javascript:void(1);" 
                       title="'.trans('distributor_detail.edit').'" 
                       onclick="edit_ainfo('.$id.');"
                       class="btn btn-warning btn-sm">
                            <i class="fa fa-edit fa-lg"></i>
                    </a>';
            }
        }

        $c_result = DB::table('modems')
            ->select(
                DB::raw('MAX(id) as id'),
                DB::raw("GROUP_CONCAT(serial_no SEPARATOR ', ') as name")
            )
            ->whereRaw("json_contains(`additional_info`, '{\"id\" : ".$id."}')")
            ->where('status', '<>', 0)
            ->first();

        if( COUNT($c_result)>0 && is_numeric($c_result->id) ){
            $clients =  $c_result->name;

            $return_text .= '
                    <a href="javascript:void(1);" 
                       title="'.trans('distributor_detail.info').'" 
                       onclick="alertBox(\'\', \''.trans("distributor_detail.not_deletable_c", ["clients" => $clients]).'\',\'info\');" 
                       class="btn btn-info btn-sm">
                            <i class="fa fa-podcast fa-lg"></i>
                    </a>
                   ';

            $deletable = false;
        }

        $r_result = DB::table('reports')
            ->select(
                DB::raw('MAX(id) as id'),
                DB::raw("GROUP_CONCAT(template_name SEPARATOR ', ') as name")
            )
            ->whereRaw("json_contains(`additional_info`, '{\"id\" : ".$id."}')")
            ->whereRaw("json_contains(`detail`, '{\"working_type\" : \"periodic\"}')")
            ->where('is_report', 0)
            ->where('status', '<>', 0)
            ->first();

        if( COUNT($r_result)>0 && is_numeric($r_result->id) ){
            $reports =  $r_result->name;

            $return_text .= '
                    <a href="javascript:void(1);" 
                       title="'.trans('distributor_detail.info').'" 
                       onclick="alertBox(\'\', \''.trans("distributor_detail.not_deletable_r", ["templates" => $reports]).'\',\'info\');" 
                       class="btn btn-info btn-sm">
                            <i class="fa fa-book fa-lg"></i>
                    </a>';

            $deletable = false;
        }

        if( Helper::has_right(Auth::user()->operations, "delete_distributor") && $deletable == true ){
            $return_text .= '
                    <a href="javascript:void(1);" 
                       title="'.trans('distributor_detail.delete').'" 
                       onclick="delete_ainfo('.$id.');"
                       class="btn btn-danger btn-sm">
                            <i class="fa fa-trash-o fa-lg"></i>
                    </a>';
        }

        return $return_text;
    }

    public function createAddInfo(Request $request, $id){
        //existance of id is checked by web.php

        //check if the user has right to do this operation
        if(Auth::user()->user_type == 3 && Auth::user()->org_id != $id)
            abort(404);

        $op_type = "new";
        $edit_id = 0;
        $distributor_id = -1;
        $old_is_category = 0;
        $name_validator = 'bail|required|unique:additional_infos,name,NULL,id,distributor_id,'.$id.'|max:100|min:3';

        if( $request->has('ainfo_op_type') && $request->input('ainfo_op_type') == "edit" ) {
            if( $request->has('ainfo_edit_id') && is_numeric($request->input('ainfo_edit_id')) ){
                $edit_id = $request->input('ainfo_edit_id');

                $result = DB::table('additional_infos')
                    ->where('status', '<>', 0)
                    ->where('id', $edit_id)
                    ->first();

                if( COUNT($result)>0 && is_numeric($result->id)){
                    if( Auth::user()->user_type == 3 && Auth::user()->org_id != $result->distributor_id ){
                        abort(404);
                    }

                    $distributor_id = $result->distributor_id;
                    $old_is_category = $result->is_category;
                }
                else{
                    abort(404);
                }
            }
            else{
                abort(404);
            }

            $op_type = "edit";
            $name_validator = 'bail|required|unique:additional_infos,name,'.$request->input("ainfo_edit_id").',id,distributor_id,'.$id.'|max:255|min:3';
        }

        $this->validate(
            $request,
            [
                'new_ainfo_name' => $name_validator
            ]
        );

        $is_category = 0;
        $new_options = array();

        if($request->has("ainfo_is_category") && $request->input("ainfo_is_category") == "on"){
            $is_category = 1;

            if($request->has("ainfo_options") && count($request->input("ainfo_options"))<1){
                abort(404);
            }

            $new_options = $request->input("ainfo_options");
        }

        if( $op_type == "new" ){ // insert new ainfo
            $last_insert_id = DB::table('additional_infos')->insertGetId(
                [
                    'name' => $request->input("new_ainfo_name"),
                    'distributor_id' => $id,
                    'is_category' => $is_category,
                    'created_by' => Auth::user()->id
                ]
            );

            if($is_category == 1 ){
                foreach($new_options as $one_option){
                    DB::table('additional_infos')->insert(
                        [
                            'name' => $one_option,
                            'distributor_id' => $id,
                            'created_by' => Auth::user()->id,
                            'parent_id' => $last_insert_id
                        ]
                    );
                }
            }

            //fire event
            Helper::fire_event("create", Auth::user(), "additional_infos", $last_insert_id);

            //return insert operation result via global session object
            session(['new_ainfo_insert_success' => true]);
        }
        else if( $op_type == "edit" ){ // update ainfo
            $updated_field = array(
                'name' => $request->input('new_ainfo_name')
            );

            // It was a category
            if( $old_is_category == 1 ){
                // get the old options
                $old_options = DB::table('additional_infos')
                    ->select('name')
                    ->where('status', '<>', 0)
                    ->where('parent_id', $edit_id)
                    ->where('distributor_id', $distributor_id)
                    ->get();

                if( COUNT($old_options)>0 && $old_options[0]->name != "" ){
                    $old_options_name = array();

                    foreach ($old_options as $one_op){
                        $old_options_name[$one_op->name] = $one_op->name;
                    }

                    // We want to update it as a category again
                    if( $is_category == 1 ){
                        foreach ($new_options as $one_op){
                            // Don't insert pre-existing ones!
                            if( isset($old_options_name[$one_op]) ){
                                unset($old_options_name[$one_op]);
                            }
                            else{ // insert new option to db
                                DB::table('additional_infos')->insert(
                                    [
                                        'name' => $one_op,
                                        'distributor_id' => $distributor_id,
                                        'created_by' => Auth::user()->id,
                                        'parent_id' => $edit_id
                                    ]
                                );
                            }
                        }
                    }
                    else{ // We want to update it as an info
                        $updated_field['is_category'] = 0;
                    }

                    // Delete old options those aren't in the new options
                    if( COUNT($old_options_name)>0 ){
                        foreach ($old_options_name as $one_name){
                            DB::table('additional_infos')
                                ->where('name', $one_name)
                                ->where('parent_id', $edit_id)
                                ->where('distributor_id', $distributor_id)
                                ->delete();
                        }
                    }
                }
                else{
                    return "ERROR_1";
                }
            }
            else{ // It was not a category
                // But now, we want to save it as a category
                if( $is_category == 1 ){
                    // insert all of the new options to db
                    foreach ($new_options as $one_op){
                        DB::table('additional_infos')->insert(
                            [
                                'name' => $one_op,
                                'distributor_id' => $distributor_id,
                                'created_by' => Auth::user()->id,
                                'parent_id' => $edit_id
                            ]
                        );
                    }

                    $updated_field['is_category'] = 1;
                }
            }

            // update name and is_category
            DB::table('additional_infos')
                ->where('id', $edit_id)
                ->update($updated_field);

            //fire event
            Helper::fire_event("update", Auth::user(), "additional_infos", $edit_id);

            //return update operation result via global session object
            session(['ainfo_update_success' => true]);
        }

        return redirect()->back();
    }

    public function deleteAddInfo(Request $request){
        if( $request->has('id') && is_numeric($request->input('id')) ){
            $result = DB::table('additional_infos')
                ->where('status', '<>', 0)
                ->where('id', $request->input('id'))
                ->first();

            if( COUNT($result)>0 && is_numeric($result->id) ){
                if( Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id ){
                    abort(404);
                }

                // Is this ainfo used in client or report template?
                $c_result = DB::table('modems')
                    ->select(
                        DB::raw('MAX(id) as id'),
                        DB::raw("GROUP_CONCAT(serial_no SEPARATOR ', ') as name")
                    )
                    ->whereRaw("json_contains(`additional_info`, '{\"id\" : ".$result->id."}')")
                    ->where('status', '<>', 0)
                    ->first();

                if( COUNT($c_result)>0 && is_numeric($c_result->id) ){
                    abort(404);
                }

                $r_result = DB::table('reports')
                    ->select(
                        DB::raw('MAX(id) as id'),
                        DB::raw("GROUP_CONCAT(template_name SEPARATOR ', ') as name")
                    )
                    ->whereRaw("json_contains(`additional_info`, '{\"id\" : ".$result->id."}')")
                    ->whereRaw("json_contains(`detail`, '{\"working_type\" : \"periodic\"}')")
                    ->where('is_report', 0)
                    ->where('status', '<>', 0)
                    ->first();

                if( COUNT($r_result)>0 && is_numeric($r_result->id) ){
                    abort(404);
                }

                /*
                DB::table('additional_infos')
                    ->where('id', $request->input("id"))
                    ->update(
                        [
                            'status' => 0
                        ]
                    ); */

                DB::table('additional_infos')
                    ->where('id', $request->input("id"))
                    ->orWhere('parent_id', $request->input("id"))
                    ->delete();

                //fire event
                Helper::fire_event("delete", Auth::user(), "additional_infos", $request->input("id"));

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
        if( $request->has('id') && is_numeric($request->input('id')) ) {
            $result = DB::table('additional_infos')
                ->where('status', '<>', 0)
                ->where('id', $request->input('id'))
                ->first();

            if (COUNT($result) > 0 && is_numeric($result->id)) {
                if (Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id) {
                    abort(404);
                }

                if( $result->is_category == 1 ){ // get options

                    // Is this ainfo used in client or report template?
                    $c_result = DB::table('modems')
                        ->select(
                            DB::raw('MAX(id) as id'),
                            DB::raw("GROUP_CONCAT(serial_no SEPARATOR ', ') as name")
                        )
                        ->whereRaw("json_contains(`additional_info`, '{\"id\" : ".$result->id."}')")
                        ->where('status', '<>', 0)
                        ->first();

                    if( COUNT($c_result)>0 && is_numeric($c_result->id) ){
                        abort(404);
                    }

                    $r_result = DB::table('reports')
                        ->select(
                            DB::raw('MAX(id) as id'),
                            DB::raw("GROUP_CONCAT(template_name SEPARATOR ', ') as name")
                        )
                        ->whereRaw("json_contains(`additional_info`, '{\"id\" : ".$result->id."}')")
                        ->whereRaw("json_contains(`detail`, '{\"working_type\" : \"periodic\"}')")
                        ->where('is_report', 0)
                        ->where('status', '<>', 0)
                        ->first();

                    if( COUNT($r_result)>0 && is_numeric($r_result->id) ){
                        abort(404);
                    }

                    $options = DB::table('additional_infos')
                        ->select(
                            DB::raw('GROUP_CONCAT(name SEPARATOR \',\') as options')
                        )
                        ->where('status', '<>', 0)
                        ->where('parent_id', $request->input('id'))
                        ->first();

                    if( COUNT($options)>0 && $options->options != "" ){
                        $options = explode(",", $options->options);
                        $result->options = json_encode($options);
                    }
                    else{
                        return "ERROR";
                    }
                }

                return json_encode($result);
            }
            else{
                abort(404);
            }
        }
        else{
            abort(404);
        }
    }

}
