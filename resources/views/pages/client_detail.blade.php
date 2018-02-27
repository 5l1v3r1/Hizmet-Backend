@extends('layouts.master')

@section('title')
    {{ trans('client_detail.title') }}
@endsection

@section('page_level_css')
    {!! $UserDataTableObj->css() !!}
@endsection

@section('content')
    <?php
        $the_client = json_decode($the_client);
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_client_summary" style="margin-bottom:20px;">
            <div class="col-md-6">
                <div class="profile-image">
                    <img src="/img/avatar/client/{{$the_client->logo}}" class="img-circle circle-border m-b-md" alt="profile">
                </div>
                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_client->name }}
                        </h2>
                        <p style="margin: 10px 0 0;">
                            {{ trans('client_detail.distributor') }}:
                            @if( (Auth::user()->user_type == 1 || Auth::user()->user_type == 2) && $the_client->distributor_id != 0 )
                                <a href="/distributor_management/detail/{{ $the_client->distributor_id }}" target="_blank">
                                      <strong> {{ $the_client->distributor }} </strong>
                                </a>
                            @else
                                <strong> {{ $the_client->distributor }} </strong>
                            @endif

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('client_management.authorized_name') }}:  <strong> {{ $the_client->authorized_name}} </strong>

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('client_management.gsm_phone') }}:  <strong> {{ $the_client->gsm_phone}} </strong>

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('client_management.phone') }}:  <strong> {{ $the_client->phone}} </strong>

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('client_management.address') }}:  <strong> {{ $the_client->location_text }} </strong>

                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <table class="table small m-b-xs">
                    <tbody>
                        <tr>
                            <td>
                                <strong>{{ trans('client_management.email') }}</strong>
                            </td>
                            <td>
                                {{ $the_client->email }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('client_management.fax') }}</strong>
                            </td>
                            <td>
                                {{ $the_client->fax }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('client_management.tax_administration') }}</strong>
                            </td>
                            <td>
                                {{ $the_client->tax_administration }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('client_management.tax_no') }}</strong>
                            </td>
                            <td>
                                {{ $the_client->tax_no }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('client_detail.created_by') }}</strong>
                            </td>
                            <td>
                                {{ $the_client->created_by }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('client_management.created_at') }}</strong>
                            </td>
                            <td>
                                {{ date('d/m/Y H:i:s',strtotime($the_client->created_at)) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- #div_client_summary -->

        <div class="row" id="div_modem_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="client_detail_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.users') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-podcast fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.modems') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-cogs fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.devices') }}
                            </a>
                        </li>
                        <!--
                        <li class="" tab="#tab-4">
                            <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                <i class="fa fa-pie-chart fa-lg" aria-hidden="true"></i>
                                \{\{ trans('client_detail.statistics') }}
                            </a>
                        </li> -->
                        <li class="" tab="#tab-5">
                            <a data-toggle="tab" href="#tab-5" aria-expanded="false">
                                <i class="fa fa-bell-o fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.alarms') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-6">
                            <a data-toggle="tab" href="#tab-6" aria-expanded="false">
                                <i class="fa fa-tasks fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.event_logs') }}
                            </a>
                        </li>
                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!!  $UserDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-1 -->

                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!!  $ModemDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-2 -->

                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!!  $DeviceDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-3 -->

                        <!--
                        <div id="tab-4" class="tab-pane">
                            <div class="panel-body">
                                Geliştirme devam ediyor...
                            </div>
                        </div> <!-- .tab-4 -->

                        <div id="tab-5" class="tab-pane">
                            <div class="panel-body">
                                {!!  $AlertsDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-5 -->

                        <div id="tab-6" class="tab-pane">
                            <div class="panel-body">
                                {!! $EventDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-6 -->
                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_modem_tabs -->

    </div>
@endsection

@section('page_level_js')
    {!! $UserDataTableObj->js() !!}

    <script>
        @if (Helper::has_right(Auth::user()->operations, "change_alert_status"))

        function delete_alert(id){
            confirmBox('','{{ trans('alerts.delete_alert_warning') }}','warning',function(){
                $.ajax({
                    method:"POST",
                    url:"/alerts/delete",
                    data:"id="+id+"&type=one_alert",
                    success:function(return_text){
                        if(return_text == "SUCCESS"){
                            cdal_dt.ajax.reload();
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
    // Keep the current tab active after page reload
    rememberTabSelection('#client_detail_tabs', !localStorage);

    if (document.location.hash && document.location.hash == '#alarms') {
        $("#client_detail_tabs a[href='#tab-5']").trigger('click');
    }
    else if(document.location.hash){
        $("#client_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
    }

    var tab_1 = false,
        tab_2 = false,
        tab_3 = false,
        tab_4 = false,
        tab_5 = false,
        tab_6 = false;

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            {!! $UserDataTableObj->ready() !!}
            tab_1 = true;
        }
        else if(selectedTab == "#tab-2" && tab_2 == false){
            {!! $ModemDataTableObj->ready() !!}
            tab_2 = true;
        }
        else if(selectedTab == "#tab-3" && tab_3 == false){
            {!! $DeviceDataTableObj->ready() !!}
            tab_3 = true;
        }
        else if(selectedTab == "#tab-4" && tab_4 == false){

            tab_4 = true;
        }
        else if(selectedTab == "#tab-5" && tab_5 == false){
            tab_5 = true;
            {!!  $AlertsDataTableObj->ready() !!}
        }
        else if(selectedTab == "#tab-6" && tab_6 == false){
            tab_6 = true;
            {!! $EventDataTableObj->ready() !!}
        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#client_detail_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);

        // clear hash and parameter values from URL
        history.pushState('', document.title, window.location.pathname);
    });

     // Just install the related tab content When the page is first loaded
    active_tab = $('#client_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#client_detail_tabs a:first").trigger('click');

@endsection