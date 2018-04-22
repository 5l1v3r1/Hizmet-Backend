<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\Helper;
use App\Helpers\DataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
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
        $prefix = "bg";
        $url = "bg_get_data";
        $default_order = '[3,"desc"]';
        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        return view('pages.blog_management')->with("BlogDataTableObj", $data_table);
    }

    public function blogDetail(Request $request, $id, $op = "showDetail")
    {


        if ($op == "showDetail") {
            $the_blog = DB::table('blog as B')
                ->select('*','B.id as bid')
                ->LeftJoin('blog_category as BC', 'B.category_id', 'BC.id')
                ->where('B.id', $id)
                ->where("B.status", '<>', 0)
                ->first();
            $the_users = DB::table('users as U')
                ->leftJoin('blog as B', 'B.created_by', 'U.id')
                ->where('B.id', $id)
                ->where("B.status", '<>', 0)
                ->first();



            return view('pages.blog_detail', ['the_blog' => json_encode($the_blog), 'the_users' => json_encode($the_users)]);
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
        $where_clause = "WHERE B.status<>0 ";


        //get customized filter object
        $filter_obj = false;
        if (isset($_GET["filter_obj"])) {
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj, true);
        }


        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(B.updated_at) BETWEEN ? AND ? ";


        if (isset($_GET["search"])) {
            $search_value = $_GET["search"]["value"];
            if (!(trim($search_value) == "" || $search_value === false)) {
                $where_clause .= " AND (";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= "B.title LIKE ? ";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= "B.summary LIKE ? ";



                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM blog B JOIN blog_category BC ON BC.id = B.category_id ' . $where_clause, $param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT B.*, BC.*, U.name as user_name, B.id as bid FROM blog B JOIN blog_category BC ON BC.id = B.category_id JOIN users U ON U.id = B.created_by ' . $where_clause, $param_array);

        $return_array["draw"] = $draw;
        $return_array["recordsTotal"] = 0;
        $return_array["recordsFiltered"] = 0;
        $return_array["data"] = array();

        if (COUNT($result) > 0) {
            $return_array["recordsTotal"] = $total_count;
            $return_array["recordsFiltered"] = $total_count;

            foreach ($result as $one_row) {

                $tmp_array = array(
                    "DT_RowId" => $one_row->bid,
                    "subject" => $one_row->title,
                    "client_name" => $one_row->user_name,
                    "category" => $one_row->c_name,
                    "status" => trans("global.blog_status_" . $one_row->status),
                    "buttons" => self::create_buttons($one_row->bid, $detail_type ,$one_row->id)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    private function create_buttons($support_id, $user_operations, $detail_type)
    {

        $return_value = "";

        if (Helper::has_right(Auth::user()->operations, "view_user_detail")) {
            $return_value .= '<a href="/blog/detail/' . $support_id . '" title="Blog düzenle" class="btn btn-info btn-sm"><i class="fa fa-address-book fa-lg"></i></a> ';
        }



        if ($return_value == "") {
            $return_value = '<i title="' . trans('global.no_authorize') . '" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }


    public function create(Request $request)
    {
        $op_type = "new";
        $created_by = Auth::user()->id;





        if ($request->has('blog_op_type') && trim($request->input('blog_op_type')) == "edit") {
            $op_type = "edit";


        }



        $this->validate($request, [
            'new_blog_title' => 'bail|required|min:3|max:255',
            'blog_selected_category' => 'bail|required|digits:1',
            'blog_status' => 'bail|required|digits:1',

        ]);



        //save the data to DB
        if ($op_type == "new") { // insert new user
            $last_insert_id = DB::table('blog')->insertGetId(
                [
                    'title' => $request->input("new_blog_title"),
                    'category_id' => $request->input("blog_selected_category"),
                    'created_by' => $request->input("selected_user_id"),
                    'content' => $request->input("content_hidden"),
                    'summary' => $request->input("summary"),
                    'status' => $request->input("blog_status")
                ]
            );

            //fire event
            Helper::fire_event("create", Auth::user(), "blog", $last_insert_id);

            //return insert operation result via global session object
            session(['new_blog_insert_success' => true]);
        } else if ($op_type == "edit") { // update user's info
            DB::table('blog')->where('id', $request->input("blog_edit_id"))
                ->update(
                    [
                        'title' => $request->input("new_blog_title"),
                        'category_id' => $request->input("blog_selected_category"),
                        'created_by' => $request->input("selected_user_id"),
                        'content' => $request->input("content_hidden"),
                        'summary' => $request->input("summary"),
                        'status' => $request->input("blog_status")

                    ]
                );



            //fire event
            Helper::fire_event("update", Auth::user(), "blog", $request->input("blog_edit_id"));

            //return update operation result via global session object
            session(['blog_update_success' => true]);
        }


        return redirect()->back();
    }

    public function category(Request $request)
    {

        //save the data to DB
        if ($request->input("op_type") == "new") { // insert new user
            $last_insert_id = DB::table('blog_category')->insertGetId(
                [
                    'c_name' => $request->input("new_category_name"),
                    'rank' => $request->input("new_category_rank"),
                    'top_category' => $request->input("new_category_top"),

                ]
            );

            //fire event
            Helper::fire_event("create", Auth::user(), "blog_category", $last_insert_id);

            //return insert operation result via global session object
            session(['new_blog_category_insert_success' => true]);
        } else if ($request->input("del_category") != 0) { // update user's info
            DB::table('blog_category')->where('id',$request->input("del_category"))
                ->delete();



            //fire event
            Helper::fire_event("delete", Auth::user(), "blog_category", $request->input("del_category"));

            //return update operation result via global session object
            session(['blog_category_delete_success' => true]);
        }


        return redirect()->back();
    }
 public function tag(Request $request)
    {

        //save the data to DB
        if ($request->input("op_type") == "new") { // insert new user
            $last_insert_id = DB::table('blog_tag')->insertGetId(
                [
                    'name' => $request->input("new_tag_name"),
                ]
            );
            DB::table('blog_etiket')->insert(
                [
                    'blog_id' => $request->input("blog_id"),
                    'tag_id' => $last_insert_id,
                ]
            );

            //fire event
            Helper::fire_event("create", Auth::user(), "blog_tag", $last_insert_id);

            //return insert operation result via global session object
            session(['new_blog_category_insert_success' => true]);
        } else if ($request->input("del_tag") != 0) { // update user's info
            DB::table('blog_etiket')->where('id',$request->input("del_tag"))
                ->delete();



            //fire event
            Helper::fire_event("delete", Auth::user(), "blog_etiket", $request->input("del_tag"));

            //return update operation result via global session object
            session(['blog_cetiket_delete_success' => true]);
        }


        return redirect()->back();
    }



}
