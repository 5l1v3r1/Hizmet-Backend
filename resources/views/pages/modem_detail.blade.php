@extends('layouts.master')

@section('title')
    {{ trans('modem_detail.title') }}
@endsection

@section('page_level_css')
    {!! $DevicesTableObj->css() !!}
@endsection

@section('content')
    <?php
        $the_modem = json_decode($the_modem);
    ?>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_modem_summary" style="margin-bottom:20px;">
            <div class="col-md-6">
                <div class="profile-image">
                    <img src="/img/avatar/client/{{$the_modem->client_avatar}}" class="img-circle circle-border m-b-md" alt="profile">
                </div>
                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_modem->serial_no }}
                        </h2>
                        <p style="margin: 10px 0 0;">
                            {{ trans('modem_detail.modem_type') }}:  <strong> {{ trans("global.".$the_modem->modem_type) }} </strong>

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('modem_detail.trademark_model') }}:  <strong> {{ $the_modem->trademark." / ". $the_modem->model }} </strong>

                        </p>

                        <p style="margin-top: 10px;">
                            {{ trans('modem_detail.client_distributor') }}:  <strong> {{ $the_modem->client_name . " / ". $the_modem->distributor }} </strong>

                        </p>
                        <p style="margin-top: 10px;">
                            {{ trans('modem_detail.explanation') }}:
                            <strong>
                            @if ($the_modem->explanation != "")

                                {{ $the_modem->explanation}}

                            @else
                                {{ trans('modem_detail.no_explanation') }}
                            @endif
                            </strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <table class="table small m-b-xs">
                    <tbody>

                    <tr>
                        <td>
                            <strong>{{ trans('modem_detail.last_connection') }}</strong>
                        </td>
                        <td>
                            @if ($the_modem->last_connection_at == NULL)
                                {{ trans('modem_detail.no_first_connection') }}
                            @else
                                {{ date('d/m/Y H:i:s',strtotime($the_modem->last_connection_at)) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('modem_detail.first_connection') }}</strong>
                        </td>
                        <td>
                            @if ($the_modem->first_connection_at == NULL)
                                {{ trans('modem_detail.no_first_connection') }}
                            @else
                                {{ date('d/m/Y H:i:s',strtotime($the_modem->first_connection_at)) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('modem_detail.address') }}</strong>
                        </td>
                        <td>
                            {{ $the_modem->location_text }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('modem_detail.created_by') }}</strong>
                        </td>
                        <td>
                            {{ $the_modem->created_by }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('modem_detail.created_at') }}</strong>
                        </td>
                        <td>
                            {{ date('d/m/Y H:i:s',strtotime($the_modem->created_at)) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- #div_modem_summary -->

        <div class="row" id="div_modem_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="modem_detail_tabs">
                        <!-- <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="true">
                                <i class="fa fa-plug fa-lg" aria-hidden="true"></i>
                                \{\{ trans('modem_detail.connection_infos') }}
                            </a>
                        </li> -->
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-cogs fa-lg" aria-hidden="true"></i>
                                {{ trans('modem_detail.connected_devices') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-bell-o fa-lg" aria-hidden="true"></i>
                                {{ trans('modem_detail.alarms') }}
                            </a>
                        </li>
                        @if( strtolower($the_modem->modem_type) == "gprs" )
                            <!-- <li class="" tab="#tab-4">
                                <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                    <i class="fa fa-signal fa-lg" aria-hidden="true"></i>
                                    \{\{ trans('modem_detail.signal') }}
                                </a>
                            </li> -->
                        @endif
                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        <!--
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body form-horizontal">
                                @if( strtolower($the_modem->modem_type) == "ethernet" ||  strtolower($the_modem->modem_type) == "wifi" )
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.ip_address') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> 192.168.3.34 </label>
                                    </div>
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.subnet_mask') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> 255.255.255.0 </label>
                                    </div>
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.gateway') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> 192.168.3.1 </label>
                                    </div>
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.dns_1') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> 192.168.3.1 </label>
                                    </div>
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.dns_2') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> 8.8.8.8 </label>
                                    </div>
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.mac_address') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> 00:04:A3:20:D2:7E </label>
                                    </div>
                                @elseif( strtolower($the_modem->modem_type) == "gprs" )
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.gsm_operator') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> Turkcell </label>
                                    </div>
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.imei_no') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> 86915800-6540351 </label>
                                    </div>
                                    <div class="row">
                                        <label class="col-lg-3 control-label"> {{ trans('modem_detail.simcard_no') }}: </label>
                                        <label class="col-lg-6 control-label" style="text-align:left;font-weight: normal;"> 8990029 3007139 40281F </label>
                                    </div>
                                @endif

                            </div>
                        </div> <!-- .tab-1 -->

                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                               {!! $DevicesTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-2 -->

                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body">
                                {!! $AlertsTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-3 -->

                        @if( strtolower($the_modem->modem_type) == "gprs" )
                            <!--<div id="tab-4" class="tab-pane">
                                <div class="panel-body">
                                    Yapım aşamasında (GPRS modeme ait GSM sinyali grafiği burada gösterilecek)
                                </div>
                            </div> <!-- .tab-4 -->
                        @endif
                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_modem_tabs -->
        
    </div>
@endsection

@section('page_level_js')
    {!! $DevicesTableObj->js() !!}

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
                            mdal_dt.ajax.reload();
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
    rememberTabSelection('#modem_detail_tabs', !localStorage);

    if (document.location.hash && document.location.hash == '#alarms') {
        $("#modem_detail_tabs a[href='#tab-3']").trigger('click');
    }
    else if(document.location.hash){
        $("#modem_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
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
            {!! $DevicesTableObj->ready() !!}
            tab_2 = true;
        }
        else if(selectedTab == "#tab-3" && tab_3 == false){
            {!! $AlertsTableObj->ready() !!}
            tab_3 = true;
        }
        @if( strtolower($the_modem->modem_type) == "gprs" )
            else if(selectedTab == "#tab-4" && tab_4 == false){

                tab_4 = true;
            }
        @endif
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#modem_detail_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);

        // clear hash and parameter values from URL
        history.pushState('', document.title, window.location.pathname);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#modem_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#modem_detail_tabs a:first").trigger('click');

@endsection