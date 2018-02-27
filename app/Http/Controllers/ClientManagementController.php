<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;

class ClientManagementController extends Controller
{
    private $columns;
    private $user_columns;
    private $modem_columns;
    private $device_columns;
    private $alerts_columns;

    public function __construct()
    {
        $this->columns = array(
            "logo"=>array("orderable"=>false,"name"=>false),
            "name"=>array(),
            "authorized_name"=>array(),
            "distributor"=>array(), // add visible options according to user type
            "email"=>array(),
            "gsm_phone"=>array(),
            "location"=>array(),
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
            "device_type" =>array("name"=>"type"),
            "modem_no" => array(),
            "client" => array( "visible" => false),
            "inductive" => array(),
            "capacitive" => array(),
            "data_period" => array(),
            "last_data_at" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );

        $this->alerts_columns = array(
            "icon" => array("orderable" => false, "name" => false),
            "type" => array(),
            "device_no" => array( "name" => "device_no_type"),
            "notification_method" => array("orderable" => false),
            "client" => array("visible" => false, "name" => "client_distributor"),
            "created_at"=>array(),
            "buttons"=>array( "orderable" => false, "name" => "operations", "nowrap" => true)
        );

    }

    public function showTable(Request $request){
        $prefix = "cm";
        $url = "cm_get_data";
        $default_order = '[7,"desc"]';

        if(Auth::user()->user_type == 3){
            $this->columns["distributor"]["visible"] = false;
        }
        $data_table = new DataTable($prefix,$url,$this->columns,$default_order,$request);

        return view('pages.client_management')->with("DataTableObj",$data_table);
    }

    public function getData( $detail_type="", $detail_org_id="" ){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();
        $where_clause = "WHERE C.status<>0 ";

        if( $detail_type == "distributor" ){
            if( !is_numeric($detail_org_id) ){
                abort(404);
            }

            $param_array[] = $detail_org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }

        //Add filter according to user type
        if(Auth::user()->user_type == 3){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        $order_column = "C.created_at";
        $order_dir = "DESC";

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;

            if($order_column =="location"){
                $order_column = "JSON_UNQUOTE(json_extract(C.location,'$.verbal'))";
            }
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(C.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "C.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR C.email LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR C.authorized_name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR C.gsm_phone LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";
                $where_clause .= " OR lcase(JSON_UNQUOTE(json_extract(C.location,'$.verbal'))) LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";
                $where_clause .= " OR lcase(JSON_UNQUOTE(json_extract(C.location,'$.text'))) LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR D.name LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM clients C LEFT JOIN distributors D ON C.distributor_id=D.id '.$where_clause,$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT C.*, D.name as distributor,D.id as distributor_id, (CASE WHEN D.created_by=0 THEN "'.trans("global.system").'" ELSE U.name END) as created_by_name, MS.modem_name as modems, JSON_UNQUOTE(json_extract(C.location,\'$.verbal\')) as location_verbal, JSON_UNQUOTE(json_extract(C.location,\'$.text\')) as location_text FROM clients C LEFT JOIN distributors D ON C.distributor_id=D.id LEFT JOIN (SELECT M.client_id as client_id,GROUP_CONCAT(M.serial_no ORDER BY M.serial_no SEPARATOR \', \') as modem_name FROM modems M WHERE M.status<>0 GROUP BY M.client_id) MS ON MS.client_id=C.id LEFT JOIN users U ON U.id=C.created_by '.$where_clause.' ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?',$param_array);

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                $logo = "<img  style='border-radius:50%;width:50px;height:50px;' src='/img/avatar/client/".$one_row->logo."' />";

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "logo" => $logo,
                    "name" => $one_row->name,
                    "authorized_name" => $one_row->authorized_name,
                    "distributor" => ($one_row->distributor == "" ? trans("global.main_distributor") : "<a href='/distributor_management/detail/" . $one_row->distributor_id . "' target='_blank' title='" . trans('user_management.show_distributor_detail') . "'>" . $one_row->distributor . "</a>"),
                    "email" => $one_row->email,
                    "gsm_phone" => $one_row->gsm_phone,
                    "phone" => $one_row->phone,
                    "fax" => $one_row->fax,
                    "location" => "<span data-toggle='tooltip' data-placement='bottom' title='" . $one_row->location_text . "'>" . $one_row->location_verbal . "</span>",
                    "created_at" => "<span data-toggle='tooltip' data-placement='bottom' title='". trans('distributor_management.created_by') . ": " . $one_row->created_by_name . "'>" . date('d/m/Y H:i',strtotime($one_row->created_at)) . "</span>",
                    "buttons" => self::create_buttons($one_row->id, $one_row->modems, $detail_type)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function create_buttons($item_id, $modems, $detail_type){
        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_client_detail")){
            $return_value .= '<a href="/client_management/detail/'.$item_id.'" title="'.trans('client_management.detail').'" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
        }

        if($detail_type == "") {
            if (Helper::has_right(Auth::user()->operations, "add_new_client")) {
                $return_value .= '<a href="javascript:void(1);" title="' . trans('client_management.edit') . '" onclick="edit_client(' . $item_id . ');" class="btn btn-warning btn-sm"><i class="fa fa-edit fa-lg"></i></a> ';
            }

            if (Helper::has_right(Auth::user()->operations, "delete_client")) {
                $return_value .= '<a ' . ($modems != "" ? "style=\"opacity:0.4;\"" : "") . ' href="javascript:void(1);" title="' . trans('client_management.delete_client') . '" onclick="' . ($modems != "" ? "alertBox('','<b>[" . $modems . "]</b> " . trans("client_management.not_deletable") . "','info');" : "delete_client(" . $item_id . ");") . '" class="btn btn-danger btn-sm"><i class="fa fa-trash-o fa-lg"></i></a> ';
            }
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    public function getInfo(Request $request){
        if($request->has("id") && is_numeric($request->input("id"))){

            $result = DB::table("clients")->where('status','<>',0)->where("id",$request->input("id"))->first();

            if(isset($result->id)){

                if(Auth::user()->user_type == 3){
                    if($result->distributor_id != Auth::user()->org_id)
                        return "ERROR";
                }

                echo json_encode($result);
            }

        }
        else{
            echo "NEXIST";
        }
    }

    public function uploadImage(Request $request){
        $uploadOk = 1;
        $message ="";

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["new_client_logo"]["tmp_name"]);
            if($check === false) {
                $message = "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Allow certain file formats
        $imageFileType = strtolower(pathinfo($_FILES["new_client_logo"]["name"],PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if file already exists
        /*if ( file_exists( "img/avatar/client/".md5_file($_FILES["new_client_logo"]["tmp_name"]).".".$imageFileType ) ) {
            $message = "Sorry, file already exists.";
            $uploadOk = 0;
        }*/

        // Check file size
        if ($_FILES["new_client_logo"]["size"] > 2048000) { //can't be larger than 2 MB
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo $message;
        }
        else {
            if ( move_uploaded_file( $_FILES['new_client_logo']['tmp_name'], "img/avatar/client/".Auth::user()->id . "__" . $_FILES["new_client_logo"]["name"]) ) {
                echo "{}";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    public function create(Request $request){
        $op_type = "new";
        $logo = "no_avatar.png";
        $old_logo = "";
        $created_by = Auth::user()->id;
        $name_validator = 'bail|required|unique:clients,name|max:255|min:3';
        $distributor_id = 0;
        $org_schema_id = 0;


        // if the user is an admin => set distributor_id to selected distributor on the form
        if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ) ){
            if( $request->has('new_client_distributor') && is_numeric($request->input('new_client_distributor')) ){
                $distributor_id = $request->input('new_client_distributor');
            }
            else{
                abort(404);
            }
        }
        else if( Auth::user()->user_type == 3 ){ // if the user is a distributor => set distributor_id to user organization_id
            $distributor_id = Auth::user()->org_id;
        }
        else if( Auth::user()->user_type == 4 ){
            if( $request->input('client_op_type') == "edit" && $request->has('client_edit_profile') && $request->input('client_edit_profile') == "yes" ) {
                if ($request->has('new_client_distributor') && is_numeric($request->input('new_client_distributor'))) {
                    $distributor_id = $request->input('new_client_distributor');
                } else {
                    abort(404);
                }
            }
            else{
                abort(404);
            }
        }
        else{ // in other case, new client can not be created
            abort(404);
        }


        //handle organization tree
        if( $request->has('hdn_org_tree_id') && is_numeric($request->input('hdn_org_tree_id')) ){
            $org_schema_id = $request->input('hdn_org_tree_id');

            if( $distributor_id != 0 ){
                $org_dist = DB::table('organization_schema as O')
                    ->select('O.distributor_id')
                    ->where('O.id', $org_schema_id)
                    ->where('status', '<>', 0)
                    ->first();

                // @TODO: Maybe it could be controlled whether this isn't a leaf node

                if( COUNT($org_dist)>0 && is_numeric($org_dist->distributor_id) ){
                    if( $org_dist->distributor_id != $distributor_id ){
                        abort(404);
                    }
                }
                else{
                    abort(404);
                }
            }
        }


        if( $request->has('client_op_type') && $request->input('client_op_type') == "edit") {
            $op_type = "edit";

            if( $request->has('client_edit_id') && is_numeric($request->input("client_edit_id")) ){
                $result = DB::table("clients")
                    ->where("id", $request->input("client_edit_id"))
                    ->where('status', '<>', 0)
                    ->first();

                if( isset($result->id) && is_numeric($result->id) ) {
                    // If the request is to update the profile information
                    // The distributor is Auth user's own distributor
                    if( Auth::user()->user_type == 4 ){
                        if( Auth::user()->org_id != $result->id ) {
                            abort(404);
                        }

                        $org_schema_id = $result->org_schema_id;
                    }
                    else if(Auth::user()->user_type == 3){ //One distributor may only edit its own client info
                        if( $result->distributor_id != Auth::user()->org_id ){
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

            $name_validator = 'bail|required|unique:clients,name,'.$request->input("client_edit_id").',id|max:255|min:3';
        }

        $this->validate($request, [
            'new_client_name' => $name_validator,
            'new_client_authorized_name' => 'bail|min:3|max:255',
            'new_client_email' => 'bail|required|email|min:3|max:255',
            'new_client_gsm_phone' => 'bail|required|min:7|max:20',
            'new_client_phone' => 'bail|min:7|max:20',
            'new_client_fax' => 'bail|min:7|max:20',
            'new_client_tax_administration' => 'bail|min:3|max:255',
            'new_client_tax_no' => 'bail|min:3|max:30',
            'new_client_location_text' => 'bail|required|min:3|max:255',
            'new_client_location_latitude' => 'bail|required|min:2|max:30',
            'new_client_location_longitude' => 'bail|required|min:2|max:30'
        ]);

        if( $request->has('hidden_client_logo') && trim($request->input('hidden_client_logo')) == "changed"){
            if( $request->has('uploaded_client_image_name') ){
                $logo = $request->input('uploaded_client_image_name');
                $logo_name = Auth::user()->id . "__" . $logo;

                $imageFileType = pathinfo("img/avatar/client/".$logo_name,PATHINFO_EXTENSION);
                $logo = md5(uniqid()).".".$imageFileType;
                rename("img/avatar/client/".$logo_name, "img/avatar/client/".$logo);
            }
        }
        else if( $op_type == "edit" && trim($request->input('hidden_client_logo')) == "not_changed" && $old_logo != "no_avatar.png"){
            $logo = $old_logo;
        }

        $location_verbal = preg_split("/[0-9]{5}/", $request->input("new_client_location_text"));
        $location_verbal = explode(',',$location_verbal[1]);
        $location_verbal = trim($location_verbal[0]);

        $location_json = [
            "text" => $request->input("new_client_location_text"),
            "latitude" => $request->input("new_client_location_latitude"),
            "longitude" => $request->input("new_client_location_longitude"),
            "verbal" => $location_verbal
        ];
        $location_json = json_encode($location_json);

        //save the data
        if( $op_type == "new" ){ // insert new client
            $last_insert_id = DB::table('clients')->insertGetId(
                [
                    'name' => $request->input("new_client_name"),
                    'authorized_name' => $request->input("new_client_authorized_name"),
                    'email' => $request->input("new_client_email"),
                    'gsm_phone' => $request->input("new_client_gsm_phone"),
                    'phone' => $request->input("new_client_phone"),
                    'fax' => $request->input("new_client_fax"),
                    'tax_administration' => $request->input("new_client_tax_administration"),
                    'tax_no' => $request->input("new_client_tax_no"),
                    'location' => $location_json,
                    'logo' => $logo,
                    'distributor_id' => $distributor_id,
                    'org_schema_id' => $org_schema_id,
                    'created_by' => $created_by
                ]
            );

            //fire event
            Helper::fire_event("create",Auth::user(),"clients",$last_insert_id);
            //return insert operation result via global session object
            session(['new_client_insert_success' => true]);
        }
        else if( $op_type == "edit" ){ // update client's info
            DB::table('clients')->where('id', $request->input("client_edit_id"))
                ->update(
                    [
                        'name' => $request->input("new_client_name"),
                        'authorized_name' => $request->input("new_client_authorized_name"),
                        'email' => $request->input("new_client_email"),
                        'gsm_phone' => $request->input("new_client_gsm_phone"),
                        'phone' => $request->input("new_client_phone"),
                        'fax' => $request->input("new_client_fax"),
                        'tax_administration' => $request->input("new_client_tax_administration"),
                        'tax_no' => $request->input("new_client_tax_no"),
                        'location' => $location_json,
                        'distributor_id' => $distributor_id,
                        'org_schema_id' => $org_schema_id,
                        'logo' => $logo
                    ]
                );

            if($old_logo != "no_avatar.png" && $old_logo != "" && trim($request->input('hidden_client_logo')) == "changed"){
                unlink("img/avatar/client/".$old_logo);
            }

            Helper::clear_unused_img("img/avatar/client");

            //fire event
            Helper::fire_event("update",Auth::user(),"clients",$request->input("client_edit_id"));

            //return update operation result via global session object
            session(['client_update_success' => true]);
        }

        return redirect()->back();
    }

    public function delete(Request $request){

        if(!($request->has("id") && is_numeric($request->input("id"))))
            return "ERROR";

        $result = DB::select('SELECT C.distributor_id as distributor,MS.modem_name as modem_name FROM clients C LEFT JOIN distributors D ON C.distributor_id=D.id LEFT JOIN (SELECT M.client_id as client_id,GROUP_CONCAT(M.serial_no ORDER BY M.serial_no SEPARATOR \', \') as modem_name FROM modems M WHERE M.status<>0 GROUP BY M.client_id) MS ON MS.client_id=C.id WHERE C.id=?',array($request->input("id")));


        if(trim($result[0]->modem_name) == ""){

            if((Auth::user()->user_type == 3 && Auth::user()->org_id == $result[0]->distributor) || Auth::user()->user_type==1 || Auth::user()->user_type == 2 ){

                DB::table('clients')->where('id', $request->input("id"))
                    ->update(
                        [
                            'status' => 0
                        ]
                    );

                //fire event
                Helper::fire_event("delete",Auth::user(),"clients",$request->input("id"));

                session(['client_delete_success' => true]);
                return "SUCCESS";
            }
            else{
                return "ERROR";
            }

        }
        else
            return "ERROR";
    }

    public function clientDetail(Request $request, $id){
        $the_client = DB::table("clients as C")
            ->select(
                "C.*",
                DB::raw('(CASE WHEN C.distributor_id=0 THEN "'.trans("global.main_distributor").'" ELSE D.name END) as distributor'),
                DB::raw("JSON_UNQUOTE(json_extract(C.location,'$.text')) as location_text"),
                'U.name as created_by'
            )
            ->leftJoin("distributors as D","D.id","C.distributor_id")
            ->join('users as U', 'U.id', 'C.created_by')
            ->where("C.id",$id)
            ->where('C.status','<>',0)
            ->first();

        //prepare user table obj which belongs to this client
        $prefix = "cdu";
        $url = "cdu_get_data/client/".$id;
        $default_order = '[5,"desc"]';
        $user_data_table = new DataTable($prefix,$url, $this->user_columns, $default_order,$request);
        $user_data_table->set_add_right(false);

        // prepare modem table obj which belongs to this client
        $prefix = "cdm";
        $url = "cdm_get_data/client/".$id;
        $default_order = '[6,"desc"]';
        $modem_data_table = new DataTable($prefix,$url, $this->modem_columns, $default_order,$request);
        $modem_data_table->set_add_right(false);

        // prepare device table obj which belongs to this client
        $prefix = "cdd";
        $url = "cdd_get_data/all_devices/client/".$id;
        $default_order = '[7,"desc"]';
        $device_data_table = new DataTable($prefix,$url, $this->device_columns, $default_order,$request);
        $device_data_table->set_add_right(false);

        //prepare alerts table obj which belongs to this modem
        $prefix = "cdal";
        $url = "al_get_data/".$request->segment(1)."/".$id;
        $default_order = '[5,"desc"]';
        $alert_table = new DataTable($prefix, $url, $this->alerts_columns, $default_order, $request);
        $alert_table->set_add_right(false);
        $alert_table->set_lang_page("alerts");

        //get event logs table from eventlogController
        $eventsTable = new EventlogsController();
        $eventsTable = $eventsTable->prepareEventTableObject($request,"client",$id);

        return view('pages.client_detail', [
                'the_client' => json_encode($the_client),
                'UserDataTableObj' => $user_data_table,
                'ModemDataTableObj' => $modem_data_table,
                'DeviceDataTableObj' => $device_data_table,
                'EventDataTableObj' => $eventsTable,
                'AlertsDataTableObj' =>$alert_table
            ]
        );
    }

}

