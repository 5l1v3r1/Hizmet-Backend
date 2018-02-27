<?php

namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Helpers\Helper;
use DateTime;
use DateInterval;
use Exception;
use Sunra\PhpSimple\HtmlDomParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemperatureController extends Controller
{
    private $columns;
    private $temperature_columns;

    public function __construct()
    {
        $this->columns = array(
            "ICAO" => array(),
            "IATA" => array(),
            "name" => array(),
            "location" => array(),
            "last_fetched_date" =>array(),
            "buttons" =>array("orderable"=>false,"name"=>"operations","nowrap"=>true)
        );

        $this->temperature_columns = array(
            "date" =>array(),
            "mean_temperature" =>array(),
            "min_temperature" =>array(),
            "max_temperature" =>array(),
            "average_humidity" =>array(),
            "min_humidity" =>array(),
            "max_humidity" =>array(),
        );
    }

    public function showTable(Request $request){

        $prefix = "at";
        $url = "at_get_data";
        $default_order = '[0,"asc"]';

        $data_table = new DataTable($prefix, $url, $this->columns, $default_order, $request);
        $data_table->set_add_right(false);

        return view('pages.temperature')->with("DataTableObj",$data_table);
    }

    public function getData(Request $request){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $order_column = "name";
        $order_dir = "ASC";

        $param_array = array();
        $where_clause = " 1=1 ";

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;


            if($order_column =="location"){
                $order_column = "JSON_UNQUOTE(json_extract(location, '$.verbal'))";
            }
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(last_fetched_date) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]="%".$search_value."%";
                $where_clause .= "name LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR ICAO LIKE ? ";
                $param_array[]="%".$search_value."%";
                $where_clause .= " OR IATA LIKE ? ";
                $param_array[]="%".strtolower($search_value)."%";
                $where_clause .= " OR lcase(JSON_UNQUOTE(json_extract(location,'$.verbal'))) LIKE ? ";
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('
                                    SELECT 
                                      count(*) as total_count 
                                    FROM 
                                      airports
                                    WHERE '.$where_clause,
            $param_array
        );
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $result = DB::table('airports')
            ->select(
                'name',
                'ICAO',
                'IATA',
                'id',
                'last_fetched_date',
                DB::raw("JSON_UNQUOTE(json_extract(location, '$.verbal')) as location_verbal"),
                DB::raw("JSON_UNQUOTE(json_extract(location, '$.text')) as location_text")
            )
            ->whereRaw($where_clause, $param_array)
            ->orderBy($order_column, $order_dir)
            ->offset($start)
            ->limit($length)
            ->get();

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){


                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "name" => $one_row->name,
                    "IATA" => $one_row->IATA,
                    "ICAO" => $one_row->ICAO,
                    "last_fetched_date" => date('d/m/Y H:i:s',strtotime($one_row->last_fetched_date)),
                    "location" => "<span data-toggle='tooltip' data-placement='bottom' title='".$one_row->location_text."'>".$one_row->location_verbal."</span>",
                    "buttons" => self::create_buttons($one_row->id)
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public function create_buttons($id){
        $return_value = "";

        if(Helper::has_right(Auth::user()->operations, "view_temperature_detail")){
            $return_value .= '<a href="/temperature/detail/'.$id.'" title="'.trans('temperature.temperature_detail').'" class="btn btn-info btn-sm"><i class="fa fa-info-circle fa-lg"></i></a> ';
        }


        if($return_value==""){
            $return_value = '<i title="'.trans('global.no_authorize').'" style="color:red;" class="fa fa-minus-circle fa-lg"></i>';
        }

        return $return_value;
    }

    public function temperatureDetail(Request $request, $id){

        $the_airport = DB::table('airports')
            ->select(
                'name',
                'ICAO',
                'IATA',
                'last_fetched_date',
                DB::raw("JSON_UNQUOTE(json_extract(location, '$.latitude')) as location_latitude"),
                DB::raw("JSON_UNQUOTE(json_extract(location, '$.longitude')) as location_longitude"),
                DB::raw("JSON_UNQUOTE(json_extract(location, '$.text')) as location_text")
            )
            ->where('id',$id)
            ->first();



        $prefix = "td";
        $url = "td_get_data/".$id;
        $default_order = '[0,"desc"]';
        $temperature_table = new DataTable($prefix, $url, $this->temperature_columns, $default_order, $request);
        $temperature_table->set_add_right(false);
        $temperature_table->set_lang_page("temperature");

        return view(
            'pages.temperature_detail',
            [
                'the_airport' => $the_airport,
                'TemperatureTableObj' => $temperature_table
            ]
        );
    }

    public function temperatureTableData(Request $request, $id){
        $return_array = array();
        $draw  = $_GET["draw"];
        $start = $_GET["start"];
        $length = $_GET["length"];
        $record_total = 0;
        $recordsFiltered = 0;
        $search_value = false;
        $order_column = "date";
        $order_dir = "DESC";

        $param_array = array($id);
        $where_clause = " airport_id=? ";

        //get customized filter object
        $filter_obj = false;
        if(isset($_GET["filter_obj"])){
            $filter_obj = $_GET["filter_obj"];
            $filter_obj = json_decode($filter_obj,true);
        }

        if(isset($_GET["order"][0]["column"])){
            $order_column = $_GET["order"][0]["column"];

            $column_item = array_keys(array_slice($this->temperature_columns, $order_column, 1));
            $column_item = $column_item[0];
            $order_column = $column_item;
        }

        if(isset($_GET["order"][0]["dir"])){
            $order_dir = $_GET["order"][0]["dir"];
        }

        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["start_date"])));
        $param_array[] = date('Y-m-d', strtotime(str_replace('/', '-', $filter_obj["end_date"])));
        $where_clause .= "AND DATE(date) BETWEEN ? AND ? ";

        if(isset($_GET["search"])){
            $search_value = $_GET["search"]["value"];
            if(!(trim($search_value)=="" || $search_value === false)){
                $where_clause .= " AND (";
                $param_array[]= $search_value;
                $where_clause .= "mean_temperature = ? ";
                $param_array[]= $search_value;
                $where_clause .= " OR min_temperature = ? ";
                $param_array[]= $search_value;
                $where_clause .= " OR max_temperature = ? ";
                $where_clause .= " ) ";
            }
        }

        $total_count = DB::select('
                                    SELECT 
                                      count(*) as total_count 
                                    FROM 
                                      temperature
                                    WHERE '.$where_clause,
            $param_array
        );
        $total_count = $total_count[0];
        $total_count = $total_count->total_count;

        $result = DB::table('temperature')
            ->whereRaw($where_clause, $param_array)
            ->orderBy($order_column, $order_dir)
            ->offset($start)
            ->limit($length)
            ->get();

        $return_array["draw"]=$draw;
        $return_array["recordsTotal"]= 0;
        $return_array["recordsFiltered"]= 0;
        $return_array["data"] = array();

        if(COUNT($result)>0){
            $return_array["recordsTotal"]=$total_count;
            $return_array["recordsFiltered"]=$total_count;

            foreach($result as $one_row){

                $tmp_array = array(
                    "DT_RowId" => $one_row->id,
                    "date" => date('d/m/Y',strtotime($one_row->date)),
                    "mean_temperature" => $one_row->mean_temperature." C°",
                    "min_temperature" => $one_row->min_temperature." C°",
                    "max_temperature" => $one_row->max_temperature." C°",
                    "min_humidity" => $one_row->min_humidity,
                    "average_humidity" => $one_row->average_humidity,
                    "max_humidity" => $one_row->max_humidity
                );

                $return_array["data"][] = $tmp_array;
            }
        }

        echo json_encode($return_array);
    }

    public static function traverseAirports(){

        set_time_limit(0);

        $airports = DB::table('airports')->get();

        $the_date = date('Y-m-d',strtotime('-1 day'));
        $year = date('Y',strtotime($the_date));
        $month = date('n',strtotime($the_date));
        $day = date('j',strtotime($the_date));

        foreach ($airports as $one_airport){

            $the_array = self::getAirportTemperature($one_airport->ICAO,$year,$month,$day);


            DB::table('temperature')->insert(
                [
                    'airport_id' => $one_airport->id,
                    'mean_temperature' => $the_array["mean_temperature"],
                    'max_temperature' => $the_array["max_temperature"],
                    'min_temperature' => $the_array["min_temperature"],
                    'average_humidity' => $the_array["average_humidity"],
                    'max_humidity' => $the_array["max_humidity"],
                    'min_humidity' => $the_array["min_humidity"],
                    'date' => $the_date
                ]
            );
        }

    }
    public static function getAirportTemperature($icao,$year,$month,$day){

        $return_array = array(
            "mean_temperature" => null,
            "max_temperature" => null,
            "min_temperature" => null,
            "average_humidity" => null,
            "max_humidity" => null,
            "min_humidity" => null
        );

        $url = "https://www.wunderground.com/history/airport/".$icao."/".$year."/".$month."/".$day."/DailyHistory.html";

        try{
            $html = HtmlDomParser::file_get_html($url);
        }
        catch(Exception $e){

            return self::getAirportTemperature($icao,$year,$month,$day);
        }


        $history_table =  $html->find('#historyTable',0);
        if($history_table == null){
            return $return_array;
        }


        $tr_data =  $history_table->children(1)->find("tr");

        if($tr_data == null)
            return $return_array;


        foreach ($tr_data as $tr){


            //echo $tr->text();

            $tr_key = $tr->children(0);

            if($tr_key == null){
                continue;
            }
            else{

                $tr_key = trim($tr_key->text());

                if($tr_key == "Mean Temperature"){

                    $cell = $tr->children(1)->find(".wx-value",0);
                    if($cell != null){
                        $cell = $cell->text();

                        if($cell != ""){

                            $return_array["mean_temperature"] = $cell;
                        }
                    }
                }
                else if($tr_key == "Max Temperature"){

                    $cell = $tr->children(1)->find(".wx-value",0);
                    if($cell != null){
                        $cell = $cell->text();

                        if($cell != ""){
                            $return_array["max_temperature"] = $cell;
                        }
                    }

                }
                else if($tr_key == "Min Temperature"){
                    $cell = $tr->children(1)->find(".wx-value",0);
                    if($cell != null){
                        $cell = $cell->text();

                        if($cell != ""){
                            $return_array["min_temperature"] = $cell;
                        }
                    }

                }
                else if($tr_key == "Average Humidity"){
                    $cell = $tr->children(1);
                    if($cell != null){
                        $cell = $cell->text();

                        if($cell != ""){
                            if(is_numeric($cell))
                                $return_array["average_humidity"] = $cell;
                        }
                    }

                }
                else if($tr_key == "Maximum Humidity"){
                    $cell = $tr->children(1);
                    if($cell != null){
                        $cell = $cell->text();

                        if($cell != ""){
                            if(is_numeric($cell))
                                $return_array["max_humidity"] = $cell;
                        }
                    }

                }
                else if($tr_key == "Minimum Humidity"){
                    $cell = $tr->children(1);
                    if($cell != null){
                        $cell = $cell->text();

                        if($cell != ""){
                            if(is_numeric($cell))
                                $return_array["min_humidity"] = $cell;
                        }
                    }

                }
            }
        }

        return $return_array;
    }
}
