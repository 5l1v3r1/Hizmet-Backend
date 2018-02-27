@extends('layouts.master')

@section('title')
    {{ trans('system_summary.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/select2-bootstrap.min.css" />
@endsection

@section('content')
    <?php
        $where_for_clients = array(
                array("C.status","<>",0)
        );

        $where_for_modems = array(
                array("M.status","<>",0)
        );

        $where_for_devices = array(
                array("D.status","<>",0)
        );

        if( Auth::user()->user_type == 4 ){
            $where_for_modems[] = array("M.client_id", Auth::user()->org_id);
            $where_for_devices[] = array("M.client_id",Auth::user()->org_id);
        }
        else if( Auth::user()->user_type == 3 ){
            $where_for_modems[] = array("C.distributor_id",Auth::user()->org_id);
            $where_for_devices[] = array("C.distributor_id",Auth::user()->org_id);
            $where_for_clients[] = array("C.distributor_id",Auth::user()->org_id);
        }
        else if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){

        }
        else{
            abort(404);
        }

        $total_client_count = DB::table('clients as C')
                ->where($where_for_clients)
                ->count();

        $total_distributor_count = DB::table('distributors as D')
                ->where('D.status','<>',0)
                ->count();

        $total_modem_count = DB::table('modems as M')
                ->select('M.id as id')
                ->join('clients as C', 'M.client_id', 'C.id')
                ->where($where_for_modems)
                ->count();

        $total_meter_count = 0;
        $total_relay_count = 0;
        $total_analyzer_count = 0;

        $devices = DB::table('devices as D')
                ->select('D.id as device_id', 'DT.type as device_type')
                ->join('device_type as DT', 'DT.id', 'D.device_type_id')
                ->join('modems as M', 'M.id', 'D.modem_id')
                ->join('clients as C', 'M.client_id', 'C.id')
                ->where($where_for_devices)
                ->get();

        foreach ($devices as $one_device){
            switch ($one_device->device_type){
                case "meter":
                    $total_meter_count++; break;
                case "relay":
                    $total_relay_count++; break;
                case "analyzer":
                    $total_analyzer_count++; break;
            }
        }

        $total_credit = 0;
        $total_branch_count = 1;
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            @if( Auth::user()->user_type == 4 )
                <div class="col-lg-2 col-md-4 col-xs-6">
                    <a href="javascript:void(0);" style="text-decoration: none">
                        <div class="widget style1 navy-bg">
                            <div class="row vertical-align">
                                <div class="col-xs-3">
                                    <i class="fa fa-sitemap fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <h2 class="font-bold"> {{ $total_branch_count }} </h2>
                                    <span> {{ trans('system_summary.branch') }} </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            @if( Auth::user()->user_type != 4 )
                <div class="col-lg-2 col-md-4 col-xs-6">
                    <a href="/client_management" style="text-decoration: none">
                        <div class="widget style1 navy-bg">
                            <div class="row vertical-align">
                                <div class="col-xs-3">
                                    <i class="fa fa-handshake-o fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <h2 class="font-bold"> {{ $total_client_count }} </h2>
                                    <span> {{ trans('system_summary.client') }} </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            @if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 )
                <div class="col-lg-2 col-md-4 col-xs-6">
                    <a href="/distributor_management" style="text-decoration: none">
                        <div class="widget style1 navy-bg">
                            <div class="row vertical-align">
                                <div class="col-xs-3">
                                    <i class="fa fa-sitemap fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <h2 class="font-bold"> {{ $total_distributor_count }} </h2>
                                    <span> {{ trans('system_summary.distributor') }} </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/modem_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-podcast fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">

                                <h2 class="font-bold"> {{ $total_modem_count }} </h2>
                                <span> {{ trans('system_summary.modem') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/meter" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-tachometer fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> {{ $total_meter_count }} </h2>
                                <span> {{ trans('system_summary.meter') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/relay" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-sliders fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> {{ $total_relay_count }} </h2>
                                <span> {{ trans('system_summary.relay') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/analyzer" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-desktop fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> {{ $total_analyzer_count }} </h2>
                                <span> {{ trans('system_summary.analyzer') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            @if( Auth::user()->user_type == 3 || Auth::user()->user_type == 4 )
                <div class="col-lg-2 col-md-4 col-xs-6">
                    <a href="javascript:void(0)" style="text-decoration: none">
                        <div class="widget style1 navy-bg">
                            <div class="row vertical-align">
                                <div class="col-xs-3">
                                    <i class="fa fa-money fa-3x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <h2 class="font-bold"> {{ $total_credit }} </h2>
                                    <span> {{ trans('system_summary.credit') }} </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        <div class="row" style="margin-top: 30px;">
            <div class="col-md-6">
                <div class="panel panel-danger" style="min-height: 400px;">
                    <div class="panel-heading">
                        <i class="fa fa-bolt"></i>
                        <span style="margin-right: 5px;"> {{ trans('system_summary.last_reactive_devices') }} </span>

                        <select class="form-horizontal" id="last_reactive_dt" name="last_reactive_dt">
                            <option value="all_devices"> {{ trans('system_summary.all_devices') }} </option>
                            <option value="meter"> {{ trans('system_summary.meters') }} </option>
                            <option value="relay"> {{ trans('system_summary.relays') }} </option>
                            <option value="analyzer"> {{ trans('system_summary.analyzers') }} </option>
                        </select>
                    </div>
                    <div class="panel-body tooltip-demo" data-html="true">
                        <table class="table table-hover no-margins" id="reactives_table">
                            <tbody>
                                <tr>
                                    <td colspan="5" style="vertical-align: middle; text-align:center; color: #cc0000;">
                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        {{ trans('system_summary.no_data_to_show') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-warning" style="min-height: 400px;">
                    <div class="panel-heading">
                        <i class="fa fa-bell-o"></i>
                        <span style="margin-right: 5px;"> {{ trans('system_summary.last_alerts') }} </span>

                        <select class="form-horizontal" id="last_alerts_dt" name="last_alerts_dt">
                            <option value="reactive"> {{ trans('system_summary.reactive') }} </option>
                            <option value="connection"> {{ trans('system_summary.connection') }} </option>
                            <option value="current"> {{ trans('system_summary.current') }} </option>
                            <option value="voltage"> {{ trans('system_summary.voltage') }} </option>
                        </select>
                    </div>
                    <div class="panel-body">
                        <table class="table table-hover no-margins" id="alerts_table">
                            <tbody>
                                <tr>
                                    <td colspan="5" style="vertical-align: middle; text-align:center; color: #cc0000;">
                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        {{ trans('system_summary.no_data_to_show') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 30px;">
            <div class="col-md-6">
                <div class="panel panel-info" style="min-height: 400px;">
                    <div class="panel-heading">
                        <i class="fa fa-cogs"></i>
                        <span style="margin-right: 5px;"> {{ trans('system_summary.last_added') }} </span>

                        <select class="form-horizontal" id="last_devices_dt" name="last_devices_dt">
                            <option value="modem"> {{ trans('system_summary.modems') }} </option>
                            <option value="meter"> {{ trans('system_summary.meters') }} </option>
                            <option value="relay"> {{ trans('system_summary.relays') }} </option>
                            <option value="analyzer"> {{ trans('system_summary.analyzers') }} </option>
                        </select>
                    </div>
                    <div class="panel-body">
                        <table class="table table-hover no-margins" id="devices_table">
                            <tbody>
                                <tr>
                                    <td colspan="5" style="vertical-align: middle; text-align:center; color: #cc0000;">
                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        {{ trans('system_summary.no_data_to_show') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-success" style="min-height: 400px;">
                    <div class="panel-heading">
                        <i class="fa fa-check-square-o"></i>
                        <span style="margin-right: 5px;"> {{ trans('system_summary.last_added') }} </span>

                        <select class="form-horizontal" id="last_ucd_dt" name="last_ucd_dt">
                            <option value="users"> {{ trans('system_summary.users') }} </option>
                            @if( Auth::user()->user_type != 4 )
                                <option value="clients"> {{ trans('system_summary.clients') }} </option>
                            @endif
                        </select>
                    </div>
                    <div class="panel-body">
                        <table class="table table-hover no-margins" id="ucds_table">
                            <tbody>
                                <tr>
                                    <td colspan="5" style="vertical-align: middle; text-align:center; color: #cc0000;">
                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        {{ trans('system_summary.no_data_to_show') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .wrapper -->
@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>

    <script>
        function get_last_reactives(type){
            if( typeof type !== undefined && type != ""){
                $.ajax({
                    method: "POST",
                    url: "/system_summary/get_reactives",
                    data: "type="+type,
                    success:function(return_text){
                        $('#reactives_table').html(return_text);
                    }
                });
            }
        }

        function get_last_alerts(type){
            if( typeof type !== undefined && type != "") {
                $.ajax({
                    method: "POST",
                    url: "/system_summary/get_alerts",
                    data: "type=" + type,
                    success: function (return_text) {
                        $('#alerts_table').html(return_text);
                    }
                });
            }
        }

        function get_last_devices(type){
            if( typeof type !== undefined && type != "") {
                $.ajax({
                    method: "POST",
                    url: "/system_summary/get_devices",
                    data: "type=" + type,
                    success: function (return_text) {
                        $('#devices_table').html(return_text);
                    }
                });
            }
        }

        function get_last_ucds(type){
            if( typeof type !== undefined && type != "") {
                $.ajax({
                    method: "POST",
                    url: "/system_summary/get_ucds",
                    data: "type=" + type,
                    success: function (return_text) {
                        $('#ucds_table').html(return_text);
                    }
                });
            }
        }


    </script>
@endsection

@section('page_document_ready')
    $.fn.select2.defaults.set( "theme", "default" );

    $("#last_reactive_dt").select2({
        minimumResultsForSearch: Infinity,
        width: 'auto',
        dropdownAutoWidth : true
    })
    .change(function(){
        the_val = $(this).val();
        //alert(the_val);
        get_last_reactives(the_val);
    })
    .val('all_devices')
    .trigger('change');

    $("#last_alerts_dt").select2({
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth : true,
        width: 'auto'
    })
    .change(function(){
        the_val = $(this).val();
        //alert(the_val);
        get_last_alerts(the_val);
    })
    .val('reactive')
    .trigger('change');

    $("#last_devices_dt").select2({
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth : true,
        width: 'auto'
    })
    .change(function(){
        the_val = $(this).val();
        //alert(the_val);
        get_last_devices(the_val);
    })
    .val('modem')
    .trigger('change');

    $("#last_ucd_dt").select2({
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth : true,
        width: 'auto'
    })
    .change(function(){
        the_val = $(this).val();
        //alert(the_val);
        get_last_ucds(the_val);
    })
    .val('users')
    .trigger('change');

@endsection