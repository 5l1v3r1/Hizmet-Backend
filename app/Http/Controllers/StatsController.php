<?php

namespace App\Http\Controllers;

use App\Helpers\HighChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{

    public function showGraphs(Request $request){

        return view('pages.graphs');

    }

    public function getDistributors(Request $request){

        if(Auth::user()->user_type == 4 || Auth::user()->user_type == 3)
            abort(404);


        $result = DB::table('distributors')
            ->select(
                'name as text',
                'id as id'
            )
            ->where('status','<>',0)->orderBy('name')->get();

        if( isset($result[0]->id) && $result[0]->id != "" ){

            return json_encode($result);
        }
        else{

        }
    }

    public function getGraphData(Request $request){

        //refuse missing post data
        if(!$request->has("data")){

            return "ERROR";
        }

        $data_obj = json_decode($request->input("data"),true);

        //refuse missing date and tab_name fields
        if(!(isset($data_obj["start_date"]) && isset($data_obj["end_date"]) && isset($data_obj["tab_name"])))
            return "ERROR";


        //check if user has right to observe data
        if(!($data_obj["tab_name"] != "device" || $data_obj["tab_name"] != "modem" || $data_obj["tab_name"] != "client"
            || $data_obj["tab_name"] != "distributor"))
            return "ERROR";

        if($data_obj["tab_name"] == "client" && Auth::user()->user_type == 4)
            return "ERROR";

        if($data_obj["tab_name"] == "distributor" && (Auth::user()->user_type == 4 || Auth::user()->user_type == 3))
            return "ERROR";


        //adjust date fields
        $data_obj["start_date"] = date('Y-m-d 00:00:00',strtotime(str_replace('/', '-', $data_obj["start_date"])));
        $data_obj["end_date"] = date('Y-m-d 23:59:59',strtotime(str_replace('/', '-', $data_obj["end_date"])));

        //prepare array which stores selected modems, clients and etc
        $selected_items = self::prepare_selected_items($data_obj);
        $selected_devices = self::prepare_selected_devices($data_obj,$selected_items);

        $return_array = array();

        $whole_consumption_data = self::prepare_device_based_data($data_obj,$selected_devices);
        $whole_alert_data = self::prepare_alert_based_data($data_obj,$selected_devices);
        //burda kaldım

        $return_array[$data_obj["tab_name"]."_most_consumption"] = self::graphMostConsumption($whole_consumption_data,$data_obj);
        $return_array[$data_obj["tab_name"]."_most_reactive"] = self::graphMostReactive($whole_consumption_data,
            $data_obj);

        return json_encode($return_array);
    }

    public static function prepare_device_based_data($data_obj,$selected_devices){

        $meter_table_list = array();
        $modbus_table_list = array();

        foreach ($selected_devices as $one_device){
            if($one_device->device_type == "meter"){
                $meter_table_list[] = $one_device->id;
            }
            else {
                $modbus_table_list[] = $one_device->id;
            }
        }

        $whole_consumption_data = array();

        //handle meters table
        $result = DB::table("device_records_meter as DRM")
            ->select(DB::raw("DRM.device_id as device_id, MAX(DRM.imported_inductive_reactive_energy_total_Q1) as inductive_max,MIN(imported_inductive_reactive_energy_total_Q1) as inductive_min,MAX(exported_capacitive_reactive_total_Q4) as capacitive_max,MIN(exported_capacitive_reactive_total_Q4) as capacitive_min,MAX(DRM.positive_active_energy_total) as total_max, MIN(DRM.positive_active_energy_total) as total_min, D.multiplier as multiplier, D.device_no as device_no, M.serial_no as modem_no,M.id as modem_id,C.name as client_name,C.id as client_id, CASE WHEN C.distributor_id=0 THEN 'system' ELSE DI.name END as distributor_name,C.distributor_id as distributor_id, CASE WHEN C.distributor_id=0 THEN '".trans("graphs.center")."' ELSE JSON_UNQUOTE(json_extract(DI.location,'$.verbal')) END as distributor_location, JSON_UNQUOTE(json_extract(M.location,'$.verbal')) as location, JSON_UNQUOTE(json_extract(M.location,'$.verbal')) as location, JSON_UNQUOTE(json_extract(C.location,'$.verbal')) as client_location,M.distinctive_identifier as distinctive_identifier"))
            ->join("devices as D","D.id","DRM.device_id")
            ->join("modems as M","M.id","D.modem_id")
            ->join("clients as C","C.id","M.client_id")
            ->leftJoin("distributors as DI","DI.id","C.distributor_id")
            ->whereRaw("FIND_IN_SET(DRM.device_id,'".implode(',',$meter_table_list)."')>0")
            ->whereRaw("DRM.device_timestamp BETWEEN '".$data_obj["start_date"]."' AND '".$data_obj["end_date"] ."'")
            ->groupBy("DRM.device_id")
            ->get();
        ;

        foreach ($result as $one_result){

            $total_min = 0;
            $total_max = 0;
            $capacitive_min = 0;
            $capacitive_max = 0;
            $inductive_min = 0;
            $inductive_max = 0;

            if(is_numeric($one_result->total_min))
                $total_min = $one_result->total_min;

            if(is_numeric($one_result->total_max))
                $total_max = $one_result->total_max;

            if(is_numeric($one_result->capacitive_min))
                $capacitive_min = $one_result->capacitive_min;

            if(is_numeric($one_result->capacitive_max))
                $capacitive_max = $one_result->capacitive_max;

            if(is_numeric($one_result->inductive_min))
                $inductive_min = $one_result->inductive_min;

            if(is_numeric($one_result->inductive_max))
                $inductive_max = $one_result->inductive_max;


            $whole_consumption_data[] = array(
                "id"=>$one_result->device_id,
                "device_no" => $one_result->device_no,
                "consumption"=>(($total_max - $total_min)*$one_result->multiplier),
                "capacitive" =>(($capacitive_max - $capacitive_min)*$one_result->multiplier),
                "inductive" =>(($inductive_max - $inductive_min)*$one_result->multiplier),
                "modem_no" => $one_result->modem_no,
                "modem_id" => $one_result->modem_id,
                "client_name" =>$one_result->client_name,
                "client_id" => $one_result->client_id,
                "client_location" =>$one_result->client_location,
                "distributor_name"=>$one_result->distributor_name,
                "distributor_id" => $one_result->distributor_id,
                "distributor_location" => $one_result->distributor_location,
                "location" => $one_result->location,
                "distinctive_identifier" => $one_result->distinctive_identifier
            );
        }

        //handle modbus table
        $result = DB::table("device_records_modbus as DRM")
            ->select(DB::raw("DRM.device_id as device_id, MAX(DRM.imported_inductive_reactive_energy_total_Q1) as inductive_max,MIN(imported_inductive_reactive_energy_total_Q1) as inductive_min,MAX(exported_capacitive_reactive_total_Q4) as capacitive_max,MIN(exported_capacitive_reactive_total_Q4) as capacitive_min,MAX(DRM.positive_active_energy_total) as total_max, MIN(DRM.positive_active_energy_total) as total_min, D.multiplier as multiplier, D.device_no as device_no, M.serial_no as modem_no,M.id as modem_id,C.name as client_name,C.id as client_id, CASE WHEN C.distributor_id=0 THEN '".trans("global.system")."' ELSE DI.name END as distributor_name,C.distributor_id as distributor_id, CASE WHEN C.distributor_id=0 THEN '".trans("graphs.center")."' ELSE JSON_UNQUOTE(json_extract(DI.location,'$.verbal')) END as distributor_location, JSON_UNQUOTE(json_extract(M.location,'$.verbal')) as location, JSON_UNQUOTE(json_extract(C.location,'$.verbal')) as client_location, M.distinctive_identifier as distinctive_identifier"))
            ->join("devices as D","D.id","DRM.device_id")
            ->join("modems as M","M.id","D.modem_id")
            ->join("clients as C","C.id","M.client_id")
            ->leftJoin("distributors as DI","DI.id","C.distributor_id")
            ->whereRaw("FIND_IN_SET(DRM.device_id,'".implode(',',$modbus_table_list)."')>0")
            ->whereRaw("DRM.device_timestamp BETWEEN '".$data_obj["start_date"]."' AND '".$data_obj["end_date"] ."'")
            ->groupBy("DRM.device_id")
            ->get();
        ;

        foreach ($result as $one_result){

            $total_min = 0;
            $total_max = 0;
            $capacitive_min = 0;
            $capacitive_max = 0;
            $inductive_min = 0;
            $inductive_max = 0;

            if(is_numeric($one_result->total_min))
                $total_min = $one_result->total_min;

            if(is_numeric($one_result->total_max))
                $total_max = $one_result->total_max;

            if(is_numeric($one_result->capacitive_min))
                $capacitive_min = $one_result->capacitive_min;

            if(is_numeric($one_result->capacitive_max))
                $capacitive_max = $one_result->capacitive_max;

            if(is_numeric($one_result->inductive_min))
                $inductive_min = $one_result->inductive_min;

            if(is_numeric($one_result->inductive_max))
                $inductive_max = $one_result->inductive_max;


            $whole_consumption_data[] = array(
                "id"=>$one_result->device_id,
                "device_no" => $one_result->device_no,
                "consumption"=>(($total_max - $total_min)*$one_result->multiplier),
                "capacitive" =>(($capacitive_max - $capacitive_min)*$one_result->multiplier),
                "inductive" =>(($inductive_max - $inductive_min)*$one_result->multiplier),
                "modem_no" => $one_result->modem_no,
                "modem_id" => $one_result->modem_id,
                "client_name" =>$one_result->client_name,
                "client_id" => $one_result->client_id,
                "client_location" =>$one_result->client_location,
                "distributor_name"=>$one_result->distributor_name,
                "distributor_id" => $one_result->distributor_id,
                "distributor_location" => $one_result->distributor_location,
                "location" => $one_result->location,
                "distinctive_identifier" => $one_result->distinctive_identifier
            );

        }
        return $whole_consumption_data;

    }
    public static function graphMostConsumption($whole_consumption_data,$data_obj){

        if($data_obj["tab_name"] == "device") {

            usort($whole_consumption_data, function ($a, $b) {

                if ($a["consumption"] < $b["consumption"])
                    return 1;
                else
                    return -1;
            });

            $whole_consumption_data = array_slice($whole_consumption_data, 0, 10);
            $trans_name = "devices";

        }
        else if($data_obj["tab_name"] == "modem"){
            $modem_data_array = array();

            foreach ($whole_consumption_data as $one_data){

                if(isset($modem_data_array[$one_data["modem_id"]])){
                    $modem_data_array[$one_data["modem_id"]]["consumption"] += $one_data["consumption"];
                }
                else{
                    $modem_data_array[$one_data["modem_id"]] = array(
                        "modem_no" => $one_data["modem_no"],
                        "client_name" => $one_data["client_name"],
                        "distributor_name" => $one_data["distributor_name"],
                        "consumption" => $one_data["consumption"],
                        "location" => $one_data["location"],
                        "distinctive_identifier" => $one_data["distinctive_identifier"]
                    );
                }

            }

            usort($modem_data_array,function($a,$b){

                if($a["consumption"]<$b["consumption"])
                    return 1;
                else
                    return -1;
            });

            $modem_data_array = array_slice($modem_data_array,0,10);
            $whole_consumption_data = $modem_data_array;

            $trans_name = "modems";
        }
        else if($data_obj["tab_name"] == "client"){

            $client_data_array = array();

            foreach ($whole_consumption_data as $one_data){

                if(isset($client_data_array[$one_data["client_id"]])){
                    $client_data_array[$one_data["client_id"]]["consumption"] += $one_data["consumption"];
                    $client_data_array[$one_data["client_id"]]["device_count"]++;
                }
                else{
                    $client_data_array[$one_data["client_id"]] = array(
                        "client_name" => $one_data["client_name"],
                        "distributor_name" => $one_data["distributor_name"],
                        "consumption" => $one_data["consumption"],
                        "location" => $one_data["client_location"],
                        "distinctive_identifier" => "",
                        "device_count" =>1
                    );
                }

            }

            usort($client_data_array,function($a,$b){

                if($a["consumption"]<$b["consumption"])
                    return 1;
                else
                    return -1;
            });

            $client_data_array = array_slice($client_data_array,0,10);
            $whole_consumption_data = $client_data_array;

            $trans_name = "clients";
        }
        else if($data_obj["tab_name"] == "distributor"){

            $distributor_data_array = array();

            foreach ($whole_consumption_data as $one_data){

                if(isset($distributor_data_array[$one_data["distributor_id"]])){
                    $distributor_data_array[$one_data["distributor_id"]]["consumption"] += $one_data["consumption"];
                    $distributor_data_array[$one_data["distributor_id"]]["device_count"]++;
                }
                else{
                    $distributor_data_array[$one_data["distributor_id"]] = array(
                        "distributor_name" => $one_data["distributor_name"],
                        "consumption" => $one_data["consumption"],
                        "distinctive_identifier" => "",
                        "location" => $one_data["distributor_location"],
                        "device_count" =>1
                    );
                }

            }

            usort($distributor_data_array,function($a,$b){

                if($a["consumption"]<$b["consumption"])
                    return 1;
                else
                    return -1;
            });

            $distributor_data_array = array_slice($distributor_data_array,0,10);
            $whole_consumption_data = $distributor_data_array;

            $trans_name = "distributors";
        }

        $active_total = array("name"=>trans('graphs.consumption_graph',array("item"=>trans("graphs.".$trans_name))),
            "data"=>array(),
            "visible"=>true);
        foreach ($whole_consumption_data as $one_data){

            if($data_obj["tab_name"] == "device"){

                if(Auth::user()->user_type == 4){
                    $org_info = $one_data["modem_no"]." / ".$one_data["device_no"];
                }
                else if(Auth::user()->user_type == 3){
                    $org_info = $one_data["client_name"]." / ".$one_data["modem_no"]." / ".$one_data["device_no"];
                }
                else if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                    $org_info = $one_data["distributor_name"]." / ".$one_data["client_name"]." / ".$one_data["modem_no"]." / ".$one_data["device_no"];
                }
                else{
                    abort(404);
                }
                $point_name = $one_data["device_no"];
            }
            else if($data_obj["tab_name"] == "modem"){
                if(Auth::user()->user_type == 4){
                    $org_info = $one_data["modem_no"];
                }
                else if(Auth::user()->user_type == 3){
                    $org_info = $one_data["client_name"]." / ".$one_data["modem_no"];
                }
                else if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                    $org_info = $one_data["distributor_name"]." / ".$one_data["client_name"]." / ".$one_data["modem_no"];
                }
                else{
                    abort(404);
                }
                $point_name = $one_data["modem_no"];
            }
            else if($data_obj["tab_name"] == "client"){

                if(Auth::user()->user_type == 3){
                    $org_info = $one_data["client_name"];
                }
                else if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                    $org_info = $one_data["distributor_name"]." / ".$one_data["client_name"];
                }
                else{
                    abort(404);
                }
                $point_name = $one_data["client_name"];

                $org_info .= "(".trans('graphs.device_count').": ".$one_data["device_count"].")";
            }
            else if($data_obj["tab_name"] == "distributor"){

                if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                    $org_info = $one_data["distributor_name"];
                }
                else{
                    abort(404);
                }
                $point_name = $one_data["distributor_name"];

                $org_info .= "(".trans('graphs.device_count').": ".$one_data["device_count"].")";
            }

            $distinctive_identifier = "";
            if($one_data["distinctive_identifier"] !="")
                $distinctive_identifier = "(".$one_data["distinctive_identifier"].")";

            $active_total["data"][] = array("name"=>$point_name,"y"=>$one_data["consumption"],"unit"=>"kW-h","org_info"=>$org_info,"location"=>$one_data["location"].$distinctive_identifier);
        }
        $data = array();
        $data[] = $active_total;

        // parameters: title, y_title, categories, series
        $the_chart = new HighChart(
            trans('graphs.consumption_graph',array("item"=>trans("graphs.".$trans_name))),
            trans('graphs.consumption'),
            false,
            $data
        );

        $the_chart->setChartType("column");
        $the_chart->setXAxisType("category");
        $the_chart->setIsLegend(false);

        return json_decode($the_chart->getOptions());



    }
    public static function graphMostReactive($whole_consumption_data,$data_obj){

        if($data_obj["tab_name"] == "device") {

            $device_capacitive_data = $whole_consumption_data;
            $device_inductive_data = $whole_consumption_data;

            usort($whole_consumption_data, function ($a, $b) {

                if ($a["capacitive"] < $b["capacitive"]){

                    if($a["inductive"] < $b["inductive"]){
                        return 1;
                    }
                    else{

                        if(($b["capacitive"] - $a["capacitive"]) < ($a["inductive"] - $b["inductive"])){

                            return -1;
                        }
                        else
                            return 1;
                    }
                }
                else{

                    if($b["inductive"] < $a["inductive"]){
                        return -1;
                    }
                    else{

                        if(($a["capacitive"] - $b["capacitive"]) < ($b["inductive"] - $a["inductive"])){

                            return 1;
                        }
                        else
                            return -1;
                    }

                }

            });

            $whole_consumption_data = array_slice($whole_consumption_data, 0, 10);
            $trans_name = "devices";

        }
        else if($data_obj["tab_name"] == "modem"){
            $modem_data_array = array();

            foreach ($whole_consumption_data as $one_data){

                if(isset($modem_data_array[$one_data["modem_id"]])){
                    $modem_data_array[$one_data["modem_id"]]["capacitive"] += $one_data["capacitive"];
                    $modem_data_array[$one_data["modem_id"]]["inductive"] += $one_data["inductive"];
                }
                else{
                    $modem_data_array[$one_data["modem_id"]] = array(
                        "modem_no" => $one_data["modem_no"],
                        "client_name" => $one_data["client_name"],
                        "distributor_name" => $one_data["distributor_name"],
                        "capacitive" => $one_data["capacitive"],
                        "inductive" => $one_data["inductive"],
                        "location" => $one_data["location"],
                        "distinctive_identifier" => $one_data["distinctive_identifier"]
                    );
                }

            }

            usort($modem_data_array, function ($a, $b) {

                if ($a["capacitive"] < $b["capacitive"]){

                    if($a["inductive"] < $b["inductive"]){
                        return 1;
                    }
                    else{

                        if(($b["capacitive"] - $a["capacitive"]) < ($a["inductive"] - $b["inductive"])){

                            return -1;
                        }
                        else
                            return 1;
                    }
                }
                else{

                    if($b["inductive"] < $a["inductive"]){
                        return -1;
                    }
                    else{

                        if(($a["capacitive"] - $b["capacitive"]) < ($b["inductive"] - $a["inductive"])){

                            return 1;
                        }
                        else
                            return -1;
                    }

                }

            });

            $modem_data_array = array_slice($modem_data_array,0,10);
            $whole_consumption_data = $modem_data_array;

            $trans_name = "modems";
        }
        else if($data_obj["tab_name"] == "client"){

            $client_data_array = array();

            foreach ($whole_consumption_data as $one_data){

                if(isset($client_data_array[$one_data["client_id"]])){
                    $client_data_array[$one_data["client_id"]]["capacitive"] += $one_data["capacitive"];
                    $client_data_array[$one_data["client_id"]]["inductive"] += $one_data["inductive"];
                    $client_data_array[$one_data["client_id"]]["device_count"]++;
                }
                else{
                    $client_data_array[$one_data["client_id"]] = array(
                        "client_name" => $one_data["client_name"],
                        "distributor_name" => $one_data["distributor_name"],
                        "capacitive" => $one_data["capacitive"],
                        "inductive" => $one_data["inductive"],
                        "location" => $one_data["client_location"],
                        "distinctive_identifier" => "",
                        "device_count" =>1
                    );
                }

            }

            usort($client_data_array, function ($a, $b) {

                if ($a["capacitive"] < $b["capacitive"]){

                    if($a["inductive"] < $b["inductive"]){
                        return 1;
                    }
                    else{

                        if(($b["capacitive"] - $a["capacitive"]) < ($a["inductive"] - $b["inductive"])){

                            return -1;
                        }
                        else
                            return 1;
                    }
                }
                else{

                    if($b["inductive"] < $a["inductive"]){
                        return -1;
                    }
                    else{

                        if(($a["capacitive"] - $b["capacitive"]) < ($b["inductive"] - $a["inductive"])){

                            return 1;
                        }
                        else
                            return -1;
                    }

                }

            });

            $client_data_array = array_slice($client_data_array,0,10);
            $whole_consumption_data = $client_data_array;

            $trans_name = "clients";
        }
        else if($data_obj["tab_name"] == "distributor"){

            $distributor_data_array = array();

            foreach ($whole_consumption_data as $one_data){

                if(isset($distributor_data_array[$one_data["distributor_id"]])){
                    $distributor_data_array[$one_data["distributor_id"]]["capacitive"] += $one_data["capacitive"];
                    $distributor_data_array[$one_data["distributor_id"]]["inductive"] += $one_data["inductive"];
                    $distributor_data_array[$one_data["distributor_id"]]["device_count"]++;
                }
                else{
                    $distributor_data_array[$one_data["distributor_id"]] = array(
                        "distributor_name" => $one_data["distributor_name"],
                        "capacitive" => $one_data["capacitive"],
                        "inductive" => $one_data["inductive"],
                        "distinctive_identifier" => "",
                        "location" => $one_data["distributor_location"],
                        "device_count" =>1
                    );
                }

            }

            usort($distributor_data_array, function ($a, $b) {

                if ($a["capacitive"] < $b["capacitive"]){

                    if($a["inductive"] < $b["inductive"]){
                        return 1;
                    }
                    else{

                        if(($b["capacitive"] - $a["capacitive"]) < ($a["inductive"] - $b["inductive"])){

                            return -1;
                        }
                        else
                            return 1;
                    }
                }
                else{

                    if($b["inductive"] < $a["inductive"]){
                        return -1;
                    }
                    else{

                        if(($a["capacitive"] - $b["capacitive"]) < ($b["inductive"] - $a["inductive"])){

                            return 1;
                        }
                        else
                            return -1;
                    }

                }

            });

            $distributor_data_array = array_slice($distributor_data_array,0,10);
            $whole_consumption_data = $distributor_data_array;

            $trans_name = "distributors";
        }

        $capacitive_total = array("name"=>trans('graphs.capacitive',array("item"=>trans("graphs.".$trans_name))),
            "data"=>array(),
            "visible"=>true);

        $inductive_total = array("name"=>trans('graphs.inductive',array("item"=>trans("graphs.".$trans_name))),
            "data"=>array(),
            "visible"=>true);

        $categories = array();
        foreach ($whole_consumption_data as $one_data){

            if($data_obj["tab_name"] == "device"){

                if(Auth::user()->user_type == 4){
                    $org_info = $one_data["modem_no"]." / ".$one_data["device_no"];
                }
                else if(Auth::user()->user_type == 3){
                    $org_info = $one_data["client_name"]." / ".$one_data["modem_no"]." / ".$one_data["device_no"];
                }
                else if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                    $org_info = $one_data["distributor_name"]." / ".$one_data["client_name"]." / ".$one_data["modem_no"]." / ".$one_data["device_no"];
                }
                else{
                    abort(404);
                }
                $point_name = $one_data["device_no"];
            }
            else if($data_obj["tab_name"] == "modem"){
                if(Auth::user()->user_type == 4){
                    $org_info = $one_data["modem_no"];
                }
                else if(Auth::user()->user_type == 3){
                    $org_info = $one_data["client_name"]." / ".$one_data["modem_no"];
                }
                else if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                    $org_info = $one_data["distributor_name"]." / ".$one_data["client_name"]." / ".$one_data["modem_no"];
                }
                else{
                    abort(404);
                }
                $point_name = $one_data["modem_no"];
            }
            else if($data_obj["tab_name"] == "client"){

                if(Auth::user()->user_type == 3){
                    $org_info = $one_data["client_name"];
                }
                else if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                    $org_info = $one_data["distributor_name"]." / ".$one_data["client_name"];
                }
                else{
                    abort(404);
                }
                $point_name = $one_data["client_name"];

                $org_info .= "(".trans('graphs.device_count').": ".$one_data["device_count"].")";
            }
            else if($data_obj["tab_name"] == "distributor"){

                if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                    $org_info = $one_data["distributor_name"];
                }
                else{
                    abort(404);
                }
                $point_name = $one_data["distributor_name"];

                $org_info .= "(".trans('graphs.device_count').": ".$one_data["device_count"].")";
            }

            $distinctive_identifier = "";
            if($one_data["distinctive_identifier"] !="")
                $distinctive_identifier = "(".$one_data["distinctive_identifier"].")";


            $categories[] = $point_name;
            $capacitive_total["data"][] = array("y"=>$one_data["capacitive"],"unit"=>"kW-h","org_info"=>$org_info,"location"=>$one_data["location"].$distinctive_identifier);

            $inductive_total["data"][] = array("y"=>$one_data["inductive"],"unit"=>"kW-h","org_info"=>$org_info,"location"=>$one_data["location"].$distinctive_identifier);
        }
        $data = array();
        $data[] = $capacitive_total;
        $data[] = $inductive_total;

        // parameters: title, y_title, categories, series
        $the_chart = new HighChart(
            trans('graphs.reactive_graph',array("item"=>trans("graphs.".$trans_name))),
            trans('graphs.consumption'),
            $categories,
            $data
        );

        $the_chart->setChartType("column");
        $the_chart->setXAxisType("category");
        //$the_chart->setIsLegend(false);

        return json_decode($the_chart->getOptions());
    }

    public static function prepare_alert_based_data($data_obj,$selected_devices){

        $whole_alert_data_array = array();

        $device_ids = array();
        foreach ($selected_devices as $one_device)
            $device_ids[] = $one_device->id;


        $alerts  = DB::table("alerts as A")
            ->select(DB::raw("A.device_id as device_id, A.type as type,D.device_no as device_no, M.serial_no as modem_no,M.id as modem_id,C.name as client_name,C.id as client_id, CASE WHEN C.distributor_id=0 THEN '".trans("global.system")."' ELSE DI.name END as distributor_name,C.distributor_id as distributor_id, CASE WHEN C.distributor_id=0 THEN '".trans("graphs.center")."' ELSE JSON_UNQUOTE(json_extract(DI.location,'$.verbal')) END as distributor_location, JSON_UNQUOTE(json_extract(M.location,'$.verbal')) as location, JSON_UNQUOTE(json_extract(C.location,'$.verbal')) as client_location, M.distinctive_identifier as distinctive_identifier"))
            ->join("devices as D","D.id","A.device_id")
            ->join("modems as M","M.id","D.modem_id")
            ->join("clients as C","C.id","M.client_id")
            ->leftJoin("distributors as DI","DI.id","C.distributor_id")
            ->whereRaw("FIND_IN_SET(A.device_id,'".implode(',',$device_ids)."')>0")
            ->whereRaw("A.created_at BETWEEN '".$data_obj["start_date"]."' AND '".$data_obj["end_date"] ."'")
            ->get();
        ;

        return $alerts;
    }

    public static function prepare_selected_items($data_obj){

        $return_array = array();
        if($data_obj["tab_name"] == "device" || $data_obj["tab_name"]=="modem"){

            if(Auth::user()->user_type == 4){

            }
            else{
                if(isset($data_obj["checked_modems"])){

                    $tmp_modem_list = $data_obj["checked_modems"];
                    foreach ($tmp_modem_list as $one_modem){

                        $tmp_modem = explode("_",$one_modem);
                        $return_array[] = $tmp_modem[0];
                    }
                }
            }
        }
        else if($data_obj["tab_name"] == "client"){
            if(Auth::user()->user_type == 4){

                abort(404);
            }
            else{
                if(isset($data_obj["checked_clients"])){

                    $tmp_client_list = $data_obj["checked_clients"];
                    foreach ($tmp_client_list as $one_client){

                        $tmp_client = explode("_",$one_client);
                        $return_array[] = $tmp_client[0];
                    }
                }
            }
        }

        return $return_array;
    }
    public static function prepare_selected_devices($data_obj,$selected_items){

        if(Auth::user()->user_type == 4){

            if($data_obj["tab_name"] == "device" || $data_obj["tab_name"] == "modem"){
                //get the devices of user_type 4 (client user)
                $devices = DB::table("devices as D")
                    ->select('D.id as id','DT.type as device_type')
                    ->join("modems as M",'M.id','D.modem_id')
                    ->join("clients as C",'C.id','M.client_id')
                    ->join("device_type as DT","DT.id","D.device_type_id")
                    ->where('C.id',Auth::user()->org_id)
                    ->where('D.status','<>',0)
                    ->where('M.status','<>',0)
                    ->where('C.status','<>',0)
                    ->get();
            }
            else{
                abort(404);
            }

        }
        else if(Auth::user()->user_type == 3){
            //get the devices of user_type 3 (distributor user)
            $where_clause = "DI.id = ".Auth::user()->org_id;

            if($data_obj["tab_name"] == "device" || $data_obj["tab_name"] == "modem"){
                if(count($selected_items)>0){
                    $where_clause .= " AND FIND_IN_SET(M.id,'".implode(',',$selected_items)."')";
                }
            }
            else if($data_obj["tab_name"] == "client"){
                if(count($selected_items)>0){
                    $where_clause .= " AND FIND_IN_SET(C.id,'".implode(',',$selected_items)."')";
                }
            }
            else{
                abort(404);
            }

            $devices = DB::table("devices as D")
                ->select('D.id as id','DT.type as device_type')
                ->join("modems as M",'M.id','D.modem_id')
                ->join("clients as C",'C.id','M.client_id')
                ->join("device_type as DT","DT.id","D.device_type_id")
                ->join("distributors as DI","DI.id","C.distributor_id")
                ->whereRaw(''.$where_clause)
                ->where('D.status','<>',0)
                ->where('M.status','<>',0)
                ->where('C.status','<>',0)
                ->where('DI.status','<>',0)
                ->get();
        }
        else{

            $where_clause = "1=1";

            if($data_obj["tab_name"] == "device" || $data_obj["tab_name"] == "modem"){
                if(count($selected_items)>0){
                    $where_clause .= " AND FIND_IN_SET(M.id,'".implode(',',$selected_items)."')";
                }
            }
            else if($data_obj["tab_name"] == "client"){
                if(count($selected_items)>0){
                    $where_clause .= " AND FIND_IN_SET(C.id,'".implode(',',$selected_items)."')";
                }
            }
            else if($data_obj["tab_name"] == "distributor"){


            }
            else{
                abort(404);
            }

            $devices = DB::table("devices as D")
                ->select('D.id as id','DT.type as device_type')
                ->join("modems as M",'M.id','D.modem_id')
                ->join("clients as C",'C.id','M.client_id')
                ->join("device_type as DT","DT.id","D.device_type_id")
                ->whereRaw(''.$where_clause)
                ->where('D.status','<>',0)
                ->where('M.status','<>',0)
                ->where('C.status','<>',0)
                ->get();
        }

        return $devices;
    }
}
