@extends('layouts.master')

@section('title')
    {{ trans('temperature.detail_title') }}
@endsection

@section('page_level_css')
    {!! $TemperatureTableObj->css() !!}
@endsection

@section('content')

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_modem_summary" style="margin-bottom:20px;">
            <div class="col-md-6">
                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_airport->name }}
                        </h2>
                        <p style="margin: 10px 0 0;">
                            {{ trans('temperature.icao_code') }}:  <strong> {{ $the_airport->ICAO
                             }} </strong>

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('temperature.iata_code') }}:  <strong> {{ $the_airport->IATA
                             }} </strong>

                        </p>

                        <p style="margin: 10px 0 0;">
                            {{ trans('temperature.last_fetched_date') }}:  <strong> {{ date('d/m/Y H:i:s',strtotime($the_airport->last_fetched_date)) }}
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
                            <strong>{{ trans('temperature.location') }}</strong>
                        </td>
                        <td>
                            {{ $the_airport->location_text }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('temperature.latitude') }}</strong>
                        </td>
                        <td>
                            {{ $the_airport->location_latitude }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('temperature.longitude') }}</strong>
                        </td>
                        <td>
                            {{ $the_airport->location_longitude }}
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div> <!-- #div_modem_summary -->

        <div class="row" id="div_temperature_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="temperature_detail_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="true">
                                <i class="fa fa-thermometer-full fa-lg" aria-hidden="true"></i>
                                {{ trans('temperature.temperature_table') }}
                            </a>
                        </li>
                    </ul> <!-- .nav -->

                    <div class="tab-content">


                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!! $TemperatureTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-2 -->
                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_modem_tabs -->

    </div>
@endsection

@section('page_level_js')
    {!! $TemperatureTableObj->js() !!}
@endsection

@section('page_document_ready')
    var tab_1 = false;

    // Keep the current tab active after page reload
    rememberTabSelection('#temperature_detail_tabs', !localStorage);

    if(document.location.hash){
        $("#temperature_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
    }

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            {!! $TemperatureTableObj->ready() !!}
            tab_1 = true;

        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#temperature_detail_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);

        // clear hash and parameter values from URL
        history.pushState('', document.title, window.location.pathname);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#temperature_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#temperature_detail_tabs a:first").trigger('click');

@endsection