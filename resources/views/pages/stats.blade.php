<?php

$wait_send_payment = DB::table('payment as P')
    ->where("P.type", "=", 2)
    ->where("P.status", "=", 1)
    ->count();

$wait_coming_payment = DB::table('payment as P')
    ->where("P.type", "=", 1)
    ->where("P.status", "=", 1)
    ->count();

$pending_coming_payment = DB::table('payment as P')
    ->where("P.type", "=", 1)
    ->where("P.status", "=", 1)
    ->sum('net_amount');

$pending_send_money =DB::table('payment as P')
    ->where("P.type", "=", 2)
    ->where("P.status", "=", 1)
    ->sum('net_amount');
$total_coming_payment =DB::table('payment as P')
    ->where("P.type", "=", 1)
    ->where("P.status", "<>", 1)
    ->sum('net_amount');
$total_send_payment =DB::table('payment as P')
    ->where("P.type", "=", 2)
    ->where("P.status", "<>", 1)
    ->sum('net_amount');
$total_wait_booking =DB::table('booking')
    ->where("assigned_id", 0)
    ->where("status", 1)
    ->count();
$total_wait_order =DB::table('booking')
    ->where("assigned_id", "<>", 0)
    ->where("status", 1)
    ->count();
$total_approved_booking =DB::table('booking')
    ->where("assigned_id", 0)
    ->where("status", 2)
    ->count();
$total_approved_order =DB::table('booking')
    ->where("assigned_id", "<>", 0)
    ->where("status", 2)
    ->count();
$total_rej_booking =DB::table('booking')
    ->where("assigned_id", 0)
    ->where("status", 3)
    ->count();
$total_rej_order =DB::table('booking')
    ->where("assigned_id", "<>", 0)
    ->where("status", 3)
    ->count();

$total_admin =DB::table('users')
    ->where("user_type", "=", 2)
    ->where("status", 1)
    ->count();
$total_super_admin =DB::table('users')
    ->where("user_type", "=", 1)
    ->where("status", 1)
    ->count();
$total_client =DB::table('clients')
    ->where("type", 1)
    ->where("status", 1)
    ->count();
$total_seller =DB::table('clients')
    ->where("type", 2)
    ->where("status", 1)
    ->count();


?>

@extends('layouts.master')

@section('title')
    İstatististikler
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css"/>

@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
    <div class="row" id="div_user_tabs">
        <div class="col-lg-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs" id="user_detail_tabs">

                    <li class="" tab="#tab-1">
                        <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                            <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                            İlan İstatistikleri
                        </a>
                    </li>
                    <li class="" tab="#tab-2">
                        <a data-toggle="tab" href="#tab-2" aria-expanded="true">
                            <i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i>
                            Sipariş İstatistikleri
                        </a>
                    </li>
                    <li class="" tab="#tab-3">
                        <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                            <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                            Ödeme İstatistikleri
                        </a>
                    </li>
                    <li class="" tab="#tab-4">
                        <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                            <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                            Kullanıcı İstatistikleri
                        </a>
                    </li>


                </ul> <!-- .nav -->

                <div class="tab-content">

                    <div id="tab-1" class="tab-pane">
                        <div class="panel-body">

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-handshake-o fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{ $total_wait_booking }} </h2>
                                                <span> Bekleyen İlanlar </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-sitemap fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{ $total_approved_booking }} </h2>
                                                <span> Onaylanan İlanlar </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>


                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-podcast fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">

                                                <h2 class="font-bold"> {{ $total_rej_booking }} </h2>
                                                <span> Reddedilen İlanlar </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>



                        </div>
                    </div> <!-- .tab-1 -->


                    <div id="tab-2" class="tab-pane">
                        <div class="panel-body">
                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-handshake-o fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{ $total_wait_order }} </h2>
                                                <span> Bekleyen Sipariş </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-sitemap fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{ $total_approved_order }} </h2>
                                                <span> Onaylanan Sipariş </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>


                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-podcast fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">

                                                <h2 class="font-bold"> {{ $total_rej_order }} </h2>
                                                <span> Reddedilen Sipariş </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div> <!-- .tab-2 -->

                    <div id="tab-3" class="tab-pane">
                        <div class="panel-body">
                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-handshake-o fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{ $wait_send_payment }} </h2>
                                                <span> {{ trans('finance.wait_send_payment') }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-sitemap fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{ $wait_coming_payment }} </h2>
                                                <span> {{ trans('finance.wait_coming_payment') }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>


                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-podcast fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">

                                                <h2 class="font-bold"> {{ $pending_coming_payment }} </h2>
                                                <span> {{ trans('finance.pending_coming_payment') }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-tachometer fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{$pending_send_money}} </h2>
                                                <span> {{ trans('finance.pending_send_money') }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-sliders fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{$total_coming_payment}} </h2>
                                                <span> {{ trans('finance.total_coming_payment') }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-desktop fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{$total_send_payment}} </h2>
                                                <span> {{ trans('finance.total_send_payment') }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div> <!-- .tab-3 -->

                    <div id="tab-4" class="tab-pane">
                        <div class="panel-body">

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-desktop fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{$total_admin}} </h2>
                                                <span> Admin Sayısı </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-desktop fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{$total_super_admin}} </h2>
                                                <span> Süper Admin Sayısı </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-desktop fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{$total_client}} </h2>
                                                <span> Toplam Alıcı </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 col-md-4 col-xs-6">
                                <a href="#" style="text-decoration: none">
                                    <div class="widget style1 navy-bg">
                                        <div class="row vertical-align">
                                            <div class="col-xs-3">
                                                <i class="fa fa-desktop fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <h2 class="font-bold"> {{$total_seller}} </h2>
                                                <span> Toplam Satıcı </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>


                        </div>
                    </div> <!-- .tab-3 -->

                </div> <!-- .tab-content -->
            </div>
        </div>
    </div> <!-- #div_order_tabs -->
    </div> <!-- .wrapper -->


@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput_locale_tr.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
    <script type="text/javascript" language="javascript"
            src="/js/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
@endsection

@section('page_document_ready')


    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (session()->has('user_update_success') && session('user_update_success'))
        {{ session()->forget('user_update_success') }}

        custom_toastr('{{ trans('user_management.update_success') }}');
    @endif



    // Keep the current tab active after page reload
    rememberTabSelection('#user_detail_tabs', !localStorage);

    if(document.location.hash){
    $("#user_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
    }

    var tab_1 = false,
    tab_2 = false,
    tab_3 = false,
    tab_4 = false;

    function load_tab_content(selectedTab){
    if(selectedTab == "#tab-1" && tab_1 == false){
    tab_1 = true;
    }
    else if(selectedTab == "#tab-2" && tab_2 == false){
    // user_authorizations();
    tab_2 = true;
    }
    else if(selectedTab == "#tab-3" && tab_3 == false){
    // user_login_history();
    tab_3 = true;
    }
    else if(selectedTab == "#tab-4" && tab_4 == false){
    tab_4 = true;

    }
    else{
    return;
    }
    }

    // Load the selected tab content When the tab is changed
    $('#user_detail_tabs a').on('shown.bs.tab', function(event){
    var selectedTab = $(event.target).attr("href");
    load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#user_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
    load_tab_content(active_tab);
    else
    $("#user_detail_tabs a:first").trigger('click');


@endsection