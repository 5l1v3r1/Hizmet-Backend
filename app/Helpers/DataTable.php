<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DataTable{

    private $prefix;
    private $url;
    private $columns;
    private $default_order;
    private $current_page;
    private $lang_page;
    private $add_right;
    private $start_date;
    private $end_date;
    private $init_fnct;

    public function __construct($prefix, $url, $columns, $default_order, $request){
        $this->prefix = $prefix;
        $this->url = $url;
        $this->columns = $columns;
        $this->default_order  = $default_order;
        $this->current_page = $request->segment(1);
        $this->lang_page = $this->current_page;
        $this->init_fnct = false;

        //set add_right operation
        $this->add_right = "add_new_".explode('_', $this->current_page)[0];

        $this->start_date = date('d/m/Y', strtotime("-10 year"));
        $this->end_date = date('d/m/Y');
    }

    public function set_date_range($start_date,$end_date){
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function set_add_right($has_right){
        $this->add_right = $has_right;
    }

    public function set_lang_page($lang_page){
        $this->lang_page = $lang_page;
    }

    public function set_init_fnct($init_fnct){
        $this->init_fnct = $init_fnct;
    }

    public function css(){
        /* $datatable = '
            <link rel="stylesheet" type="text/css" href="/css/plugins/dataTables/dataTables.bootstrap.css">
            <link rel="stylesheet" type="text/css" href="/js/plugins/dataTables/buttons/css/buttons.dataTables.css">
        '; */

        $datatable = '
            <link rel="stylesheet" type="text/css" href="/js/plugins/dataTables_new/media/css/dataTables.bootstrap.min.css">
            <link rel="stylesheet" type="text/css" href="/js/plugins/dataTables_new/extensions/Buttons/css/buttons.bootstrap.min.css">
            <link rel="stylesheet" type="text/css" href="/js/plugins/dataTables_new/extensions/Buttons/css/buttons.dataTables.min.css">
            
        ';

        return $datatable . '
            <link rel="stylesheet" type="text/css" href="/css/plugins/chosen/chosen.css">
            <link rel="stylesheet" type="text/css" href="/css/plugins/awesome-checkbox/awesome-bootstrap-checkbox.css">
            <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-datepicker/bootstrap-datepicker3.min.css">
            <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
            <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/select2-bootstrap.min.css" />
            <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all" />
            <style> .no_wrap{white-space: nowrap;} </style>
        ';
    }

    public function js(){
        /* $datatable = '
            <script type="text/javascript" language="javascript" src="/js/plugins/dataTables/jquery.dataTables.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/dataTables/dataTables.bootstrap.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/dataTables/buttons/js/dataTables.buttons.min.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/dataTables/buttons/js/jszip.min.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/dataTables/buttons/js/pdfmake.min.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/dataTables/buttons/js/vfs_fonts.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/dataTables/buttons/js/buttons.html5.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/dataTables/buttons/js/buttons.print.js"></script>
        '; */

        $datatable = '
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/media/js/jquery.dataTables.min.js">
            </script>
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/media/js/dataTables.bootstrap.min.js">
            </script>
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/extensions/Buttons/js/dataTables.buttons.min.js">
            </script>
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/extensions/Buttons/js/buttons.bootstrap.min.js">
            </script>
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/extensions/Buttons/js/jszip.min.js">
            </script>
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/extensions/Buttons/js/pdfmake.min.js">
            </script>
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/extensions/Buttons/js/vfs_fonts.js">
            </script>
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/extensions/Buttons/js/buttons.html5.min.js">
            </script>
            <script type="text/javascript" language="javascript" 
                src="/js/plugins/dataTables_new/extensions/Buttons/js/buttons.print.min.js">
            </script>
        ';

        return $datatable . '
            <script type="text/javascript" language="javascript" src="/js/plugins/chosen/chosen.jquery.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-datepicker/bootstrap-datepicker.tr.min.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/moment/moment.min.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/moment/tr.js"></script>
            <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
            <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput.min.js"></script>
	        <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput_locale_tr.js"></script>
        ';
    }

    public function ready(){
        $return_value = "";
        $columns = '';
        $class_name = "";

        foreach ($this->columns as $key=>$one_column){
            $columns .= '{"data":"'.$key.'"';
            if(isset($one_column["orderable"]) && $one_column["orderable"]==false)
                $columns .= ', "orderable": false';
            if(isset($one_column["visible"]) && $one_column["visible"]==false)
                $columns .= ', "visible": false';
            if(isset($one_column["nowrap"]) && $one_column["nowrap"]==true)
                $class_name .= " no_wrap"; //$columns .= ', "className": "no_wrap"';
            if(isset($one_column["text_right"]) && $one_column["text_right"]==true)
                $class_name .= " text-right";

            $columns .= ', "className": "'.$class_name.'"';
            $columns .= '},';
        }
        $columns = trim($columns,',');

        $table_width = "$('table#".$this->prefix."_table').css('width', '100%');";

        $return_value  .= $this->prefix.'_dt = ' .
            '$(\'table#'. $this->prefix.'_table\').DataTable({
                "processing": true,
                "serverSide": true,
                "iDisplayLength":30,
                "lengthMenu": [[30, 50, 100, 300], [30, 50, 100, 300]],
                "ajax": {
                    "url":"/'.$this->url.'",
                    "data":function ( d ) {
                        d.filter_obj = JSON.stringify('. $this->prefix.'_filter_obj);
                    }
                },
                "language": {
                    "url": "/js/plugins/dataTables/'.App::getLocale().'.json"
                },
                "columns": [
                    '.$columns.'
                ],
                "order": ['.$this->default_order.'],
                "pagingType": "full_numbers",
                dom: \'<"top"flB>rt<"bottom"ip><"clear">\',
                buttons: [
                    {
                        extend: \'pdfHtml5\',
                        orientation: \'landscape\',
                        pageSize: \'LEGAL\',
                        exportOptions: {
                            columns: \':visible\'
                        },
                        customize: function (doc) {
                            doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join(\'*\').split(\'\');
                        },
                        extension: \'.pdf\'
                    },
                    "excel"
                ]
            });
        
            $("#'. $this->prefix.'_table").on("preXhr.dt", function ( e, settings, data ) {
                $("#div_'. $this->prefix.'_table").hide();
                $("#'. $this->prefix.'_loading_div").show();
            });
        ';

        if($this->init_fnct != false){
            $return_value .='
                $("#'. $this->prefix.'_table").on("init.dt", function ( e, settings, data ) {
                   '.$this->init_fnct.'
                });   
            ';
        }

        $return_value .= '
            $("#'. $this->prefix .'_table").on("click","td a.detail_button",function(){
                var tr = $(this).closest("tr");
                var row = '. $this->prefix .'_dt.row( tr );
    
                if(row.child.isShown()){
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass("shown");
                }
                else{
                    // Open this row
                    row.child( row.data().detail ).show();
                    row.child().addClass("detail-backgorund");
                    tr.addClass("shown");
                }
            });
        ';

        $return_value .=
            $this->prefix . '_search_timer = 0;
                $("#'. $this->prefix.'_table").on( "draw.dt", function () {
                $("#'. $this->prefix.'_table_filter input")
                .unbind() // Unbind previous default bindings
                .bind("input", function(e) {
                    if('. $this->prefix.'_search_timer){
                        clearTimeout('. $this->prefix.'_search_timer);
                    }
                
                    if(this.value.length >= 3 || e.keyCode == 13) {
                        the_value = this.value;
                        '. $this->prefix.'_search_timer = setTimeout(function(){
                            '. $this->prefix.'_dt.search(the_value).draw();
                        }, 600);
                    }
            
                    if(this.value == "") {
                        clearTimeout('. $this->prefix.'_search_timer);
                        '. $this->prefix.'_dt.search("").draw();
                    }
                
                    return;
                });
        
                //perform some style adjustment
                $("#'. $this->prefix.'_table_filter").find("input").attr("placeholder","'.trans("global.quick_search").'");
                $("#'. $this->prefix.'_table_filter").find("input").appendTo("#div_'. $this->prefix.'_search_custom");
                $("#'. $this->prefix.'_table_length").find("select").prependTo("#div_'. $this->prefix.'_length_custom");
                $("#'. $this->prefix.'_table_filter").remove();
                $("#'. $this->prefix.'_table_length").remove();
        
                //$("#div_'. $this->prefix.'_table").find(".dt-buttons").appendTo("#'. $this->prefix.'_report_buttons");
                pdf_button = $("#div_'. $this->prefix.'_table").find(".buttons-pdf");
                pdf_button.removeClass("dt-button");
                pdf_button.appendTo("#'.$this->prefix.'_pdf_div");
                pdf_button.addClass("btn btn-sm btn-white full-width");
                pdf_button.prepend(\'<i class="fa fa-file-pdf-o"></i> \');
                
                excel_button = $("#div_'. $this->prefix.'_table").find(".buttons-excel");
                excel_button.removeClass("dt-button");
                excel_button.appendTo("#'.$this->prefix.'_excel_div");
                excel_button.addClass("btn btn-sm btn-white full-width");
                excel_button.prepend(\'<i class="fa fa-file-excel-o"></i> \');
                
                $("#div_'. $this->prefix.'_table").find(".dt-buttons").remove();
                
                $("#'. $this->prefix.'_table_info,#'. $this->prefix.'_table_paginate").addClass("col-lg-6 col-md-6 col-xs-12 form-group");
                $("#'. $this->prefix.'_table_paginate").css("text-align","right");
            
                $("#'. $this->prefix.'_loading_div").hide();
                $("#div_'. $this->prefix.'_table").show();
            });
        
            $("#div_'. $this->prefix.'_date_filter .input-daterange").datepicker({
                format:"dd/mm/yyyy",
                endDate: "0d",
                todayBtn: "linked",
                language: "'.App::getLocale().'",
                autoclose: true,
                todayHighlight: true
            }).on("changeDate",function(e){
                '. $this->prefix.'_filter_obj.start_date = $("#'.$this->prefix.'_start_date").val();
                '. $this->prefix.'_filter_obj.end_date = $("#'.$this->prefix.'_end_date").val();
            });
            
            ' . $table_width;

        return $return_value;
    }

    public function html()
    {
        $column_names = '';
        foreach ($this->columns as $key => $one_column) {
            $tmp_name = trans($this->lang_page . "." . $key);

            if (isset($one_column["name"]) && $one_column["name"] != false) {
                $tmp_name = trans($this->lang_page . "." . $one_column["name"]);
            } else if (isset($one_column["name"]) && $one_column["name"] === false) {
                $tmp_name = "";
            }

            $tooltip = "";
            if(isset($one_column["tooltip"]))
                $tooltip = 'data-toggle="tooltip" data-placement="bottom" title="" data-original-title="'.$one_column["tooltip"].'"';
            $column_names .= '<th '.$tooltip.' >' . $tmp_name . '</th>';
        }

        $return_value = '<div class="row">
                    <div class="col-lg-3 col-md-6 col-xs-12 form-group" id="div_' . $this->prefix . '_search_custom"></div>
                    <div class="col-lg-3 col-md-6 col-xs-12" style="text-align: left;">
                        <div class="form-group" id="div_' . $this->prefix . '_date_filter">
                            <div class="input-daterange input-group">
                                <input id="' . $this->prefix . '_start_date" type="text" class="input-sm form-control" name="start" value="' . $this->start_date . '">
                                <span class="input-group-addon">-</span>
                                <input id="' . $this->prefix . '_end_date" type="text" class="input-sm form-control" name="end" value="' .$this->end_date . '">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-1 col-md-2 col-xs-6 form-group" id="div_' . $this->prefix . '_length_custom" style="padding-right: 5px;"></div>
                    
                     <div class="col-lg-2 col-md-4 col-xs-6 form-group">
                        <button style="" onclick="' . $this->prefix . '_dt.ajax.reload()" type="button" class="btn btn-sm btn-white full-width">
                            <i class="fa fa-refresh"></i> ' . trans("global.refresh") . '
                        </button>
                    </div>
                    
                    <div class="col-lg-2 col-xs-12 col-md-6 form-group" id="' . $this->prefix . '_report_buttons">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-xs-6" id="' . $this->prefix . '_pdf_div"></div>
                            <div class="col-lg-6 col-md-6 col-xs-6" id="' . $this->prefix . '_excel_div"></div>
                        </div>
                    </div>
                    
                    <div class="col-lg-1 col-md-12 col-xs-12 ">';

            if ($this->add_right != false && Helper::has_right(Auth::user()->operations,  $this->add_right)) {
                $return_value .='<button id="'.$this->prefix.'_add_new_button" type = "button" class="btn btn-sm btn-primary full-width" onclick ="show_add_new_form();" ><i class="fa fa-plus" ></i > '.trans("global.add").'</button >';
            }

         $return_value .='</div></div><br>                
                <div id="'. $this->prefix.'_loading_div" class="loader"> '.trans("global.loading").'</div>
                
                <div class="project-list table-responsive" id="div_'. $this->prefix.'_table" style="display:none;">
                    <table class="table table-hover display" id="'. $this->prefix.'_table">
                        <thead>
                            <tr>
                                '.$column_names.'
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>';


        $return_value .='<script>
                var '. $this->prefix . '_filter_obj = {
                    start_date:"'.$this->start_date.'",
                    end_date:"'.$this->end_date.'"
                };
        
                var '. $this->prefix.'_dt = "";
            </script>';

        return  $return_value;
    }
}