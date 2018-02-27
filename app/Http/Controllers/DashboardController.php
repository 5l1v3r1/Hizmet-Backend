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
    public function assetMapData(Request $request){
        if( $request->has('type') && ( $request->input('type') == "meter" || $request->input('type') == "relay" || $request->input('type') == "analyzer" || $request->input('type') == "modem" ) && $request->has('filter') ){
            $device_type = $request->input('type');
            $filter = $request->input('filter');
        }
        else{
            abort(404);
        }

        // the color changes according to device type
        $device_color = array(
            "modem" => "#0033cc",
            "meter" => "#408000",
            "relay" => "#664400",
            "analyzer" => "#000000"
        );

        // @TODO: Daha sonra sadece aktif cihazları(status=1) getir diye düzenlenecek
        $where_for_modems = array(array("M.status","<>",0));
        $where_for_devices = array(array("D.status","<>",0));

        if( Auth::user()->user_type == 4 ){
            $where_for_modems[] = array("M.client_id", Auth::user()->org_id);
            $where_for_devices[] = array("M.client_id", Auth::user()->org_id);
        }
        else if( Auth::user()->user_type == 3 ){
            $where_for_modems[] = array("C.distributor_id", Auth::user()->org_id);
            $where_for_devices[] = array("C.distributor_id", Auth::user()->org_id);
        }

        if( $device_type == "modem" ){
            $devices = DB::table('modems as M')
                ->select(
                    'M.id as device_id',
                    'M.serial_no as device_no',
                    'M.last_connection_at as last_connection_at',
                    DB::raw('MAX(D.data_period) as data_period'),
                    'C.id as client_id',
                    'C.name as client_name',
                    DB::raw("'modem' as device_type"),
                    DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.text')) as location_text"),
                    DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.verbal')) as location_verbal"),
                    DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.latitude')) as location_latitude"),
                    DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.longitude')) as location_longitude")
                )
                ->join('clients as C', 'M.client_id', 'C.id')
                ->join('devices as D', 'M.id', 'D.modem_id')
                ->where($where_for_modems)
                ->groupBy('D.modem_id')
                ->get();

            $devices_unconnected = array();
            $devices_without_error = array();

            foreach($devices as $one_row){
                if($one_row->last_connection_at != null) {
                    $last_data_diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($one_row->last_connection_at));
                    $minutes = round($last_data_diff / 60);
                    $minutes_verbal = Helper::secondsToTime($last_data_diff);

                    if ($minutes > $one_row->data_period) {
                        $one_row->alert_exp = trans("devices.last_connection_verbal", array("verbal" => $minutes_verbal));
                        $one_row->alert_type = "warning";
                        $devices_unconnected[] = $one_row;
                    }
                    else{
                        $devices_without_error[] = $one_row;
                    }
                }
                else{
                    $one_row->alert_exp = '<span style="color: #cc0000;"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> ' . trans("asset_map.no_connection_yet", ['device' => trans('asset_map.modem') ]) . '</span>';

                    $one_row->alert_type = "warning";
                    $devices_unconnected[] = $one_row;
                }
            }

            if( $filter == "connection" ){
                $devices = $devices_unconnected;
            }
            else if( $filter == "normal" ){
                $devices = $devices_without_error;
            }
        }
        else{
            $where_for_devices[] = array("DT.type",$device_type);

            $devices = DB::table('devices as D')
                ->select(
                    'D.id as device_id',
                    'D.device_no as device_no',
                    'C.id as client_id',
                    'C.name as client_name',
                    'DT.type as device_type',
                    'D.data_period as data_period',
                    'D.last_data_at as last_data_at',
                    DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.text')) as location_text"),
                    DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.verbal')) as location_verbal"),
                    DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.latitude')) as location_latitude"),
                    DB::raw("JSON_UNQUOTE(json_extract(M.location,'$.longitude')) as location_longitude")
                )
                ->join('device_type as DT', 'DT.id', 'D.device_type_id')
                ->join('modems as M', 'M.id', 'D.modem_id')
                ->join('clients as C', 'M.client_id', 'C.id')
                ->where($where_for_devices)
                ->get();

            $devices_unconnected = array();
            $devices_reactive = array();
            $devices_without_error = $devices;

            if( $filter == "connection" || $filter == "normal" ){
                foreach($devices as $key=>$one_row){
                    if($one_row->last_data_at != null) {
                        $last_data_diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($one_row->last_data_at));
                        $minutes = round($last_data_diff / 60);
                        $minutes_verbal = Helper::secondsToTime($last_data_diff);

                        if ($minutes > $one_row->data_period) {
                            $one_row->alert_exp = "<span style='color: #cc0000;'>" . trans("devices.last_data_verbal", array("verbal" => $minutes_verbal)) . "</span>";
                            $one_row->alert_type = "warning";
                            $devices_unconnected[] = $one_row;
                            unset($devices_without_error[$key]);
                        }
                    }
                    else{
                        $one_row->alert_exp = '<span style="color: #cc0000;"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> ' . trans("asset_map.no_connection_yet", ['device' => trans('asset_map.'.$one_row->device_type) ]) . '</span>';
                        $one_row->alert_type = "warning";
                        $devices_unconnected[] = $one_row;
                        unset($devices_without_error[$key]);
                    }
                }
            }

            if( $filter == "reactive" || $filter == "normal" ){
                $reactive_devices = self::getLastReactives($request, true);

                if( is_array($reactive_devices) && COUNT($reactive_devices)>0){
                    foreach ($reactive_devices as $od){
                        foreach ($devices as $key=>$one_device){
                            if( $one_device->device_no == $od->device_no ){
                                $one_device->alert_exp = "<span style='color: #cc0000;'>" . $od->reactive . "</span>";
                                $one_device->alert_type = "danger";
                                $devices_reactive[] = $one_device;
                                unset($devices_without_error[$key]);
                                break;
                            }
                        }
                    }
                }
            }

            if( $filter == "normal" ){
                $devices = $devices_without_error;
            }
            else if( $filter == "connection" ){
                $devices = $devices_unconnected;
            }
            else if( $filter == "reactive" ){
                $devices = $devices_reactive;
            }
            else{
                $devices = array();
            }
        }

        $return_array = array();

        foreach ($devices as $one_result){
            if( !(isset($one_result->alert_exp) && isset($one_result->alert_type)) ){
                $one_result->alert_exp = "<span style='color:#00cc00;font-weight: bold;'><i class='fa fa-check fa-lg'></i> ". trans('asset_map.no_problem') ."</span>";
                $one_result->alert_type = "normal";
            }

            $device_url = "";
            if( $one_result->device_type == "modem" ){
                $device_url = "/modem_management/detail/".$one_result->device_id."/#alarms";
            }
            else{
                $device_url = "/".$one_result->device_type."/detail/".$one_result->device_id."/#alarms";
            }

            $popover_content = '<div style="font-size: 12px;">';

            $popover_content .= '<p><strong>' . trans('asset_map.device_no') . ': </strong>'. $one_result->device_no .'</p>';

            $popover_content .= '<p><strong>' . trans('asset_map.address') . ': </strong>' . $one_result->location_text . '</p>';

            $popover_content .= '<p>'. $one_result->alert_exp .' </p>';

            $popover_content .= '<hr style="margin: 5px 0;">
                                <div class="text-center">
                                    <a href="'.$device_url.'" target="_blank">
                                        <i class="fa fa-arrow-circle-right" aria-hidden="true"></i> '.trans('asset_map.show_all_alerts').'
                                    </a>
                                </div></div>';

            $client = '<a href="/client_management/detail/'.$one_result->client_id.'" title="'.trans('asset_map.go_client_detail').'" target="_blank"> '.$one_result->client_name . '</a>';

            $return_array[] = array(
                "id" => $one_result->device_id,
                "device_no" => $one_result->device_no,
                "alart_exp" => $one_result->alert_exp,
                "alert_type" => $one_result->alert_type, // It can be danger or warning according to alarm type
                "latitude" => $one_result->location_latitude,
                "longitude" => $one_result->location_longitude,
                "color" => $device_color[$one_result->device_type], // It changes according to device type
                "title" => $one_result->location_verbal,
                "client" => $client,
                "content" => $popover_content,
                "filter" => $filter
            );

        }

        return json_encode($return_array);
    }

    // Maybe someday we will use this feature. But we will not use it for now. (by uk)
    public function assetCityData(Request $request){
        if( !$request->has('city') ){
            abort(404);
        }

        $city = strtolower("Ankara");

        // get modems of the city
        $modems_count = DB::table('modems as M')
                ->where('status', '<>', 0)
                ->whereRaw("lcase(JSON_UNQUOTE(json_extract(M.location,'$.verbal'))) LIKE %".strtolower($city)."%")
                ->count();

        $return_array = array(
            "modems" => $modems_count
        );

        return json_encode($return_array);
    }

    public function getLastReactives(Request $request, $asset_map=false){
        if( !($request->has('type') && $request->input('type') != "") ){
            return "ERROR";
        }

        $reactive_devices = array();
        $return_table = "";
        $type = $request->input('type');

        $columns = array(
            "device_no_type",
            "client",
            "reactive",
            "last_data_date"
        );

        $param_array = array();
        $where_clause = " D.status <> 0 ";

        //Add filter according to user type
        if( Auth::user()->user_type == 3 ){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }
        else if( Auth::user()->user_type == 4 ){
            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND C.id=? ";
        }

        if( $type != "all_devices" ){
            $param_array[] = "". $type;
            $where_clause .= " AND DT.type = ? ";
        }

        $result = DB::table('devices as D')
            ->select('D.*', 'DT.type as device_type', 'C.id as client_id', 'C.name as client_name', 'M.serial_no as modem_no')
            ->leftJoin('device_type as DT', 'DT.id', 'D.device_type_id')
            ->leftJoin('modems as M', 'M.id', 'D.modem_id')
            ->whereRaw($where_clause, $param_array)
            ->leftJoin('clients as C', 'C.id', 'M.client_id')
            ->get();

        if( $result && COUNT($result)>0 && is_numeric($result[0]->id) ){
            $is_table_show = false;

            $return_table = "<thead><tr>";

            foreach ($columns as $column) {
                if( Auth::user()->user_type == 4 && $column == "client" ){
                    continue;
                }

                $return_table .= "<th><small>". trans('system_summary.'.$column) ."</small></th>";
            }

            $return_table .= "<th></th></tr></thead><tbody>";

            foreach ($result as $one_data){
                $device = $one_data->device_no . ' (' . trans('global.'.$one_data->device_type) . ')';
                $device_url = '/'.$one_data->device_type.'/detail/'.$one_data->id.'/#tab-1';
                $inductive_ratio = 0;
                $capacitive_ratio = 0;
                $contract_power_tooltip = '';
                $reactive_info = array();

                // Calculate inductive and capacitive from invoice day to now
                $invoice_day = ($one_data->invoice_day < 10 ? "0" : "") . $one_data->invoice_day;
                $start_date = date($invoice_day.'/m/Y');
                if( $invoice_day > date('d') ){
                    $start_date = date($invoice_day.'/m/Y', strtotime("-1 month"));
                }
                $start_date = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));

                if( $one_data->device_type == "meter"){
                    $table = "device_records_meter";
                }
                else if($one_data->device_type == "relay" || $one_data->device_type == "analyzer"){
                    $table = "device_records_modbus";
                }

                $device_records = DB::table(''.$table.' as DR')
                    ->select(
                        'DR.device_serial_no as device_no',
                        'DR.positive_active_energy_total as active',
                        'DR.imported_inductive_reactive_energy_total_Q1 as inductive',
                        'DR.exported_capacitive_reactive_total_Q4 as capacitive',
                        'DR.server_timestamp as date',
                        'DR.id as id'
                    )
                    ->where('DR.device_serial_no', $one_data->device_no)
                    ->where('DR.modem_serial_no', $one_data->modem_no)
                    ->where(DB::raw('DATE(DR.server_timestamp)'), '>=', $start_date)
                    ->orderBy('DR.id', 'desc')
                    ->get();

                if( COUNT($device_records) > 0 && isset($device_records[0]->device_no) ){
                    $rr = $device_records->toArray();
                    $first_record = end($rr);
                    $last_record = $rr[0];
                    $active_consumption = 0;
                    $inductive_ratio = 0;
                    $capacitive_ratio = 0;

                    $contract_power_tooltip = 'data-toggle="tooltip" data-placement="bottom" title="<div style=\'text-align: left;\'><b>- '.trans('devices.contract_power').':</b> '.$one_data->contract_power.' <br /> <b>- '.trans('devices.invoice_day').':</b> '.$one_data->invoice_day.' <br/> <b>- '.trans('devices.first_data').':</b> '.date('d/m/Y H:i:s', strtotime($first_record->date)).' <br/> <b>- '.trans('devices.last_data').':</b> '.date('d/m/Y H:i:s', strtotime($last_record->date)).' </div>"';

                    $is_reactive = false;

                    if( isset($last_record->active) && !is_null($last_record->active) && isset($first_record->active) && !is_null($first_record->active) ){
                        $active_consumption = ($last_record->active - $first_record->active);
                    }

                    if( $active_consumption > 0 ){
                        if( !(is_null($last_record->inductive) || trim($last_record->inductive) == "") ){
                            $inductive_consumption = ($last_record->inductive - $first_record->inductive);
                            $inductive_ratio = ($inductive_consumption/$active_consumption)*100;
                            $inductive_ratio = (float)number_format($inductive_ratio,2);
                        }

                        if( !(is_null($last_record->capacitive) || trim($last_record->capacitive) == "") ){
                            $capacitive_consumption = ($last_record->capacitive - $first_record->capacitive);
                            $capacitive_ratio = ($capacitive_consumption/$active_consumption)*100;
                            $capacitive_ratio = (float)number_format($capacitive_ratio,2);
                        }

                        // Control reactive penalty according to contract power
                        if( $one_data->contract_power <= 9 ){ }
                        else if( $one_data->contract_power > 9 && $one_data->contract_power < 30 ){
                            if( $inductive_ratio != 0 ){
                                if( $inductive_ratio >= 33 ){
                                    $is_reactive = true;
                                }
                            }

                            if( is_float($capacitive_ratio) ){
                                if( $capacitive_ratio >= 20 ){
                                    $is_reactive = true;
                                }
                            }
                        }
                        else if( $one_data->contract_power >= 30 ){
                            if( $inductive_ratio != 0 ){
                                if( $inductive_ratio >= 20 ){
                                    $is_reactive = true;
                                }
                            }

                            if( is_float($capacitive_ratio) ){
                                if( $capacitive_ratio >= 15 ){
                                    $is_reactive = true;
                                }
                            }
                        }

                        if( $is_reactive == true ){
                            if( $inductive_ratio > $capacitive_ratio ){
                                $reactive_info[$one_data->device_no] = array(
                                    "reactive" => '<span '.$contract_power_tooltip.' style="color: #cc0000;font-weight: bold;">% ' . $inductive_ratio . ' ('.trans('system_summary.inductive').')</span>'
                                );
                            }else{
                                $reactive_info[$one_data->device_no] = array(
                                    "reactive" => '<span '.$contract_power_tooltip.' style="color: #cc0000;font-weight: bold;">% ' . $capacitive_ratio . '</span> ('.trans('system_summary.capacitive').')'
                                );
                            }

                            $one_data->reactive = $reactive_info[$one_data->device_no]["reactive"];
                            $reactive_devices[] = $one_data;
                        }
                    }
                } // is count device_record > 0

                if( COUNT($reactive_info) > 0 ){
                    $is_table_show = true;

                    $return_table .= '
                    <tr>
                        <td style="vertical-align:middle;"> '. $device .' </td>
                        
                        '.(Auth::user()->user_type == 4?'':'<td style="vertical-align:middle;"><a href="/client_management/detail/'.$one_data->client_id.'/#alarms" title="'.trans('system_summary.go_client_detail').'">'.$one_data->client_name.'</a></td>').'
                        ';

                    $return_table .= '<td style="vertical-align:middle;"> '. $reactive_info[$one_data->device_no]['reactive'] .'</td>';

                    $return_table .= '<td style="vertical-align:middle;"> '.($one_data->last_data_at==null?trans('system_summary.never_connection_yet'):date("d/m/Y H:i", strtotime($one_data->last_data_at))).' </td>';

                    $return_table .= '
                            <td style="vertical-align:middle;">
                                <a href="'.$device_url.'" title="'.trans("system_summary.go_device_detail") .'" class="btn btn-danger btn-sm">
                                    <i class="fa fa-info-circle fa-lg"></i>
                                </a>
                            </td>
                        </tr>';
                } // fill in the table
            } // all_devices foreach

            if( $asset_map == true ){
                return $reactive_devices;
            }

            if( $is_table_show == true ){
                $return_table .= "
                            <tr>
                                <td colspan='".(COUNT($columns)+1)."'>
                                    <a href='/all_devices' style='color:#ed5565;'>
                                        <i class='fa fa-arrow-circle-right' aria-hidden='true'></i> 
                                        ".trans('system_summary.show_all_devices')."
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    ";
            }
            else{
                $typei = $type;
                if( $type == "all_devices" ){
                    $typei = "device";
                }
                $return_table = "<tbody>
                                <tr>
                                    <td colspan='".(COUNT($columns)+1)."' style='vertical-align: middle; text-align:center; color: #00cc00;'>
                                        <i class='fa fa-check fa-2x' aria-hidden='true'></i> 
                                        ".trans('system_summary.no_reactive_device', ['device' => trans('global.'.$typei)])."
                                    </td>
                                </tr>
                            </tbody>";
            }
        }
        else{
            $return_table = "<tbody>
                                <tr>
                                    <td colspan='".(COUNT($columns)+1)."' style='vertical-align: middle; text-align:center; color: #cc0000;'>
                                        <i class='fa fa-times-circle fa-lg' aria-hidden='true'></i> 
                                        ".trans('system_summary.nexist_device')."
                                    </td>
                                </tr>
                            </tbody>";
        }

        return $return_table;
    }

    public function getLastAlerts(Request $request){
        if( !($request->has('type') && $request->input('type') != "") ){
            return "ERROR";
        }

        $return_table = "";
        $type = $request->input('type'); // it can be reactive, connection, current or voltage
        $columns = array(
            "device_no_type",
            "client",
            "date"
        );

        $param_array = array();
        $where_clause = " A.status = 1 ";

        if( $type == "reactive" ){
            $columns = array(
                "device_no_type",
                "client",
                "reactive_ratio_type",
                "date"
            );
        }
        else if( $type == "connection" ){
            $columns = array(
                "device_no_type",
                "client",
                "last_connection_date"
            );
        }

        //Add filter according to user type
        if(Auth::user()->user_type == 4){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.id = ? ";
        }
        else if(Auth::user()->user_type == 3){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND DD.id = ? ";
        }

        $result = DB::table('alerts as A')
            ->select('A.*', 'D.id as device_id','D.device_no as device_no', 'M.id as modem_id','M.serial_no as modem_no', 'DD.name as distributor_name', 'DD.id as distributor_id', 'C.name as client_name', 'C.id as client_id', 'DT.type as device_type')
            ->leftJoin('devices as D', 'D.id', 'A.device_id')
            ->leftJoin('device_type as DT', 'D.device_type_id', 'DT.id')
            ->leftJoin('modems as M', 'D.modem_id', 'M.id')
            ->leftJoin('clients as C', 'M.client_id', 'C.id')
            ->leftJoin('distributors as DD', 'C.distributor_id', 'DD.id')
            ->where('D.status', '<>', 0)
            ->where('M.status', '<>', 0)
            ->where('A.type', $type)
            ->whereRaw($where_clause, $param_array)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        if( $result && COUNT($result)>0 && is_numeric($result[0]->id) ){
            $return_table = "<thead><tr>";

            foreach ($columns as $column) {
                if( Auth::user()->user_type == 4 && $column == "client" ){
                    continue;
                }

                $return_table .= "<th><small>". trans('system_summary.'.$column) ."</small></th>";
            }

            $return_table .= "<th></th></tr></thead><tbody>";

            foreach ($result as $one_data){
                $alert_detail = json_decode($one_data->detail);

                $device_url = '/'.$one_data->device_type.'/detail/'.$one_data->device_id.'/#alarms';
                $device = $one_data->device_no . ' (' . trans('global.'.$one_data->device_type) . ')';

                if( $type == "connection" && $one_data->sub_type == "modem" ){
                    $device = $one_data->modem_no . ' (' . trans('global.modem') . ')';
                    $device_url = '/modem_management/detail/'.$one_data->modem_id.'/#alarms';
                }

                if( $type == "reactive" ){
                    $device_url = '/'.$one_data->device_type.'/detail/'.$one_data->device_id.'/#tab-1';
                }

                $return_table .= '
                    <tr>
                        <td style="vertical-align:middle;"> '. $device .' </td>
                        '.(Auth::user()->user_type == 4?'':'<td style="vertical-align:middle;"><a href="/client_management/detail/'.$one_data->client_id.'/#alarms" title="'.trans('system_summary.go_client_detail').'">'.$one_data->client_name.'</a></td>').'
                        ';

                    if( $type == "reactive" ){
                        $return_table .= '<td style="vertical-align:middle;"> <span style="color: #cc0000;font-weight: bold;"> % '. number_format($alert_detail->ratio, 2).'</span> ('.trans('devices.'.$one_data->sub_type).') 
                        </td>';

                        $return_table .= '<td style="vertical-align:middle;"> '.date("d/m/Y H:i:s", strtotime($one_data->created_at)).' </td>';
                    }
                    else if( $type == "connection" ){
                        $return_table .= '<td style="vertical-align:middle;"> '.($alert_detail->last_connection_date==null?trans('system_summary.never_connection_yet'):date("d/m/Y H:i:s", strtotime($alert_detail->last_connection_date))).' </td>';
                    }
                    else{
                        $return_table .= '<td style="vertical-align:middle;"> '.date("d/m/Y", strtotime($one_data->created_at)).' </td>';
                    }

                $return_table .= '
                            <td style="vertical-align:middle;">
                                <a href="'.$device_url.'" title="'.trans("system_summary.go_device_detail") .'" class="btn btn-warning btn-sm">
                                    <i class="fa fa-info-circle fa-lg"></i>
                                </a>
                            </td>
                        </tr>';
            }

            $return_table .= "
                    <tr>
                        <td colspan='".(COUNT($columns)+1)."'>
                            <a href='/alerts/#alarms' style='color:#f8ac59;'>
                                <i class='fa fa-arrow-circle-right' aria-hidden='true'></i> 
                                ".trans('system_summary.show_all_alerts')."
                            </a>
                        </td>
                    </tr>
                </tbody>
            ";
        }
        else{
            $return_table = "<tbody>
                                <tr>
                                    <td colspan='".(COUNT($columns)+1)."' style='vertical-align: middle; text-align:center; color: #00cc00;'>
                                        <i class='fa fa-check fa-2x' aria-hidden='true'></i> 
                                        ".trans('system_summary.no_alarms_to_show')."
                                    </td>
                                </tr>
                            </tbody>";
        }

        return $return_table;
    }

    public function getLastDevices(Request $request){
        if( !($request->has('type') && $request->input('type') != "") ){
            return "ERROR";
        }

        $return_table = "";
        $all_link = "javascript:void(1);";

        $type = $request->input('type');

        $param_array = array();
        $where_clause = " 1=1 ";

        //Add filter according to user type
        if( Auth::user()->user_type == 3 ){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }
        else if( Auth::user()->user_type == 4 ){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.id=? ";
        }

        if( $type == "modem"){
            $all_link = "/modem_management";
            $title_type = "modem";

            $columns = array(
                "modem_no",
                "type",
                "client",
                "last_connection_date"
            );

            $where_clause .= " AND M.status<>0 ";

            $result = DB::table('modems as M')
                ->select('M.id as id', 'M.serial_no as device_no', 'M.last_connection_at as last_connection_at', 'MT.type as type', 'C.id as client_id', 'C.name as client_name')
                ->leftJoin('modem_type as MT', 'MT.id', 'M.modem_type_id')
                ->leftJoin('clients as C', 'C.id', 'M.client_id')
                ->whereRaw($where_clause, $param_array)
                ->orderBy('M.id', 'desc')
                ->limit(5)
                ->get();
        }
        else if( $type == "meter" || $type == "relay" || $type == "analyzer" ){
            $all_link = "/".$type;
            $title_type = "device";

            $columns = array(
                "device_no",
                "modem_no",
                "client",
                "last_data_date"
            );

            $param_array[] = $type;
            $where_clause .= " AND D.status<>0 AND DT.type=?";

            $result = DB::table('devices as D')
                ->select('D.id as id', 'D.device_no as device_no', 'D.last_data_at as last_data_at', 'M.id as modem_id', 'M.serial_no as modem_no', 'C.id as client_id', 'C.name as client_name')
                ->leftJoin('device_type as DT', 'DT.id', 'D.device_type_id')
                ->leftJoin('modems as M', 'M.id', 'D.modem_id')
                ->leftJoin('clients as C', 'C.id', 'M.client_id')
                ->whereRaw($where_clause, $param_array)
                ->orderBy('D.id', 'desc')
                ->limit(5)
                ->get();
        }

        if( $result && COUNT($result)>0 && is_numeric($result[0]->id) ){
            $return_table = "<thead><tr>";

            foreach ($columns as $column) {
                if( Auth::user()->user_type == 4 && $column == "client" ){
                    continue;
                }

                $return_table .= "<th><small>". trans('system_summary.'.$column) ."</small></th>";
            }

            $return_table .= "<th></th></tr></thead><tbody>";

            foreach ($result as $one_data){
                $device = $one_data->device_no;
                $device_url = 'javascript:void(0);';

                if( $type == "modem" ){
                    $device_url = '/modem_management/detail/'.$one_data->id.'';
                }
                else{
                    $device_url = '/'.$type.'/detail/'.$one_data->id.'/#tab-1';
                }

                $return_table .= '<tr><td style="vertical-align:middle;"> '. $device .' </td>';

                if( $type == "modem" ){
                    $return_table .= '<td style="vertical-align:middle;"> '. $one_data->type .' </td>';
                }
                else{
                    $return_table .= '<td style="vertical-align:middle;"><a href="/modem_management/detail/'.$one_data->modem_id.'" title="'.trans('system_summary.go_modem_detail').'"> '. $one_data->modem_no .' </a></td>';
                }

                        
                $return_table .= (Auth::user()->user_type == 4?'':'<td style="vertical-align:middle;"><a href="/client_management/detail/'.$one_data->client_id.'/#alarms" title="'.trans('system_summary.go_client_detail').'">'.$one_data->client_name.'</a></td>').'
                        ';

                if( $type == "modem" ){
                    $return_table .= '<td style="vertical-align:middle;"> '.($one_data->last_connection_at==null?trans('system_summary.never_connection_yet'):date("d/m/Y H:i:s", strtotime($one_data->last_connection_at))).' </td>';
                }
                else{
                    $return_table .= '<td style="vertical-align:middle;"> '.($one_data->last_data_at==null?trans('system_summary.never_connection_yet'):date("d/m/Y H:i:s", strtotime($one_data->last_data_at))).' </td>';
                }

                $return_table .= '
                            <td style="vertical-align:middle;">
                                <a href="'.$device_url.'" title="'.trans("system_summary.go_".$title_type."_detail") .'" class="btn btn-info btn-sm">
                                    <i class="fa fa-info-circle fa-lg"></i>
                                </a>
                            </td>
                        </tr>';
            }

            $return_table .= "
                    <tr>
                        <td colspan='".(COUNT($columns)+1)."'>
                            <a href='".$all_link."' style='color:#0d898b;'>
                                <i class='fa fa-arrow-circle-right' aria-hidden='true'></i> 
                                ".trans('system_summary.show_all_devicesi', ['devices'=>trans('system_summary.'.$type.'si')])."
                            </a>
                        </td>
                    </tr>
                </tbody>
            ";
        }
        else{
            $return_table = "<tbody>
                                <tr>
                                    <td colspan='".(COUNT($columns)+1)."' style='vertical-align: middle; text-align:center; color: #cc0000;'>
                                        <i class='fa fa-exclamation-triangle' aria-hidden='true'></i> 
                                        ".trans('system_summary.no_data_to_show')."
                                    </td>
                                </tr>
                            </tbody>";
        }

        return $return_table;
    }

    public function getLastUcds(Request $request){
        if( !($request->has('type') && $request->input('type') != "") ){
            return "ERROR";
        }

        $type = $request->input('type');

        $return_table = "";
        $all_link = "javascript:void(1);";
        $param_array = array();
        $where_clause = " 1=1 ";

        if( $type == "users"){
            $all_link = "/user_management";
            $title_type = "user";

            $columns = array(
                "name_surname",
                "user_type",
                "company",
                "created_at"
            );

            $where_clause .= " AND U.status<>0 AND U.user_type<>1 ";

            //Add filter according to user type
            if( Auth::user()->user_type == 4){
                $param_array[]= Auth::user()->org_id;
                $where_clause .= " AND U.org_id = ? ";
            }
            else if( Auth::user()->user_type == 3){
                $param_array[]= Auth::user()->org_id;
                $where_clause .= " AND U.porg_id = ? ";

                $param_array[]= 4;
                $where_clause .= " AND U.user_type = ? ";
            }
            else if(Auth::user()->user_type == 2){
                $param_array[]= 2;
                $where_clause .= " AND U.user_type <> ? ";
            }
            else if(Auth::user()->user_type == 1){
                $param_array[]= Auth::user()->id;
                $where_clause .= " AND U.id <> ? ";
            }

            $result = DB::table('users as U')
                ->select('U.id as id', 'U.name as name', 'U.email as email', 'U.created_at as created_at', 'UT.type as type', DB::raw('(CASE WHEN U.user_type=3 THEN D.name WHEN U.user_type=4 THEN C.name ELSE "'.trans('global.main_distributor').'" END) as org_name, (CASE WHEN U.user_type=3 THEN concat("d_", D.id) WHEN U.user_type=4 THEN concat("c_",C.id) ELSE concat("md_",0) END) as org_id'))
                ->leftJoin('user_type as UT', 'UT.id', 'U.user_type')
                ->leftJoin('clients as C', 'C.id', 'U.org_id')
                ->leftJoin('distributors as D', 'D.id', 'U.org_id')
                ->whereRaw($where_clause, $param_array)
                ->orderBy('U.id', 'desc')
                ->limit(5)
                ->get();
        }
        else if( $type == "clients" ){
            $all_link = "/client_management";
            $title_type = "user";

            $columns = array(
                "name",
                "distributor",
                "address",
                "created_at"
            );

            $where_clause .= " AND C.status<>0 ";

            //Add filter according to user type
            if( Auth::user()->user_type == 4){
                $param_array[]= Auth::user()->org_id;
                $where_clause .= " AND C.id = ? ";
            }
            else if( Auth::user()->user_type == 3){
                $param_array[]= Auth::user()->org_id;
                $where_clause .= " AND C.distributor_id = ? ";
            }

            $result = DB::table('clients as C')
                ->select('C.id as id', 'C.name as name', 'D.id as distributor_id', 'D.name as distributor_name', DB::raw('JSON_UNQUOTE(json_extract(C.location,"$.verbal")) as address'), 'C.created_at as created_at')
                ->leftJoin('distributors as D', 'D.id', 'C.distributor_id')
                ->whereRaw($where_clause, $param_array)
                ->orderBy('C.id', 'desc')
                ->limit(5)
                ->get();
        }

        if( $result && COUNT($result)>0 && is_numeric($result[0]->id) ){
            $return_table = "<thead><tr>";

            foreach ($columns as $column) {
                if( Auth::user()->user_type == 4 && $column == "company" ){
                    continue;
                }

                $return_table .= "<th><small>". trans('system_summary.'.$column) ."</small></th>";
            }

            $return_table .= "<th></th></tr></thead><tbody>";

            foreach ($result as $one_data){
                $detail_url = 'javascript:void(0);';

                if( $type == "users" ){
                    $detail_url = '/user_management/detail/'.$one_data->id.'';
                }
                else if( $type == "clients" ){
                    $detail_url = '/client_management/detail/'.$one_data->id.'';
                }
                else if( $type == "distributors" ){
                    $detail_url = '/distributor_management/detail/'.$one_data->id.'';
                }

                $return_table .= '<tr><td style="vertical-align:middle;"> '. $one_data->name .' </td>';

                if( $type == "users" ){
                    $return_table .= '<td style="vertical-align:middle;"> '. trans('global.'.$one_data->type) .' </td>';

                    if( Auth::user()->user_type == 3 ){
                        $return_table .= '<td style="vertical-align:middle;"><a href="/client_management/detail/'.$one_data->org_id.'" title="'.trans('system_summary.go_client_detail').'">'.$one_data->org_name.'</a></td>';
                    }
                    else if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){
                        $org_prefix = explode('_', $one_data->org_id);

                        if( $org_prefix[0] == "md" ){
                            $return_table .= '<td style="vertical-align:middle;"> '.$one_data->org_name.' </td>';
                        }
                        else if( $org_prefix[0] == "d" ){
                            $return_table .= '<td style="vertical-align:middle;"><a href="/distributor_management/detail/'.$org_prefix[1].'" title="'.trans('system_summary.go_distributor_detail').'">'.$one_data->org_name.'</a></td>';
                        }
                        else if( $org_prefix[0] == "c" ){
                            $return_table .= '<td style="vertical-align:middle;"><a href="/client_management/detail/'.$org_prefix[1].'" title="'.trans('system_summary.go_client_detail').'">'.$one_data->org_name.'</a></td>';
                        }
                    }
                }
                else if( $type == "clients" ){
                    if( $one_data->distributor_id == 0){
                        $return_table .= '<td style="vertical-align:middle;"> '. trans('global.main_distributor') .' </td>';
                    }
                    else{
                        if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){
                            $return_table .= '<td style="vertical-align:middle;"><a href="/distributor_management/detail/'.$one_data->distributor_id.'" title="'.trans('system_summary.go_distributor_detail').'">'.$one_data->distributor_name.'</a></td>';
                        }
                        else{
                            $return_table .= '<td style="vertical-align:middle;"> '. $one_data->distributor_name .' </td>';
                        }
                    }

                    $return_table .= '<td style="vertical-align:middle;"> '. $one_data->address .' </td>';
                }

                $return_table .= '<td style="vertical-align:middle;"> '. date("d/m/Y", strtotime($one_data->created_at)) .' </td>';

                $return_table .= '
                            <td style="vertical-align:middle;">
                                <a href="'.$detail_url.'" title="'.trans("system_summary.go_".$title_type."_detail") .'" class="btn btn-success btn-sm">
                                    <i class="fa fa-info-circle fa-lg"></i>
                                </a>
                            </td>
                        </tr>';
            }

            if( Auth::user()->user_type != 4) {
                $return_table .= "
                        <tr>
                            <td colspan='" . (COUNT($columns) + 1) . "'>
                                <a href='" . $all_link . "' style='color:#1c84c6;'>
                                    <i class='fa fa-arrow-circle-right' aria-hidden='true'></i> 
                                    " . trans('system_summary.show_all_ucds', ['type' => trans('system_summary.' . $type . 'i')]) . "
                                </a>
                            </td>
                        </tr>
                    </tbody>
                ";
            }
        }
        else{
            $return_table = "<tbody>
                                <tr>
                                    <td colspan='".(COUNT($columns)+1)."' style='vertical-align: middle; text-align:center; color: #cc0000;'>
                                        <i class='fa fa-exclamation-triangle' aria-hidden='true'></i> 
                                        ".trans('system_summary.no_data_to_show')."
                                    </td>
                                </tr>
                            </tbody>";
        }

        return $return_table;
    }
}

