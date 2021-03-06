<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\Helper;
use App\Helpers\DataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventlogsController extends Controller
{

    private $columns;

    public function __construct()
    {
        $this->columns = array(
            "icon" => array("name" => false, "orderable" => false),
            "event_type" => array("orderable" => false),
            "actor" => array("orderable" => false),
            "affected" => array("orderable" => false),
            "date" => array("nowrap" => true)
        );
    }

    public function prepareEventTableObject($request, $type, $id)
    {
        $prefix = "el";
        $url = "el_get_data/" . $type . "/" . $id;
        $default_order = '[4,"desc"]';

        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        $data_table->set_date_range(date('d/m/Y', strtotime("-1 week")), date('d/m/Y'));
        $data_table->set_init_fnct('
            //change placeholder of search filter of datatable
            $("#div_' . $prefix . '_search_custom").find("input").attr("placeholder","' . trans("global.search_user") . '");
            $("#el_table").find("th").first().next().remove();
            $("#el_table").find("th").first().attr("colspan","2");
            
            $("#el_table").find("th").first().html("\
                <div class=\"row\" style=\"margin-top: -5px;\"> \
                    <div class=\"col-lg-6\" style=\"padding:0 8px 10px;\"> \
                        <select id=\"el_select_table\" style=\"width:100%;\"> \
                            <option value=\"all\">' . trans("event_logs.all_events") . '</option> \
                            ' . (Auth::user()->user_type != 4 ? ' \
                                <option value=\"booking\">' . trans("event_logs.bookings") . '</option> \
                                <option value=\"order\">' . trans("event_logs.orders") . '</option> \
                                <option value=\"offers\">' . trans("event_logs.offers") . '</option> \
                                \
                            ' : "") . ' \
                            <option value=\"users\">' . trans("event_logs.user_events") . '</option> \
                            <option value=\"clients\">' . trans("event_logs.client_events") . '</option> \
                            <option value=\"sellers\">' . trans("event_logs.seller_events") . '</option> \
                            \
                        </select> \
                    </div> \
                    \
                </div> \
            ");
            
            $("#el_select_table").select2({
                minimumResultsForSearch: Infinity
            });
            
            $("#el_select_type").select2({
                minimumResultsForSearch: Infinity
            });
            
            $("#el_select_table").change(function(){
                the_val = $(this).val();        
                $("#el_select_type").empty();
        
                
                el_filter_obj.category = the_val;
                el_filter_obj.type = "all";                
                el_dt.ajax.reload();
            });
            
            
            $("#el_select_type").change(function(){
                the_val = $(this).val();
                
                el_filter_obj.category = $("#el_select_table").val();
                el_filter_obj.type = the_val;                
                el_dt.ajax.reload();                
            });
        ');

        $data_table->set_add_right(false);
        return $data_table;
    }

    public function showTable(Request $request)
    {
        $data_table = self::prepareEventTableObject($request, "all", "all");
        return view('pages.event_logs')->with("DataTableObj", $data_table);
    }

    public function getData($type, $id)
    {
        $return_array = array();
        $draw = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $where_clause = "WHERE 1=1 ";
        $order_column = "EL.date";
        $order_dir = "DESC";

        if (isset($_GET["order"][0]["column"])) {
            $order_column = $_GET["order"][0]["column"];
            $column_item = array_keys(array_slice($this->columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;
        }

        if (isset($_GET["order"][0]["dir"])) {
            $order_dir = $_GET["order"][0]["dir"];
        }

        //get customized filter object
        $filter_obj = false;
        if (isset($_GET["filter_obj"])) {
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj, true);
        }

        $param_array = array();
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(EL.date) BETWEEN ? AND ? ";


        //filter data according to displayed page
        if ($type != "all") {
            if ($type == "user") {
                $param_array[] = $id;
                $param_array[] = $id;
                $where_clause .= " AND (EL.user_id = ? OR (EL.table_name='users' AND EL.affected_id=?))";
            }elseif ($type == "user") {
                $param_array[] = $id;
                $param_array[] = $id;
                $where_clause .= " AND (EL.user_id = ? OR (EL.table_name='users' AND EL.affected_id=?))";
            } else if ($type == "client") {
                $param_array[] = $id;
                $param_array[] = $id;
                $where_clause .= " AND (EL.user_id = ? OR (EL.table_name='users' AND EL.affected_id=?))";

            } else if ($type == "distributor") {
                $param_array[] = $id;
                $param_array[] = $id;
                $where_clause .= " AND (EL.user_id = ? OR (EL.table_name='users' AND EL.affected_id=?))";
            }elseif ($type == "booking") {
                $param_array[] = $id;
                $param_array[] = $id;
                $where_clause .= " AND (EL.user_id = ? OR (EL.table_name='users' AND EL.affected_id=?))";
            }elseif ($type == "order") {
                $param_array[] = $id;
                $param_array[] = $id;
                $where_clause .= " AND (EL.user_id = ? OR (EL.table_name='users' AND EL.affected_id=?))";
            }elseif ($type == "offer") {
                $param_array[] = $id;
                $param_array[] = $id;
                $where_clause .= " AND (EL.user_id = ? OR (EL.table_name='users' AND EL.affected_id=?))";
            }
        }

        // get custom filters(event category and type) if exist
        if (isset($filter_obj["category"]) && $filter_obj["category"] != "") {
            if ($filter_obj["category"] != "all") {
                $param_array[] = $filter_obj["category"];
                $where_clause .= " AND EL.table_name = ? ";
            }
        }

        if (isset($filter_obj["type"]) && $filter_obj["type"] != "") {
            if ($filter_obj["type"] != "all") {
                if ($filter_obj["type"] == "activation") {
                    $param_array[] = "user_status_deactivated";
                    $param_array[] = "user_status_activated";
                    $where_clause .= " AND (EL.event_type = ? OR EL.event_type =? )";
                } else if ($filter_obj["type"] == "sessions") {
                    $param_array[] = "login";
                    $param_array[] = "logout";
                    $where_clause .= " AND (EL.event_type = ? OR EL.event_type =? )";
                } else if ($filter_obj["type"] == "update") {
                    $param_array[] = "update";
                    $param_array[] = "profile_update";
                    $where_clause .= " AND (EL.event_type = ? OR EL.event_type =? )";
                } else {
                    $param_array[] = $filter_obj["type"];
                    $where_clause .= " AND EL.event_type = ? ";
                }
            }
        }

        if (isset($_GET["search"])) {
            $search_value = $_GET["search"]["value"];
            if (!(trim($search_value) == "" || $search_value === false)) {
                $where_clause .= " AND (";
                $param_array[] = "%" . $search_value . "%";
                $where_clause .= "AU.name LIKE ? ";
                $where_clause .= "AND EL.event_type NOT LIKE 'corrupted_data' ";
                //$param_array[]="%".$search_value."%";
                //$where_clause .= " OR FS.active_unit_price LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        if (Auth::user()->user_type == 3) {
            $param_array[] = Auth::user()->org_id;
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND ((AU.user_type=3 AND AU.org_id=?) OR (AU.user_type=4 AND AU.porg_id=?)) ";
        } else if (Auth::user()->user_type == 4) {

            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND (AU.user_type=4 AND AU.org_id=?) ";
        }

        $total_count = DB::select('SELECT count(*) as total_count FROM event_logs EL LEFT JOIN users AU ON AU.id=EL.user_id ' . $where_clause, $param_array);
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT EL.*, AU.name as actor FROM event_logs EL LEFT JOIN users AU ON AU.id=EL.user_id ' . $where_clause . ' ORDER BY ' . $order_column . ' ' . $order_dir . ' LIMIT ? OFFSET ?', $param_array);

        $return_array["draw"] = $draw;
        $return_array["recordsTotal"] = 0;
        $return_array["recordsFiltered"] = 0;
        $return_array["data"] = array();

        if (COUNT($result) > 0) {
            $return_array["recordsTotal"] = $total_count;
            $return_array["recordsFiltered"] = $total_count;

            foreach ($result as $one_row) {
                $affected_detail = self::createAffected($one_row->table_name, $one_row->affected_id, $one_row->event_type);

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "icon" => $affected_detail["icon"],
                    "event_type" => $affected_detail["event_type"],
                    "actor" => "<a href='/user_management/detail/" . $one_row->user_id . "' title='" . trans('event_logs.go_actor') . "' target='_blank'>" . $one_row->actor . "</a>",
                    "affected" => $affected_detail["affected_value"],
                    "date" => date('d/m/Y H:i:s', strtotime($one_row->date))
                );

                if ($one_row->event_type == "corrupted_data") {
                    $tmp_array["actor"] = trans("global.system");
                }

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    private function createAffected($table_name, $affected_id, $event_type)
    {
        $return_array = array();
        $affected_value = "";
        $event_value = trans("event_logs." . $table_name . "_" . $event_type);
        $icon = '';
        $icon_color = "red";
        $small_icon = "times";

        if ($event_type == "create") {
            $icon_color = "green";
            $small_icon = "plus";
        } else if ($event_type == "update") {
            $icon_color = "#6699ff";
            $small_icon = "refresh";
        } else if ($event_type == "delete") {
            $icon_color = "red";
            $small_icon = "times";
        } else if ($event_type == "profile_update") {
            $icon_color = "#3232bd";
            $small_icon = "refresh";
        } else if ($event_type == "user_change_authorization") {
            $icon_color = "orange";
            $small_icon = "star";
        } else if ($event_type == "user_status_activated") {
            $icon_color = "green";
            $small_icon = "check";
        } else if ($event_type == "user_status_deactivated") {
            $icon_color = "darkRed";
            $small_icon = "circle";
        } else if ($event_type == "login") {
            $icon_color = "green";
            $small_icon = "unlock";
        } else if ($event_type == "logout") {
            $icon_color = "orange";
            $small_icon = "lock";
        } else if ($event_type == "corrupted_data") {
            $icon_color = "darkRed";
            $small_icon = "exclamation-circle";
        }

        if ($table_name == "users") {
            $result = DB::select("SELECT name FROM users WHERE id=" . $affected_id);
            if (isset($result[0]->name)) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/user_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->name . "</a>";
            }
            $icon .= '<i class="fa fa-user-o fa-2x" style="color:' . $icon_color . ';"></i>';
        } else if ($table_name == "clients") {
            $result = DB::select("SELECT name FROM clients WHERE id=" . $affected_id);
            if (isset($result[0]->name)) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/client_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->name . "</a>";
            }
            $icon .= '<i class="fa fa-handshake-o fa-2x" style="color:' . $icon_color . ';"></i>';
        }else if ($table_name == "sellers") {
            $result = DB::select("SELECT name FROM clients WHERE id=" . $affected_id);
            if (isset($result[0]->name)) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/seller_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->name . "</a>";
            }
            $icon .= '<i class="fa fa-handshake-o fa-2x" style="color:' . $icon_color . ';"></i>';
        } else if ($table_name == "distributors") {
            $result = DB::select("SELECT name FROM distributors WHERE id=" . $affected_id);
            if (isset($result[0]->name)) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->name;
                else
                    $affected_value = "<a href='/distributors_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->name . "</a>";
            }
            $icon .= '<i class="fa fa-sitemap fa-2x" style="color:' . $icon_color . ';"></i>';
        }else if ($table_name == "booking") {
            $result = DB::select("SELECT booking_title FROM booking WHERE id=" . $affected_id);
            if (isset($result[0]->booking_title)) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->booking_title;
                else
                    $affected_value = "<a href='/booking_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->booking_title . "</a>";

            }
            $icon .= '<i class="fa fa-sitemap fa-2x" style="color:' . $icon_color . ';"></i>';
        }else if ($table_name == "order") {
            $result = DB::select("SELECT booking_title FROM booking WHERE id=" . $affected_id);
            if (isset($result[0]->booking_title)) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->booking_title;
                else
                    $affected_value = "<a href='/order_management/detail/" . $affected_id . "' target='_blank'>" . $result[0]->booking_title . "</a>";
            }
            $icon .= '<i class="fa fa-sitemap fa-2x" style="color:' . $icon_color . ';"></i>';
        }else if ($table_name == "offers") {
            $result = DB::select("SELECT id FROM booking_offers WHERE id=" . $affected_id);
            if (isset($result[0]->id)) {
                if ($event_type == "delete")
                    $affected_value = $result[0]->id;
                else
                    $affected_value = "<a href='/booking_management/offer/" . $affected_id . "' target='_blank'>" . $result[0]->id . "</a>";
            }
            $icon .= '<i class="fa fa-sitemap fa-2x" style="color:' . $icon_color . ';"></i>';
        }


        $icon .= '&nbsp;' .
            //'<span style="position:relative;">' .
            '<span style="position:relative;">' .
            '<i class="fa fa-' . $small_icon . '" style="position: absolute;color:' . $icon_color . ';"></i>' .
            '</span>';
        //$icon .= '</span>';

        $return_array["event_type"] = $event_value;
        $return_array["affected_value"] = $affected_value;
        $return_array["icon"] = $icon;
        return $return_array;

    }

}
