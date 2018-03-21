@extends('layouts.master')

@section('title')
    {{ trans('system_summary.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/select2-bootstrap.min.css"/>
@endsection

@section('content')
    <?php

    $total_client_count = DB::table('clients as C')
        ->where("C.type", "=", 1)
        ->count();

    $total_seller_count = DB::table('clients as C')
        ->where("C.type", "=", 2)
        ->count();

    $total_wait_booking = DB::table('booking as B')
        ->where("B.status", "=", 1)
        ->where("B.assigned_id", "=", 0)
        ->count();

    $total_published_booking = DB::table('booking as B')
        ->where("B.status", "=", 2)
        ->where("B.assigned_id", "=", 0)
        ->count();
    $total_waiting_order = DB::table('booking as B')
        ->where("B.status", "<>", 5)
        ->where("B.assigned_id", "<>", 0)
        ->count();
    $total_finish_order = DB::table('booking as B')
        ->where("B.status", "=", 5)
        ->where("B.assigned_id", "<>", 0)
        ->count();


    ?>

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
                                <h2 class="font-bold"> {{ $total_client_count }} </h2>
                                <span> {{ trans('system_summary.client') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/seller_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-sitemap fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> {{ $total_seller_count }} </h2>
                                <span> {{ trans('system_summary.seller') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/booking_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-podcast fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">

                                <h2 class="font-bold"> {{ $total_wait_booking }} </h2>
                                <span> {{ trans('system_summary.wait_booking') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/booking_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-tachometer fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> {{$total_published_booking}} </h2>
                                <span> {{ trans('system_summary.booking') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/order_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-sliders fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> {{$total_waiting_order}} </h2>
                                <span> {{ trans('system_summary.wait_order') }} </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-xs-6">
                <a href="/order_management" style="text-decoration: none">
                    <div class="widget style1 navy-bg">
                        <div class="row vertical-align">
                            <div class="col-xs-3">
                                <i class="fa fa-desktop fa-3x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h2 class="font-bold"> {{$total_finish_order}} </h2>
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
                        <table class="table table-hover no-margins" id="booking_table">
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
                        <table class="table table-hover no-margins" id="order_table">
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
                        <span style="margin-right: 5px;"> {{ trans('system_summary.last_clients') }} </span>

                    </div>
                    <div class="panel-body">
                        <table class="table table-hover no-margins" id="client_table">
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
                        <span style="margin-right: 5px;"> {{ trans('system_summary.last_sellers') }} </span>


                    </div>
                    <div class="panel-body">
                        <table class="table table-hover no-margins" id="seller_table">
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
        function get_last_booking() {

            $.ajax({
                method: "POST",
                url: "/system_summary/get_booking",
                data: "type=a",
                success: function (return_text) {
                    $('#booking_table').html(return_text);
                }
            });

        }

        function get_last_order() {

            $.ajax({
                method: "POST",
                url: "/system_summary/get_order",
                data: "type=a",
                success: function (return_text) {
                    $('#order_table').html(return_text);
                }
            });

        }

        function get_last_client() {

            $.ajax({
                method: "POST",
                url: "/system_summary/get_client",
                data: "type=a",
                success: function (return_text) {
                    $('#client_table').html(return_text);
                }
            });

        }

        function get_last_seller() {

            $.ajax({
                method: "POST",
                url: "/system_summary/get_seller",
                data: "type= a",
                success: function (return_text) {
                    $('#seller_table').html(return_text);
                }
            });

        }


    </script>
@endsection

@section('page_document_ready')


    get_last_booking();
    get_last_order();
    get_last_seller();
    get_last_client();

@endsection