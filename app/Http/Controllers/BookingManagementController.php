<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;

class BookingManagementController extends Controller
{
    private $columns;
    private $booking_columns;



    public function __construct()
    {
        $this->columns = array(

            "booking_title"=>array(),
            "client_name"=>array(),
            "status"=>array("orderable"=>false),
            "booking_date"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );
        $this->booking_columns = array(

            "booking_title"=>array(),
            "client_name"=>array(),
            "status"=>array("orderable"=>false),
            "booking_date"=>array(),
            "buttons"=>array("orderable"=>false,"name"=>"operations","nowrap"=>true),
        );



    }

    public function showTable(Request $request){
        $prefix = "bm";
        $url = "bm_get_data";
        $default_order = '[4,"desc"]';
        $data_table = new DataTable($prefix,$url,$this->columns,$default_order,$request);

        return view('pages.booking_management')->with("BookingDataTableObj",$data_table);
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
        $where_clause = "WHERE B.status<>0 ";


        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }


        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(B.booking_date) BETWEEN ? AND ? ";


        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "B.booking_title LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR C.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR B.status LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";


                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM booking B JOIN clients C ON C.id=B.client_id '.$where_clause,$param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT B.*, C.name as name FROM booking B JOIN clients C ON C.id=B.client_id '.$where_clause ,$param_array);

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
                    "booking_title" => $one_row->booking_title,
                    "client_name" => $one_row->name,
                    "status" => trans("global.status_" . $one_row->status),
                    "booking_date" => date('d/m/Y H:i',strtotime($one_row->booking_date)),
                    "buttons" => self::create_buttons($one_row->id, $detail_type)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function create_buttons($item_id, $detail_type){
        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_client_detail")){
            $return_value .= '<a href="/booking_management/detail/'.$item_id.'" title="'.trans('booking_management.detail').'" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
        }



        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;

    }

    public function getInfo(Request $request){


    }

    public function uploadImage(Request $request){


    }

    public function create(Request $request){


    }

    public function delete(Request $request){



    }
    public function bookingChange(Request $request, $id){


    }

    public function bookingDetail(Request $request, $id){

        $the_booking = DB::table("booking as B")
            ->select(
                "B.*",
                'C.name as name'
            )
            ->join('client as C', 'C.id', 'B.client_id')
            ->where("B.id",$id)
            ->where('B.status','<>',0)
            ->first();




        // prepare booking table obj which belongs to this client
        $prefix = "bdb";
        $url = "bm_get_data/".$id;
        $default_order = '[6,"desc"]';
        $booking_data_table = new DataTable($prefix,$url, $this->columns, $default_order,$request);
        $booking_data_table->set_add_right(false);






        return view('pages.client_detail', [
                'the_booking' => json_encode($the_booking),
                'BookingDataTableObj' => $booking_data_table

            ]
        );
    }

}

