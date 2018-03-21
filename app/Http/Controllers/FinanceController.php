<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;

class FinanceController extends Controller
{

    private $payment_colums;
    private $payment_colums2;



    public function __construct()
    {

        $this->payment_colums = array(

            "client_name" => array(),
            "payment_date" => array(),
            "net_amount" => array(),
            "status" => array("orderable" => false),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );

        $this->payment_colums2 = array(

            "client_name" => array(),
            "payment_date" => array(),
            "net_amount" => array(),
            "status" => array("orderable" => false),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );



    }


    public function getData($detail_type = "", $client_id = "")
    {
        $where_clause = "WHERE P.status<>0  ";
        if ($detail_type == "coming") {

            $where_clause .= "and P.type=1  ";

        }
        elseif($detail_type == "sending"){
            $where_clause .= "and P.type=2 ";

        }

        if(is_numeric($client_id)){
            $where_clause .= "and P.client_id=".$client_id." ";
        }

        $return_array = array();
        $draw = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();




        //get customized filter object
        $filter_obj = false;
        if (isset($_GET["filter_obj"])) {
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj, true);
        }


        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(P.date) BETWEEN ? AND ? ";


        if (isset($_GET["search"])) {
            $search_value = $_GET["search"]["value"];
            if (!(trim($search_value) == "" || $search_value === false)) {
                $where_clause .= " AND (";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= "P.id LIKE ? ";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= " OR C.name LIKE ? ";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= " OR P.net_amount LIKE ? ";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= " OR P.status LIKE ? ";
                $param_array[] = "%" . strtolower($search_value) . "%";

                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM payment P JOIN clients C ON C.id=P.client_id ' . $where_clause, $param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT P.*, C.name as name FROM payment P JOIN clients C ON C.id=P.client_id ' . $where_clause, $param_array);

        $return_array["draw"] = $draw;
        $return_array["recordsTotal"] = 0;
        $return_array["recordsFiltered"] = 0;
        $return_array["data"] = array();

        if (COUNT($result) > 0) {
            $return_array["recordsTotal"] = $total_count;
            $return_array["recordsFiltered"] = $total_count;

            foreach ($result as $one_row) {

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "client_name" => $one_row->name,
                    "net_amount" => $one_row->net_amount." TL",
                    "payment_date" => date('d/m/Y H:i', strtotime($one_row->date)),
                    "status" => trans("global.booking_status_" . $one_row->status),
                    "buttons" => self::create_buttons($one_row->id, $detail_type ,$one_row->booking_id)
                );

                $return_array["data"][] = $tmp_array;
            }
        }


        echo json_encode($return_array);
    }


    public function create_buttons($id, $detail_type, $oid)
    {
        $return_value = "";
        if ($detail_type == "coming") {
            if (Helper::has_right(Auth::user()->operations, "view_finance_detail")) {
                $return_value .= '<a href="javascript:void(1);" title="' . trans('finance.edit_payment') . '" onclick="edit_payment(' . $id . ');" class="btn btn-danger btn-sm"><i class="fa fa-edit fa-lg"></i></a>  ';
                $return_value .= '<a href="/order_billing/'.$id.'?download=pdf" title="' . trans('finance.download_invoice') . '" class="btn btn-warning btn-sm"><i class="fa fa-download fa-lg"></i></a>  ';

            }


            if ($return_value == "") {
                $return_value = '<i title="' . trans('global.no_authorize') . '" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';

            }

            return $return_value;


        } else {

            if (Helper::has_right(Auth::user()->operations, "view_finance_detail")) {
                $return_value .= '<a href="javascript:void(1);" title="' . trans('finance.edit_payment') . '" onclick="edit_payment(' . $id . ');" class="btn btn-danger btn-sm"><i class="fa fa-edit fa-lg"></i></a>  ';
                $return_value .= '<a href="/send_billing/'.$id.'?download=pdf" title="' . trans('finance.download_invoice') . '" class="btn btn-warning btn-sm"><i class="fa fa-download fa-lg"></i></a>  ';
            }


            if ($return_value == "") {
                $return_value = '<i title="' . trans('global.no_authorize') . '" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
            }

            return $return_value;
        }

    }


    public function create(Request $request, $type="")
    {

        $op_type = $request->input("payment_mode");


        if( $op_type == "new" ){
            $last_insert_id = DB::table('payment')->insertGetId(
                [
                    'status' => $request->input("payment_status"),
                    'booking_id' => $request->input("booking_name_select"),
                    'client_id' => $request->input("payment_client_name"),
                    'amount' => $request->input("amount"),
                    'net_amount' => $request->input("net_amount"),
                    'tax' => $request->input("tax_rate"),
                    'type' => $request->input("type"),

                ]
            );

            //fire event
            Helper::fire_event("create",Auth::user(),"payment",$last_insert_id);
            //return insert operation result via global session object
            session(['new_payment_insert_success' => true]);


        }
        else if( $op_type == "edit" ){

            // update client's info
            DB::table('payment')->where('id', $request->input("payment_id"))
                ->update(
                    [
                        'booking_id' => $request->input("booking_name_select"),
                        'client_id' => $request->input("payment_client_name"),
                        'amount' =>  $request->input("amount"),
                        'net_amount' => $request->input("net_amount"),
                        'tax' => $request->input("tax_rate"),
                        'status' => $request->input("payment_status"),



                    ]
                );

            //fire event
            Helper::fire_event("update",Auth::user(),"payment",$request->input("payment_id"));

            //return update operation result via global session object
            session(['payment_update_success' => true]);

        }


        return redirect()->back();


    }

    public function getFinanceInfo(Request $request){

        if($request->has("id") && is_numeric($request->input("id"))){

            $the_info = DB::table("payment")
                ->where('id',$request->input("id"))
                ->first();

            echo json_encode($the_info);
        }
        else{
            abort(404);
        }
    }

    public function getSelectUser(Request $request, $user_type){

        if($request->has("id") && is_numeric($request->input("id"))){

            if($user_type=="client"){
                $the_info = DB::table("clients")
                    ->where('status','<>', '0')
                    ->where('type','=', '1')
                    ->get();
            }
            elseif($user_type=="seller")
            {
                $the_info = DB::table("clients")
                    ->where('status','<>', '0')
                    ->where('type','=', '2')
                    ->get();
            }else{
                $the_info = DB::table("clients")
                    ->where('status','<>', '0')
                    ->get();
            }



            echo json_encode($the_info);
        }
        else{
            abort(404);
        }
    }


    public function showFinance(Request $request)
    {

        $prefix = "cp";
        $url = "cp_get_data/coming";
        $default_order = '[3,"desc"]';
        $coming_data_table = new DataTable($prefix, $url, $this->payment_colums, $default_order, $request);


        $prefix = "sp";
        $url = "sp_get_data/sending";
        $default_order = '[3,"desc"]';
        $sending_data_table = new DataTable($prefix, $url, $this->payment_colums2, $default_order, $request);



        return view('pages.finance', [
                'ComingDataTableObj' => $coming_data_table,
                'SendingDataTableObj' => $sending_data_table
            ]
        );
    }


}

