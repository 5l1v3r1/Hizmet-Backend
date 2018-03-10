<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;

class SellerManagementController extends Controller
{
    private $columns;
    private $user_columns;
    private $booking_columns;
    private $order_columns;
    private $event_columns;
    private $message_columns;


    public function __construct()
    {
        $this->columns = array(

            "name"=>array(),
            "email"=>array(),
            "gsm_phone"=>array(),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );

        $this->user_columns = array(

            "name"=>array("name"=>"username"),
            "user_type"=>array("visible"=>false),
            "org_name"=>array("visible"=>false),
            "email"=>array(),
            "status"=>array("orderable"=>false),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );
        $this->booking_columns = array(

            "name"=>array("name"=>"username"),
            "user_type"=>array("visible"=>false),
            "org_name"=>array("visible"=>false),
            "email"=>array(),
            "status"=>array("orderable"=>false),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );
        $this->order_columns = array(

            "name"=>array("name"=>"username"),
            "user_type"=>array("visible"=>false),
            "org_name"=>array("visible"=>false),
            "email"=>array(),
            "status"=>array("orderable"=>false),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );
        $this->event_columns = array(

            "name"=>array("name"=>"username"),
            "user_type"=>array("visible"=>false),
            "org_name"=>array("visible"=>false),
            "email"=>array(),
            "status"=>array("orderable"=>false),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );
        $this->message_columns = array(

            "name"=>array("name"=>"username"),
            "user_type"=>array("visible"=>false),
            "org_name"=>array("visible"=>false),
            "email"=>array(),
            "status"=>array("orderable"=>false),
            "created_at"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );

    }

    public function showTable(Request $request){
        $prefix = "sm";
        $url = "sm_get_data";
        $default_order = '[4,"desc"]';
        $data_table = new DataTable($prefix,$url,$this->columns,$default_order,$request);

        return view('pages.seller_management')->with("UserDataTableObj",$data_table);
    }

    public function getData($detail_type="", $detail_org_id="" ){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();
        $where_clause = "WHERE C.status<>0 and C.type=2 ";


        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
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
                $where_clause .= " OR C.gsm_phone LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";


                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM clients C '.$where_clause,$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT * FROM clients C '.$where_clause ,$param_array);

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
                    "email" => $one_row->email,
                    "gsm_phone" => $one_row->gsm_phone,
                    "phone" => $one_row->phone,
                    "created_at" => date('d/m/Y H:i',strtotime($one_row->created_at)),
                    "buttons" => self::create_buttons($one_row->id, $detail_type)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function create_buttons($item_id, $detail_type){
        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_seller_detail")){
            $return_value .= '<a href="/seller_management/detail/'.$item_id.'" title="'.trans('seller_management.detail').'" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
        }

        if($detail_type == "") {
            if (Helper::has_right(Auth::user()->operations, "add_new_client")) {
                $return_value .= '<a href="javascript:void(1);" title="' . trans('seller_management.edit') . '" onclick="edit_client(' . $item_id . ');" class="btn btn-warning btn-sm"><i class="fa fa-edit fa-lg"></i></a> ';
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


    }



    public function clientDetail(Request $request, $id){

        $the_seller = DB::table("clients as C")
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


        // prepare booking table obj which belongs to this client
        $prefix = "cdb";
        $url = "cdb_get_data/".$id;
        $default_order = '[6,"desc"]';
        $booking_data_table = new DataTable($prefix,$url, $this->booking_columns, $default_order,$request);
        $booking_data_table->set_add_right(false);

        // prepare order table obj which belongs to this client
        $prefix = "cdo";
        $url = "cdo_get_data/".$id;
        $default_order = '[6,"desc"]';
        $order_data_table = new DataTable($prefix,$url, $this->booking_columns, $default_order,$request);
        $order_data_table->set_add_right(false);



        //get event logs table from eventlogController
        $eventsTable = new EventlogsController();
        $eventsTable = $eventsTable->prepareEventTableObject($request,"user",$id);

        return view('pages.seller_detail', [
                'the_seller' => json_encode($the_seller),
                'UserDataTableObj' => $user_data_table,
                'BookingDataTableObj' => $booking_data_table,
                'OrderDataTableObj' => $order_data_table

            ]
        );
    }

}

