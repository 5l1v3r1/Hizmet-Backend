<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;

class OrderManagementController extends Controller
{
    private $columns;
    private $order_columns;
    private $offer_columns;


    public function __construct()
    {
        $this->columns = array(

            "order_title" => array(),
            "client_name" => array(),
            "status" => array("orderable" => false),
            "order_date" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );
        $this->order_columns = array(

            "order_title" => array(),
            "client_name" => array(),
            "status" => array("orderable" => false),
            "order_date" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );
        $this->offer_columns = array(


            "offer_name" => array(),
            "status" => array("orderable" => false),
            "offer_date" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );


    }

    public function showTable(Request $request)
    {
        $prefix = "om";
        $url = "om_get_data";
        $default_order = '[4,"desc"]';
        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        $data_table->set_add_right(false);

        return view('pages.order_management')->with("OrderDataTableObj", $data_table);
    }

    public function getData($detail_type = "", $detail_org_id = "")
    {
        $where_clause = "WHERE B.status<>0 and B.assigned_id<>0 ";
        if ($detail_type == "seller") {

            $where_clause .= "and B.assigned_id= ".$detail_org_id." ";

        }
        elseif($detail_type == "client"){
            $where_clause .= "and B.client_id= ".$detail_org_id." and B.assigned_id<>0 ";

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
        $where_clause .= "AND DATE(B.booking_date) BETWEEN ? AND ? ";


        if (isset($_GET["search"])) {
            $search_value = $_GET["search"]["value"];
            if (!(trim($search_value) == "" || $search_value === false)) {
                $where_clause .= " AND (";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= "B.booking_title LIKE ? ";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= " OR C.name LIKE ? ";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= " OR B.status LIKE ? ";
                $param_array[] = "%" . strtolower($search_value) . "%";


                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM booking B JOIN clients C ON C.id=B.client_id ' . $where_clause, $param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT B.*, C.name as name, CO.name as assigned_name FROM booking B JOIN clients C ON C.id=B.client_id JOIN clients CO ON CO.id=B.assigned_id ' . $where_clause, $param_array);

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
                    "order_title" => $one_row->booking_title,
                    "client_name" => $one_row->name,
                    "assigned_name" => $one_row->assigned_name,
                    "status" => trans("global.booking_status_" . $one_row->status),
                    "order_date" => date('d/m/Y H:i', strtotime($one_row->order_date)),
                    "buttons" => self::create_buttons($one_row->id, $detail_type = "booking")
                );

                $return_array["data"][] = $tmp_array;
            }
        }


        echo json_encode($return_array);
    }

    public function getOffer(Request $request, $id)
    {
        $return_array = array();
        $draw = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();
        $where_clause = "WHERE BO.booking_id= ".$id." ";


        //get customized filter object
        $filter_obj = false;
        if (isset($_GET["filter_obj"])) {
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj, true);
        }


        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(BO.offer_date) BETWEEN ? AND ? ";


        if (isset($_GET["search"])) {
            $search_value = $_GET["search"]["value"];
            if (!(trim($search_value) == "" || $search_value === false)) {
                $where_clause .= " AND (";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= " C.name LIKE ? ";
                $param_array[] = "%" . strtolower($search_value) . "%";


                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM booking_offers BO JOIN clients C ON C.id=BO.client_id ' . $where_clause, $param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT BO.*, C.name as name FROM booking_offers BO JOIN clients C ON C.id=BO.client_id ' . $where_clause, $param_array);

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
                    "offer_name" => $one_row->name,
                    "status" => trans("global.booking_status_" . $one_row->status),
                    "offer_date" => date('d/m/Y H:i', strtotime($one_row->offer_date)),
                    "buttons" => self::create_buttons($one_row->id, $detail_type = "offer")
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function create_buttons($item_id, $detail_type)
    {
        $return_value = "";
        if ($detail_type == "offer") {
            if (Helper::has_right(Auth::user()->operations, "view_client_detail")) {
                $return_value .= '<a href="/booking_management/offer/' . $item_id . '" title="' . trans('order_management.detail') . '" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
            }


            if ($return_value == "") {
                $return_value = '<i title="' . trans('global.no_authorize') . '" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
            }

            return $return_value;


        } else {

            if (Helper::has_right(Auth::user()->operations, "view_client_detail")) {
                $return_value .= '<a href="/order_management/detail/' . $item_id . '" title="' . trans('order_management.detail') . '" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
            }


            if ($return_value == "") {
                $return_value = '<i title="' . trans('global.no_authorize') . '" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
            }

            return $return_value;
        }

    }

    public function getInfo(Request $request)
    {


    }

    public function uploadImage(Request $request)
    {


    }

    public function create(Request $request)
    {
        $op_type = $request->input("order_op_type");


        if( $op_type == "new" ){


        }
        else if( $op_type == "edit_order" ){

            // update client's info
            DB::table('booking')->where('id', $request->input("order_edit_id"))
                ->update(
                    [
                        'booking_title' => $request->input("new_order_name"),
                        'client_id' => $request->input("new_user_clients"),
                        'order_date' =>  $request->input("new_order_date"),
                        'assigned_id' => $request->input("new_assigned_id"),
                        'status' => $request->input("new_order_status"),



                    ]
                );

            //fire event
            Helper::fire_event("update",Auth::user(),"order",$request->input("order_edit_id"));

            //return update operation result via global session object
            session(['order_update_success' => true]);

        }
        else if( $op_type == "edit_offer" ){

            // update client's info
            DB::table('booking_offers')->where('id', $request->input("offer_edit_id"))
                ->update(
                    [
                        'prices' => $request->input("new_prices"),
                        'assigned_id' => $request->input("new_offer_assigned_id"),
                        'booking_id' => $request->input("new_offfer_booking_id"),


                    ]
                );

            //fire event
             Helper::fire_event("update",Auth::user(),"offers",$request->input("offer_edit_id"));

            //return update operation result via global session object
            session(['offer_update_success' => true]);

        }

        return redirect()->back();


    }

    public function delete(Request $request)
    {


    }

    public function orderChange(Request $request, $id)
    {

        if (!(Helper::has_right(Auth::user()->operations, 'change_user_status'))) {
            return "ERROR";
        }

        if (!($id == $request->input('id') && ($request->input('status') == 1 || $request->input('status') == 2))) {
            return "ERROR";
        }

        DB::table('booking')
            ->where('id', $request->input("id"))
            ->where('status', '<>', 0)
            ->update(
                [
                    'status' => $request->input("status")
                ]
            );

        //return update operation result via global session object
        if ($request->input('status') == 1) {

            //fire event
             Helper::fire_event("order_status_activated",Auth::user(),"order",$request->input("id"));

            session(['order_status_activated' => true]);
        } else {

            //fire event
             Helper::fire_event("order_status_deactivated",Auth::user(),"order",$request->input("id"));

            session(['order_status_deactivated' => true]);
        }

        return "SUCCESS";


    }

    public function payment(Request $request ){


        DB::table('payment')->insert(
            [
                'client_id' => $request->input("client_id"),
                'net_amount' => $request->input("price"),
                'booking_id' => $request->input("order_id")

            ]
        );


        return "SUCCESS";
    }

    public function orderDetail(Request $request, $id)
    {

        $the_order = DB::table("booking as B")
            ->select(
                "B.*",
                'C.*',
                'S.*',
                'BO.*',
                'B.id as order_id',
                'B.client_id as order_client_id',
                'B.assigned_id as order_assigned_id',
                'B.status as order_status',
                'BO.id as offer_id',
                'BO.booking_id as offer_booking_id',
                'BO.prices as offer_prices',
                'BO.client_id as offer_client_id',
                'BO.assigned_id as offer_assigned_id',
                'P.id as invoice_id',
                'P.booking_id as pbid'
            )
            ->join('clients as C', 'C.id', 'B.client_id')
            ->Leftjoin('services as S', 'S.id', 'B.service_id')
            ->Leftjoin('booking_offers as BO', 'BO.booking_id', 'B.id')
            ->Leftjoin('payment as P', 'P.booking_id', 'B.id')
            ->where("B.id", $id)
            ->where('B.status', '<>', 0)
            ->first();



        $prefix = "oo";
        $url = "oo_get_data/" . $id;
        $default_order = '[3,"desc"]';
        $offer_data_table = new DataTable($prefix, $url, $this->offer_columns, $default_order, $request);
        $offer_data_table->set_add_right(false);


        return view('pages.order_detail', [
                'the_order' => json_encode($the_order),
                'OfferDataTableObj' => $offer_data_table


            ]
        );
    }

    public function offerDetail(Request $request, $id)
    {
        $the_booking = DB::table("booking_offers as O")
            ->select(
                "B.*",
                'O.*',
                'C.name as client_name',
                'CA.name as assigned_name',
                'O.status as offer_status'

            )
            ->join('booking as B', 'B.id', 'O.booking_id')
            ->join('clients as C', 'C.id', 'O.client_id')
            ->join('clients as CA', 'CA.id', 'O.assigned_id')
            ->join('services as S', 'S.id', 'B.service_id')
            ->where("O.id", $id)
            ->where('O.status', '<>', 0)
            ->first();


        return view('pages.order_offer', [
                'the_booking' => json_encode($the_booking)


            ]
        );
    }

}

