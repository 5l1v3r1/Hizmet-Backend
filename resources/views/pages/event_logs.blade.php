@extends('layouts.master')

@section('title')
    {{ trans('event_logs.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}

    <style>
        .select2-results__message{
            display: none;
        }

    </style>
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_event_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("event_logs.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content">
                        {!! $DataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_level_js')
    {!! $DataTableObj->js() !!}
@endsection

@section('page_document_ready')
    {!! $DataTableObj->ready() !!}
@endsection