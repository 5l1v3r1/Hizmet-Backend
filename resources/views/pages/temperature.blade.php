@extends('layouts.master')

@section('title')
    {{ trans('temperature.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}

@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">

        <div class="row" id="div_airport_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("modem_management.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
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