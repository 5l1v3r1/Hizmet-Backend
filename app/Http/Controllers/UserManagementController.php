<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\User;
use Illuminate\Http\Request;
use App\Helpers\DataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;

class UserManagementController extends Controller
{
    private $columns;

    public function __construct()
    {
        $this->columns = array(
            "avatar"=>array("orderable"=>false,"name"=>false),
            "name"=>array(),
            "user_type"=>array(),
            "org_name"=>array(),
            "email"=>array(),
            "status"=>array("orderable"=>false),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );
    }

    public function showTable(Request $request){
        $prefix = "um";
        $url = "um_get_data";
        $default_order = '[6,"desc"]';
        $data_table = new DataTable($prefix,$url,$this->columns,$default_order,$request);
        return view('pages.user_management')->with("DataTableObj",$data_table);
    }

    /**
     * Return user info to show user detail page
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function userDetail(Request $request, $id, $op="showDetail"){
        // Auth user may want to view own profile page. Go profile page
        if( $op == "showDetail" && Auth::user()->id == $id ){
            return redirect('/my_profile');
        }

        // Is Auth user authorized to view/edit the requested user's detail
        // Auth user's org_id must be equal to porg_id of the requested user
        $requested_user = DB::table('users')
                        ->select('porg_id')
                        ->where('id',$id)
                        ->where('status', '<>', 0)
                        ->first();

        if( !isset($requested_user->porg_id) || (Auth::user()->user_type != 1 && Auth::user()->user_type != 2 && Auth::user()->org_id != $requested_user->porg_id) ){
            abort(404);
        }

        if( $op == "showDetail" ){
            $the_user = DB::table('users as U')
                ->select(DB::raw('U.*, UT.type as type, UC.id as created_id, UC.name as created_by, (CASE WHEN U.user_type=3 THEN D.name WHEN U.user_type=4 THEN C.name ELSE "'.trans('global.main_distributor').'" END) as org_name'))
                ->join('user_type as UT', 'U.user_type', 'UT.id')
                ->leftJoin('distributors as D', 'U.org_id', 'D.id')
                ->leftJoin('clients as C', 'U.org_id', 'C.id')
                ->leftJoin('users as UC', 'U.created_by', 'UC.id')
                ->where('U.id',$id)
                ->where("U.status",'<>', 0)
                ->first();


            //get event logs table from eventlogController
            $eventsTable = new EventlogsController();
            $eventsTable = $eventsTable->prepareEventTableObject($request,"user",$id);
            return view('pages.user_detail', ['the_user' => json_encode($the_user),'EventDataTableObj'=>$eventsTable]);
        }
        else if( $op == "changeStatus" ){
            if(!(Helper::has_right(Auth::user()->operations,'change_user_status'))){
                return "ERROR";
            }

            if( !($id == $request->input('id') && ($request->input('status') == 1 || $request->input('status') == 2)) ){
                return "ERROR";
            }

            DB::table('users')
                ->where('id', $request->input("id"))
                ->where('status', '<>', 0)
                ->update(
                    [
                        'status' => $request->input("status")
                    ]
                );

            //return update operation result via global session object
            if($request->input('status') == 1) {

                //fire event
                Helper::fire_event("user_status_activated",Auth::user(),"users",$request->input("id"));

                session(['user_status_activated' => true]);
            }
            else {

                //fire event
                Helper::fire_event("user_status_deactivated",Auth::user(),"users",$request->input("id"));

                session(['user_status_deactivated' => true]);
            }

            return "SUCCESS";
        }
        else if( $op == "deleteUser" ){
            if( $id != $request->input('id') ){
                return "ERROR";
            }

            if(!(Helper::has_right(Auth::user()->operations,'delete_user'))){
                return "ERROR";
            }

            DB::table('users')
                ->where('id', $request->input("id"))
                ->where('status', '<>', 0)
                ->update(
                    [
                        'status' => 0
                    ]
                );

            //fire event
            Helper::fire_event("delete",Auth::user(),"users",$request->input("id"));

            //return update operation result via global session object
            session(['user_delete_success' => true]);

            return "SUCCESS";
        }
        else if($op == "changeAuthorization"){
            $the_operations = trim(str_replace('_op',',',$request->input("operations")),',');

            $default_operations = DB::table("user_type as UT")
                            ->select('UT.default_operations')
                            ->join('users as U','U.user_type','=','UT.id')
                            ->where('U.id',$request->input('id'))
                            ->where('U.status', '<>', 0)
                            ->first();

            $default_operations = $default_operations->default_operations;

            if($default_operations != '["all"]'){
               $default_operations = json_decode($default_operations);
               $default_operations = array_flip($default_operations);

               $arr_ops = explode(',',$the_operations);
               foreach ($arr_ops as $arr_op) {
                   if(!isset($default_operations[$arr_op])){
                       return "ERROR";
                   }
               }
            }

            if (ctype_digit(str_replace(',','',$the_operations))){
                $the_operations = "[" . $the_operations . "]";

                DB::table('users')
                    ->where('id', $request->input("id"))
                    ->where('status', '<>', 0)
                    ->update(
                        [
                            'operations' => $the_operations
                        ]
                    );

                //fire event
                Helper::fire_event("user_change_authorization",Auth::user(),"users",$request->input("id"));

                //return update operation result via global session object
                session(['user_change_authorization' => true]);

                return "SUCCESS";
            }

            return "ERROR";

        }
        else {
            abort(404);
        }
    }

    public function getData($detail_type="", $detail_org_id=""){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();

        $where_clause = "WHERE 1=1 ";

        if($detail_type == "client"){
            if( !is_numeric($detail_org_id) ){
                abort(404);
            }
            $param_array[] = $detail_org_id;
            $where_clause .= " AND U.org_id=? AND U.user_type=4 ";
        }
        else if( $detail_type == "distributor" ){
            if( !is_numeric($detail_org_id) ){
                abort(404);
            }
            $param_array[] = $detail_org_id;
            $where_clause .= " AND U.porg_id=? AND (U.user_type=4 OR U.user_type=3) ";
        }

        if( Auth::user()->user_type == 3){
            $param_array[]= Auth::user()->org_id;
            $where_clause .= " AND U.porg_id = ? ";

            $param_array[]= 4;
            $where_clause .= " AND U.user_type = ? ";
        }
        else if(Auth::user()->user_type == 2){
            $param_array[]= 2;
            $where_clause .= " AND U.user_type <> ? ";
        }

        $order_column = "created_at";
        $order_dir = "DESC";

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
            if($order_column == "user_type"){
                $order_column = "type";
            }
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(U.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(trim($search_value) == ""){

            }
            else{
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "U.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR U.email LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR UT.type LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR D.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR C.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR M.name LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        $user_types = Helper::user_type_virtual_table();
        $main_distributor = trans('global.main_distributor');

        $total_count = DB::select('SELECT count(*) as total_count FROM '.$user_types.' as  UT, users U LEFT JOIN distributors D ON D.id=U.org_id LEFT JOIN clients C ON C.id=U.org_id LEFT JOIN (SELECT "'.$main_distributor.'" as name) M ON U.user_type=2 '.$where_clause.' AND U.user_type = UT.id AND U.user_type<>1 AND U.status<>0',$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT U.*, UT.type as type, UT.id as user_type_id, (CASE WHEN U.created_by!=0 THEN UU.name ELSE "'.trans('global.system').'" END) as created_by_name, (CASE WHEN U.user_type=3 THEN D.name WHEN U.user_type=4 THEN C.name ELSE "'.$main_distributor.'" END) as org_name, (CASE WHEN U.user_type=3 THEN D.id WHEN U.user_type=4 THEN C.id ELSE 0 END) as org_id FROM '.$user_types.' UT, users U LEFT JOIN distributors D ON D.id=U.org_id LEFT JOIN clients C ON C.id=U.org_id LEFT JOIN (SELECT "'.$main_distributor.'" as name) M ON U.user_type=2 LEFT JOIN users UU ON UU.id=U.created_by '.$where_clause.' AND U.user_type = UT.id AND U.user_type<>1 AND U.status<>0 ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?',$param_array);

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                $avatar = "<img class='img-responsive' style='border-radius:50%;height:50px;width:50px;' src='/img/avatar/user/".$one_row->avatar."' />";

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "avatar" => $avatar,
                    "name" => $one_row->name,
                    "user_type" => $one_row->type,
                    "email" => $one_row->email,
                    "org_name" => 'sistem',
                    "status" => trans("global.status_" . $one_row->status),
                    "created_at" => "<span data-toggle='tooltip' data-placement='bottom' title='". trans('user_management.created_by') . ": " . $one_row->created_by_name . "'>" . date('d/m/Y H:i',strtotime($one_row->created_at)) . "</span>",
                    "buttons" => self::create_buttons($one_row->id, Auth::user()->operations, $detail_type));

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }


    /**
     * Create operations column's button for each user in the user management page
     *
     * @param $user_id
     * @param $user_operations
     * @return string
     */
    private function create_buttons($user_id, $user_operations,$detail_type){

        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_user_detail")){
            $return_value .= '<a href="/user_management/detail/'.$user_id.'" title="'.trans('user_management.user_detail').'" class="btn btn-info btn-sm"><i class="fa fa-user fa-lg"></i></a> ';
        }

        if($detail_type == ""){
            if(Helper::has_right(Auth::user()->operations, "add_new_user")){
                $return_value .= '<a href="javascript:void(1);" title="'.trans('user_management.edit_user').'" onclick="edit_user('.$user_id.');" class="btn btn-warning btn-sm"><i class="fa fa-edit fa-lg"></i></a>';
            }
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    /**
     * Save user avatar
     *
     * @param Request $request
     */
    public function uploadImage(Request $request){
        $uploadOk = 1;
        $message ="";

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["new_user_logo"]["tmp_name"]);
            if($check === false) {
                $message = "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Allow certain file formats
        $imageFileType = strtolower(pathinfo($_FILES["new_user_logo"]["name"],PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if file already exists
        /*if ( file_exists( "img/avatar/user/".md5_file($_FILES["new_user_logo"]["tmp_name"]).".".$imageFileType ) ) {
            $message = "Sorry, file already exists.";
            $uploadOk = 0;
        }*/

        // Check file size
        if ($_FILES["new_user_logo"]["size"] > 2048000) { //can't be larger than 2 MB
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo $message;
        }
        else {
            if ( move_uploaded_file( $_FILES['new_user_logo']['tmp_name'], "img/avatar/user/".Auth::user()->id . "__" . $_FILES["new_user_logo"]["name"]) ) {
                echo "{}";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    /**
     * Return user info to edit in the user management page
     *
     * @param Request $request
     * @return string
     */
    public function getInfo(Request $request){
        if($request->has("id") && is_numeric($request->input("id"))){
            $result = DB::table("users")
                            ->where("id",$request->input("id"))
                            ->where('status','<>',0)
                            ->first();

            if(isset($result->id)){
                if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){
                }
                else if( (!Auth::user()->user_type == 3 && Auth::user()->org_id == $result->porg_id) ){
                    return "ERROR";
                }

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

    /**
     * Edit user info in the profile page
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editProfileInfo(Request $request){

        $logo = "no_avatar.png";
        $old_logo = "";
        $password = "";
        $password_validator = 'bail|required|min:6|max:20';

        if( $request->has('user_edit_id') && is_numeric($request->input("user_edit_id")) && strlen($request->input("user_edit_id")) < 6 ){


            //by Kadir 18 November 2016
            if(Auth::user()->id != $request->input("user_edit_id"))
                abort(404);

            $result = DB::table("users")
                    ->where("id",$request->input("user_edit_id"))
                    ->where('status', '<>', 0)
                    ->first();

            if( isset($result->avatar)){
                $old_logo = $result->avatar;
                $password = $result->password;
            }
        }
        else{
            return redirect()->back();
        }

        if( !$request->exists('new_user_password') || ($request->exists('new_user_password') && trim($request->input('new_user_password')) == "") ){
            $password_validator = 'bail|min:6|max:20';
        }
        else if( $request->has('new_user_password') ){
            $password = bcrypt($request->input("new_user_password"));
        }
        else{
            return redirect()->back();
        }

        $this->validate($request, [
            'new_user_name' => 'bail|required|min:3|max:255',
            'new_user_email' => 'bail|required|email|max:255|unique:users,email,'.$request->input("user_edit_id").',id',
            'new_user_password' => $password_validator
        ]);

        // User avatar operations
        if( $request->has('hidden_user_logo') && trim($request->input('hidden_user_logo')) == "changed"){
            if( $request->has('uploaded_image_name') ){
                $logo = $request->input('uploaded_image_name');
                $logo_name = Auth::user()->id . "__" . $logo;

                $imageFileType = pathinfo("img/avatar/user/".$logo_name,PATHINFO_EXTENSION);
                $logo = md5(uniqid()).".".$imageFileType;
                rename("img/avatar/user/".$logo_name, "img/avatar/user/".$logo);
            }
        }
        else if( trim($request->input('hidden_user_logo')) == "not_changed" && $old_logo != "no_avatar.png"){
            $logo = $old_logo;
        }

        DB::table('users')
            ->where('status', '<>', 0)
            ->where('id', $request->input("user_edit_id"))
            ->update(
                [
                    'name' => $request->input("new_user_name"),
                    'email' => $request->input("new_user_email"),
                    'password' => $password,
                    'avatar' => $logo
                ]
            );

        if($old_logo != "no_avatar.png" && $old_logo != "" && trim($request->input('hidden_user_logo')) == "changed"){
            unlink("img/avatar/user/".$old_logo);
        }

        Helper::clear_unused_img("img/avatar/user");


        //fire event
        Helper::fire_event("profile_update",Auth::user(),"users",$request->input("user_edit_id"));

        //return update operation result via global session object
        session(['account_update_success' => true]);

        return redirect()->back();
    }

    /**
     * Add/Edit user
     *
     * @param Request $request
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function create(Request $request){
        $op_type = "new";
        $logo = "no_avatar.png";
        $old_logo = "";
        $password = "";
        $operations = '["none"]';
        $default_operations = array();
        $created_by = Auth::user()->id;
        $old_user_type = "";

        $password_validator = 'bail|required|min:6|max:20';
        $email_validator = 'bail|required|email|max:255|unique:users,email';


        $user_types = DB::table("user_type")->get();
        foreach ( $user_types as $one_type ){
            $default_operations[$one_type->id] = $one_type->default_operations;
        }

        if( $request->has('new_user_type') && $request->input("new_user_type") == 4 ){

            $operations = $default_operations[4];

            //get porg_id (distributor_id) of the client

        }
        else if( $request->has('new_user_type') && $request->input("new_user_type") == 3 ){

            $operations = $default_operations[3];
        }
        else if( $request->has('new_user_type') && $request->input("new_user_type") == 2 ){

            $operations = $default_operations[2];
        }
        else if( $request->has('new_user_type') && $request->input("new_user_type") == 5 ){

            $operations = $default_operations[5];
        }

        if( $request->has('user_op_type') && trim($request->input('user_op_type')) == "edit" ) {
            $op_type = "edit";

            if( $request->has('user_edit_id') && is_numeric($request->input("user_edit_id")) ){
                $result = DB::table("users")->where("id",$request->input("user_edit_id"))->first();

                if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){}
                else if(Auth::user()->user_type == 3){
                    if($result->porg_id != Auth::user()->org_id)
                        abort(404);
                }
                else{
                    abort(404);
                }

                if( isset($result->avatar)){
                    $old_logo = $result->avatar;
                    $password = $result->password;
                    if($result->user_type == $request->input('new_user_type')){
                        $operations = $result->operations;
                    }
                }
            }
            else{
                return redirect('/user_management');
            }

            if( !$request->exists('new_user_password') || ($request->exists('new_user_password') && trim($request->input('new_user_password')) == "") ){
                $password_validator = 'bail|min:6|max:20';
            }
            else if( $request->has('new_user_password') ){
                $password = bcrypt($request->input("new_user_password"));
            }

            $email_validator = 'bail|required|email|max:255|unique:users,email,'.$request->input("user_edit_id").',id';
        }
        else if( $request->has('user_op_type') && trim($request->input('user_op_type')) == "new" && $request->has('new_user_password') ){
            $password = bcrypt($request->input("new_user_password"));
        }

        $this->validate($request, [
            'new_user_name' => 'bail|required|min:3|max:255',
            'new_user_email' => $email_validator,
            'new_user_password' => $password_validator,
            'new_user_type' => 'bail|required|digits:1',

        ]);

        // User avatar operations
        if( $request->has('hidden_user_logo') && trim($request->input('hidden_user_logo')) == "changed"){
            if( $request->has('uploaded_image_name') ){
                $logo = $request->input('uploaded_image_name');
                $logo_name = Auth::user()->id . "__" . $logo;

                $imageFileType = pathinfo("img/avatar/user/".$logo_name,PATHINFO_EXTENSION);
                $logo = md5(uniqid()).".".$imageFileType;
                rename("img/avatar/user/".$logo_name, "img/avatar/user/".$logo);
            }
        }
        else if( $op_type == "edit" && trim($request->input('hidden_user_logo')) == "not_changed" && $old_logo != "no_avatar.png"){
            $logo = $old_logo;
        }

        //save the data to DB
        if( $op_type == "new" ){ // insert new user
            $last_insert_id = DB::table('users')->insertGetId(
                [
                    'name' => $request->input("new_user_name"),
                    'email' => $request->input("new_user_email"),
                    'password' => $password,
                    'user_type' => $request->input("new_user_type"),
                    'avatar' => $logo,
                    'operations' => $operations,
                    'created_by' => $created_by
                ]
            );

            //fire event
            Helper::fire_event("create",Auth::user(),"users",$last_insert_id);

            //return insert operation result via global session object
            session(['new_user_insert_success' => true]);
        }
        else if( $op_type == "edit" ){ // update user's info
            DB::table('users')->where('id', $request->input("user_edit_id"))
                ->update(
                    [
                        'name' => $request->input("new_user_name"),
                        'email' => $request->input("new_user_email"),
                        'password' => $password,
                        'user_type' => $request->input("new_user_type"),
                        'operations' => $operations,
                        'avatar' => $logo,

                    ]
                );

            if($old_logo != "no_avatar.png" && $old_logo != "" && trim($request->input('hidden_user_logo')) == "changed"){
                unlink("img/avatar/user/".$old_logo);
            }

            Helper::clear_unused_img("img/avatar/user");


            //fire event
            Helper::fire_event("update",Auth::user(),"users",$request->input("user_edit_id"));

            //return update operation result via global session object
            session(['user_update_success' => true]);
        }

        return redirect()->back();
    }


}
