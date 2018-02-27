@extends('layouts.master')

@section('title')
    {{ trans('comparison.title') }}
@endsection

@section('page_level_css')

@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_comparison_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="comparison_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="true">
                                <i class="fa fa-database fa-lg" aria-hidden="true"></i>
                                {{ trans('comparison.data_comparison') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="true">
                                <i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i>
                                {{ trans('comparison.invoice_comparison') }}
                            </a>
                        </li>
                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body">
                                veri karşılaştırması
                            </div>
                        </div> <!-- .tab-1 -->

                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body">
                                fatura karşılaştırması
                            </div>
                        </div> <!-- .tab-1 -->
                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_comparison_tabs -->
    </div>
@endsection

@section('page_level_js')
@endsection

@section('page_document_ready')

    // Keep the current tab active after page reload
    rememberTabSelection('#comparison_tabs', !localStorage);

    var tab_1 = false,
        tab_2 = false;

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            tab_1 = true;
        }
        else if(selectedTab == "#tab-2" && tab_2 == false){
            tab_2 = true;
        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#comparison_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#comparison_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#comparison_tabs a:first").trigger('click');
@endsection