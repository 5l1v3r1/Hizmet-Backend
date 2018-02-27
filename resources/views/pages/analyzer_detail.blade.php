@extends('layouts.master')

@section('title')
    {{ trans('devices.analyzer_detail_title') }}
@endsection

@section('page_level_css')
    {!! $EnergyDataTableObj->css() !!}
@endsection

@section('content')
    <?php
        $the_analyzer = json_decode($the_device);
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_analyzer_summary" style="margin-bottom:20px;">
            <div class="col-md-6">
                <div class="profile-image">
                    <img src="/img/avatar/client/{{$the_analyzer->client_avatar}}" class="img-circle circle-border m-b-md" alt="profile">
                </div>
                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_analyzer->device_no }}
                        </h2>
                        <p style="margin: 5px 0 0;">
                            {{ trans('devices.trademark_model') }}:  <strong> {{ $the_analyzer->trademark." / ". $the_analyzer->model." (".trans("global.".$the_analyzer->connection_type).")" }} </strong>
                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('devices.modem_no') }}:  <a href="/modem_management/detail/{{$the_analyzer->modem_id}}" target="_blank"><strong> {{ $the_analyzer->modem_serial }} </strong></a>
                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('devices.client_distributor') }}:
                            <a title="{{ trans('devices.go_client_detail') }}" href="/client_management/detail/{{$the_analyzer->client_id}}" target="_blank">
                                <strong> {{ $the_analyzer->client_name . " / " }} </strong>
                            </a>
                            @if ( $the_analyzer->distributor_id != 0 )
                                <a title="{{ trans('devices.go_distributor_detail') }}" href="/distributor_management/detail/{{$the_analyzer->distributor_id}}" target="_blank">
                                    <strong> {{ $the_analyzer->distributor }} </strong>
                                </a>
                            @else
                                <strong> {{ $the_analyzer->distributor }} </strong>
                            @endif
                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('devices.address') }}:  <strong> {{ $the_analyzer->location_text }} </strong>
                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('devices.explanation') }}:  <strong>
                                @if( $the_analyzer->explanation != "" && $the_analyzer->explanation != null )
                                    {{ $the_analyzer->explanation }}
                                @else
                                    {{ trans('devices.no_explanation') }}
                                @endif    </strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <table class="table table-responsive small m-b-xs">
                    <tbody>
                        <tr>
                            <td>
                                <strong>{{ trans('devices.last_data') }}</strong>
                            </td>
                            <td>
                                @if ($the_analyzer->last_data_at == NULL)
                                    {{ trans('devices.no_first_connection') }}
                                @else
                                    {{ date('d/m/Y H:i:s',strtotime($the_analyzer->last_data_at)) }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('devices.last_connection') }}</strong>
                            </td>
                            <td>
                                @if ($the_analyzer->last_connection_at == NULL)
                                    {{ trans('devices.no_first_connection') }}
                                @else
                                    {{ date('d/m/Y H:i:s',strtotime($the_analyzer->last_connection_at)) }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('devices.created_by') }}</strong>
                            </td>
                            <td>
                                {{ $the_analyzer->created_by }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('devices.created_at') }}</strong>
                            </td>
                            <td>
                                {{ date('d/m/Y H:i:s',strtotime($the_analyzer->created_at)) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('devices.expired_date') }}</strong>
                            </td>
                            <td>
                                @if ($the_analyzer->expired_date == NULL)
                                    31/12/2017 23:59:59
                                @else
                                    {{ date('d/m/Y H:i:s',strtotime($the_analyzer->expired_date)) }}
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- #div_analyzer_summary -->

        <div class="row" id="div_analyzer_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="analyzer_detail_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-lightbulb-o fa-lg" aria-hidden="true"></i>
                                {{ trans('devices.energy') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-bolt fa-lg" aria-hidden="true"></i>
                                {{ trans('devices.current_voltage') }}
                            </a>
                        </li>
                        <!--
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-th-list fa-lg" aria-hidden="true"></i>
                                \{\{ trans('devices.load_profile') }}
                            </a>
                        </li> -->
                        <li class="" tab="#tab-4">
                            <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                <i class="fa fa-heartbeat fa-lg" aria-hidden="true"></i>
                                {{ trans('devices.control') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-5">
                            <a data-toggle="tab" href="#tab-5" aria-expanded="false">
                                <i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i>
                                {{ trans('devices.invoice') }}
                            </a>
                        </li>
                        <!--
                        <li class="" tab="#tab-6">
                            <a data-toggle="tab" href="#tab-6" aria-expanded="false">
                                <i class="fa fa-file-word-o fa-lg" aria-hidden="true"></i>
                                \{\{ trans('devices.report') }}
                            </a>
                        </li> -->
                        <li class="" tab="#tab-7">
                            <a data-toggle="tab" href="#tab-7" aria-expanded="false">
                                <i class="fa fa-area-chart fa-lg" aria-hidden="true"></i>
                                {{ trans('devices.graphs') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-8">
                            <a data-toggle="tab" href="#tab-8" aria-expanded="false">
                                <i class="fa fa-bell-o fa-lg" aria-hidden="true"></i>
                                {{ trans('devices.alarms') }}
                            </a>
                        </li>
                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-primary btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.first_data_at") }}: @if ($the_analyzer->first_data_at == NULL)
                                                    -
                                                @else
                                                    {{ date('d/m/Y H:i:s',strtotime($the_analyzer->first_data_at)) }}
                                                @endif</button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-info btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.last_data_at") }}: @if ($the_analyzer->last_data_at == NULL)
                                                    -
                                                @else
                                                    {{ date('d/m/Y H:i:s',strtotime($the_analyzer->last_data_at)) }}
                                                @endif</button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-warning btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.multiplier") }}: {{  $the_analyzer->multiplier }}</button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-danger btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.device_time") }}: N/A </button>
                                        </div>
                                    </div>
                                </div> <!-- .row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-primary btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.d_period") }}: {{ $the_analyzer->data_period }} dk </button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-info btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.invoice_day") }}: {{ $the_analyzer->invoice_day }}</button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-warning btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{  $the_analyzer->fee_scale_name }}</button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-danger btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{  trans("devices.".$the_analyzer->fee_scale_type) }} </button>
                                        </div>
                                    </div>
                                </div> <!-- .row -->
                                <br/>

                                <div class="row" id="div_energy_dataTable">
                                    <div class="col-lg-12">
                                        {!! $EnergyDataTableObj->html() !!}
                                    </div>
                                </div> <!-- #div_index_dataTable -->
                            </div>
                        </div> <!-- .tab-1 -->

                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-primary btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.multiplier") }}: {{  $the_analyzer->multiplier }}</button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-info btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.current_transformer_ratio") }}: {{$the_analyzer->current_transformer_ratio }}
                                            </button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-warning btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.voltage_transformer_ratio") }}: {{$the_analyzer->voltage_transformer_ratio }}
                                            </button>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <button type="button" class="btn btn-danger btn-block m-r-sm" style="cursor: default;margin-bottom: 10px;"> {{ trans("devices.d_period") }}: {{ $the_analyzer->data_period }} dk</button>
                                        </div>
                                    </div>
                                </div> <!-- .row -->

                                <br/>

                                <div class="row" id="div_curvol_dataTable">
                                    <div class="col-lg-12">
                                        {!! $CurVolDataTableObj->html() !!}
                                    </div>
                                </div> <!-- #div_index_dataTable -->
                            </div>
                        </div> <!-- .tab-2 -->

                        <!--
                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body">
                                Yük Profili
                            </div>
                        </div> <!-- .tab-3 -->

                        <div id="tab-4" class="tab-pane">
                            <div class="panel-body">
                                Geliştirilmesine devam ediliyor...
                            </div>
                        </div> <!-- .tab-4 -->

                        <div id="tab-5" class="tab-pane">
                            <div class="panel-body">
                                Geliştirilmesine devam ediliyor...
                            </div>
                        </div> <!-- .tab-5 -->

                        <!--
                        <div id="tab-6" class="tab-pane">
                            <div class="panel-body">
                                Daha sonra düzenlenecek
                            </div>
                        </div> <!-- .tab-6 -->

                        <div id="tab-7" class="tab-pane">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <select name="mdgraph_graphType" id="mdgraph_graphType" style="width:100%;">
                                            <option value=""></option>
                                            <option value="active_reactive_consumption"> {{ trans('devices.active_reactive_consumption_graph') }} </option>
                                            <option value="active_reactive_power"> {{ trans('devices.active_reactive_power_graph') }} </option>
                                            <option value="reactive_rates"> {{ trans('devices.reactive_rates_graph') }} </option>
                                            <option value="current"> {{ trans('devices.current_graph') }} </option>
                                            <option value="voltage"> {{ trans('devices.voltage_graph') }} </option>
                                            <option value="cosfi"> {{ trans('devices.cosfi_graph') }} </option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <select name="mdgraph_showType" id="mdgraph_showType" style="width:60%;">
                                            <option value="periodic"> {{ trans('devices.periodic') }} </option>
                                            <option value="daily"> {{ trans('devices.daily') }} </option>
                                            <option value="monthly"> {{ trans('devices.monthly') }} </option>
                                            <option value="yearly"> {{ trans('devices.yearly') }} </option>
                                        </select>
                                        <span style="width:38%;float:right;">
                                            <select name="mdgraph_dataType" id="mdgraph_dataType" style="width:100%;">
                                                <option value="kw"> kW-h </option>
                                                <option value="price"> {{ trans('devices.price') }} </option>
                                                <option value="co2"> CO<sub>2</sub> (kg) </option>
                                            </select>
                                        </span>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <div class="input-daterange input-group" id="mdgraph_datepicker">
                                            <input type="text" class="input-sm form-control" name="mdgraph_start_date" id="mdgraph_start_date" value="{{ date('d/m/Y') }}" />
                                            <span class="input-group-addon">-</span>
                                            <input type="text" class="input-sm form-control" name="mdgraph_end_date" id="mdgraph_end_date" value="{{ date('d/m/Y') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-sm btn-success btn-block" id="mdgraph_draw_button" name="mdgraph_draw_button">
                                            {{ trans('devices.draw_graph') }}
                                        </button>
                                    </div>
                                </div>

                                <br /><br />
                                <div class="row">

                                    <div class="loader" id="graph_loading_div"> </div>

                                    <div class="col-lg-12" id="div_prepared_graph" style="display:none;">

                                    </div>
                                </div>
                                <br /><br />
                            </div>
                        </div> <!-- .tab-7 -->

                        <div id="tab-8" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!! $AlertsDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-8 -->

                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_analyzer_tabs -->
    </div>
@endsection

@section('page_level_js')
    {!! $EnergyDataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/highcharts/highcharts.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/highcharts/modules/exporting.js"></script>

    <script>
        the_chart = null;
        @if (Helper::has_right(Auth::user()->operations, "change_alert_status"))

            function delete_alert(id){
            confirmBox('','{{ trans('alerts.delete_alert_warning') }}','warning',function(){
                $.ajax({
                    method:"POST",
                    url:"/alerts/delete",
                    data:"id="+id+"&type=one_alert",
                    success:function(return_text){
                        if(return_text == "SUCCESS"){
                            ddal_dt.ajax.reload();
                        }
                        else{
                            alertBox('','{{ trans("global.unexpected_error") }}', 'warning');
                        }
                    }
                });

            },true);
        }
        @endif
    </script>
@endsection

@section('page_document_ready')
    $.fn.select2.defaults.set( "theme", "default" );

    $("#mdgraph_graphType").select2({
        minimumResultsForSearch: Infinity,
        placeholder: "{{ trans('devices.select_graph') }}"
    }).change(function(){
        the_val = $(this).val();

        $("#mdgraph_showType").removeAttr('disabled');
        $("#mdgraph_dataType").removeAttr('disabled');

        if(the_val == "active_reactive_consumption"){

        }
        else if(the_val == "active_reactive_power"){
            $("#mdgraph_dataType").val("kw");
            $("#mdgraph_dataType").trigger('change');
            $("#mdgraph_dataType").attr('disabled', '');

            $("#mdgraph_showType").val("periodic");
            $("#mdgraph_showType").trigger('change');
            $("#mdgraph_showType").attr('disabled', '');

            $("#mdgraph_start_date").datepicker("setDate", "{{ $periodic_start_date }}");

        }
        else if(the_val == "reactive_rates"){

            $("#mdgraph_dataType").val("kw");
            $("#mdgraph_dataType").trigger('change');
            $("#mdgraph_dataType").attr('disabled', '');
        }
        else if(the_val == "current" || the_val == "voltage" || the_val == "cosfi"){
            $("#mdgraph_dataType").val("kw");
            $("#mdgraph_dataType").trigger('change');
            $("#mdgraph_dataType").attr('disabled', '');

            $("#mdgraph_showType").val("periodic");
            $("#mdgraph_showType").trigger('change');
            $("#mdgraph_showType").attr('disabled', '');

            $("#mdgraph_start_date").datepicker("setDate", "{{ $periodic_start_date }}");
        }
        else{
            $("#mdgraph_dataType").val("kw");
            $("#mdgraph_dataType").trigger('change');
            $("#mdgraph_dataType").attr('disabled', '');
        }
    });

    $("#mdgraph_showType").select2({
        minimumResultsForSearch: Infinity
    }).change(function(){
        the_val = $(this).val();

        if(the_val == "daily"){
            $("#mdgraph_start_date").datepicker("setDate", "{{ $periodic_start_date }}");
            $("#mdgraph_end_date").datepicker("setDate", "{{ $periodic_today }}");
        }
        else if( the_val == "monthly" ){
            $("#mdgraph_start_date").datepicker("setDate", "01/01/"+(new Date().getFullYear()));
            $("#mdgraph_end_date").datepicker("setDate", "{{ $periodic_today }}");
        }
        else if( the_val == "yearly" ){
            $("#mdgraph_start_date").datepicker("setDate", "01/01/"+((new Date().getFullYear())-5));
            $("#mdgraph_end_date").datepicker("setDate", "{{ $periodic_today }}");
        }
        else{
            $("#mdgraph_start_date").datepicker("setDate", "{{ $periodic_today }}");
            $("#mdgraph_end_date").datepicker("setDate", "{{ $periodic_today }}");
        }
    });

    $("#mdgraph_dataType").select2({
        minimumResultsForSearch: Infinity
    });

    $('#mdgraph_datepicker').datepicker({
        format:"dd/mm/yyyy",
        endDate: "today",
        todayBtn: "linked",
        language: "{{ App::getLocale() }}",
        autoclose: true,
        todayHighlight: true
    });

    $('#mdgraph_draw_button').click(function(){
        if( $("#mdgraph_graphType").val() == "" ){
            return alertBox('','{{ trans('devices.select_graphType_warning') }}','warning');
        }

        graph_type = $("#mdgraph_graphType").val();
        show_type = $("#mdgraph_showType").val();
        data_type = $("#mdgraph_dataType").val();
        start_date = $("#mdgraph_start_date").val();
        end_date = $("#mdgraph_end_date").val();

        $('#div_prepared_graph').hide();
        $('#graph_loading_div').show();

        $.ajax({
            method:"POST",
            url:"/device_management/get_graph_data/{{ $the_analyzer->device_id }}",
            data:"graph_type="+graph_type+"&show_type="+show_type+"&data_type="+data_type+"&start_date="+start_date+"&end_date="+end_date,
            async:false,
            success:function(return_text){
                if(return_text == "NEXIST"){
                    $('#div_prepared_graph').html(' \
                        <div class="alert alert-warning"> \
                            <i class="fa fa-exclamation-triangle fa-lg"></i> {{ trans('devices.graph_nexist_data') }} \
                        </div>'
                    );
                }
                else if( return_text == "ERROR" ){
                    $('#div_prepared_graph').html(' \
                        <div class="alert alert-danger"> \
                            <i class="fa fa-times-circle fa-lg"></i> {{ trans('devices.graph_unexpected_error') }} \
                        </div>'
                    );
                }
                else{
                    return_text = $.parseJSON(return_text);

                    if( return_text["show_type"] == "daily" ){
                        return_text["tooltip"] = {
                           formatter: function () {
                                return '<b>'+this.series.name+':</b> '+Highcharts.numberFormat(this.point.y,3,",",".")+' '+this.point.unit+'<br>'+Highcharts.dateFormat('%d %B %Y %A', this.point.x);
                            }
                        };
                    }
                    else if( return_text["show_type"] == "monthly" ){
                        return_text["tooltip"] = {
                           formatter: function () {
                                return '<b>'+this.series.name+':</b> '+Highcharts.numberFormat(this.point.y,3,",",".")+' '+this.point.unit+'<br>'+Highcharts.dateFormat('%B %Y', this.point.x);
                            }
                        };
                    }
                    else if( return_text["show_type"] == "yearly" ){
                       return_text["tooltip"] = {
                           formatter: function () {
                                return '<b>'+this.series.name+':</b> '+Highcharts.numberFormat(this.point.y,3,",",".")+' '+this.point.unit+'<br>'+Highcharts.dateFormat('%Y', this.point.x);
                            }
                        };
                    }
                    else{ // periodic
                        return_text["tooltip"] = {
                           formatter: function () {
                                return '<b>'+this.series.name+':</b> '+Highcharts.numberFormat(this.point.y,3,",",".")+' '+this.point.unit+'<br>'+Highcharts.dateFormat('%d %B %Y %H:%M:%S', this.point.x);
                            }
                        };
                    }

                    the_chart = Highcharts.chart('div_prepared_graph', return_text);

                    $('#graph_loading_div').hide();
                    $('#div_prepared_graph').show();
                    the_chart.reflow();
                }
            }
        });

    });

    // Keep the current tab active after page reload
    rememberTabSelection('#analyzer_detail_tabs', !localStorage);

    if (document.location.hash && document.location.hash == '#alarms') {
        $("#analyzer_detail_tabs a[href='#tab-8']").trigger('click');
    }
    else if(document.location.hash){
        $("#analyzer_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
    }

    var tab_1 = false,
        tab_2 = false,
        tab_3 = false,
        tab_4 = false,
        tab_5 = false,
        tab_6 = false,
        tab_7 = false,
        tab_8 = false;

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            {!! $EnergyDataTableObj->ready() !!}
            tab_1 = true;
        }
        else if(selectedTab == "#tab-2" && tab_2 == false){
            {!! $CurVolDataTableObj->ready() !!}
            tab_2 = true;
        }
        else if(selectedTab == "#tab-3" && tab_3 == false){

            tab_3 = true;
        }
        else if(selectedTab == "#tab-4" && tab_4 == false){

            tab_4 = true;
        }
        else if(selectedTab == "#tab-5" && tab_5 == false){

            tab_5 = true;
        }
        else if(selectedTab == "#tab-6" && tab_6 == false){

            tab_6 = true;
        }
        else if(selectedTab == "#tab-7" && tab_7 == false){
            tab_7 = true;

            $("#mdgraph_graphType").val('active_reactive_consumption');
            $('#mdgraph_graphType').trigger('change');

            $("#mdgraph_showType").val("periodic");
            $('#mdgraph_showType').trigger('change');

            $("#mdgraph_dataType").val("kw");
            $('#mdgraph_dataType').trigger('change');

            $("#mdgraph_start_date").datepicker("setDate", "{{ $periodic_today }}");
            $("#mdgraph_end_date").datepicker("setDate", "{{ $periodic_today }}");

            var highchartsOptions = Highcharts.setOptions({
                lang: {
                    loading: 'Yükleniyor...',
                    months: [
                        '{{ trans('devices.january') }}',
                        '{{ trans('devices.february') }}',
                        '{{ trans('devices.march') }}',
                        '{{ trans('devices.april') }}',
                        '{{ trans('devices.may_l') }}',
                        '{{ trans('devices.june') }}',
                        '{{ trans('devices.july') }}',
                        '{{ trans('devices.august') }}',
                        '{{ trans('devices.september') }}',
                        '{{ trans('devices.october') }}',
                        '{{ trans('devices.november') }}',
                        '{{ trans('devices.december') }}'
                    ],
                    weekdays: [
                        '{{ trans('devices.sunday') }}',
                        '{{ trans('devices.monday') }}',
                        '{{ trans('devices.tuesday') }}',
                        '{{ trans('devices.wednesday') }}',
                        '{{ trans('devices.thirsday') }}',
                        '{{ trans('devices.friday') }}',
                        '{{ trans('devices.saturday') }}'
                    ],
                    shortMonths: [
                        '{{ trans('devices.jan') }}',
                        '{{ trans('devices.feb') }}',
                        '{{ trans('devices.mar') }}',
                        '{{ trans('devices.apr') }}',
                        '{{ trans('devices.may') }}',
                        '{{ trans('devices.jun') }}',
                        '{{ trans('devices.jul') }}',
                        '{{ trans('devices.aug') }}',
                        '{{ trans('devices.sep') }}',
                        '{{ trans('devices.oct') }}',
                        '{{ trans('devices.nov') }}',
                        '{{ trans('devices.dec') }}'
                    ],
                    shortWeekdays: [
                        '{{ trans('devices.sun') }}',
                        '{{ trans('devices.mon') }}',
                        '{{ trans('devices.tue') }}',
                        '{{ trans('devices.wed') }}',
                        '{{ trans('devices.thu') }}',
                        '{{ trans('devices.fri') }}',
                        '{{ trans('devices.sat') }}'
                    ],
                    exportButtonTitle: "{{ trans('devices.export_title') }}",
                    printChart: "{{ trans('devices.print') }}",
                    printButtonTitle: "{{ trans('devices.print') }}",
                    rangeSelectorFrom: "{{ trans('devices.range_start') }}",
                    rangeSelectorTo: "{{ trans('devices.range_end') }}",
                    rangeSelectorZoom: "{{ trans('devices.period') }}",
                    downloadPNG: 'PNG',
                    downloadJPEG: 'JPEG',
                    downloadPDF: 'PDF',
                    downloadSVG: 'SVG',
                    resetZoom: "{{ trans('devices.reset_zoom') }}",
                    resetZoomTitle: "Zoom Out",
                    //thousandsSep: ".",
                    //decimalPoint: ','
                }
            });


            //fire click event for create graph for the first time/default
            $("#mdgraph_draw_button").click();
        }
        else if(selectedTab == "#tab-8" && tab_8 == false){
            {!! $AlertsDataTableObj->ready() !!}
            tab_8 = true;
        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#analyzer_detail_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);

        // clear hash and parameter values from URL
        history.pushState('', document.title, window.location.pathname);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#analyzer_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#analyzer_detail_tabs a:first").trigger('click');
@endsection