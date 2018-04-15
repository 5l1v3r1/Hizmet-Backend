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
    private $offer_columns;


    public function __construct()
    {
        $this->columns = array(

            "booking_title" => array(),
            "client_name" => array(),
            "status" => array("orderable" => false),
            "booking_date" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );
        $this->booking_columns = array(

            "booking_title" => array(),
            "client_name" => array(),
            "status" => array("orderable" => false),
            "booking_date" => array(),
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
        $prefix = "bm";
        $url = "bm_get_data";
        $default_order = '[4,"desc"]';
        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        $data_table->set_add_right(false);
        return view('pages.booking_management')->with("BookingDataTableObj", $data_table);
    }

    public function getData($detail_type = "", $detail_org_id = "")
    {
        $return_array = array();
        $draw = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();
        $where_clause = "WHERE B.status<>0 and assigned_id=0 ";


        if ($detail_type == "client") {
            if (!is_numeric($detail_org_id)) {
                abort(404);
            }

            $param_array[] = $detail_org_id;
            $where_clause .= " AND C.id=? ";
        }
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
        $result = DB::select('SELECT B.*, C.name as name FROM booking B JOIN clients C ON C.id=B.client_id ' . $where_clause, $param_array);

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
                    "booking_title" => $one_row->booking_title,
                    "client_name" => $one_row->name,
                    "status" => trans("global.booking_status_" . $one_row->status),
                    "booking_date" => date('d/m/Y H:i', strtotime($one_row->booking_date)),
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
        $where_clause = "WHERE BO.booking_id= " . $id . " ";


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
                $return_value .= '<a href="/booking_management/offer/' . $item_id . '" title="' . trans('booking_management.detail') . '" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
            }


            if ($return_value == "") {
                $return_value = '<i title="' . trans('global.no_authorize') . '" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
            }

            return $return_value;


        } else {

            if (Helper::has_right(Auth::user()->operations, "view_client_detail")) {
                $return_value .= '<a href="/booking_management/detail/' . $item_id . '" title="' . trans('booking_management.detail') . '" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
            }


            if ($return_value == "") {
                $return_value = '<i title="' . trans('global.no_authorize') . '" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
            }

            return $return_value;
        }

    }


    public function create(Request $request)
    {
        $op_type = $request->input("booking_op_type");
        $status = $request->input("new_offer_status");





        if ($op_type == "edit_booking") {
            if ($request->input("new_assigned_id")) {
                $assigned_id = $request->input("new_assigned_id");

            }else{
                $assigned_id = 0;
            }



            // update booking detail
            DB::table('booking')->where('id', $request->input("booking_edit_id"))
                ->update(
                    [
                        'client_id' => $request->input("new_user_clients"),
                        'booking_date' => $request->input("new_booking_date"),
                        'assigned_id' => $assigned_id,
                        'booking_title' => $request->input("new_booking_title"),
                        'status' => $request->input("new_booking_status"),

                    ]
                );



            //fire event
            Helper::fire_event("update",Auth::user(),"booking",$request->input("booking_edit_id"));

            Helper::fire_alert("booking", "update ", $request->input("booking_edit_id"));


            session(['booking_update_success' => true]);


        } elseif ($op_type == "edit_offer") {


            // update offer info
            $update=DB::table('booking_offers')->where('id', $request->input("offer_edit_id"))
                ->update(
                    [
                        'prices' => $request->input("new_prices"),
                        'note' => $request->input("new_note"),
                        'status' => $request->input("new_offer_status"),

                    ]
                );
            if ($update && $status == 2){
                DB::table('booking')->where('id', $request->input("offer_booking_id"))
                    ->update(
                        [
                            'assigned_id' => $request->input("offer_assigned_id"),
                            'status' => $status

                        ]
                    );


            }
            if ($update && $status <> 2){
                DB::table('booking')->where('id', $request->input("offer_booking_id"))
                    ->update(
                        [
                            'assigned_id' => 0,
                            'status' => $status

                        ]
                    );


            }

            //fire event
            Helper::fire_event("update",Auth::user(),"offers",$request->input("offer_edit_id"));
            Helper::fire_alert("offer", "update ", $request->input("offer_edit_id"));
            //return update operation result via global session object

            session(['booking_update_success' => true]);

        }

        return redirect()->back();


    }

    public function delete(Request $request)
    {


    }

    public function bookingChange(Request $request, $id)
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
            Helper::fire_event("booking_status_activated",Auth::user(),"booking",$request->input("id"));

            session(['booking_status_activated' => true]);
        } else {

            //fire event
            Helper::fire_event("booking_status_deactivated",Auth::user(),"booking",$request->input("id"));

            session(['booking_status_deactivated' => true]);
        }

        return "SUCCESS";


    }

    public function bookingDetail(Request $request, $id)
    {

        $the_booking = DB::table("booking as B")
            ->select(
                "B.*",
                'C.*',
                'S.*',
                'C.id as client_id',
                'B.assigned_id as assigned_id',
                'B.id as booking_id',
                'B.status as booking_status'
            )
            ->join('clients as C', 'C.id', 'B.client_id')
            ->join('services as S', 'S.id', 'B.service_id')
            ->where("B.id", $id)
            ->where('B.status', '<>', 0)
            ->first();

        $the_booking_offer = DB::table("booking_offers as BO")
            ->select(
                "BO.*",
                'C.*',
                'S.*'
            )
            ->join('clients as C', 'C.id', 'BO.assigned_id')
            ->join('booking as B', 'B.id', 'BO.booking_id')
            ->join('services as S', 'S.id', 'B.service_id')
            ->where("BO.id", $id)
            ->where('BO.status', '<>', 0)
            ->first();


        // prepare booking table obj which belongs to this client
        $prefix = "bd";
        $url = "bd_get_data/" . $id;
        $default_order = '[6,"desc"]';
        $booking_data_table = new DataTable($prefix, $url, $this->booking_columns, $default_order, $request);
        $booking_data_table->set_add_right(false);

        $prefix = "bo";
        $url = "bo_get_data/" . $id;
        $default_order = '[3,"desc"]';
        $offer_data_table = new DataTable($prefix, $url, $this->offer_columns, $default_order, $request);
        $offer_data_table->set_add_right(false);


        return view('pages.booking_detail', [
                'the_booking' => json_encode($the_booking),
                'the_booking_offer' => json_encode($the_booking_offer),
                'BookingDataTableObj' => $booking_data_table,
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
                'O.status as offer_status',
                'O.assigned_id as offer_assigned_id'

            )
            ->join('booking as B', 'B.id', 'O.booking_id')
            ->join('clients as C', 'C.id', 'O.client_id')
            ->join('clients as CA', 'CA.id', 'O.assigned_id')
            ->join('services as S', 'S.id', 'B.service_id')
            ->where("O.id", $id)
            ->where('O.status', '<>', 0)
            ->first();


        return view('pages.booking_offer', [
                'the_booking' => json_encode($the_booking)


            ]
        );
    }

}

