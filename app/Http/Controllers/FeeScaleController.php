<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;

class FeeScaleController extends Controller
{
    private $columns;

    public function __construct()
    {
        $this->columns = array(
            "name"=>array(),
            "active_unit_price"=>array(),
            "reactive_unit_price"=>array(),
            "t1_unit_price"=>array(),
            "t2_unit_price"=>array(),
            "t3_unit_price"=>array(),
            "created_by"=>array(),
            "updated_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );
    }

    public function showTable(Request $request){
        $prefix = "fs";
        $url = "fs_get_data";
        $default_order = '[7,"desc"]';

        $data_table = new DataTable($prefix,$url,$this->columns,$default_order,$request);

        return view('pages.fee_scale')->with("DataTableObj",$data_table);
    }

    public function getData(){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $where_clause = "WHERE FS.status<>0 ";
        $order_column = "FS.updated_at";
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
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array = array();
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(FS.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "FS.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR FS.active_unit_price LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR FS.reactive_unit_price LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR FS.t1_unit_price LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR FS.t2_unit_price LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR FS.t3_unit_price LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR U.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR S.name LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        if( Auth::user()->user_type == 3 ){
            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND (FS.org_id=? OR FS.org_id=0) ";
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM fee_scales FS LEFT JOIN users U ON U.id=FS.created_by LEFT JOIN (SELECT "'.trans('fee_scale.system').'" as name) S ON FS.created_by=0 '.$where_clause,$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT FS.*, (CASE WHEN FS.created_by<>0 THEN U.name ELSE "'.trans('fee_scale.system').'" END) as created_by FROM fee_scales FS LEFT JOIN (SELECT "'.trans('fee_scale.system').'" as name) S ON FS.created_by=0 LEFT JOIN users U ON U.id=FS.created_by '.$where_clause.' ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?',$param_array);

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){
                $tmp_array = array("DT_RowId"=>$one_row->id,"name"=>$one_row->name,"active_unit_price"=>$one_row->active_unit_price,"reactive_unit_price"=> $one_row->reactive_unit_price,"t1_unit_price"=> $one_row->t1_unit_price,"t2_unit_price"=>$one_row->t2_unit_price, "t3_unit_price"=>$one_row->t3_unit_price, "created_by"=>$one_row->created_by,
                    "updated_at"=>" <span data-toggle='tooltip' data-placement='bottom' title='".trans("fee_scale.created_at").": ".date('d/m/Y H:i',strtotime($one_row->created_at))."'>".date('d/m/Y H:i',strtotime($one_row->updated_at))."</span>",

                    "buttons"=>self::create_buttons($one_row->id,$one_row->org_id));

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function create_buttons($item_id,$org_id){
        $return_value = "";


        if( Helper::has_right(Auth::user()->operations, "add_new_fee_scale") ){
            if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) || (Auth::user()->user_type == 3 && Auth::user()->org_id == $org_id) ){
                $return_value .= '<a href="javascript:void(1);" title="'.trans('fee_scale.edit').'" onclick="edit_fee('.$item_id.');" class="btn btn-warning btn-sm"><i class="fa fa-edit fa-lg"></i></a> ';
            }
        }


        //check if this fee is used already
        $devices = DB::select("SELECT GROUP_CONCAT(D.device_no SEPARATOR ', ') as used_devices FROM devices D WHERE D.status<>0 AND D.fee_scale_id=?",[$item_id]);

        //$devices = DB::select('SELECT GROUP_CONCAT(D.device_no SEPARATOR ", ") as used_devices FROM devices D WHERE D.status<>0 AND JSON_CONTAINS(fee_scale_id,\'{"id":"'.$original_id.'"}\',CONCAT("$[",(JSON_LENGTH(fee_scale_id)-1),"]"))');

        $devices = $devices[0]->used_devices;

        if( Helper::has_right(Auth::user()->operations, "delete_fee_scale") ){
            if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) || (Auth::user()->user_type == 3 && Auth::user()->org_id == $org_id) ){

            $return_value .= '<a '.($devices!=""?"style=\"opacity:0.4;\"":"").' href="javascript:void(1);" title="'.trans('fee_scale.delete').'" onclick="'.($devices!=""?"alertBox('','".trans("fee_scale.not_deletable",["devices"=>$devices])."','info');":"delete_fee(".$item_id.");").'" class="btn btn-danger btn-sm"><i class="fa fa-trash-o fa-lg"></i></a> ';
            }
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    public function getInfo(Request $request){
        if($request->has("id") && is_numeric($request->input("id"))){

            // Auth user has right to edit
            $result = DB::table('fee_scales')
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

            $result = DB::table("fee_scales")
                ->where("id",$request->input("id"))
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
        $name_validator = 'bail|required|min:3|max:255|unique:fee_scales,name';
        $edit_info = false;

        if( $request->has('fee_op_type') && trim($request->input('fee_op_type')) == "edit" ) {
            $op_type = "edit";

            // Auth user has right to edit
            $edit_info = DB::table('fee_scales')->where('id',$request->input('fee_edit_id'))->first();
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

            $name_validator = 'bail|required|min:3|max:255|unique:fee_scales,name,'.$edit_info->id.',id';
        }

        $regex = "/^[0-9]+(\.[0-9]{1,6}$)?/";
        $this->validate($request, [
            'new_fee_name' => $name_validator,
            'new_fee_aup' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_raup' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_t1' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_t2' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_t3' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_dc' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_ef' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_ts' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_ect' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_tlup' => 'bail|required|min:1|max:10|regex:'.$regex,
            'new_fee_tp' => 'bail|required|min:1|max:5|regex:'.$regex,
            'new_fee_pup' => 'bail|required|min:1|max:10|regex:'.$regex
        ]);

        //save the data to DB
        if( $op_type == "new" ){ // insert new user
            $last_insert_id = DB::table('fee_scales')->insertGetId(
                [
                    'name' => $request->input("new_fee_name"),
                    'active_unit_price' => $request->input("new_fee_aup"),
                    'reactive_unit_price' => $request->input("new_fee_raup"),
                    't1_unit_price' => $request->input("new_fee_t1"),
                    't2_unit_price' => $request->input("new_fee_t2"),
                    't3_unit_price' => $request->input("new_fee_t3"),
                    'distribution_cost' => $request->input("new_fee_dc"),
                    'energy_fund' => $request->input("new_fee_ef"),
                    'trt_share' => $request->input("new_fee_ts"),
                    'consumption_tax' => $request->input("new_fee_ect"),
                    'transformer_loss_unit_price' => $request->input("new_fee_tlup"),
                    'transformer_power' => $request->input("new_fee_tp"),
                    'power_unit_price' => $request->input("new_fee_pup"),
                    'created_by' => $created_by,
                    'org_id' => Auth::user()->org_id
                ]
            );

            //fire event
            Helper::fire_event("create",Auth::user(),"fee_scales",$last_insert_id);


            //return insert operation result via global session object
            session(['new_fee_insert_success' => true]);
        }
        else if( $op_type == "edit" ){ // update user's info

            DB::table('fee_scales')->where('id', $request->input("fee_edit_id"))
                ->update(
                    [
                        'name' => $request->input("new_fee_name"),
                        'active_unit_price' => $request->input("new_fee_aup"),
                        'reactive_unit_price' => $request->input("new_fee_raup"),
                        't1_unit_price' => $request->input("new_fee_t1"),
                        't2_unit_price' => $request->input("new_fee_t2"),
                        't3_unit_price' => $request->input("new_fee_t3"),
                        'distribution_cost' => $request->input("new_fee_dc"),
                        'energy_fund' => $request->input("new_fee_ef"),
                        'trt_share' => $request->input("new_fee_ts"),
                        'consumption_tax' => $request->input("new_fee_ect"),
                        'transformer_loss_unit_price' => $request->input("new_fee_tlup"),
                        'transformer_power' => $request->input("new_fee_tp"),
                        'power_unit_price' => $request->input("new_fee_pup"),
                        'org_id' => $edit_info->org_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                );

            //fire event
            Helper::fire_event("update",Auth::user(),"fee_scales",$request->input("fee_edit_id"));

            //return update operation result via global session object
            session(['fee_update_success' => true]);
        }

        return redirect()->back();
    }

    public function delete(Request $request){
        if(!($request->has("id") && is_numeric($request->input("id"))))
            return "ERROR";

        $fee_info = DB::table("fee_scales")->where('id',$request->input("id"))->first();
        if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) || (Auth::user()->user_type == 3 && Auth::user()->org_id == $fee_info->org_id) ){

            $fee_devices = DB::select("SELECT id FROM devices WHERE status<>0 AND fee_scale_id=?",[$fee_info->id]);

            //$fee_devices = DB::select('SELECT id FROM devices WHERE status<>0 AND JSON_CONTAINS(fee_scale_id,\'{"id":"'.$fee_info->original_id.'"}\',CONCAT("$[",(JSON_LENGTH(fee_scale_id)-1),"]"))');

            if(count($fee_devices)>0 && isset($fee_devices[0]->id)){
                return "ERROR";
            }
            else{

                DB::table('fee_scales')->where('id', $request->input("id"))
                    ->update(
                        [
                            'status' => 0
                        ]
                    );

                //fire event
                Helper::fire_event("delete",Auth::user(),"fee_scales",$request->input("id"));

                session(['fee_scale_delete_success' => true]);
                return "SUCCESS";
            }
        }
        else{
            return "ERROR";
        }

    }
}
