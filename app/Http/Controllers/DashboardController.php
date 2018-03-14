<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;

class DashboardController extends Controller
{
    public function assetMapData(Request $request)
    {

    }

    // Maybe someday we will use this feature. But we will not use it for now. (by uk)
    public function assetCityData(Request $request)
    {

    }

    public function getLastBooking(Request $request)
    {

        $columns = array(
            "order_title",
            "client_id",
            "order_date",
            "detail"
        );


        $result = DB::table('booking as B')
            ->select('B.*', 'C.name as client_name')
            ->Join('clients as C', 'C.id', 'B.client_id')
            ->where('B.status', '<>', 0)
            ->where('B.assigned_id', '==', 0)
            ->orderBy('B.id', 'desc')
            ->limit(5)
            ->get();

        $return_table = "<thead><tr>";
        foreach ($columns as $column) {

            $return_table .= "<th><small>" . trans('system_summary.' . $column) . "</small></th>";
        }
        $return_table .= "<th></th></tr></thead><tbody>";

        foreach ($result as $one_data) {


            $return_table .= '<tr><td style="vertical-align:middle;"> ' . $one_data->booking_title . ' </td>';
            $return_table .= '<td style="vertical-align:middle;"> ' . $one_data->client_name . '  </td>';
            $return_table .= '<td style="vertical-align:middle;">  ' . date("d/m/Y", strtotime($one_data->booking_date)) . '</td>';

            $return_table .= '<td style="vertical-align:middle;">
                                    <a href="/booking_management/detail/' . $one_data->id . '" title="asd" class="btn btn-success btn-sm">
                                        <i class="fa fa-info-circle fa-lg"></i>
                                    </a>
                               </td>
                            </tr>';


        }
        $return_table .= " </tbody>";
        return $return_table;

    }

    public function getLastOrder(Request $request)
    {

        $columns = array(
            "order_title",
            "client_id",
            "seller_id",
            "order_date",
            "detail"
        );


        $result = DB::table('booking as B')
            ->select('B.*', 'C.name as client_name', 'S.name as seller_name')
            ->Join('clients as C', 'C.id', 'B.client_id')
            ->LeftJoin('clients as S', 'S.id', 'B.assigned_id')
            ->where('B.status', '<>', 0)
            ->where('B.assigned_id', '<>', 0)
            ->orderBy('B.id', 'desc')
            ->limit(5)
            ->get();

        $return_table = "<thead><tr>";
        foreach ($columns as $column) {

            $return_table .= "<th><small>" . trans('system_summary.' . $column) . "</small></th>";
        }
        $return_table .= "<th></th></tr></thead><tbody>";

        foreach ($result as $one_data) {


            $return_table .= '<tr><td style="vertical-align:middle;"> ' . $one_data->booking_title . ' </td>';
            $return_table .= '<td style="vertical-align:middle;"> ' . $one_data->client_name . '  </td>';
            $return_table .= '<td style="vertical-align:middle;"> ' . $one_data->seller_name . '</td>';
            $return_table .= '<td style="vertical-align:middle;"> ' . date("d/m/Y", strtotime($one_data->order_date)) . '</td>';
            $return_table .= '<td style="vertical-align:middle;">
                                    <a href="/order_management/detail/' . $one_data->id . '" title="asd" class="btn btn-success btn-sm">
                                        <i class="fa fa-info-circle fa-lg"></i>
                                    </a>
                               </td>
                            </tr>';

        }
        $return_table .= " </tbody>";
        return $return_table;
    }

    public function getLastClient(Request $request)
    {
        $columns = array(
            "client_id",
            "client_name",
            "client_email",
            "detail"
        );


        $result = DB::table('clients as C')
            ->select('*')
            ->where('type', '=', 1)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $return_table = "<thead><tr>";
        foreach ($columns as $column) {

            $return_table .= "<th><small>" . trans('system_summary.' . $column) . "</small></th>";
        }
        $return_table .= "<th></th></tr></thead><tbody>";

        foreach ($result as $one_data) {


            $return_table .= '<tr><td style="vertical-align:middle;"> ' . $one_data->id . ' </td>';
            $return_table .= '<td style="vertical-align:middle;"> ' . $one_data->name . '  </td>';
            $return_table .= '<td style="vertical-align:middle;"> ' . $one_data->email . ' </td>';
            $return_table .= '<td style="vertical-align:middle;">
                                    <a href="/client_management/detail/' . $one_data->id . '" title="asd" class="btn btn-success btn-sm">
                                        <i class="fa fa-info-circle fa-lg"></i>
                                    </a>
                               </td>
                            </tr>';

        }
        $return_table .= " </tbody>";
        return $return_table;
    }

    public function getLastSeller(Request $request)
    {
        $columns = array(
            "seller_id",
            "seller_name",
            "seller_email",
            "detail"
        );


        $result = DB::table('clients as C')
            ->select('*')
            ->where('type', '=', 2)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $return_table = "<thead><tr>";
        foreach ($columns as $column) {

            $return_table .= "<th><small>" . trans('system_summary.' . $column) . "</small></th>";
        }
        $return_table .= "<th></th></tr></thead><tbody>";

        foreach ($result as $one_data) {


            $return_table .= '<tr><td style="vertical-align:middle;"> ' . $one_data->id . ' </td>';
            $return_table .= '<td style="vertical-align:middle;"> ' . $one_data->name . '  </td>';
            $return_table .= '<td style="vertical-align:middle;"> ' . $one_data->email . ' </td>';
            $return_table .= '<td style="vertical-align:middle;">
                                    <a href="/seller_management/detail/' . $one_data->id . '" title="asd" class="btn btn-success btn-sm">
                                        <i class="fa fa-info-circle fa-lg"></i>
                                    </a>
                               </td>
                            </tr>';

        }
        $return_table .= " </tbody>";
        return $return_table;
    }
}

