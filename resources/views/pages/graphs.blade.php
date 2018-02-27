@extends('layouts.master')

@section('title')
    {{ trans('graphs.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/plugins/chosen/chosen.css">
    <link rel="stylesheet" type="text/css" href="/css/plugins/awesome-checkbox/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-datepicker/bootstrap-datepicker3.min.css">
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/select2-bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/jsTree/themes/default/style.min.css" />
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">

            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="graph_tabs">

                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-cogs fa-lg" aria-hidden="true"></i>
                                {{ trans('graphs.devices_graphs') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-podcast fa-lg" aria-hidden="true"></i>
                                {{ trans('graphs.modem_graphs') }}
                            </a>
                        </li>

                        @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || Auth::user()->user_type
                        == 3)
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-handshake-o fa-lg" aria-hidden="true"></i>
                                {{ trans('graphs.client_graphs') }}
                            </a>
                        </li>
                        @endif

                        @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                        <li class="" tab="#tab-4">
                            <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                <i class="fa fa-sitemap fa-lg" aria-hidden="true"></i>
                                {{ trans('graphs.distributor_graphs') }}
                            </a>
                        </li>
                            @endif

                    </ul>

                    <div class="tab-content">

                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">

                                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 form-group">
                                                <div class="input-daterange input-group"
                                                     id="device_grap_datepicker">
                                                    <input type="text" class="datepicker_input_start input-sm form-control" name="device_graph_start_date" id="device_graph_start_date" value="{{ date('d/m/Y') }}" />
                                                    <span class="input-group-addon">-</span>
                                                    <input type="text" class="datepicker_input_end input-sm
                                                    form-control" name="device_graph_end_date" id="device_graph_end_date" value="{{ date('d/m/Y') }}" />
                                                </div>
                                            </div>

                                            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                                                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 form-group">
                                                    <select name="device_distributors" id="device_distributors" class="form-control" style="width:100%;" >
                                                        <option value="-1">{{ trans("graphs.all_distributors")
                                                        }}</option>
                                                        <option value="0">{{ trans('global.main_distributor')
                                                        }}</option>
                                                    </select>
                                                </div>
                                            @endif
                                            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || Auth::user()->user_type == 3)
                                                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 form-group" id="device_org_schema_div">
                                                    <div id="device_org_schema"></div>
                                                </div>
                                            @endif

                                            <div class="col-lg-2 col-md-6 col-xs-12 form-group">
                                                <button style="" onclick="drawTabGraphs('device')" type="button" class="btn btn-sm btn-white full-width">
                                                    <i class="fa fa-refresh"></i> {{ trans("graphs.create_graph") }}
                                                </button>
                                            </div>
                                        </div>

                                            <br /><br />
                                            <div class="row">

                                                <div class="col-lg-6 col-md-12 col-sm-12" id="device_most_consumption">
                                                    En çok Tüketim yapanlar
                                                </div>

                                                <div class="col-lg-6 col-md-12 col-sm-12" id="device_most_reactive">
                                                    En çok reaktif yapanlar
                                                </div>

                                                <div class="col-lg-6 col-md-12 col-sm-12"
                                                     id="device_most_alarm_reactive">
                                                    En çok reaktif alarm
                                                </div>
                                                <div class="col-lg-6 col-md-12 col-sm-12"
                                                     id="device_most_alarm_connection">
                                                    En çok bağlantı alarm
                                                </div>
                                                <div class="col-lg-6 col-md-12 col-sm-12"
                                                     id="device_most_alarm_voltage">
                                                    En çok gerilim alarm
                                                </div>
                                                <div class="col-lg-6 col-md-12 col-sm-12"
                                                     id="device_most_alarm_current">
                                                    En çok akım alarm
                                                </div>
                                            </div>
                                            <br /><br />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">

                                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 form-group">
                                                <div class="input-daterange input-group"
                                                     id="modem_grap_datepicker">
                                                    <input type="text" class="datepicker_input_start input-sm form-control" name="modem_graph_start_date" id="modem_graph_start_date" value="{{ date('d/m/Y') }}" />
                                                    <span class="input-group-addon">-</span>
                                                    <input type="text" class="datepicker_input_end input-sm
                                                    form-control" name="modem_graph_end_date" id="modem_graph_end_date" value="{{ date('d/m/Y') }}" />
                                                </div>
                                            </div>

                                            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                                                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 form-group">
                                                    <select name="modem_distributors" id="modem_distributors"
                                                            class="form-control" style="width:100%;" >
                                                        <option value="-1">{{ trans("graphs.all_distributors")
                                                        }}</option>
                                                        <option value="0">{{ trans('global.main_distributor')
                                                        }}</option>
                                                    </select>
                                                </div>
                                            @endif
                                            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || Auth::user()->user_type == 3)
                                                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 form-group" id="modem_org_schema_div">
                                                    <div id="modem_org_schema"></div>
                                                </div>
                                            @endif

                                            <div class="col-lg-2 col-md-4 col-xs-12 form-group">
                                                <button style="" onclick="drawTabGraphs('modem')" type="button" class="btn btn-sm btn-white full-width">
                                                    <i class="fa fa-refresh"></i> {{ trans("graphs.create_graph") }}
                                                </button>
                                            </div>
                                        </div>

                                        <br /><br />
                                        <div class="row">


                                            <div class="col-lg-6 col-md-12 col-sm-12" id="modem_most_consumption">
                                                En çok Tüketim yapanlar
                                            </div>

                                            <div class="col-lg-6 col-md-12 col-sm-12" id="modem_most_reactive">
                                                En çok reaktif yapanlar
                                            </div>

                                            <div class="col-lg-6 col-md-12 col-sm-12" id="modem_most_alarm_reactive">
                                                En çok reaktif alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="modem_most_alarm_connection">
                                                En çok bağlantı alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="modem_most_alarm_voltage">
                                                En çok gerilim alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="modem_most_alarm_current">
                                                En çok akım alarm
                                            </div>

                                            <div class="col-lg-6 col-md-12 col-sm-12" id="modem_most_device">
                                                En çok cihazı olanlar
                                            </div>
                                        </div>
                                        <br /><br />
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || Auth::user()->user_type
                        == 3)
                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">

                                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 form-group">
                                                <div class="input-daterange input-group"
                                                     id="client_grap_datepicker">
                                                    <input type="text" class="datepicker_input_start input-sm form-control" name="client_graph_start_date" id="client_graph_start_date" value="{{ date('d/m/Y') }}" />
                                                    <span class="input-group-addon">-</span>
                                                    <input type="text" class="datepicker_input_end input-sm
                                                    form-control" name="client_graph_end_date" id="client_graph_end_date" value="{{ date('d/m/Y') }}" />
                                                </div>
                                            </div>

                                            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                                                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 form-group">
                                                    <select name="client_distributors" id="client_distributors" class="form-control" style="width:100%;" >
                                                        <option value="-1">{{ trans("graphs.all_distributors")
                                                        }}</option>
                                                        <option value="0">{{ trans('global.main_distributor')
                                                        }}</option>
                                                    </select>
                                                </div>
                                            @endif
                                            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || Auth::user()->user_type == 3)
                                                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 form-group" id="client_org_schema_div">
                                                    <div id="client_org_schema"></div>
                                                </div>
                                            @endif

                                            <div class="col-lg-2 col-md-6 col-xs-12 form-group">
                                                <button style="" onclick="drawTabGraphs('client')" type="button" class="btn btn-sm btn-white full-width">
                                                    <i class="fa fa-refresh"></i> {{ trans("graphs.create_graph") }}
                                                </button>
                                            </div>
                                        </div>

                                        <br /><br />
                                        <div class="row">

                                            <div class="col-lg-6 col-md-12 col-sm-12" id="client_most_consumption">
                                                En çok Tüketim yapanlar
                                            </div>

                                            <div class="col-lg-6 col-md-12 col-sm-12" id="client_most_reactive">
                                                En çok reaktif yapanlar
                                            </div>

                                            <div class="col-lg-6 col-md-12 col-sm-12"
                                                 id="client_most_alarm_reactive">
                                                En çok reaktif alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="client_most_alarm_connection">
                                                En çok bağlantı alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="client_most_alarm_voltage">
                                                En çok gerilim alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="client_most_alarm_current">
                                                En çok akım alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="client_most_device">
                                                En çok cihazı olan
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="device_most_modem">
                                                En çok modemi olan
                                            </div>
                                        </div>
                                        <br /><br />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                        <div id="tab-4" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">

                                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 form-group">
                                                <div class="input-daterange input-group"
                                                     id="distributor_grap_datepicker">
                                                    <input type="text" class="datepicker_input_start input-sm form-control" name="distributor_graph_start_date" id="distributor_graph_start_date" value="{{ date('d/m/Y') }}" />
                                                    <span class="input-group-addon">-</span>
                                                    <input type="text" class="datepicker_input_end input-sm
                                                    form-control" name="distributor_graph_end_date" id="distributor_graph_end_date" value="{{ date('d/m/Y') }}" />
                                                </div>
                                            </div>


                                            <div class="col-lg-2 col-md-6 col-xs-12 form-group">
                                                <button style="" onclick="drawTabGraphs('distributor')" type="button" class="btn btn-sm btn-white full-width">
                                                    <i class="fa fa-refresh"></i> {{ trans("graphs.create_graph") }}
                                                </button>
                                            </div>
                                        </div>

                                        <br /><br />
                                        <div class="row">

                                            <div class="col-lg-6 col-md-12 col-sm-12" id="distributor_most_consumption">
                                                En çok Tüketim yapanlar
                                            </div>

                                            <div class="col-lg-6 col-md-12 col-sm-12" id="distributor_most_reactive">
                                                En çok reaktif yapanlar
                                            </div>

                                            <div class="col-lg-6 col-md-12 col-sm-12"
                                                 id="distributor_most_alarm_reactive">
                                                En çok reaktif alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12"
                                                 id="distributor_most_alarm_connection">
                                                En çok bağlantı alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12"
                                                 id="distributor_most_alarm_voltage">
                                                En çok gerilim alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12"
                                                 id="distributor_most_alarm_current">
                                                En çok akım alarm
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="device_most_device">
                                                En çok cihazı olan
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="device_most_modem">
                                                En çok modemi olan
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12" id="device_most_client">
                                                En çok müşterisi olan
                                            </div>
                                        </div>
                                        <br /><br />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/chosen/chosen.jquery.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-datepicker/bootstrap-datepicker.tr.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput_locale_tr.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/jsTree/jstree.min.js"></script>

    <script type="text/javascript" language="javascript" src="/js/plugins/highcharts/highcharts.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/highcharts/modules/exporting.js"></script>

    <script>

        @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
        function prepare_client_options(element,org_element,show_modems){
            $.ajax({
                method:"POST",
                url:"/graphs/get_distributors",
                data:"",
                async:false,
                success:function(return_text){
                    if(return_text == "NEXIST"){
                        alertBox('','{{ trans('devices.nexist_modems') }}','info');
                    }
                    else if( return_text == "ERROR" ){
                        alertBox('','{{ trans('global.unexpected_error') }}','error');
                    }
                    else{

                        $("#"+element).select2({
                            minimumResultsForSearch: 10,
                            data: JSON.parse(return_text)
                        }).change(function(){

                            load_org_tree($(this).val(),org_element,show_modems)
                        });


                    }
                }
            });
        }
        @endif

        @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || Auth::user()->user_type == 3)
        function load_org_tree(id,org_element,show_modems){


            $('#'+org_element).jstree('destroy');
            $('#'+org_element+'_div').hide();

            if(id=="-1")
                return;

            $('#'+org_element+'_div').show();

            no_modems = "";
            if(show_modems == false)
                no_modems = "&no_modems=no_modems";

            var lang_obj = {
                add: '{{ trans("organization_schema.add_node") }}',
                delete: '{{ trans("organization_schema.delete_node") }}',
                rename: '{{ trans("organization_schema.rename") }}',
                error: '{{ trans("global.unexpected_error") }}',
                node_deleted: '{{ trans("organization_schema.node_deleted") }}',
                loading: '{{ trans('global.loading') }}',
                new_node: '{{ trans('organization_schema.new_element') }}',
                delete_node_warning: '{{ trans("organization_schema.delete_node_warning") }}',
                same_name_warning: '{{ trans('organization_schema.same_name_warning') }}',
                unexpected_error: '{{ trans('global.unexpected_error') }}'
            };

            $.ajax({
                method: "POST",
                url: "/organization_schema/get_organization_schema",
                data: "distributor_id="+id+"&show_clients=true"+no_modems,
                async: false,
                success: function(return_text){
                    if( return_text != "" && return_text != "ERROR" ){
                        // data, distributor_id, div, lang, contextMenu, checkbox ,multiple
                        org_schema = createJsTree(
                            return_text,
                            id,
                            org_element,
                            lang_obj,
                            false,
                            true,
                            true,
                            false,
                            1
                        );
                    }
                    else{
                        alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
                    }
                }
            });
        }
        @endif

        function drawTabGraphs(tab_name){

            if($("#"+tab_name+"_graph_start_date").val() == "" || $("#"+tab_name+"_graph_end_date").val()==""){
                return alertBox('','{{ trans("graphs.date_fields_required") }}','warning');
            }
            data_obj = {

                start_date : $("#"+tab_name+"_graph_start_date").val(),
                end_date : $("#"+tab_name+"_graph_end_date").val(),
                tab_name : tab_name
            }


            if((tab_name == "device" || tab_name == "modem") &&  $("#"+tab_name+"_distributors").val() != "-1"){

                checked_elements = $("#"+tab_name+"_org_schema").jstree('get_checked');
                checked_modems = [];

                $.each(checked_elements, function(index, value){

                    if(value.indexOf("_modem")!== -1){

                        checked_modems.push(value);
                    }

                });


                @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || Auth::user()->user_type == 3)
                    if(checked_modems.length < 1)
                        return alertBox("","{{ trans("graphs.at_least_one_modem_required") }}","warning");

                    data_obj["checked_modems"] = checked_modems;
                @endif
            }

            if(tab_name == "client" &&  $("#"+tab_name+"_distributors").val() != "-1"){

                checked_elements = $("#"+tab_name+"_org_schema").jstree('get_checked');
                checked_clients = [];

                $.each(checked_elements, function(index, value){

                        if(value.indexOf("_client")!== -1){

                            checked_clients.push(value);
                        }

                });

                if(checked_clients.length < 1)
                    return alertBox("","{{ trans("graphs.at_least_one_client_required") }}","warning");

                data_obj["checked_clients"] = checked_clients;

            }

            $('body').prepend("<div id='bg_block_screen'><div class='loader'></div>{{ trans("global.preparing") }}...</div>");
            //get the chart data
            $.ajax({
                method:"POST",
                url:"/graphs/get_graph_data",
                data:"data="+JSON.stringify(data_obj),
                success:function(return_text){
                    return_text = $.parseJSON(return_text);



                    return_text[tab_name+"_most_consumption"]["tooltip"] = {
                        formatter: function () {
                            return '<b>'+this.point.org_info+':</b> <br/>'+'{{ trans("graphs.location")
                            }}: '+this.point.location+'<br/>'+Highcharts.numberFormat
                                (this
                                        .point.y,3,",",
                                    ".")+' ' +
                                ''+this.point.unit+'<br>';
                        }
                    };
                    Highcharts.chart(tab_name+'_most_consumption', return_text[tab_name+"_most_consumption"]);

                    return_text[tab_name+"_most_reactive"]["tooltip"] = {
                        formatter: function () {
                            return '<b>'+this.point.org_info+':</b> <br/>'+'{{ trans("graphs.location")
                            }}: '+this.point.location+'<br/>'+Highcharts.numberFormat
                                (this
                                        .point.y,3,",",
                                    ".")+' ' +
                                ''+this.point.unit+'<br><b>'+this.series.name+'</b>';
                        }
                    };
                    Highcharts.chart(tab_name+'_most_reactive', return_text[tab_name+"_most_reactive"]);

                    /*Highcharts.chart(tab_name+'_most_reactive', return_text[tab_name+"_most_reactive"]);

                    Highcharts.chart(tab_name+'_most_alarm_reactive',
                        return_text[tab_name+"_most_alarm_reactive"]);

                    Highcharts.chart(tab_name+'_most_alarm_connection',
                        return_text[tab_name+"_most_alarm_connection"]);

                    Highcharts.chart(tab_name+'_most_alarm_voltage',
                        return_text[tab_name+"_most_alarm_voltage"]);

                    Highcharts.chart(tab_name+'_most_alarm_current',
                        return_text[tab_name+"_most_alarm_current"]);*/

                    $("#bg_block_screen").remove();
                }
            });


        }
    </script>
@endsection

@section('page_document_ready')


    $(".datepicker_input_start").val("{{ date('01/m/Y') }}");
    $(".datepicker_input_end").val("{{ date('d/m/Y') }}");
    $('#device_grap_datepicker,#modem_grap_datepicker,#distributor_grap_datepicker,#client_grap_datepicker')
    .datepicker({
        format:"dd/mm/yyyy",
        endDate: "today",
        todayBtn: "linked",
        language: "{{ App::getLocale() }}",
        autoclose: true,
        todayHighlight: true
    });


    // Keep the current tab active after page reload
    rememberTabSelection('#graph_tabs', !localStorage);


    var tab_1 = false,
        tab_2 = false,
        tab_3 = false,
        tab_4 = false;

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            tab_1 = true;

            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)

                    prepare_client_options("device_distributors","device_org_schema",true);
                    $("#device_distributors").val("-1").trigger("change");
            @endif


            @if (Auth::user()->user_type == 3)
                    load_org_tree({{ Auth::user()->org_id }},"device_org_schema");
            @endif

            drawTabGraphs('device');
        }
        else if(selectedTab == "#tab-2" && tab_2 == false){
            tab_2 = true;

            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)

                prepare_client_options("modem_distributors","modem_org_schema",true);
                $("#modem_distributors").val("-1").trigger("change");
            @endif


            @if (Auth::user()->user_type == 3)
                load_org_tree({{ Auth::user()->org_id }},"modem_org_schema");
            @endif
            drawTabGraphs('modem');
        }
        else if(selectedTab == "#tab-3" && tab_3 == false){
            tab_3 = true;

            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)

                prepare_client_options("client_distributors","client_org_schema",false);
                $("#client_distributors").val("-1").trigger("change");
            @endif


            @if (Auth::user()->user_type == 3)
                load_org_tree({{ Auth::user()->org_id }},"client_org_schema",false);
            @endif

            drawTabGraphs('client');

        }else if(selectedTab == "#tab-4" && tab_4 == false){
            tab_4 = true;
            drawTabGraphs('distributor');
        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#graph_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#graph_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#graph_tabs a:first").trigger('click');
@endsection