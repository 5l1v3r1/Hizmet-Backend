@extends('layouts.master')

@section('title')
    {{ trans('order_detail.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css"/>

    {!! $ComingDataTableObj->css() !!}
@endsection

@section('content')
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


    ?>

    <div class="wrapper wrapper-content animated fadeInRight">


        <div class="row" id="div_order_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="order_detail_tabs">

                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('finance.general_state') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="true">
                                <i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i>
                                {{ trans('finance.coming_payment') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('finance.send_payment') }}
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
                        </div> <!-- .tab-1 -->


                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body">
                                {!!  $ComingDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-2 -->

                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body">
                                {!!  $SendingDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-3 -->


                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_order_tabs -->
    </div> <!-- .wrapper -->

    @include('pages.forms.finance_edit')
@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput_locale_tr.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
    <script type="text/javascript" language="javascript"
            src="/js/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>


    {!! $ComingDataTableObj->js() !!}



@endsection

@section('page_document_ready')



    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (session()->has('payment_update_success') && session('payment_update_success'))
        {{ session()->forget('payment_update_success') }}

        custom_toastr('{{ trans('payment.update_success') }}');
    @endif
    @if (session()->has('new_payment_insert_success') && session('new_payment_insert_success'))
        {{ session()->forget('new_payment_insert_success') }}

        custom_toastr('{{ trans('payment.insert_success') }}');
    @endif









    // Keep the current tab active after page reload
    rememberTabSelection('#order_detail_tabs', !localStorage);

    if(document.location.hash){
    $("#order_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
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
    {!! $ComingDataTableObj->ready() !!}
    $('#user_type').val("client");
    tab_2 = true;
    }
    else if(selectedTab == "#tab-3" && tab_3 == false){
    {!! $SendingDataTableObj->ready() !!}
    $('#user_type').val("seller");
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
    $('#order_detail_tabs a').on('shown.bs.tab', function(event){
    var selectedTab = $(event.target).attr("href");
    load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#order_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
    load_tab_content(active_tab);
    else
    $("#order_detail_tabs a:first").trigger('click');





@endsection