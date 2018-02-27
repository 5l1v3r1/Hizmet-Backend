@extends('layouts.master')

@section('title')
    {{ trans('system_summary.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/select2-bootstrap.min.css" />
@endsection

@section('content')


    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">

            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/client_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-handshake-o fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> 22 </h2>
                                <span> {{ trans('system_summary.client') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/distributor_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-sitemap fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> 33 </h2>
                                <span> {{ trans('system_summary.seller') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/modem_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-podcast fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">

                                <h2 class="font-bold"> 34</h2>
                                <span> {{ trans('system_summary.wait_booking') }} </span>
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
                                <h2 class="font-bold"> 35 </h2>
                                <span> {{ trans('system_summary.booking') }} </span>
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
                                <h2 class="font-bold"> 666 </h2>
                                <span> {{ trans('system_summary.wait_order') }} </span>
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
                                <h2 class="font-bold"> 43 </h2>
                                <span> {{ trans('system_summary.finish_order') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>
        <div class="row" style="margin-top: 30px;">
            <div class="col-md-6">
                <div class="panel panel-danger" style="min-height: 400px;">
                    <div class="panel-heading">
                        <i class="fa fa-bolt"></i>
                        <span style="margin-right: 5px;"> {{ trans('system_summary.last_booking') }} </span>


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
                        <span style="margin-right: 5px;"> {{ trans('system_summary.last_order') }} </span>

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
                        <span style="margin-right: 5px;"> {{ trans('system_summary.waiting_booking') }} </span>

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
                        <span style="margin-right: 5px;"> {{ trans('system_summary.waiting_order') }} </span>


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


@endsection