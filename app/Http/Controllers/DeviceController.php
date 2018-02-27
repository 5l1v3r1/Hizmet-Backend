<?php

namespace App\Http\Controllers;

use App\Helpers\HighChart;
use Illuminate\Http\Request;

use App\Http\Requests;

use App\Helpers\DataTable;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    private $device_columns;
    private $index_columns;
    private $curvol_columns;
    private $energy_columns;
    private $alerts_columns;

    //other constants
    private $co2_emission_value = 459.598;

    public function __construct()
    {
        $this->device_columns = array(
            "status"=>array("orderable"=>false),
            "device_no" => array(),
            "device_type" =>array("name"=>"type"),
            "modem_no" => array(),
            "client" => array("name" => "client_distributor"),
            "inductive" => array(),
            "capacitive" => array(),
            "data_period" => array(),
            "last_data_at" => array(),
            "buttons" => array("orderable" => false, "name" => "operations", "nowrap" => true),
        );

        $this->index_columns = array(
            "server_date" => array("nowrap"=>true),
            "active" => array("orderable"=>false, "name" => "active_with_unit"),
            "inductive" => array("orderable"=>false, "name" => "inductive_with_unit"),
            "capacitive" => array("orderable"=>false, "name" => "capacitive_with_unit"),
            "inductive_ratio" => array("orderable"=>false),
            "capacitive_ratio" => array("orderable"=>false),
            "t1" => array("orderable"=>false),
            "t2" => array("orderable"=>false),
            "t3" => array("orderable"=>false),
            "demand" => array("orderable"=>false),
            "demand_date" => array("orderable"=>false),
            "m_active" => array("orderable"=>false,"visible"=>false),
            "m_t1" => array("orderable"=>false,"visible"=>false),
            "m_t2" => array("orderable"=>false,"visible"=>false),
            "m_t3" => array("orderable"=>false,"visible"=>false),
            "m_distribution_cost" => array("orderable"=>false,"visible"=>false),
            "m_reactive_cost" => array("orderable"=>false,"visible"=>false),
            "m_trt" => array("orderable"=>false,"visible"=>false),
            "m_energy_fund" => array("orderable"=>false,"visible"=>false),
            "m_etv" => array("orderable"=>false,"visible"=>false,"tooltip"=>trans("devices.etv_exp")),
            "m_total" => array("orderable"=>false,"visible"=>false),
            "c_active" => array("orderable"=>false,"visible"=>false),
            "c_total" => array("orderable"=>false,"visible"=>false),
            "c_t1" => array("orderable"=>false,"visible"=>false),
            "c_t2" => array("orderable"=>false,"visible"=>false),
            "c_t3" => array("orderable"=>false,"visible"=>false),
        );

        $this->curvol_columns = array(
            "server_date" => array("orderable"=>false),
            "current_l1" => array("orderable" => false),
            "current_l2" => array("orderable"=>false),
            "current_l3" => array("orderable"=>false),
            "voltage_l1" => array("orderable"=>false),
            "voltage_l2" => array("orderable"=>false),
            "voltage_l3" => array("orderable"=>false),
            "cosfi_l1" => array("orderable"=>false),
            "cosfi_l2" => array("orderable"=>false),
            "cosfi_l3" => array("orderable"=>false),
            "active_power_l1" => array("orderable"=>false),
            "active_power_l2" => array("orderable"=>false),
            "active_power_l3" => array("orderable"=>false),
            "reactive_power_l1" => array("orderable"=>false),
            "reactive_power_l2" => array("orderable"=>false),
            "reactive_power_l3" => array("orderable"=>false),
        );

        $this->energy_columns = array(
            "server_date" => array("nowrap"=>true),
            "active" => array("orderable"=>false, "name" => "active_energy_with_unit"),
            "inductive" => array("orderable"=>false, "name" => "inductive_energy_with_unit"),
            "capacitive" => array("orderable"=>false, "name" => "capacitive_energy_with_unit"),
            "inductive_ratio" => array("orderable"=>false),
            "capacitive_ratio" => array("orderable"=>false),
            "t1" => array("orderable"=>false),
            "t2" => array("orderable"=>false),
            "t3" => array("orderable"=>false),
            "m_active" => array("orderable"=>false,"visible"=>false),
            "m_t1" => array("orderable"=>false,"visible"=>false),
            "m_t2" => array("orderable"=>false,"visible"=>false),
            "m_t3" => array("orderable"=>false,"visible"=>false),
            "m_distribution_cost" => array("orderable"=>false,"visible"=>false),
            "m_reactive_cost" => array("orderable"=>false,"visible"=>false),
            "m_trt" => array("orderable"=>false,"visible"=>false),
            "m_energy_fund" => array("orderable"=>false,"visible"=>false),
            "m_etv" => array("orderable"=>false,"visible"=>false,"tooltip"=>trans("devices.etv_exp")),
            "m_total" => array("orderable"=>false,"visible"=>false),
            "c_active" => array("orderable"=>false,"visible"=>false),
            "c_total" => array("orderable"=>false,"visible"=>false),
            "c_t1" => array("orderable"=>false,"visible"=>false),
            "c_t2" => array("orderable"=>false,"visible"=>false),
            "c_t3" => array("orderable"=>false,"visible"=>false),
        );

        $this->alerts_columns = array(
            "icon" => array("orderable" => false, "name" => false),
            "type" => array(),
            "device_no" => array("visible" => false, "name" => "device_no_type"),
            "notification_method" => array("orderable" => false),
            "client" => array("visible" => false, "name" => "client_distributor"),
            "created_at"=>array(),
            "buttons"=>array( "orderable" => false, "name" => "operations", "nowrap" => true)
        );

    }

    /**
     * Return Device info to detail page
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function deviceDetail(Request $request, $id){
        $the_device = DB::table('devices as D')
            ->select(
                'D.*',
                'DT.*',
                'D.id as device_id',
                'C.id as client_id',
                'C.name as client_name',
                'C.logo as client_avatar',
                DB::raw("(CASE WHEN C.distributor_id=0 THEN '". trans('global.main_distributor') ."' ELSE DD.name END) as distributor"), 
                'C.distributor_id as distributor_id',
                'U.name as created_by',
                DB::raw('JSON_UNQUOTE(json_extract(M.location,\'$.text\')) as location_text'),
                'M.serial_no as modem_serial',
                'M.id as modem_id',
                'F.name as fee_scale_name'
            )
            ->leftJoin('device_type as DT','D.device_type_id','=','DT.id')
            ->leftJoin('modems as M','D.modem_id','=','M.id')
            ->leftJoin('clients as C','M.client_id','=','C.id')
            ->leftJoin('distributors as DD','C.distributor_id','=','DD.id')
            ->leftJoin('users as U','D.created_by','=','U.id')
            ->leftJoin('fee_scales as F','F.id','=','D.fee_scale_id')
            ->where('D.id',$id)
            ->where('D.status','<>',0)
            ->where('DT.type',$request->segment(1))
            ->first();


        if(!(isset($the_device->id) && is_numeric($the_device->id))){
            abort(404);
        }

        //Has Auth user right to show this device detail?
        if( Auth::user()->user_type == 4 ){
            if(Auth::user()->org_id != $the_device->client_id)
                return "ERROR_1";
        }
        else if( Auth::user()->user_type == 3 ){
            if(Auth::user()->org_id != $the_device->distributor_id)
                return "ERROR_2";
        }

        /*$device_fee_scale = json_decode($the_device->fee_scale_id);
        //$device_fee_scale = end($device_fee_scale);
        //$device_fee_scale_id = $device_fee_scale->id;
        $device_fee_scale_type = $device_fee_scale->type;

        $device_fee_scale_name = DB::select('SELECT id, name FROM fee_scales WHERE original_id=? ORDER BY updated_at DESC LIMIT 1',[$device_fee_scale_id]);
        $device_fee_scale_name = $device_fee_scale_name[0]->name;

        $the_device->fee_scale_name = $device_fee_scale_name;
        $the_device->fee_scale_type = trans("devices.".$device_fee_scale_type);
        */

        $param_array = array('the_device' => json_encode($the_device));

        if($request->segment(1) == "meter"){
            // prepare index table object for meter detail
            $prefix = "mdindex";
            $url = "mdindex_get_data/".$id;
            $default_order = '[0,"desc"]';
            $meter_index_table = new DataTable($prefix,$url,$this->index_columns,$default_order,$request);
            $param_array["IndexDataTableObj"] = $meter_index_table;
            $meter_index_table->set_lang_page("devices");

            $invoice_day = ($the_device->invoice_day < 10 ? "0" : "") . $the_device->invoice_day;
            $start_date = date($invoice_day.'/m/Y');
            $today = date('d/m/Y');
            if( $invoice_day > date('d') ){
                $start_date = date($invoice_day.'/m/Y', strtotime("-1 month"));
            }
            $meter_index_table->set_date_range($start_date, date('d/m/Y'));

            $meter_index_table->set_init_fnct('' .
                '$("#div_mdindex_search_custom").html("' .
                    '<select style=\"width:60%;\" id=\"index_data_period\">' .
                        '<option value=\"periodic\">'.trans('devices.periodic').'</option>' .
                        '<option value=\"daily\">'.trans('devices.daily').'</option>' .
                        '<option value=\"monthly\">'.trans('devices.monthly').'</option>' .
                        '<option value=\"yearly\">'.trans('devices.yearly').'</option>' .
                    '</select>' .
                    '<span style=\"width:38%;float:right;\">' .
                        '<select style=\"width:100%;\" id=\"index_data_type\">' .
                            '<option value=\"index_kw\">kW-h</option>' .
                            '<option value=\"index_m\">'.trans('devices.price').'</option>' .
                            '<option value=\"index_c\">CO<sub>2</sub> (kg)</option>' .
                        '</select>' .
                    '</span>");
                
                
                $("#'.$prefix.'_start_date").datepicker("setDate", "'.$start_date.'");
                $("#'.$prefix.'_end_date").datepicker("setDate", "'.date('d/m/Y').'");
                
                $("#index_data_period").select2({
                     minimumResultsForSearch: Infinity
                }).change(function(){ 
                    the_val = $(this).val();
                    mdindex_filter_obj.table_showType = the_val;
                    
                    if(the_val == "daily"){
                 
                        $("#'.$prefix.'_start_date").datepicker("setDate", "'.$start_date.'");
                        $("#'.$prefix.'_end_date").datepicker("setDate", "'.$today.'");
                        
                    }
                    else if( the_val == "monthly" ){
                        $("#'.$prefix.'_start_date").datepicker("setDate", "01/01/"+(new Date().getFullYear()));
                        $("#'.$prefix.'_end_date").datepicker("setDate", "'.$today.'");
                    }
                    else if( the_val == "yearly" ){
                        $("#'.$prefix.'_start_date").datepicker("setDate", "01/01/"+((new Date().getFullYear())-5));
                        $("#'.$prefix.'_end_date").datepicker("setDate", "'.$today.'");
                    }
                    else{
                        $("#'.$prefix.'_start_date").datepicker("setDate", "'.$start_date.'");
                        $("#'.$prefix.'_end_date").datepicker("setDate", "'.$today.'");
                    }
                    
                    '.$prefix.'_filter_obj.show_type = the_val;
                    mdindex_dt.ajax.reload();
                });
                
                $("#index_data_type").select2({
                     minimumResultsForSearch: Infinity
                }).change(function(){
                    //hide all columns then show appropriate ones
                    mdindex_dt.columns().visible(false);
                    
                    the_val = $(this).val();
                    if(the_val == "index_m"){
                        
                        if("'.$the_device->fee_scale_type.'"=="single_rate_tariff")
                            mdindex_dt.columns( [ 0,11,15,16,17,18,19,20 ] ).visible( true, true );
                        else
                            mdindex_dt.columns( [ 0,12,13,14,15,16,17,18,19,20 ] ).visible( true, true );
                    }
                    else if(the_val == "index_c"){
                        mdindex_dt.columns( [ 0, 21, 22, 23,24,25 ] ).visible( true, true );
                    }
                    else{
                        mdindex_dt.columns( [ 0,1,2,3,4,5,6,7,8,9,10 ] ).visible( true, true );
                    }
                });
                
                //disable add new button
                $("#mdindex_add_new_button").attr("disabled","");
            
            ');

            // prepare current/voltage table object for meter detail
            $prefix = "mdcurvol";
            $url = "mdcurvol_get_data/".$id;
            $default_order = '[0,"desc"]';
            $meter_curvol_table = new DataTable($prefix,$url,$this->curvol_columns,$default_order,$request);
            $param_array["CurVolDataTableObj"] = $meter_curvol_table;
            $meter_curvol_table->set_lang_page("devices");
            $meter_curvol_table->set_date_range(date('d/m/Y'),date('d/m/Y'));


            $init_fnc = "
                $('#".$prefix."_table').find('thead').prepend('" .
                    "<tr>" .
                        //"<th style=\"text-align:center;vertical-align:middle;\" rowspan=\"2\" class=\"sorting_desc\">".trans("devices.server_date")."</th>" .
                        "<th style=\"border-bottom-width: 0px;\"></th>" .
                        "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.current_th")."</th>" .
                        "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.voltage_th")."</th>" .
                        "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.cosfi_th")."</th>" .
                        "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.active_power_th")."</th>" .
                        "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.reactive_power_th")."</th>" .
                    "</tr>');";

            //$init_fnc .= "$('#".$prefix."_table').find('thead').find('tr').first().next().find('th').first().remove();";
            $init_fnc .= "$('#".$prefix."_table').find('thead').find('tr').first().next().find('th').first().css('border-top-width', '0px');";

            //set columns styles
            $init_fnc .= '
                $(mdcurvol_dt.column(3).nodes()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(3).header()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(6).nodes()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(6).header()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(9).nodes()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(9).header()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(12).nodes()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(12).header()).css("border-right","1px solid #dddddd");
                
                mdcurvol_dt.columns.adjust().draw();
            ';

            //set disabled elements
            $init_fnc .='
                $("#mdcurvol_add_new_button").attr("disabled","");
                $("#div_mdcurvol_search_custom input").attr("disabled","");
            ';
            $meter_curvol_table ->set_init_fnct($init_fnc);


            $param_array["periodic_start_date"] = $start_date;
            $param_array["periodic_today"] = $today;

        }
        else if($request->segment(1) == "analyzer" || $request->segment(1) == "relay"){
            // prepare index table object for meter detail
            $prefix = "adenergy";
            $url = "adenergy_get_data/".$id;
            $default_order = '[0,"desc"]';
            $analyzer_energy_table = new DataTable($prefix, $url, $this->energy_columns, $default_order, $request);
            $param_array["EnergyDataTableObj"] = $analyzer_energy_table;
            $analyzer_energy_table->set_lang_page("devices");

            $invoice_day = ($the_device->invoice_day < 10 ? "0" : "") . $the_device->invoice_day;
            $start_date = date($invoice_day.'/m/Y');
            $today = date('d/m/Y');
            if( $invoice_day > date('d') ){
                $start_date = date($invoice_day.'/m/Y', strtotime("-1 month"));
            }
            $analyzer_energy_table->set_date_range($start_date, date('d/m/Y'));

            $analyzer_energy_table->set_init_fnct('' .
                '$("#div_adenergy_search_custom").html("' .
                    '<select style=\"width:60%;\" id=\"energy_data_period\">' .
                        '<option value=\"periodic\">'.trans('devices.periodic').'</option>' .
                        '<option value=\"daily\">'.trans('devices.daily').'</option>' .
                        '<option value=\"monthly\">'.trans('devices.monthly').'</option>' .
                        '<option value=\"yearly\">'.trans('devices.yearly').'</option>' .
                    '</select>' .
                    '<span style=\"width:38%;float:right;\">' .
                       '<select style=\"width:100%;\" id=\"energy_data_type\">' .
                            '<option value=\"energy_kw\">kW-h</option>' .
                            '<option value=\"energy_m\">'.trans('devices.price').'</option>' .
                            '<option value=\"energy_c\">CO<sub>2</sub> (kg)</option>' .
                        '</select>' .
                    '</span>");
                
                $("#'.$prefix.'_start_date").datepicker("setDate", "'.$start_date.'");
                $("#'.$prefix.'_end_date").datepicker("setDate", "'.date('d/m/Y').'");
                
                $("#energy_data_period").select2({
                     minimumResultsForSearch: Infinity
                }).change(function(){ 
                    the_val = $(this).val();
                    //adenergy_filter_obj.table_showType = the_val;
                    
                    if(the_val == "daily"){
                        $("#'.$prefix.'_start_date").datepicker("setDate", "'.$start_date.'");
                        $("#'.$prefix.'_end_date").datepicker("setDate", "'.$today.'");
                        
                    }
                    else if( the_val == "monthly" ){
                        $("#'.$prefix.'_start_date").datepicker("setDate", "01/01/"+(new Date().getFullYear()));
                        $("#'.$prefix.'_end_date").datepicker("setDate", "'.$today.'");
                    }
                    else if( the_val == "yearly" ){
                        $("#'.$prefix.'_start_date").datepicker("setDate", "01/01/"+((new Date().getFullYear())-5));
                        $("#'.$prefix.'_end_date").datepicker("setDate", "'.$today.'");
                    }
                    else{
                        $("#'.$prefix.'_start_date").datepicker("setDate", "'.$start_date.'");
                        $("#'.$prefix.'_end_date").datepicker("setDate", "'.$today.'");
                    }
                    
                    '.$prefix.'_filter_obj.show_type = the_val;
                    '.$prefix.'_dt.ajax.reload();
                });
                
                $("#energy_data_type").select2({
                     minimumResultsForSearch: Infinity
                }).change(function(){
                    //hide all columns then show appropriate ones
                    '.$prefix.'_dt.columns().visible(false);
                    
                    the_val = $(this).val();
                    if(the_val == "energy_m"){                        
                        if("'.$the_device->fee_scale_type.'" == "single_rate_tariff")
                            adenergy_dt.columns( [ 0,9,13,14,15,16,17,18 ] ).visible( true, true );
                        else
                            adenergy_dt.columns( [ 0,10,11,12,13,14,15,16,17,18 ] ).visible( true, true );
                    }
                    else if(the_val == "energy_c"){
                        adenergy_dt.columns( [ 0, 19, 20, 21, 22, 23 ] ).visible( true, true );
                    }
                    else{
                        adenergy_dt.columns( [ 0,1,2,3,4,5 ] ).visible( true, true );
                    }
                });
                
                //disable add new button
                $("#'.$prefix.'_add_new_button").attr("disabled","");
            ');

            // prepare current/voltage table object for analyzer detail
            $prefix = "mdcurvol";
            $url = "mdcurvol_get_data/".$id;
            $default_order = '[0,"desc"]';
            $meter_curvol_table = new DataTable($prefix,$url,$this->curvol_columns,$default_order,$request);
            $param_array["CurVolDataTableObj"] = $meter_curvol_table;
            $meter_curvol_table->set_lang_page("devices");
            $meter_curvol_table->set_date_range(date('d/m/Y'),date('d/m/Y'));

            $init_fnc = "
                $('#".$prefix."_table').find('thead').prepend('" .
                "<tr>" .
                //"<th style=\"text-align:center;vertical-align:middle;\" rowspan=\"2\" class=\"sorting_desc\">".trans("devices.server_date")."</th>" .
                "<th style=\"border-bottom-width: 0px;\"></th>" .
                "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.current_th")."</th>" .
                "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.voltage_th")."</th>" .
                "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.cosfi_th")."</th>" .
                "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.active_power_th")."</th>" .
                "<th style=\"text-align:center;\" colspan=\"3\">".trans("devices.reactive_power_th")."</th>" .
                "</tr>');";

            //$init_fnc .= "$('#".$prefix."_table').find('thead').find('tr').first().next().find('th').first().remove();";
            $init_fnc .= "$('#".$prefix."_table').find('thead').find('tr').first().next().find('th').first().css('border-top-width', '0px');";

            //set columns styles
            $init_fnc .= '
                
               $(mdcurvol_dt.column(3).nodes()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(3).header()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(6).nodes()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(6).header()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(9).nodes()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(9).header()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(12).nodes()).css("border-right","1px solid #dddddd");
                $(mdcurvol_dt.column(12).header()).css("border-right","1px solid #dddddd");
                
                mdcurvol_dt.columns.adjust().draw();
            ';

            //set disabled elements
            $init_fnc .='
                $("#mdcurvol_add_new_button").attr("disabled","");
                $("#div_mdcurvol_search_custom input").attr("disabled","");
            ';
            $meter_curvol_table ->set_init_fnct($init_fnc);

            $param_array["periodic_start_date"] = $start_date;
            $param_array["periodic_today"] = $today;
        }


        //prepare alerts table obj which belongs to this modem
        $prefix = "ddal";
        $url = "al_get_data/".$request->segment(1)."/".$id;
        $default_order = '[5,"desc"]';
        $alert_table = new DataTable($prefix, $url, $this->alerts_columns, $default_order, $request);
        $param_array["AlertsDataTableObj"] = $alert_table;
        $alert_table->set_add_right(false);
        $alert_table->set_lang_page("alerts");


        return view('pages.'.$request->segment(1).'_detail', $param_array);
    }

    /**
     * Prepare devices list on page [meter|relay|analyzer|all_devices]
     *
     * @param Request $request
     * @return $this
     */
    public function deviceTable(Request $request){
        if(Auth::user()->user_type == 4)
            $this->device_columns["client"]["visible"] = false;

        if(Auth::user()->user_type == 3)
            $this->device_columns["client"]["name"] = "client";

        if($request->segment(1) != "all_devices")
            $this->device_columns["device_type"]["visible"] = false;

        $prefix = "dt";
        $url = "get_device_data/".$request->segment(1);
        $default_order = '[8,"desc"]';

        $data_table = new DataTable($prefix, $url, $this->device_columns, $default_order, $request);
        $data_table->set_lang_page("devices");
        $data_table->set_add_right("add_new_device");

        return view('pages.device')->with(["DataTableObj"=>$data_table,"device_type"=>$request->segment(1)]);
    }

    /**
     * Return devices data to fill dataTable on page [meter|relay|analyzer|all_devices]
     *
     * @param Request $request
     * @param $device_type
     * @param string $detail_type
     * @param string $detail_org_id
     */
    public function getDeviceData(Request $request, $device_type, $detail_type = "", $detail_org_id = ""){
        if( !(Helper::has_right(Auth::user()->operations, "view_".$device_type)) ){
            abort(404);
        }

        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $order_column = "D.last_data_at";
        $order_dir = "DESC";
        $param_array = array();
        $where_clause = " WHERE D.status<>0 ";

        if($detail_type == "client"){
            if( !is_numeric($detail_org_id) ){
                abort(404);
            }

            $param_array[] = $detail_org_id;
            $where_clause .= " AND M.client_id=? ";
        }
        else if($detail_type == "distributor"){
            if( !is_numeric($detail_org_id) ){
                abort(404);
            }

            $param_array[] = $detail_org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }
        else if($detail_type == "modem"){
            if( !is_numeric($detail_org_id) ){
                abort(404);
            }

            $param_array[] = $detail_org_id;
            $where_clause .= " AND M.id=? ";
        }

        if( Auth::user()->user_type == 3 ){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }
        else if( Auth::user()->user_type == 4 ){
            $param_array[]=Auth::user()->org_id;
            $where_clause .= " AND C.id=? ";
        }

        if( $device_type != "all_devices" ){
            $param_array[] = $device_type;
            $where_clause .= " AND DT.type=? ";
        }

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->device_columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;

            if($order_column == "client"){
                $order_column = " C.name ";
            }
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(D.created_at) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "D.device_no LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR M.serial_no LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR M.distinctive_identifier LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR C.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR DD.name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR D.data_period LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR DTV.device_type LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('
                              SELECT 
                                count(*) as total_count 
                              FROM 
                                devices D, 
                                device_type DT, 
                                modems M, 
                                '.Helper::device_type_virtual_table().' as DTV,
                                clients C
                              LEFT JOIN distributors DD ON DD.id=C.distributor_id
                              '.$where_clause.' AND 
                                D.modem_id=M.id AND 
                                D.device_type_id=DT.id AND 
                                C.id=M.client_id AND 
                                DTV.type=DT.type',
                              $param_array
                            );

        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;

        $result = DB::select('
                        SELECT 
                            D.*,
                            D.status as status, 
                            D.expired_date as expired_date,
                            DTV.device_type as device_type_name, 
                            DT.type as device_type, 
                            M.serial_no as modem_no,
                            M.distinctive_identifier as distinctive_identifier,
                            JSON_UNQUOTE(json_extract(M.location, \'$.verbal\')) as location_verbal, 
                            C.name as client_name,
                            C.id as client_id,
                            (CASE WHEN C.distributor_id=0 THEN "'. trans('global.main_distributor').'" ELSE DD.name END) as distributor,
                            DD.id as distributor_id 
                        FROM 
                            devices D, 
                            device_type DT,
                            '.Helper::device_type_virtual_table().' as DTV, 
                            modems M, 
                            clients C
                        LEFT JOIN distributors as DD ON DD.id = C.distributor_id
                        '.$where_clause.' AND 
                            D.modem_id = M.id AND 
                            D.device_type_id = DT.id AND 
                            C.id = M.client_id AND 
                            DTV.type = DT.type 
                        ORDER BY '.$order_column.' '.$order_dir.' 
                        LIMIT ? OFFSET ?',
                        $param_array
                    );

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"] = $total_count;
            $return_array["recordsFiltered"] = $total_count;

            $status_reactive = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.reactive_warning').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:#ff9900;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-bolt" style="position: absolute;color:red;"></i></span></span>';

            $status_reactive_warning = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.reactive_pre_warning').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:#ff9900;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-bolt" style="position: absolute;color:#ff9900;"></i></span></span>';

            $status_no_data = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.no_data_warning').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:#ff9900;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-database" style="position: absolute;color:#0033cc;"></i></span></span>';

            $status_no_active_consumption = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.no_active_consumption_warning').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:orange;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-random" style="position: absolute;color:#0033cc;"></i></span></span>';

            $status_expiration_date = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.expiration_date_warning').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:#ff9900;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-clock-o" style="position: absolute;color:red;"></i></span></span>';

            $status_never_connection = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.no_connection_yet').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:#ff9900;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-chain-broken" style="position: absolute;color:red;"></i></span></span>';

            $status_no_connection = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.connection_failed').'"><i class="fa fa-exclamation-triangle fa-2x" style="color:#ff9900;"></i>&nbsp;<span style="position:relative;"><i class="fa fa-chain-broken" style="position: absolute;color:red;"></i></span></span>';

            $green_text = "color: green; font-weight: bold";
            $orange_text = "color: #ff9900; font-weight: bold";
            $blue_text = "color: #0033cc; font-weight: bold";
            $red_text = "color: red; font-weight: bold";

            foreach($result as $one_row){
                $inductive_text_color = $green_text;
                $capacitive_text_color = $green_text;
                $contract_power_tooltip_i = "";
                $contract_power_tooltip_c = "";
                $is_status_success = true;
                $status = '<i class="fa fa-check-square-o fa-2x" aria-hidden="true" style="color: green;"></i>';

                // Calculate inductive and capacitive from invoice day to now
                $invoice_day = ($one_row->invoice_day < 10 ? "0" : "") . $one_row->invoice_day;
                $start_date = date($invoice_day.'/m/Y');
                if( $invoice_day > date('d') ){
                    $start_date = date($invoice_day.'/m/Y', strtotime("-1 month"));
                }
                $start_date = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));

                if( $one_row->device_type == "meter"){
                    $table = "device_records_meter";
                }
                else if($one_row->device_type == "relay" || $one_row->device_type == "analyzer"){
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
                            ->where('DR.device_serial_no', $one_row->device_no)
                            ->where(DB::raw('DATE(DR.server_timestamp)'), '>=', $start_date)
                            ->orderBy('DR.id', 'desc')
                            ->get();

                $active_consumption = 0;
                $inductive_ratio = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.no_data_yet').'">X</span>';
                $capacitive_ratio = $inductive_ratio;

                $last_data_at = ($one_row->last_data_at!=null?"<span data-toggle='tooltip' data-placement='bottom' title='". trans('devices.first_data_at') . ": " . date('d/m/Y H:i',strtotime($one_row->first_data_at)) . "'>" . date('H:i',strtotime($one_row->last_data_at)) . "</span>":"<span style='color: red;'>".trans("devices.no_connection")."</span>");

                if( COUNT($device_records) > 0 ){
                    $rr = $device_records->toArray();
                    $first_record = end($rr);
                    $last_record = $rr[0];

                    $active_consumption = ($last_record->active - $first_record->active);

                    if( $active_consumption > 0 ){
                        if( is_null($last_record->inductive) || trim($last_record->inductive) == ""){
                            $inductive_ratio = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.na_explanation').'">NA</span>';
                        }
                        else{
                            $inductive_consumption = ($last_record->inductive - $first_record->inductive);
                            $inductive_ratio = ($inductive_consumption/$active_consumption)*100;
                            $inductive_ratio = (float)number_format($inductive_ratio,2);
                        }

                        if( is_null($last_record->capacitive) || trim($last_record->capacitive) == ""){
                            $capacitive_ratio = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.na_explanation').'">NA</span>';
                        }
                        else{
                            $capacitive_consumption = ($last_record->capacitive - $first_record->capacitive);
                            $capacitive_ratio = ($capacitive_consumption/$active_consumption)*100;
                            $capacitive_ratio = (float)number_format($capacitive_ratio,2);
                        }
                    }
                    else if( $active_consumption == 0 ){
                        $inductive_ratio = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.no_active_consumption').'">AX</span>';
                        $capacitive_ratio = $inductive_ratio;
                    }

                    // Control reactive penalty according to contract power
                    if( $one_row->contract_power <= 9 ){ }
                    else if( $one_row->contract_power > 9 && $one_row->contract_power < 30 ){
                        if( is_float($inductive_ratio) ){ // This means that inductive_ratio is not NA or AX
                            if( $inductive_ratio >= 33 ){
                                $inductive_text_color = $red_text;
                                $status = $status_reactive;
                                $is_status_success = false;
                            }
                            else if( $inductive_ratio >= 25 ){
                                $inductive_text_color = $orange_text;
                                $status = $status_reactive_warning;
                                $is_status_success = false;
                            }

                            $inductive_ratio .= " %";

                            $contract_power_tooltip_i = 'data-toggle="tooltip" data-placement="bottom" title="<div style=\'text-align: left;\'><b>- '.trans('devices.contract_power').':</b> '.$one_row->contract_power.' <br /> <b>- '.trans('devices.invoice_day').':</b> '.$one_row->invoice_day.' <br/> <b>- '.trans('devices.first_data').':</b> '.date('d/m/Y H:i:s', strtotime($first_record->date)).' <br/> <b>- '.trans('devices.last_data').':</b> '.date('d/m/Y H:i:s', strtotime($last_record->date)).' </div>"';
                        }
                        else{ // inductive_ratio is equal to AX or NA
                            if( $active_consumption == 0 ){
                                $inductive_text_color = $blue_text;
                                $status = $status_no_active_consumption;
                                $is_status_success = false;
                            }
                            else{
                                $inductive_text_color = $blue_text;
                                $status = $status_no_data;
                                $is_status_success = false;
                            }
                        }

                        if( is_float($capacitive_ratio) ){
                            if( $capacitive_ratio >= 20 ){
                                $capacitive_text_color = $red_text;
                                $status = $status_reactive;
                                $is_status_success = false;
                            }
                            else if( $capacitive_ratio >= 15 ){
                                $capacitive_text_color = $orange_text;
                                $status = $status_reactive_warning;
                                $is_status_success = false;
                            }

                            $capacitive_ratio .= " %";

                            $contract_power_tooltip_c = 'data-toggle="tooltip" data-placement="bottom" title="<div style=\'text-align: left;\'><b>- '.trans('devices.contract_power').':</b> '.$one_row->contract_power.' <br /> <b>- '.trans('devices.invoice_day').':</b> '.$one_row->invoice_day.' <br/> <b>- '.trans('devices.first_data').':</b> '.date('d/m/Y H:i:s', strtotime($first_record->date)).' <br/> <b>- '.trans('devices.last_data').':</b> '.date('d/m/Y H:i:s', strtotime($last_record->date)).' </div>"';
                        }
                        else{ // capacitive_ratio is equal to AX or NA
                            if( $active_consumption == 0 ){
                                $capacitive_text_color = $blue_text;
                                $status = $status_no_active_consumption;
                                $is_status_success = false;
                            }
                            else {
                                $capacitive_text_color = $blue_text;
                                $status = $status_no_data;
                                $is_status_success = false;
                            }
                        }
                    }
                    else if( $one_row->contract_power >= 30 ){
                        if( is_float($inductive_ratio) ){
                            if( $inductive_ratio >= 20 ){
                                $inductive_text_color = $red_text;
                                $status = $status_reactive;
                                $is_status_success = false;
                            }
                            else if( $inductive_ratio >= 16 ){
                                $inductive_text_color = $orange_text;
                                $status = $status_reactive_warning;
                                $is_status_success = false;
                            }

                            $inductive_ratio .= " %";
                            $contract_power_tooltip_i = 'data-toggle="tooltip" data-placement="bottom" title="<div style=\'text-align: left;\'><b>- '.trans('devices.contract_power').':</b> '.$one_row->contract_power.' <br /> <b>- '.trans('devices.invoice_day').':</b> '.$one_row->invoice_day.' <br/> <b>- '.trans('devices.first_data').':</b> '.date('d/m/Y H:i:s', strtotime($first_record->date)).' <br/> <b>- '.trans('devices.last_data').':</b> '.date('d/m/Y H:i:s', strtotime($last_record->date)).' </div>"';
                        }
                        else{
                            if( $active_consumption == 0 ){
                                $inductive_text_color = $blue_text;
                                $status = $status_no_active_consumption;
                                $is_status_success = false;
                            }
                            else {
                                $inductive_text_color = $blue_text;
                                $status = $status_no_data;
                                $is_status_success = false;
                            }
                        }

                        if( is_float($capacitive_ratio) ){
                            if( $capacitive_ratio >= 15 ){
                                $capacitive_text_color = $red_text;
                                $status = $status_reactive;
                                $is_status_success = false;
                            }
                            else if( $capacitive_ratio >= 12 ){
                                $capacitive_text_color = $orange_text;
                                $status = $status_reactive_warning;
                                $is_status_success = false;
                            }

                            $capacitive_ratio .= " %";

                            $contract_power_tooltip_c = 'data-toggle="tooltip" data-placement="bottom" title="<div style=\'text-align: left;\'><b>- '.trans('devices.contract_power').':</b> '.$one_row->contract_power.' <br /> <b>- '.trans('devices.invoice_day').':</b> '.$one_row->invoice_day.' <br/> <b>- '.trans('devices.first_data').':</b> '.date('d/m/Y H:i:s', strtotime($first_record->date)).' <br/> <b>- '.trans('devices.last_data').':</b> '.date('d/m/Y H:i:s', strtotime($last_record->date)).' </div>"';
                        }
                        else{
                            if( $active_consumption == 0 ){
                                $capacitive_text_color = $blue_text;
                                $status = $status_no_active_consumption;
                                $is_status_success = false;
                            }
                            else {
                                $capacitive_text_color = $blue_text;
                                $status = $status_no_data;
                                $is_status_success = false;
                            }
                        }
                    }

                    $last_data_diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($one_row->last_data_at));
                    $minutes = round($last_data_diff / 60);
                    $minutes_verbal = Helper::secondsToTime($last_data_diff);

                    if( $minutes > $one_row->data_period ){
                        $last_data_at = "<i class='fa fa-info-circle ' style='font-size:20px;color:#cc0000;float:left;margin-right:3px;'></i> <div style='float:left;color:#cc0000;font-weight: bold;' data-toggle='tooltip' data-placement='bottom' title='". trans("devices.last_data_verbal",array("verbal"=>$minutes_verbal)). "'> " . date('d/m/Y',strtotime($one_row->last_data_at)) . " </div>";

                        if($is_status_success == true)
                            $status = $status_no_connection;
                    }

                } // end if COUNT($device_records)>0
                else{
                    $inductive_text_color = $red_text;
                    $capacitive_text_color = $red_text;

                    $inductive_ratio = '<span data-toggle="tooltip" data-placement="bottom" title="'.trans('devices.no_connection_yet').'">!X!</span>';
                    $capacitive_ratio = $inductive_ratio;
                    $status = $status_never_connection;
                }

                // prepare client column info
                $client_link = '<a title="'.trans('devices.go_client_detail').'" href="/client_management/detail/'.$one_row->client_id.'" target="_blank"> '.$one_row->client_name.'</a>';

                if( Auth::user()->user_type == 1 || Auth::user()->user_type==2 ){
                    if( $one_row->distributor_id == 0 || $one_row->distributor_id == "" ){
                        $client_link .= " / " . $one_row->distributor;
                    }else{
                        $client_link .= " / " . '<a title="'.trans('devices.go_distributor_detail').'" href="/distributor_management/detail/'.$one_row->distributor_id.'" target="_blank">'.$one_row->distributor.'</a>';
                    }
                }

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "device_no" => $one_row->device_no,
                    "device_type" => $one_row->device_type_name,
                    "modem_no" => "<a href='/modem_management/detail/".$one_row->modem_id."' target='_blank' title='".trans("devices.go_modem_detail")."'>".$one_row->modem_no."</a> <br/>" . $one_row->distinctive_identifier,
                    "client" => $client_link,
                    "status" => $status,
                    "inductive" => "<span ".$contract_power_tooltip_i." style='".$inductive_text_color."'>" . $inductive_ratio . "</span>",
                    "capacitive" => "<span ".$contract_power_tooltip_c." style='".$capacitive_text_color."'>" . $capacitive_ratio . "</span>",
                    "data_period" => $one_row->data_period . " dk",
                    "last_data_at" => $last_data_at,
                    "buttons" => self::create_buttons($one_row->id, $one_row->device_type,$one_row->status, date("d/m/Y H:i", strtotime($one_row->expired_date)),$detail_type)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    /**
     * Return Device Info to edit device
     *
     * @param Request $request
     * @return string
     */
    public function getDeviceInfo(Request $request){
        if( $request->has('id') && is_numeric($request->input('id')) && $request->has('type') ){
            $device_id = $request->input('id');
        }
        else{
            return "ERROR";
        }

        //Auth user is rigth to edit this device
        if( Auth::user()->user_type == 4 ){
            return "ERROR";
        }

        $result = DB::table('devices as D')
                ->select('D.*','C.distributor_id as distributor_id','DT.type as device_type')
                ->join('device_type as DT', 'DT.id', '=', 'D.device_type_id')
                ->join('modems as M', 'M.id', '=', 'D.modem_id')
                ->join('clients as C', 'C.id', '=', 'M.client_id')
                ->where('D.id', $request->input('id'))
                ->where('D.status','<>',0)
                ->first();

        if( isset($result->id) && is_numeric($result->id) ){
            if( Auth::user()->user_type == 3 ){
                if( Auth::user()->org_id != $result->distributor_id ){
                    return "ERROR_1";
                }
            }

            /*$fee_scale_array = json_decode($result->fee_scale_id);
            $fee_scale_array = end($fee_scale_array);
            $result->fee_scale_id = $fee_scale_array->id;
            $result->fee_scale_type = $fee_scale_array->type;*/

            return json_encode($result);
        }
        else{
            return "ERROR_3";
        }
    }

    public function create_buttons($item_id, $item_type, $status, $expired_date,$detail_type){
        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_device_detail")){
            $return_value .= '<a href="/'.$item_type.'/detail/'.$item_id.'" title="'.trans('devices.detail').'" class="btn btn-info btn-sm">
            <i class="fa fa-info-circle fa-lg"></i></a> ';
        }

        if($detail_type == ""){
            if(Helper::has_right(Auth::user()->operations, "add_new_device")){
                $return_value .= '<a href="javascript:void(0)" onclick="edit_device('.$item_id.');" title="'.trans('devices.detail').'" class="btn btn-warning btn-sm">
            <i class="fa fa-edit fa-lg"></i></a> ';
            }

            if(Helper::has_right(Auth::user()->operations, "delete_device")){

                $return_value .= '<a '.($status==1?"style=\"opacity:0.4;\"":"").' href="javascript:void(1);" title="'.trans('devices.delete_device').'" onclick="delete_device('.$item_id.','.$status.', \''.$expired_date.'\');" class="btn btn-danger btn-sm"><i class="fa fa-trash-o fa-lg"></i></a> ';
            }
        }

        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    /**
     * Return The devices supported by the system from device_type table
     *
     * @param Request $request
     * @return string
     */
    public function getDevices(Request $request){
        $device_type = $request->input("device_type");

        if($device_type !="meter" && $device_type!="relay" && $device_type!="analyzer"){
            return "ERROR";
        }
        else{
            $result = DB::table("device_type")
                    ->select(DB::raw("id, CONCAT(model,' (',trademark,')') as text"))
                    ->where("type", $device_type)
                    ->orderBy("trademark")
                    ->get();

            if( isset($result[0]->id) && $result[0]->id != "" ){
                return json_encode($result);
            }
            else{
                return "NEXIST";
            }
        }
    }

    public function getModems(){
        $where_clause = " M.status <> 0 ";
        $param_array = array();

        if(Auth::user()->user_type == 4)
            return "ERROR";
        else if(Auth::user()->user_type == 3){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND C.distributor_id=? ";
        }

        /* OLD VERSION
        $result = DB::select("SELECT M.id as id,C.logo as logo, M.serial_no as serial_no,CONCAT(M.serial_no,',',MT.trademark,',',MT.model,',',C.name,',',D.name) as text, MT.trademark as trademark, MT.model as model, MT.type as type,C.name as client_name, D.name as distributor FROM distributors D,modems M, clients C, modem_type as MT WHERE D.id=C.distributor_id AND MT.id=M.modem_type_id AND C.id=M.client_id AND M.status<>0 ".$where_clause." ORDER BY serial_no",$param_array);
        */

        $result = DB::table('modems as M')
                ->select(
                    'M.id as id',
                    'C.logo as logo',
                    'M.serial_no as serial_no',
                    DB::raw("CONCAT(M.serial_no, ',', MT.trademark, ',', MT.model, ',', C.name, ',', D.name) as text"),
                    'MT.trademark as trademark',
                    'MT.model as model',
                    'MT.type as type',
                    'C.name as client_name',
                    DB::raw("(CASE WHEN C.distributor_id=0 THEN '". trans('global.main_distributor') ."' ELSE D.name END) as distributor")
                )
                ->leftJoin('modem_type as MT', 'MT.id', 'M.modem_type_id')
                ->leftJoin('clients as C', 'C.id', 'M.client_id')
                ->leftJoin('distributors as D', 'D.id', 'C.distributor_id')
                ->whereRaw($where_clause, $param_array)
                ->orderBy('M.serial_no', 'DESC')
                ->get();

        if( isset($result[0]->id) && $result[0]->id != "" ){
            return json_encode($result);
        }
        else{
            return "NEXIST";
        }
    }

    public function getFeeScales(){
        $where_clause = "";
        $param_array = array();

        if(Auth::user()->user_type == 4)
            return "ERROR";
        else if(Auth::user()->user_type == 3){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND (FS.org_id=? OR FS.org_id=0) ";
        }

        $result = DB::select("SELECT FS.id as id, FS.name as text, (CASE WHEN FS.created_by=0 THEN '".trans('global.system')."' ELSE U.name END) as created_by FROM fee_scales FS LEFT JOIN users U ON U.id=FS.created_by WHERE FS.status<>0 ".$where_clause." ORDER BY FS.name",$param_array);

        if( isset($result[0]->id) && $result[0]->id != "" ){
            return json_encode($result);
        }
        else{
            return "NEXIST";
        }
    }

    public function getAlertDefinitions(){

        $where_clause = "";
        $param_array = array();

        if(Auth::user()->user_type == 4)
            return "ERROR";
        else if(Auth::user()->user_type == 3){
            $param_array[] = Auth::user()->org_id;
            $where_clause .= " AND (AD.org_id=? OR AD.org_id=0) ";
        }

        $result = DB::select("SELECT AD.id as id, AD.name as text,AD.type as type,AD.policy as policy, (CASE WHEN AD.org_id=0 THEN '".trans('global.system')."' ELSE U.name END) as created_by FROM alert_definitions AD LEFT JOIN users U ON U.id=AD.created_by WHERE AD.status<>0 ".$where_clause." ORDER BY AD.name",$param_array);

        if( isset($result[0]->id) && $result[0]->id != "" ){
            return json_encode($result);
        }
        else{
            return "NEXIST";
        }
    }

    public function create(Request $request){
        //client cannot add device
        if(Auth::user()->user_type == 4)
            abort(404);

        if( $request->has('device_op_type') && ( $request->input('device_op_type') == "new" || $request->input('device_op_type') == "edit" ) ){
            $op_type = $request->input('device_op_type');
        }
        else{
            abort(404);
        }

        //form elements to be inserted/updated
        $device_no = $request->input("device_serial_no");
        $device_type_id = $request->input("device_model");
        $modem_id = $request->input("device_modem");
        $current_transformer_ratio = 1;
        $voltage_transformer_ratio = 1;
        $multiplier = 1;
        $data_period = $request->input("device_data_period");
        $contract_power = $request->input("device_contract_power");
        $modbus_address = 1;
        $type_code = "";
        $invoice_day = $request->input("device_invoice_day");
        $fee_scale_id = $request->input("device_invoice_fee_scale");
        $fee_scale_type = $request->input("device_invoice_fee_scale_type") == "time_of_use_tariff"?"time_of_use_tariff":"single_rate_tariff";
        $old_fee_scale_id = "";
        $connection_type = "rs485";
        $explanation = $request->input("device_explanation");
        $device_type = $request->input("device_type");
        $alert_definitions = "";
        $alert_phones = array();
        $alert_emails = array();

        //validate alarm specific data
        if( $request->has("device_alert_definitions") && COUNT($request->input("device_alert_definitions"))>0 ){
            $alert_definitions = implode(',', $request->input("device_alert_definitions"));

            $alert_phones = $request->has('device_alert_sms')?$request->input("device_alert_sms"):array();
            $alert_emails = $request->has('device_alert_emails')?$request->input("device_alert_emails"):array();

            foreach($alert_phones as $one_phone){
                if( !(strlen($one_phone) == 10 &&  is_numeric($one_phone)) ){
                    abort(404);
                }
            }

            foreach($alert_emails as $one_email){
                if (!filter_var($one_email, FILTER_VALIDATE_EMAIL)) {
                    abort(404);
                }
            }
        }

        $validate_array = array();
        $validate_array["device_serial_no"] = 'bail|required|alpha_num|min:3|max:50|unique:devices,device_no';

        if( $op_type == "edit" ){
            $validate_array["device_serial_no"] = 'bail|required|alpha_num|min:3|max:50|unique:devices,device_no,'.$request->input('device_edit_id').',id';

            if($request->has('device_edit_id') && is_numeric($request->input('device_edit_id'))){

                $result = DB::select("SELECT C.distributor_id as distributor, D.fee_scale_id as fee_scale_id FROM devices D, clients C, modems M WHERE D.modem_id=M.id AND M.client_id=C.id AND D.id=?",array($request->input('device_edit_id')));

                if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){

                }
                else if(Auth::user()->user_type == 3){

                    if(!(isset($result[0]->distributor) && Auth::user()->org_id == $result[0]->distributor))
                        abort(404);
                }
                else{
                    abort(404);
                }

                /*$old_fee_scale_array = json_decode($result[0]->fee_scale_id);
                $old_fee_scale_array_tmp = end($old_fee_scale_array);
                $old_fee_scale_id = $old_fee_scale_array_tmp->id;
                $old_fee_scale_type = $old_fee_scale_array_tmp->type;

                if($old_fee_scale_id != $fee_scale_id || $old_fee_scale_type != $fee_scale_type){

                    $old_fee_scale_array[] = array("id"=>$fee_scale_id, "type" => $fee_scale_type , "date"=>date('Y-m-d H:i:s'));
                    $fee_scale_id = json_encode($old_fee_scale_array);
                }
                else{
                    $fee_scale_id = $result[0]->fee_scale_id;
                }*/
            }
            else{
                abort(404);
            }
        }

        $validate_array["device_model"] = 'bail|required|digits_between:1,11|exists:device_type,id';
        $validate_array["device_modem"] = 'bail|required|digits_between:1,11|exists:modems,id';
        $validate_array["device_data_period"] = 'bail|required|digits_between:1,3';
        $validate_array["device_invoice_fee_scale"] = 'bail|required|digits_between:1,11|exists:fee_scales,id';
        $validate_array["device_invoice_fee_scale_type"] = 'bail|required';
        $validate_array["device_invoice_day"] = 'bail|required|digits_between:1,2';
        $validate_array["device_contract_power"] = 'bail|required|digits_between:1,3';
        $validate_array["device_explanation"] = 'bail|min:3|max:500';

        if($device_type == "meter"){
            $validate_array["device_current_transformer_ratio"] = 'bail|required|digits_between:1,4';
            $validate_array["device_voltage_transformer_ratio"] = 'bail|required|digits_between:1,4';
            $validate_array["device_connection_type"] = 'bail|required|alpha_num|min:3|max:20';
            $validate_array["device_type_code"] = 'bail|min:1|max:100';

            $current_transformer_ratio = $request->input("device_current_transformer_ratio");
            $voltage_transformer_ratio = $request->input("device_voltage_transformer_ratio");
            $multiplier = $current_transformer_ratio * $voltage_transformer_ratio;
            $type_code = $request->input("device_type_code");
            $connection_type = $request->input("device_connection_type");

        }
        else if($device_type == "relay"){
            $validate_array["device_modbus_address"] = 'bail|required|digits_between:1,5';

            $modbus_address = $request->input("device_modbus_address");
        }
        else if($device_type == "analyzer"){
            $validate_array["device_current_transformer_ratio"] = 'bail|required|digits_between:1,4';
            $validate_array["device_voltage_transformer_ratio"] = 'bail|required|digits_between:1,4';
            $validate_array["device_modbus_address"] = 'bail|required|digits_between:1,5';

            $current_transformer_ratio = $request->input("device_current_transformer_ratio");
            $voltage_transformer_ratio = $request->input("device_voltage_transformer_ratio");
            $multiplier = $current_transformer_ratio * $voltage_transformer_ratio;
            $modbus_address = $request->input("device_modbus_address");
        }
        else{
            abort(404);
        }

        $this->validate($request, $validate_array);

        //check if the user is a distributor, then he is only allowed to add new device with his own clients
        if(Auth::user()->user_type == 3){
            $modem_id = $request->input("device_modem");

            $result = DB::select("SELECT M.id as id FROM modems M, clients C WHERE C.id=M.client_id AND C.distributor_id=? AND M.id=?",array(Auth::user()->org_id,$modem_id));

            if(!(isset($result[0]->id) && is_numeric($result[0]->id))){
                abort(404);
            }
        }

        //save the data to DB
        if( $op_type == "new" ){ // insert new user
            $last_insert_id = DB::table('devices')->insertGetId(
                [
                    'device_no' => $device_no,
                    'device_type_id' => $device_type_id,
                    'modem_id' => $modem_id,
                    'current_transformer_ratio' => $current_transformer_ratio,
                    'voltage_transformer_ratio' => $voltage_transformer_ratio,
                    'multiplier' => $multiplier,
                    'data_period' => $data_period,
                    'contract_power' => $contract_power,
                    'modbus_address' => $modbus_address,
                    'type_code' => $type_code,
                    'invoice_day' => $invoice_day,
                    'fee_scale_id' => $fee_scale_id,
                    'fee_scale_type' => $fee_scale_type,
                    'connection_type' => $connection_type,
                    'explanation' => $explanation,
                    'created_by' => Auth::user()->id,
                    'alert_definitions' => $alert_definitions,
                    'alert_phones' => json_encode($alert_phones),
                    'alert_emails' => json_encode($alert_emails)
                ]
            );

            //fire event
            Helper::fire_event("create", Auth::user(), $device_type."s", $last_insert_id);
            //return insert operation result via global session object
            session(['new_'.$device_type.'_insert_success' => true]);
        }
        else if( $op_type == "edit" ){ // update user's info
            DB::table('devices')->where('id', $request->input("device_edit_id"))
                ->update(
                    [
                        'device_no' => $device_no,
                        'device_type_id' => $device_type_id,
                        'modem_id' => $modem_id,
                        'current_transformer_ratio' => $current_transformer_ratio,
                        'voltage_transformer_ratio' => $voltage_transformer_ratio,
                        'multiplier' => $multiplier,
                        'data_period' => $data_period,
                        'contract_power' => $contract_power,
                        'modbus_address' => $modbus_address,
                        'type_code' => $type_code,
                        'invoice_day' => $invoice_day,
                        'fee_scale_id' => $fee_scale_id,
                        'fee_scale_type' => $fee_scale_type,
                        'connection_type' => $connection_type,
                        'explanation' => $explanation,
                        'alert_definitions' => $alert_definitions,
                        'alert_phones' => json_encode($alert_phones),
                        'alert_emails' => json_encode($alert_emails)
                    ]
                );

            //fire event
            Helper::fire_event("update", Auth::user(), $device_type."s", $request->input("device_edit_id"));

            //return update operation result via global session object
            session([$device_type.'_update_success' => true]);
        }

        return redirect()->back();

    }

    public function delete(Request $request){
        if(!($request->has("id") && is_numeric($request->input("id"))))
            return "ERROR";

        $result = DB::table('devices as D')
            ->select('C.distributor_id as distributor_id','D.id as id','DT.type as device_type')
            ->join('modems as M', 'M.id', '=', 'D.modem_id')
            ->join('clients as C', 'C.id', '=', 'M.client_id')
            ->join('device_type as DT', 'DT.id','D.device_type_id')
            ->where('D.id', $request->input('id'))
            ->where('D.status','<>',0)
            ->first();

        if( isset($result->id) && is_numeric($result->id) ){
            if( Auth::user()->user_type == 3 ){
                if( Auth::user()->org_id != $result->distributor_id ){
                    return "ERROR_1";
                }
            }

            DB::table('devices')->where('id', $request->input("id"))
                ->update(
                    [
                        'status' => 0
                    ]
                );

            //fire event
            Helper::fire_event("delete",Auth::user(),$result->device_type."s",$request->input("id"));

            session(['device_delete_success' => true]);
            return "SUCCESS";
        }
        else{
            return "ERROR_3";
        }
    }

    public function getMeterIndex(Request $request, $id, $table_name="meter"){
        //first check if the user has right to observe this data
        $result = DB::select("
                              SELECT 
                                D.multiplier as multiplier,
                                D.device_no as device_no, 
                                M.serial_no as modem_no,
                                C.id as client_id, 
                                C.distributor_id as distributor_id, 
                                D.fee_scale_id as fee_scale_id 
                              FROM 
                                devices D, 
                                modems M, 
                                clients C 
                              WHERE 
                                D.modem_id=M.id AND 
                                M.client_id=C.id AND 
                                D.status<>0 AND 
                                D.id=?",
                              array($id)
                            );

        if(!(isset($result[0]->device_no) && $result[0]->device_no!=""))
            abort(404);

        if(Auth::user()->user_type == 4){
            if(Auth::user()->org_id != $result[0]->client_id)
                abort(404);
        }
        else if(Auth::user()->user_type == 3){
            if(Auth::user()->org_id != $result[0]->distributor_id)
                abort(404);
        }

        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $order_column = "DRM.id";
        $order_dir = "DESC";
        $show_type = "periodic";
        $multiplier = $result[0]->multiplier;

        // 1 kWh = 459,598 (soruce:http://www.sunearthtools.com/tools/CO2-emissions-calculator.php)
        $co2_emission_value = $this->co2_emission_value;

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array = array();
        $param_array[]= $result[0]->device_no;
        $param_array[]= $result[0]->modem_no;
        $where_clause = "WHERE DRM.device_serial_no=? AND DRM.modem_serial_no=? ";

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);

            if(isset($filter_obj["show_type"]))
                $show_type = $filter_obj["show_type"];
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(DRM.server_timestamp) BETWEEN ? AND ? ";

        if($show_type == "periodic"){
            $total_count = DB::select('SELECT count(*) as total_count FROM device_records_'.$table_name.' DRM '.$where_clause ,$param_array);

            $total_count = $total_count[0];
            $total_count = $total_count->total_count;
        }
        else if($show_type == "daily"){
            $total_count = DB::select('SELECT COUNT(DRM.server_timestamp) as total_count FROM device_records_'.$table_name.' DRM '.$where_clause.' GROUP BY DATE(DRM.server_timestamp)' ,$param_array);

            $total_count = COUNT($total_count);
        }
        else if($show_type == "monthly"){
            $total_count = DB::select('SELECT COUNT(DRM.server_timestamp) as total_count FROM device_records_'.$table_name.' DRM '.$where_clause.' GROUP BY YEAR(DRM.server_timestamp), MONTH(DRM.server_timestamp)' ,$param_array);

            $total_count = COUNT($total_count);
        }
        else if($show_type == "yearly"){
            $total_count = DB::select('SELECT COUNT(DRM.server_timestamp) as total_count FROM device_records_'.$table_name.' DRM '.$where_clause.' GROUP BY YEAR(DRM.server_timestamp)' ,$param_array);

            $total_count = COUNT($total_count);
        }

        $param_array[] = $length;
        $param_array[] = $start;

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if($show_type == "periodic"){
            $result = DB::select('
                SELECT 
                  DRM.* 
                FROM 
                  device_records_'.$table_name.' DRM '.$where_clause.' 
                ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?
                ',$param_array);

            if(COUNT($result)>0){
                $return_array["recordsTotal"]=$total_count;
                $return_array["recordsFiltered"]=$total_count;

                foreach($result as $key=>$one_row){
                    $active_consumption = 0;
                    $t1_consumption = 0;
                    $t2_consumption = 0;
                    $t3_consumption = 0;
                    $inductive_consumption = 0;
                    $capacitive_consumption = 0;
                    $inductive_ratio = 0;
                    $capacitive_ratio = 0;
                    $reactive_cost = 0;
                    $reactive_desc = "";
                    $inductive_text_color = "";
                    $capacitive_text_color = "";

                    $active = $one_row->positive_active_energy_total;
                    $inductive = $one_row->imported_inductive_reactive_energy_total_Q1;
                    $capacitive = $one_row->exported_capacitive_reactive_total_Q4;
                    $t1 = $one_row->positive_active_energy_t1;
                    $t2 = $one_row->positive_active_energy_t2;
                    $t3 = $one_row->positive_active_energy_t3;

                    if(isset($result[$key+1])){
                        $active_consumption = ($active - $result[$key+1]->positive_active_energy_total)*$multiplier;
                        $inductive_consumption = ($inductive - $result[$key+1]->imported_inductive_reactive_energy_total_Q1)*$multiplier;
                        $capacitive_consumption = ($capacitive - $result[$key+1]->exported_capacitive_reactive_total_Q4)*$multiplier;

                        $t1_consumption = ($t1 - $result[$key+1]->positive_active_energy_t1)*$multiplier;
                        $t2_consumption = ($t2 - $result[$key+1]->positive_active_energy_t2)*$multiplier;
                        $t3_consumption = ($t3 - $result[$key+1]->positive_active_energy_t3)*$multiplier;
                    }

                    if($active_consumption>0){
                        $inductive_ratio = ($inductive_consumption/$active_consumption)*100;
                        $capacitive_ratio = ($capacitive_consumption/$active_consumption)*100;
                    }

                    if($inductive_ratio >= $one_row->inductive_penalty_limit){
                        $inductive_text_color = "color:red;font-weight:bold;";
                        $reactive_cost = $one_row->inductive_cost;
                        $reactive_desc = trans("devices.reactive_reason_by_inductive");
                    }

                    if($capacitive_ratio >= $one_row->capacitive_penalty_limit){
                        $capacitive_text_color = "color:red;font-weight:bold;";

                        if($one_row->capacitive_cost > $reactive_cost){
                            $reactive_cost = $one_row->capacitive_cost;
                            $reactive_desc = trans("devices.reactive_reason_by_capacitive");
                        }
                    }

                    $m_total = 0;
                    if( $one_row->fee_scale_type == "single_rate_tariff" ){
                        $m_total += $one_row->active_cost;
                    }
                    else if( $one_row->fee_scale_type == "time_of_use_tariff" ){
                        $m_total += $one_row->t1_cost+$one_row->t2_cost+$one_row->t3_cost;
                    }
                    $m_total += $one_row->distribution_cost+ $one_row->trt_share+ $one_row->energy_fund+ $one_row->etv + $reactive_cost;

                    $tmp_array = array(
                        "DT_RowId" => $one_row->id,
                        "server_date"=>date('H:i d/m/Y',strtotime($one_row->server_timestamp)),
                        "active" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.active_consumption").": ".number_format($active_consumption, 2)." kW-h'>".number_format($active, 2)."</span>",
                        "inductive" =>"<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.inductive_consumption").": ".number_format($inductive_consumption, 2)." kVAr-h'>".number_format($inductive, 2)."</span>",
                        "capacitive" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.capacitive_consumption").": ".number_format($capacitive_consumption, 2)." kVAr-h'>".number_format($capacitive, 2)."</span>",
                        "inductive_ratio" => "<span style='".$inductive_text_color."'>".number_format($inductive_ratio, 1) . " %</span>",
                        "capacitive_ratio" => "<span style='".$capacitive_text_color."' >".number_format($capacitive_ratio, 1) . " %</span>",
                        "t1" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.t1_consumption").": ".number_format($t1_consumption, 2)." kW'>".number_format($one_row->positive_active_energy_t1, 2)."</span>",
                        "t2" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.t2_consumption").": ".number_format($t2_consumption, 2)." kW'>".number_format($one_row->positive_active_energy_t2, 2)."</span>",
                        "t3" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.t3_consumption").": ".number_format($t3_consumption, 2)." kW'>".number_format($one_row->positive_active_energy_t3, 2)."</span>",
                        "demand" => isset($one_row->demand_value)?number_format($one_row->demand_value, 2):"",
                        "demand_date" => isset($one_row->demand_date)?date('d/m/Y H:i:s',strtotime($one_row->demand_date)):"",
                        "m_active" => number_format($one_row->active_cost, 2) . " TL",
                        "m_t1" => number_format($one_row->t1_cost, 2) . " TL",
                        "m_t2" => number_format($one_row->t2_cost, 2) . " TL",
                        "m_t3" => number_format($one_row->t3_cost, 2) . " TL",
                        "m_distribution_cost"=> number_format($one_row->distribution_cost, 2) . " TL",
                        "m_reactive_cost" => "<span data-toggle='tooltip' data-placement='bottom' title='".$reactive_desc."'>".number_format($reactive_cost, 2) . " TL</span>",
                        "m_trt" => number_format($one_row->trt_share, 2) . " TL",
                        "m_energy_fund" => number_format($one_row->energy_fund, 2) . " TL",
                        "m_etv" => number_format($one_row->etv, 2) . " TL",
                        "m_total" => number_format($m_total, 2) . " TL",
                        "c_active" => number_format(($active_consumption * $co2_emission_value)/1000, 2) . " kg",
                        "c_t1" => number_format(($t1_consumption * $co2_emission_value)/1000, 2) . " kg",
                        "c_t2" => number_format(($t2_consumption * $co2_emission_value)/1000, 2) . " kg",
                        "c_t3" => number_format(($t3_consumption * $co2_emission_value)/1000, 2) . " kg",
                        "c_total" => number_format((($t1_consumption+$t2_consumption+$t3_consumption)*$co2_emission_value)/1000, 2) . " kg"
                    );

                    $return_array["data"][] = $tmp_array;
                }
            }
        }
        else if($show_type == "daily" || $show_type == "monthly" || $show_type == "yearly"){
            $date_format = "d/m/Y";
            $date_text = "D";
            $group_clause = "DATE(DRM.server_timestamp)";

            if($show_type == "monthly"){
                $group_clause = "YEAR(DRM.server_timestamp), MONTH(DRM.server_timestamp) ";
                $date_format = "Y";
                $date_text = "M";
            }
            else if($show_type == "yearly"){
                $group_clause = "YEAR(DRM.server_timestamp)";
                $date_format = "Y";
                $date_text = false;
            }

            $demand_string = "";
            if( $table_name == "meter" ){
                $demand_string =  'MAX(DRM.demand_value) as demand_value,
                                   MAX(DRM.demand_date) as demand_date,';
            }

            $result = DB::select('
                    SELECT 
                      _DRM.*,
                      DRM2.inductive_penalty_limit as inductive_penalty_limit,
                      DRM2.capacitive_penalty_limit as capacitive_penalty_limit,
                      DRM2.fee_scale_type as fee_scale_type 
                      FROM (SELECT
                        MAX(DRM.id) as id,
                        MAX(DRM.server_timestamp) as server_timestamp,
                        MAX(DRM.device_serial_no) as device_serial_no,
                        MAX(DRM.positive_active_energy_total) as active_max,
                        MIN(DRM.positive_active_energy_total) as active_min,
                        MAX(DRM.positive_active_energy_t1) as t1_max,
                        MIN(DRM.positive_active_energy_t1) as t1_min,
                        MAX(DRM.positive_active_energy_t2) as t2_max,
                        MIN(DRM.positive_active_energy_t2) as t2_min,
                        MAX(DRM.positive_active_energy_t3) as t3_max,
                        MIN(DRM.positive_active_energy_t3) as t3_min,
                        MAX(DRM.imported_inductive_reactive_energy_total_Q1) as inductive_max,
                        MIN(DRM.imported_inductive_reactive_energy_total_Q1) as inductive_min,
                        MAX(DRM.exported_capacitive_reactive_total_Q4) as capacitive_max,
                        MIN(DRM.exported_capacitive_reactive_total_Q4) as capacitive_min,
                        '.$demand_string.'
                        SUM(DRM.active_cost) as active_cost,
                        SUM(DRM.t1_cost) as t1_cost,
                        SUM(DRM.t2_cost) as t2_cost,
                        SUM(DRM.t3_cost) as t3_cost,
                        SUM(DRM.distribution_cost) as distribution_cost,
                        SUM(DRM.inductive_cost) as inductive_cost,
                        SUM(DRM.capacitive_cost) as capacitive_cost,
                        SUM(DRM.trt_share) as trt_share,
                        SUM(DRM.energy_fund) as energy_fund,
                        SUM(DRM.etv) as etv
                      FROM 
                        device_records_'.$table_name.' DRM 
                      '.$where_clause.'
                      GROUP BY '.$group_clause.'
                      ORDER BY MAX(DRM.id) DESC LIMIT ? OFFSET ?
                    ) as _DRM
                    LEFT JOIN device_records_'.$table_name.' DRM2 ON DRM2.server_timestamp=_DRM.server_timestamp AND DRM2.device_serial_no=_DRM.device_serial_no
                    ORDER BY _DRM.id DESC
              ',$param_array);

            if(COUNT($result)>0){
                $return_array["recordsTotal"]=$total_count;
                $return_array["recordsFiltered"]=$total_count;

                foreach($result as $key=>$one_row){
                    $inductive_ratio = 0;
                    $capacitive_ratio = 0;
                    $reactive_cost = 0;
                    $reactive_desc = "";
                    $inductive_text_color = "";
                    $capacitive_text_color = "";

                    $active = $one_row->active_max;
                    $t1 = $one_row->t1_max;
                    $t2 = $one_row->t2_max;
                    $t3 = $one_row->t3_max;
                    $inductive = $one_row->inductive_max;
                    $capacitive = $one_row->capacitive_max;

                    $active_consumption = ($one_row->active_max - $one_row->active_min)*$multiplier;
                    $t1_consumption = ($one_row->t1_max - $one_row->t1_min)*$multiplier;
                    $t2_consumption = ($one_row->t2_max - $one_row->t2_min)*$multiplier;
                    $t3_consumption = ($one_row->t3_max - $one_row->t3_min)*$multiplier;
                    $inductive_consumption = ($one_row->inductive_max - $one_row->inductive_min)*$multiplier;
                    $capacitive_consumption = ($one_row->capacitive_max - $one_row->capacitive_min)*$multiplier;

                    if($active_consumption>0){
                        $inductive_ratio = ($inductive_consumption/$active_consumption)*100;
                        $capacitive_ratio = ($capacitive_consumption/$active_consumption)*100;
                    }

                    if($inductive_ratio >= $one_row->inductive_penalty_limit){
                        $inductive_text_color = "color:red;font-weight:bold;";
                        $reactive_cost = $one_row->inductive_cost;
                        $reactive_desc = trans("devices.reactive_reason_by_inductive");
                    }

                    if($capacitive_ratio >= $one_row->capacitive_penalty_limit){
                        $capacitive_text_color = "color:red;font-weight:bold;";
                        if($one_row->capacitive_cost > $reactive_cost){
                            $reactive_cost = $one_row->capacitive_cost;
                            $reactive_desc = trans("devices.reactive_reason_by_capacitive");
                        }
                    }

                    $m_total = 0;
                    if( $one_row->fee_scale_type == "single_rate_tariff" ){
                        $m_total += $one_row->active_cost;
                    }
                    else if( $one_row->fee_scale_type == "time_of_use_tariff" ){
                        $m_total += $one_row->t1_cost+$one_row->t2_cost+$one_row->t3_cost;
                    }
                    $m_total += $one_row->distribution_cost+$one_row->trt_share+$one_row->energy_fund+$one_row->etv + $reactive_cost;

                    $tmp_array = array(
                        "DT_RowId" => $one_row->id,
                        "server_date" => ($date_text==false?'':trans('devices.'.strtolower(date($date_text,strtotime($one_row->server_timestamp))))." ") . date($date_format,strtotime($one_row->server_timestamp)),
                        "active" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.active_consumption").": ".number_format($active_consumption,2)." kW-h'>".number_format($active,2)."</span>",
                        "inductive" =>"<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.inductive_consumption").": ".number_format($inductive_consumption,2)." kVAr-h'>".number_format($inductive,2)."</span>",
                        "capacitive" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.capacitive_consumption").": ".number_format($capacitive_consumption,2)." kVAr-h'>".number_format($capacitive,2)."</span>",
                        "inductive_ratio" => "<span style='".$inductive_text_color."'>".number_format($inductive_ratio, 1) . " %</span>",
                        "capacitive_ratio" => "<span style='".$capacitive_text_color."'>".number_format($capacitive_ratio, 1) . " %</span>",
                        "t1" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.t1_consumption").": ".number_format($t1_consumption,2)." kWh'>".number_format($t1,2)."</span>",
                        "t2" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.t2_consumption").": ".number_format($t2_consumption, 2)." kWh'>".number_format($t2, 2)."</span>",
                        "t3" => "<span data-toggle='tooltip' data-placement='bottom' title='".trans("devices.t3_consumption").": ".number_format($t3_consumption, 2)." kWh'>".number_format($t3, 2)."</span>",
                        "demand" => isset($one_row->demand_value)?number_format($one_row->demand_value, 2):"",
                        "demand_date" => isset($one_row->demand_date)?date('d/m/Y H:i:s',strtotime($one_row->demand_date)):"",
                        "m_active" => number_format($one_row->active_cost, 2) . " TL",
                        "m_t1" => number_format($one_row->t1_cost, 2) . " TL",
                        "m_t2" => number_format($one_row->t2_cost, 2) . " TL",
                        "m_t3" => number_format($one_row->t3_cost, 2) . " TL",
                        "m_distribution_cost"=> number_format($one_row->distribution_cost, 2) . " TL",
                        "m_reactive_cost" => "<span data-toggle='tooltip' data-placement='bottom' title='".$reactive_desc."'>".number_format($reactive_cost, 2) . " TL</span>",
                        "m_trt" => number_format($one_row->trt_share, 2) . " TL",
                        "m_energy_fund" => number_format($one_row->energy_fund, 2) . " TL",
                        "m_etv" => number_format($one_row->etv, 2) . " TL",
                        "m_total" => number_format($m_total, 2) . " TL",
                        "c_active" => number_format(($active_consumption * $co2_emission_value)/1000, 2) . " kg",
                        "c_t1" => number_format(($t1_consumption * $co2_emission_value)/1000, 2) . " kg",
                        "c_t2" => number_format(($t2_consumption * $co2_emission_value)/1000, 2) . " kg",
                        "c_t3" => number_format(($t3_consumption * $co2_emission_value)/1000, 2) . " kg",
                        "c_total" => number_format((($t1_consumption+$t2_consumption+$t3_consumption)*$co2_emission_value)/1000, 2) . " kg"
                    );

                    $return_array["data"][] = $tmp_array;
                }
            }
        }

        echo json_encode($return_array);
    }

    public function getMeterCurVol(Request $request, $id){
        //first check if the user has right to observe this data
        $result = DB::select("SELECT D.multiplier as multiplier,D.device_no as device_no, M.serial_no as modem_no,C.id as client_id, C.distributor_id as distributor_id, DT.type as device_type FROM devices D,device_type DT, modems M, clients C WHERE DT.id=D.device_type_id AND D.modem_id=M.id AND M.client_id=C.id AND D.status<>0 AND D.id=?",array($id));

        if(!(isset($result[0]->device_no) && $result[0]->device_no!=""))
            abort(404);

        if(Auth::user()->user_type == 4){
            if(Auth::user()->org_id != $result[0]->client_id)
                abort(404);
        }
        else if(Auth::user()->user_type == 3){
            if(Auth::user()->org_id != $result[0]->distributor_id)
                abort(404);
        }

        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $table_name = ($result[0]->device_type=="meter")?"meter":"modbus";
        $order_column = "DRM.server_timestamp";
        $order_dir = "DESC";

        $multiplier = $result[0]->multiplier;

        $param_array = array();
        $param_array[]= $result[0]->device_no;
        $param_array[]= $result[0]->modem_no;
        $where_clause = "WHERE DRM.device_serial_no=? AND DRM.modem_serial_no=? ";

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(DRM.server_timestamp) BETWEEN ? AND ? ";

        $total_count = DB::select('SELECT count(*) as total_count FROM device_records_'.$table_name.' DRM '.$where_clause ,$param_array);

        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $param_array[] = $length;
        $param_array[] = $start;
        $result = DB::select('SELECT DRM.* FROM device_records_'.$table_name.' DRM '.$where_clause.' ORDER BY '.$order_column.' '.$order_dir.' LIMIT ? OFFSET ?',$param_array);

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $key=>$one_row){
                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "server_date" => date('H:i d/m/Y',strtotime($one_row->server_timestamp)),
                    "current_l1" => number_format($one_row->instantaneous_current_L1 * $multiplier,1),
                    "current_l2" => number_format($one_row->instantaneous_current_L2 * $multiplier,1),
                    "current_l3" => number_format($one_row->instantaneous_current_L3 * $multiplier,1),
                    "voltage_l1" => number_format($one_row->instantaneous_voltage_L1,0),
                    "voltage_l2" => number_format($one_row->instantaneous_voltage_L2,0),
                    "voltage_l3" => number_format($one_row->instantaneous_voltage_L3,0),
                    "cosfi_l1" => is_null($one_row->instantaneous_power_factor_L1)?"X":number_format($one_row->instantaneous_power_factor_L1,2),
                    "cosfi_l2" => is_null($one_row->instantaneous_power_factor_L2)?"X":number_format($one_row->instantaneous_power_factor_L2,2),
                    "cosfi_l3" => is_null($one_row->instantaneous_power_factor_L3)?"X":number_format($one_row->instantaneous_power_factor_L3,2),
                    "active_power_l1" => is_null($one_row->positive_active_instantaneous_power_L1)?"X":number_format($one_row->positive_active_instantaneous_power_L1 * $multiplier,2),
                    "active_power_l2" => is_null($one_row->positive_active_instantaneous_power_L2)?"X":number_format($one_row->positive_active_instantaneous_power_L2 * $multiplier,2),
                    "active_power_l3" => is_null($one_row->positive_active_instantaneous_power_L3)?"X":number_format($one_row->positive_active_instantaneous_power_L3 * $multiplier,2),
                    "reactive_power_l1" => is_null($one_row->positive_reactive_instantaneous_power_L1)?"X":number_format($one_row->positive_reactive_instantaneous_power_L1 * $multiplier,2),
                    "reactive_power_l2" => is_null($one_row->positive_reactive_instantaneous_power_L2)?"X":number_format($one_row->positive_reactive_instantaneous_power_L2 * $multiplier,2),
                    "reactive_power_l3" => is_null($one_row->positive_reactive_instantaneous_power_L3)?"X":number_format($one_row->positive_reactive_instantaneous_power_L3 * $multiplier,2)
            );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function getAnalyzerEnergy(Request $request, $id){
        self::getMeterIndex($request, $id, "modbus");
    }

    public function getGraphData(Request $request, $id){
        if( !$request->has('graph_type') ){
            return "ERROR";
        }

        //check if the user has right to dislay this data
        $the_device = DB::table('devices as D')
            ->select(
                'D.device_no as device_no',
                'D.id as id',
                'C.id as client_id',
                'C.distributor_id as distributor_id',
                'M.serial_no as modem_serial',
                'M.id as modem_id',
                'D.invoice_day as invoice_day',
                'D.multiplier as multiplier',
                'DT.type as device_type'
            )
            ->leftJoin('modems as M','D.modem_id','=','M.id')
            ->leftJoin('clients as C','M.client_id','=','C.id')
            ->leftJoin('device_type as DT','D.device_type_id','=','DT.id')
            ->where('D.id',$id)
            ->where('D.status','<>',0)
            ->first();


        if(!(isset($the_device->id) && is_numeric($the_device->id))){
            abort(404);
        }

        //Has Auth user right to show this device detail?
        if( Auth::user()->user_type == 4 ){
            if(Auth::user()->org_id != $the_device->client_id)
                return "ERROR";
        }
        else if( Auth::user()->user_type == 3 ){
            if(Auth::user()->org_id != $the_device->distributor_id)
                return "ERROR";
        }

        $graph_type = $request->input('graph_type');
        // set start day default value according to invoice day
        $invoice_day = ($the_device->invoice_day < 10 ? "0" : "") . $the_device->invoice_day;
        $start_date = date($invoice_day.'/m/Y');
        if( $invoice_day > date('d') ){
            $start_date = date($invoice_day.'/m/Y', strtotime("-1 month"));
        }
        $end_date = date('d/m/Y');
        $show_type = "periodic";
        $data_type = "kw";
        $param_array = array();
        $multiplier = $the_device->multiplier;
        $table_name = "meter";
        if($the_device->device_type != "meter")
            $table_name = "modbus";

        // 1 kWh = 459,598 (soruce:http://www.sunearthtools.com/tools/CO2-emissions-calculator.php)
        $co2_emission_value = $this->co2_emission_value;

        //chart parameters
        $title = "";
        $y_title = "";
        $categories = "";
        $data = "";
        $unit = "";
        $chart_type = "spline";

        if( $request->has('start_date') ){
            $start_date = $request->input('start_date');
        }

        if( $request->has('end_date') ){
            $end_date = $request->input('end_date');
        }

        if( $request->has('show_type') ){
            $show_type = $request->input('show_type');
        }

        if( $request->has('data_type') ){
            $data_type = $request->input('data_type');
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));
        $param_array[] = $the_device->device_no;

        if($graph_type == "active_reactive_consumption" || $graph_type == "reactive_rates"){
            $title = trans("devices.active_reactive_consumption_graph");
            if($graph_type == "reactive_rates"){
                    $title = trans("devices.reactive_rates_graph");
                    $y_title = trans("devices.reactive_rates")." (%) ";

                    $data_type = "kw";
            }

            $consumption_array = array("name"=>trans("devices.active_consumption"), "data"=>array());
            $inductive_array = array("name"=>trans("devices.inductive_consumption"), "data"=>array());
            $capacitive_array = array("name"=>trans("devices.capacitive_consumption"), "data"=>array());
            $t1_array = array("name"=>trans("devices.t1_consumption"), "data"=>array(),"visible"=>false);
            $t2_array = array("name"=>trans("devices.t2_consumption"), "data"=>array(), "visible"=>false);
            $t3_array = array("name"=>trans("devices.t3_consumption"), "data"=>array(),"visible"=>false);

            $inductive_ratio_array = array("name"=>trans("devices.inductive_ratio"), "data"=>array());
            $capacitive_ratio_array = array("name"=>trans("devices.capacitive_ratio"), "data"=>array());

            if($show_type == "daily" || $show_type == "monthly" || $show_type == "yearly"){
                $categories = false;

                $group_clause = "DATE(DRM.server_timestamp)";

                if($show_type == "monthly"){
                    $group_clause = "YEAR(DRM.server_timestamp), MONTH(DRM.server_timestamp) ";
                }
                else if($show_type == "yearly"){
                    $group_clause = "YEAR(DRM.server_timestamp)";
                }

                $result = DB::select('
                      SELECT
                        MAX(DRM.server_timestamp) as server_timestamp,
                        MAX(DRM.positive_active_energy_total) as active_max,
                        MIN(DRM.positive_active_energy_total) as active_min,
                        MAX(DRM.positive_active_energy_t1) as t1_max,
                        MIN(DRM.positive_active_energy_t1) as t1_min,
                        MAX(DRM.positive_active_energy_t2) as t2_max,
                        MIN(DRM.positive_active_energy_t2) as t2_min,
                        MAX(DRM.positive_active_energy_t3) as t3_max,
                        MIN(DRM.positive_active_energy_t3) as t3_min,
                        MAX(DRM.imported_inductive_reactive_energy_total_Q1) as inductive_max,
                        MIN(DRM.imported_inductive_reactive_energy_total_Q1) as inductive_min,
                        MAX(DRM.exported_capacitive_reactive_total_Q4) as capacitive_max,
                        MIN(DRM.exported_capacitive_reactive_total_Q4) as capacitive_min,
                        SUM(DRM.active_cost) as active_cost,
                        SUM(DRM.t1_cost) as t1_cost,
                        SUM(DRM.t2_cost) as t2_cost,
                        SUM(DRM.t3_cost) as t3_cost,
                        SUM(DRM.inductive_cost) as inductive_cost,
                        SUM(DRM.capacitive_cost) as capacitive_cost,
                        UNIX_TIMESTAMP(CONVERT_TZ(DATE(MAX(DRM.server_timestamp)), "+00:00", "SYSTEM")) as unix_date,
                        UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(MAX(DRM.server_timestamp),"%Y-01-02"), "+00:00", "SYSTEM")) as unix_year,
                        UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(MAX(DRM.server_timestamp),"%Y-%m-02"), "+00:00", "SYSTEM")) as unix_month
                      FROM 
                        device_records_'.$table_name.' DRM 
                      WHERE DATE(DRM.server_timestamp) BETWEEN ? AND ? AND DRM.device_serial_no=?
                      GROUP BY '.$group_clause.'
                      ORDER BY MAX(DRM.id) ASC
                      ',$param_array
                );

                if( COUNT($result) > 0 ){
                    if($data_type == "price"){
                        $y_title = trans("devices.price");
                    }
                    else if($data_type=="co2"){
                        $y_title = trans("devices.co2");
                    }
                    else{
                        $y_title = trans("devices.active_consumption")." (kW-h) / ".trans("devices.reactive_consumption")." (kVAr-h)";

                        if($graph_type == "reactive_rates"){
                            $y_title = trans("devices.reactive_rates")." (%) ";
                        }
                    }

                    foreach($result as $key=>$one_result) {
                        $unix_time_value = $one_result->unix_date * 1000;

                        if($show_type == "yearly"){
                            $unix_time_value = $one_result->unix_year * 1000;
                        }
                        else if($show_type == "monthly"){
                            $unix_time_value = $one_result->unix_month * 1000;
                        }

                        $active_consumption = ($one_result->active_max - $one_result->active_min)*$multiplier;
                        $t1_consumption = ($one_result->t1_max - $one_result->t1_min)*$multiplier;
                        $t2_consumption = ($one_result->t2_max - $one_result->t2_min)*$multiplier;
                        $t3_consumption = ($one_result->t3_max - $one_result->t3_min)*$multiplier;
                        $inductive_consumption = ($one_result->inductive_max - $one_result->inductive_min)*$multiplier;
                        $capacitive_consumption = ($one_result->capacitive_max - $one_result->capacitive_min)*$multiplier;

                        if($data_type =="price"){
                            $consumption_array["data"][] = array("x"=>$unix_time_value, "y"=>$one_result->active_cost,"unit"=>"TL");
                            $inductive_array["data"][] = array("x"=>$unix_time_value, "y"=>$one_result->inductive_cost,"unit"=>"TL");
                            $capacitive_array["data"][] = array("x"=>$unix_time_value, "y"=>$one_result->capacitive_cost,"unit"=>"TL");
                            $t1_array["data"][] = array("x"=>$unix_time_value, "y"=>$one_result->t1_cost,"unit"=>"TL");
                            $t2_array["data"][] = array("x"=>$unix_time_value, "y"=>$one_result->t2_cost,"unit"=>"TL");
                            $t3_array["data"][] = array("x"=>$unix_time_value, "y"=>$one_result->t3_cost,"unit"=>"TL");
                        }
                        else if($data_type == "co2"){
                            $consumption_array["data"][] = array("x"=>$unix_time_value, "y"=>(($active_consumption * $co2_emission_value)/1000),"unit"=>"kg");
                            $t1_array["data"][] = array("x"=>$unix_time_value, "y"=>(($t1_consumption * $co2_emission_value)/1000),"unit"=>"kg");
                            $t2_array["data"][] = array("x"=>$unix_time_value, "y"=>($t2_consumption * $co2_emission_value)/1000, "unit"=>"kg");
                            $t3_array["data"][] = array("x"=>$unix_time_value, "y"=>($t3_consumption * $co2_emission_value)/1000, "unit"=>"kg");
                            $inductive_array["data"][] = array("x"=>$unix_time_value, "y"=>($inductive_consumption * $co2_emission_value)/1000, "unit"=>"kg");
                            $capacitive_array["data"][] = array("x"=>$unix_time_value, "y"=>($capacitive_consumption * $co2_emission_value)/1000, "unit"=>"kg");
                        }
                        else{
                            if($graph_type == "reactive_rates"){
                                $inductive_ratio = 0;
                                $capacitive_ratio = 0;
                                if($active_consumption > 0){
                                    $inductive_ratio = ($inductive_consumption/$active_consumption)*100;
                                    $capacitive_ratio = ($capacitive_consumption/$active_consumption)*100;
                                }

                                $inductive_ratio_array["data"][] = array("x"=>$unix_time_value,"y"=>$inductive_ratio,"unit"=>"%");
                                $capacitive_ratio_array["data"][] = array("x"=>$unix_time_value, "y"=>$capacitive_ratio,"unit"=>"%");
                            }
                            else{
                                $consumption_array["data"][] = array("x"=>$unix_time_value, "y"=>$active_consumption,"unit"=>"kW-h");
                                $t1_array["data"][] = array("x"=>$unix_time_value, "y" => $t1_consumption,"unit"=>"kW-h");
                                $t2_array["data"][] = array("x"=>$unix_time_value, "y" => $t2_consumption,"unit"=>"kW-h");
                                $t3_array["data"][] = array("x"=>$unix_time_value, "y" => $t3_consumption,"unit"=>"kW-h");
                                $inductive_array["data"][] = array("x"=>$unix_time_value, "y"=>$inductive_consumption,"unit"=>"kVAr-h");
                                $capacitive_array["data"][] = array("x"=>$unix_time_value, "y"=>$capacitive_consumption,"unit"=>"kVAr-h");
                            }
                        }
                    }

                    $data = array();

                    if($graph_type == "reactive_rates"){
                        $data[] = $inductive_ratio_array;
                        $data[] = $capacitive_ratio_array;
                    }
                    else{
                        $data[] = $consumption_array;
                        $data[] = $inductive_array;
                        $data[] = $capacitive_array;
                        $data[] = $t1_array;
                        $data[] = $t2_array;
                        $data[] = $t3_array;
                    }
                }
                else{
                    //@TODO: Highchart no_data
                }
            }
            else{
                $categories = false;

                $result = DB::select('
                    SELECT 
                      DRM.positive_active_energy_total as active,
                      DRM.positive_active_energy_t1 as t1,
                      DRM.positive_active_energy_t2 as t2,
                      DRM.positive_active_energy_t3 as t3,
                      DRM.imported_inductive_reactive_energy_total_Q1 as inductive,
                      DRM.exported_capacitive_reactive_total_Q4 as capacitive,
                      active_cost,
                      t1_cost,
                      t2_cost,
                      t3_cost,
                      inductive_cost,
                      capacitive_cost,
                      UNIX_TIMESTAMP(CONVERT_TZ(DRM.server_timestamp,"+00:00", "SYSTEM")) as unix_date
                    FROM 
                      device_records_'.$table_name.' DRM 
                    WHERE DATE(DRM.server_timestamp) BETWEEN ? AND ? AND DRM.device_serial_no=? 
                    ORDER BY DRM.server_timestamp ASC
                    ',$param_array
                );

                if( COUNT($result) > 0 ){
                    if($data_type == "price"){
                        $y_title = trans("devices.price");
                        //$unit = " TL";
                    }
                    else if($data_type=="co2"){
                        $y_title = trans("devices.co2");
                        //$unit = " kg";
                    }
                    else{
                        $y_title = trans("devices.active_consumption")." (kW-h) / ".trans("devices.reactive_consumption")." (kVAr-h)";
                        //$unit = " kW";

                        if($graph_type == "reactive_rates"){
                            $y_title = trans("devices.reactive_rates")." (%) ";
                            //$unit = " %";
                        }
                    }

                    foreach($result as $key=>$one_result){
                        $active_consumption = 0;
                        $t1_consumption = 0;
                        $t2_consumption = 0;
                        $t3_consumption = 0;
                        $inductive_consumption = 0;
                        $capacitive_consumption = 0;

                        if(isset($result[$key-1])){
                            $active_consumption = ($one_result->active - $result[$key-1]->active)*$multiplier;
                            $inductive_consumption = ($one_result->inductive - $result[$key-1]->inductive)*$multiplier;
                            $capacitive_consumption = ($one_result->capacitive - $result[$key-1]->capacitive)*$multiplier;
                            $t1_consumption = ($one_result->t1 - $result[$key-1]->t1)*$multiplier;
                            $t2_consumption = ($one_result->t2 - $result[$key-1]->t2)*$multiplier;
                            $t3_consumption = ($one_result->t3 - $result[$key-1]->t3)*$multiplier;

                            if($data_type == "co2"){
                                $consumption_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>($active_consumption * $co2_emission_value)/1000,"unit"=>"kg");
                                $inductive_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>($inductive_consumption * $co2_emission_value)/1000,"unit"=>"kg");
                                $capacitive_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>($capacitive_consumption * $co2_emission_value)/1000,"unit"=>"kg");
                                $t1_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>($t1_consumption * $co2_emission_value)/1000,"unit"=>"kg");
                                $t2_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>($t2_consumption * $co2_emission_value)/1000,"unit"=>"kg");
                                $t3_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>($t3_consumption * $co2_emission_value)/1000,"unit"=>"kg");

                            }
                            else if($data_type == "kw"){
                                $consumption_array["data"][] = array("x"=>$one_result->unix_date * 1000, "y"=>$active_consumption,"unit"=>"kW-h");
                                $inductive_array["data"][] = array("x"=>$one_result->unix_date * 1000, "y"=>$inductive_consumption,"unit"=>"kVAr-h");
                                $capacitive_array["data"][] = array("x"=>$one_result->unix_date * 1000, "y"=>$capacitive_consumption,"unit"=>"kVAr-h");
                                $t1_array["data"][] = array("x"=>$one_result->unix_date * 1000, "y"=>$t1_consumption,"unit"=>"kW-h");
                                $t2_array["data"][] = array("x"=>$one_result->unix_date * 1000, "y"=>$t2_consumption,"unit"=>"kW-h");
                                $t3_array["data"][] = array("x"=>$one_result->unix_date * 1000, "y"=>$t3_consumption,"unit"=>"kW-h");

                                $inductive_ratio = 0;
                                $capacitive_ratio = 0;
                                if($active_consumption > 0){
                                    $inductive_ratio = ($inductive_consumption/$active_consumption)*100;
                                    $capacitive_ratio = ($capacitive_consumption/$active_consumption)*100;
                                }

                                $inductive_ratio_array["data"][] = array("x"=>$one_result->unix_date * 1000, "y"=>$inductive_ratio,"unit"=>"%");
                                $capacitive_ratio_array["data"][] = array("x"=>$one_result->unix_date * 1000, "y"=>$capacitive_ratio,"unit"=>"%");
                            }

                        }

                        if($data_type == "price"){
                            $consumption_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->active_cost,"unit"=>"TL");
                            $inductive_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->inductive_cost,"unit"=>"TL");
                            $capacitive_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->capacitive_cost,"unit"=>"TL");
                            $t1_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>$one_result->t1_cost,"unit"=>"TL");
                            $t2_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>$one_result->t2_cost,"unit"=>"TL");
                            $t3_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>$one_result->t3_cost,"unit"=>"TL");
                        }
                    }

                    $data = array();
                    if($graph_type == "reactive_rates"){
                        $data[] = $inductive_ratio_array;
                        $data[] = $capacitive_ratio_array;
                    }
                    else{
                        $data[] = $consumption_array;
                        $data[] = $inductive_array;
                        $data[] = $capacitive_array;
                        $data[] = $t1_array;
                        $data[] = $t2_array;
                        $data[] = $t3_array;
                    }
                }
                else{
                    //@TODO: Highchart no_data
                }
            }

        }
        else if($graph_type == "active_reactive_power"){
            $title = trans("devices.active_reactive_power_graph");
            $y_title = trans("devices.active_power_th")." / ".trans("devices.reactive_power_th");
            //$unit = " kW";
            $categories = false;

            $active_total = array("name"=>trans("devices.active_total"), "data"=>array(), "visible"=>true);
            $active_l1_array = array("name"=>trans("devices.active_l1"), "data"=>array(), "visible"=>false);
            $active_l2_array = array("name"=>trans("devices.active_l2"), "data"=>array(), "visible"=>false);
            $active_l3_array = array("name"=>trans("devices.active_l3"), "data"=>array(), "visible"=>false);

            $reactive_total = array("name"=>trans("devices.reactive_total"), "data"=>array(), "visible"=>true);
            $reactive_l1_array = array("name"=>trans("devices.reactive_l1"), "data"=>array(), "visible"=>false);
            $reactive_l2_array = array("name"=>trans("devices.reactive_l2"), "data"=>array(), "visible"=>false);
            $reactive_l3_array = array("name"=>trans("devices.reactive_l3"), "data"=>array(), "visible"=>false);

            if( $the_device->device_type != "meter" ){
                $inductive_total = array("name"=>trans("devices.inductive_total"), "data"=>array(), "visible"=>false);
                $capacitive_total = array("name"=>trans("devices.capacitive_total"), "data"=>array(), "visible"=>false);
            }

            $result = DB::select('
                    SELECT 
                      DRM.positive_active_instantaneous_power_total as active_total,
                      DRM.positive_active_instantaneous_power_L1 as active_l1,
                      DRM.positive_active_instantaneous_power_L2 as active_l2,
                      DRM.positive_active_instantaneous_power_L3 as active_l3,
                      DRM.positive_reactive_instantaneous_power_total as reactive_total,
                      DRM.positive_reactive_instantaneous_power_L1 as reactive_l1,
                      DRM.positive_reactive_instantaneous_power_L2 as reactive_l2,
                      DRM.positive_reactive_instantaneous_power_L3 as reactive_l3,
                      DRM.positive_inductive_instantaneous_power_total as inductive_total,
                      DRM.positive_capacitive_instantaneous_power_total as capacitive_total,
                      UNIX_TIMESTAMP(CONVERT_TZ(DRM.server_timestamp,"+00:00", "SYSTEM")) as unix_date
                    FROM 
                      device_records_'.$table_name.' DRM 
                    WHERE DATE(DRM.server_timestamp) BETWEEN ? AND ? AND DRM.device_serial_no=? 
                    ORDER BY DRM.server_timestamp ASC
                    ',$param_array
            );

            if( COUNT($result) > 0 ){
                foreach($result as $one_result){
                    $active_total["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->active_total*$multiplier,"unit"=>"kW-h");
                    $active_l1_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->active_l1*$multiplier,"unit"=>"kW-h");
                    $active_l2_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->active_l2*$multiplier,"unit"=>"kW-h");
                    $active_l3_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->active_l3*$multiplier,"unit"=>"kW-h");

                    $reactive_total["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->reactive_total*$multiplier,"unit"=>"kVAr-h");
                    $reactive_l1_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->reactive_l1*$multiplier,"unit"=>"kVAr-h");
                    $reactive_l2_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->reactive_l2*$multiplier,"unit"=>"kVAr-h");
                    $reactive_l3_array["data"][] = array("x"=>$one_result->unix_date*1000, "y"=>$one_result->reactive_l3*$multiplier,"unit"=>"kVAr-h");

                    if( $the_device->device_type != "meter" ){
                        $inductive_total["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>$one_result->inductive_total*$multiplier,"unit"=>"kVAr-h");
                        $capacitive_total["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>$one_result->capacitive_total*$multiplier,"unit"=>"kVAr-h");
                    }
                }

                $data = array();
                $data[] = $active_total;
                $data[] = $active_l1_array;
                $data[] = $active_l2_array;
                $data[] = $active_l3_array;

                $data[] = $reactive_total;
                $data[] = $reactive_l1_array;
                $data[] = $reactive_l2_array;
                $data[] = $reactive_l3_array;

                if( $the_device->device_type != "meter" ){
                    $data[] = $inductive_total;
                    $data[] = $capacitive_total;
                }
            }
        }
        else if($graph_type == "current"){
            $title = trans("devices.current_graph");
            $y_title = trans("devices.current_th");
            $unit = " A";

            $l1_array = array("name"=>trans("devices.current_l1"), "data"=>array(),"visible"=>true);
            $l2_array = array("name"=>trans("devices.current_l2"), "data"=>array(), "visible"=>true);
            $l3_array = array("name"=>trans("devices.current_l3"), "data"=>array(),"visible"=>true);

            $categories = false;

            $result = DB::select('
                    SELECT 
                      DRM.instantaneous_current_L1  as l1,
                      DRM.instantaneous_current_L2 as l2,
                      DRM.instantaneous_current_L3 as l3,
                      UNIX_TIMESTAMP(CONVERT_TZ(DRM.server_timestamp,"+00:00", "SYSTEM")) as unix_date
                    FROM 
                      device_records_'.$table_name.' DRM 
                    WHERE DATE(DRM.server_timestamp) BETWEEN ? AND ? AND DRM.device_serial_no=? 
                    ORDER BY DRM.server_timestamp ASC
                    ',$param_array
            );

            if( COUNT($result) > 0 ){
                foreach($result as $one_result){
                    $l1_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>($one_result->l1)*$multiplier,"unit"=>"A");
                    $l2_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>($one_result->l2)*$multiplier,"unit"=>"A");
                    $l3_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>($one_result->l3)*$multiplier,"unit"=>"A");
                }

                $data = array();

                $data[] = $l1_array;
                $data[] = $l2_array;
                $data[] = $l3_array;

            }
        }
        else if($graph_type == "voltage"){
            $title = trans("devices.voltage_graph");
            $y_title = trans("devices.voltage_th");

            $unit = " V";

            $l1_array = array("name"=>trans("devices.voltage_l1"), "data"=>array(),"visible"=>true);
            $l2_array = array("name"=>trans("devices.voltage_l2"), "data"=>array(), "visible"=>true);
            $l3_array = array("name"=>trans("devices.voltage_l3"), "data"=>array(),"visible"=>true);

            $categories = false;

            $result = DB::select('
                    SELECT 
                      DRM.instantaneous_voltage_L1 as l1,
                      DRM.instantaneous_voltage_L2 as l2,
                      DRM.instantaneous_voltage_L3 as l3,
                      UNIX_TIMESTAMP(CONVERT_TZ(DRM.server_timestamp,"+00:00", "SYSTEM")) as unix_date
                    FROM 
                      device_records_'.$table_name.' DRM 
                    WHERE DATE(DRM.server_timestamp) BETWEEN ? AND ? AND DRM.device_serial_no=? 
                    ORDER BY DRM.server_timestamp ASC
                    ',$param_array
            );

            if( COUNT($result) > 0 ){

                foreach($result as $one_result){
                    $l1_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>$one_result->l1,"unit"=>"V");
                    $l2_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>$one_result->l2,"unit"=>"V");
                    $l3_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>$one_result->l3,"unit"=>"V");
                }

                $data = array();
                $data[] = $l1_array;
                $data[] = $l2_array;
                $data[] = $l3_array;
            }
        }
        else if($graph_type == "cosfi"){
            $title = trans("devices.cosfi_graph");
            $y_title = trans("devices.cosfi_th");

            $unit = " ";

            $l1_array = array("name"=>trans("devices.cosfi_l1"), "data"=>array(),"visible"=>true);
            $l2_array = array("name"=>trans("devices.cosfi_l2"), "data"=>array(), "visible"=>true);
            $l3_array = array("name"=>trans("devices.cosfi_l3"), "data"=>array(),"visible"=>true);

            $categories = false;

            $result = DB::select('
                    SELECT 
                      DRM.instantaneous_power_factor_L1 as l1,
                      DRM.instantaneous_power_factor_L2 as l2,
                      DRM.instantaneous_power_factor_L3 as l3,
                      UNIX_TIMESTAMP(CONVERT_TZ(DRM.server_timestamp,"+00:00", "SYSTEM")) as unix_date
                    FROM 
                      device_records_'.$table_name.' DRM 
                    WHERE DATE(DRM.server_timestamp) BETWEEN ? AND ? AND DRM.device_serial_no=? 
                    ORDER BY DRM.server_timestamp ASC
                    ',$param_array
            );

            if( COUNT($result) > 0 ){
                foreach($result as $one_result){
                    $l1_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>($one_result->l1),"unit"=>" ");
                    $l2_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>($one_result->l2),"unit"=>" ");
                    $l3_array["data"][] = array("x"=>$one_result->unix_date*1000,"y"=>($one_result->l3),"unit"=>" ");
                }

                $data = array();
                $data[] = $l1_array;
                $data[] = $l2_array;
                $data[] = $l3_array;
            }
        }

        // parameters: title, y_title, categories, series
        $the_chart = new HighChart(
            $title,
            $y_title,
            $categories,
            $data
        );

        $the_chart->setUnit($unit);
        $the_chart->setShowType($show_type);
        $the_chart->setChartType($chart_type);

        return $the_chart->getOptions();
    }
}
