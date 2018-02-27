<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HighChart{

    private $title;
    private $subtitle;
    private $categories;
    private $y_title;
    private $series;
    private $xAxis_type;
    private $unit;
    private $show_type;
    private $chart_type;
    private $is_legend;

    public function __construct($title, $y_title, $categories, $series)
    {
        $this->title = $title;
        $this->subtitle = false;
        $this->unit = false;
        $this->xAxis_type = "datetime";
        $this->show_type = "periodic";
        $this->y_title = $y_title;
        $this->categories = $categories; //typeof array or false if auto x-axis labelling
        $this->chart_type = "spline";
        $this->is_legend = true;

        //typeof array
        $this->series = $series;
    }

    public function setSubtitle($subtitle){
        $this->subtitle = $subtitle;
    }

    public function setXAxisType($type){
        $this->xAxis_type = $type;
    }

    public function setUnit($unit){
        $this->unit = $unit;
    }

    public function setShowType($show_type){
        $this->show_type = $show_type;
    }

    public function setChartType($chart_type){
        $this->chart_type = $chart_type;
    }

    public function setIsLegend($is_legend){
        $this->is_legend = $is_legend;
    }

    public function getOptions(){
        $return_array = array();

        $columnChart = array(
            "renderTo" => "div_prepared_graph",
            "type" => $this->chart_type,
            "zoomType" => 'x'
            // karşılaştırma olmayan tek grafiklerin aylık gösterimlerinde falan bar chart güzel olabilir
        );

        $return_array["chart"] = $columnChart;

        $return_array["title"] = array(
            "text" => $this->title,
            "x" => -20
        );

        if($this->subtitle != false){
            $return_array["subtitle"] = array(
                "text" => $this->subtitle,
                "x" => -20
            );
        }

        if($this->categories != false){
            $return_array["xAxis"]["categories"] = $this->categories;
        }
        else{
            $return_array["xAxis"]["type"] = $this->xAxis_type;

            //periodic and daily show_types can use default label_format
            if( $this->show_type == "monthly" ){
                $return_array["xAxis"]["dateTimeLabelFormats"] = array(
                    "day" => '%b %y'
                );
            }
            else if( $this->show_type == "yearly" ){
                $return_array["xAxis"]["dateTimeLabelFormats"] = array(
                    "day" => '%Y'
                );
            }

        }

        $return_array["xAxis"]["labels"] = array(
                "rotation" => -45,
                "align" => "right",
                "style" => array(
                    "fontSize" => "10px"
                    //"fontFamily" => "Verdana, sans-serif"
                )
            );

        $return_array["yAxis"] = array(
            "title" => array(
                "text" => $this->y_title
            )
        );

        $return_array["legend"] = array(
            "enabled" => $this->is_legend
        );

        $return_array["credits"] = array(
            "enabled" => false
        );

        $return_array["exporting"] = array(
            "enabled" => true
        );

        $return_array["plotOptions"] = array(
            "column" => array(
                "colorByPoint" => true,
                "maxPointWidth" => 50
            )
        );

        $return_array["series"] = $this->series;


        // Other auxiliary variables
        if($this->unit != false){
            $return_array["unit"] = $this->unit;
        }

        // periodic, daily, monthly or yearly (default: periodic)
        $return_array["show_type"] = $this->show_type;

        return json_encode($return_array);
    }
}