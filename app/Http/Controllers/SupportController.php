<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\Helper;
use App\Helpers\DataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    private $columns;

    public function __construct()
    {
        $this->columns = array(

            "subject" => array(),
            "category" => array(),
            "client_name" => array(),
            "status" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );
    }

    public function showTable(Request $request)
    {
        $prefix = "sp";
        $url = "sp_get_data";
        $default_order = '[3,"desc"]';
        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        return view('pages.support_management')->with("SupportDataTableObj", $data_table);
    }

    /**
     * Return user info to show user detail page
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function userDetail(Request $request, $id, $op = "showDetail")
    {


        if ($op == "showDetail") {
            $the_support = DB::table('support as S')
                ->LeftJoin('support_category as SCA', 'S.category_id', 'SCA.id')
                ->leftJoin('support_content as SC', 'S.id', 'SC.s_id')
                ->where('S.id', $id)
                ->where("S.status", '<>', 0)
                ->first();
            $the_clients = DB::table('clients as C')
                ->leftJoin('support as S', 'S.created_by', 'C.id')
                ->where('S.id', $id)
                ->where("S.status", '<>', 0)
                ->first();
            $the_users = DB::table('users as U')
                ->leftJoin('support as S', 'S.interested', 'U.id')
                ->where('S.id', $id)
                ->where("S.status", '<>', 0)
                ->first();
            $the_content = DB::table('support_content as SC')
                ->leftJoin('users as U', 'U.id', 'SC.user_id')
                ->leftJoin('clients as C', 'C.id', 'SC.user_id')
                ->where('SC.s_id', $id)
                ->orderBy('SC.id','ASC')
                ->get();


            return view('pages.support_detail', ['the_support' => json_encode($the_support), 'the_clients' => json_encode($the_clients), 'the_users' => json_encode($the_users), 'the_content' => json_encode($the_content)]);
        }

           else {
            abort(404);
        }
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
        $where_clause = "WHERE S.status<>0 ";


        //get customized filter object
        $filter_obj = false;
        if (isset($_GET["filter_obj"])) {
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj, true);
        }


        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(S.created_at) BETWEEN ? AND ? ";


        if (isset($_GET["search"])) {
            $search_value = $_GET["search"]["value"];
            if (!(trim($search_value) == "" || $search_value === false)) {
                $where_clause .= " AND (";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= "S.subject LIKE ? ";



                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM support S JOIN support_category SC ON SC.id = S.category_id ' . $where_clause, $param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT S.*, SC.*, C.name as client_name, S.id as sid FROM support S JOIN support_category SC ON SC.id = S.category_id JOIN clients C ON C.id = S.created_by ' . $where_clause, $param_array);

        $return_array["draw"] = $draw;
        $return_array["recordsTotal"] = 0;
        $return_array["recordsFiltered"] = 0;
        $return_array["data"] = array();

        if (COUNT($result) > 0) {
            $return_array["recordsTotal"] = $total_count;
            $return_array["recordsFiltered"] = $total_count;

            foreach ($result as $one_row) {

                $tmp_array = array(
                    "DT_RowId" => $one_row->sid,
                    "subject" => $one_row->subject,
                    "client_name" => $one_row->client_name,
                    "category" => $one_row->name,
                    "status" => trans("global.booking_status_" . $one_row->status),
                    "buttons" => self::create_buttons($one_row->sid, $detail_type ,$one_row->id)
                );

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
    private function create_buttons($support_id, $user_operations, $detail_type)
    {

        $return_value = "";

        if (Helper::has_right(Auth::user()->operations, "view_user_detail")) {
            $return_value .= '<a href="/support/detail/' . $support_id . '" title="Talebi düzenle" class="btn btn-info btn-sm"><i class="fa fa-support fa-lg"></i></a> ';
        }



        if ($return_value == "") {
            $return_value = '<i title="' . trans('global.no_authorize') . '" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    /**
     * Return user info to edit in the user management page
     *
     * @param Request $request
     * @return string
     */
    public function getInfo(Request $request)
    {
        if ($request->has("id") && is_numeric($request->input("id"))) {
            $result = DB::table("users")
                ->where("id", $request->input("id"))
                ->where('status', '<>', 0)
                ->first();

            if (isset($result->id)) {
                if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) {
                } else if ((!Auth::user()->user_type == 3 && Auth::user()->org_id == $result->porg_id)) {
                    return "ERROR";
                }

                echo json_encode($result);
            } else {
                echo "ERROR";
            }
        } else {
            echo "NEXIST";
        }
    }


    /**
     * Add/Edit user
     *
     * @param Request $request
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function create(Request $request)
    {
        $op_type = "new";
        $created_by = Auth::user()->id;





        if ($request->has('support_op_type') && trim($request->input('support_op_type')) == "edit") {
            $op_type = "edit";


        }



        $this->validate($request, [
            'new_support_title' => 'bail|required|min:3|max:255',
            'support_selected_category' => 'bail|required|digits:1',
            'support_status' => 'bail|required|digits:1',

        ]);



        //save the data to DB
        if ($op_type == "new") { // insert new user
            $last_insert_id = DB::table('support')->insertGetId(
                [
                    'subject' => $request->input("new_support_title"),
                    'category_id' => $request->input("support_selected_category"),
                    'interested' => $request->input("selected_interested_id"),
                    'created_by' => $request->input("selected_client_id"),
                    'status' => $request->input("support_status")
                ]
            );

            //fire event
            Helper::fire_event("create", Auth::user(), "support", $last_insert_id);

            //return insert operation result via global session object
            session(['new_support_insert_success' => true]);
        } else if ($op_type == "edit") { // update user's info
            DB::table('support')->where('id', $request->input("support_edit_id"))
                ->update(
                    [
                        'subject' => $request->input("new_support_title"),
                        'category_id' => $request->input("support_selected_category"),
                        'interested' => $request->input("selected_interested_id"),
                        'created_by' => $request->input("selected_client_id"),
                        'status' => $request->input("support_status")

                    ]
                );



            //fire event
           Helper::fire_event("update", Auth::user(), "support", $request->input("support_edit_id"));

            //return update operation result via global session object
            session(['support_update_success' => true]);
        }


        return redirect()->back();
    }
    public function message_send(Request $request){

        $type = 1;
        $created_by = Auth::user()->id;


            if($request->has('are_you_admin') && trim($request->input('are_you_admin')) == "2"){
                $type=2;
            }



        DB::table('support_content')->insert(
            [
                's_id' => $request->input("support_send_id"),
                'content' => $request->input("send_content"),
                'message_type' => $type,
                'user_id' => $request->input("admin_id"),
            ]
        );


        return redirect()->back();
    }


}
