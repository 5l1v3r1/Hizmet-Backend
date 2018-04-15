<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;

class AlertController extends Controller
{
    private $columns;
    private $definition_columns;

    public function __construct()
    {
        $this->columns = array(

            "DT_RowId"=>array(),
            "type"=>array(),
            "sub_type"=>array(),
            "device_no" => array(),
            "created_at"=>array(),
            "status"=>array(),
            "buttons"=>array("orderable" => false, "name" => "operations", "nowrap" => true)
        );


    }

    public function showTable(Request $request){


        $prefix = "al";
        $url = "al_get_data";
        $default_order = '[0,"desc"]';
        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        $data_table->set_add_right(false);
        return view('pages.alerts')->with("DataTableObj", $data_table);


    }

    public function getData(Request $request, $type="", $id=""){

        $return_array = array();
        $draw = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $param_array = array();
        $where_clause = "WHERE A.status<>0 ";


        //get customized filter object
        $filter_obj = false;
        if (isset($_GET["filter_obj"])) {
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj, true);
        }


        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(A.created_at) BETWEEN ? AND ? ";


        if (isset($_GET["search"])) {
            $search_value = $_GET["search"]["value"];
            if (!(trim($search_value) == "" || $search_value === false)) {
                $where_clause .= " AND (";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= "A.type LIKE ? ";



                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM alerts A ' . $where_clause, $param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT A.* FROM alerts A ' . $where_clause, $param_array);

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
                    "type" => $one_row->type,
                    "sub_type" => $one_row->sub_type,
                    "device_no" => $one_row->device_id,
                    "detail" => self::prepare_detail($one_row->type, $one_row->sub_type ,$one_row->device_id),
                    "created_at" => $one_row->created_at,
                    "status" => trans("global.alert_status_" . $one_row->status),
                    "buttons" => self::prepare_buttons($one_row->id, $type ,$one_row->id)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function prepare_buttons($alert_id)
    {
        $return_value = "";

        // show alert detail button
        $return_value .= '
            <a href="javascript:void(0);" title="Detaylar" class="btn btn-info btn-sm detail_button" onclick="read_alert('.$alert_id.')">
                <i class="fa fa-info-circle fa-lg"> </i>
            </a>
        ';

        if( Helper::has_right(Auth::user()->operations, "change_alert_status") ){
            $return_value .= '
                <a href="javascript:void(0);" onclick="delete_alert('.$alert_id.')" title="'.trans('alerts.delete').'" class="btn btn-warning btn-sm">
                    <i class="fa fa-trash-o fa-lg"></i>
                </a>
            ';

        }

        return $return_value;
    }

    public function prepare_detail($type,$sub_type,$device)
    {
        $return_value = "";
        if($type=="booking"){
            if($sub_type=="update"){
                $link="/booking_management/detail/".$device;
            }
            else{
                $link="/booking_management/";
            }
        } elseif($type=="order"){
            if($sub_type=="update"){
                $link="/order_management/detail/".$device;
            }
            else{
                $link="/order_management/";
            }
        }elseif($type=="offer"){
            if($sub_type=="update"){
                $link="/booking_management/offer/".$device;
            }
            else{
                $link="/booking_management/offer/".$device;
            }
        }elseif($type=="user"){
            if($sub_type=="update"){
                $link="/user_management/detail/".$device;
            }
            else{
                $link="/user_management/";
            }
        }elseif($type=="client"){
            if($sub_type=="update"){
                $link="/client_management/detail/".$device;
            }
            else{
                $link="/client_management/";
            }
        }elseif($type=="seller"){
            if($sub_type=="update"){
                $link="/seller_management/detail/".$device;
            }
            else{
                $link="/seller_management/";
            }
        }elseif($type=="finance"){

                $link="/finance/";

        }


        // show alert detail button
        $return_value .= '
            Detayları görmek için Butona tıklayınız
            <a href="'.$link.'" title="Detaylar" class="btn btn-info btn-sm detail_button">
                <i class="fa fa-info-circle fa-lg"> </i>
            </a>
        ';



        return $return_value;
    }




    public function create(Request $request){

    }



    public function updateUserRead(Request $request){

        if(!($request->has("id") && is_numeric($request->input("id")))) {
            abort(404);
        }


        DB::table('users')
            ->where('id', Auth::user()->id)
            ->update(
                [
                    'last_read_alert' => $request->input("id")
                ]
            );

        return "SUCCESS";

    }
    public function updateRead(Request $request){

        if(!($request->has("id") && is_numeric($request->input("id")))) {
            abort(404);
        }





        $result=DB::table('alerts')
            ->where('id', $request->input("id"))
            ->update(
                [
                    'status' => 2
                ]
            );

        return $result;

    }
}
