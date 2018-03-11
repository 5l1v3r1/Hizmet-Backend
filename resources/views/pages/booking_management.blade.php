@extends('layouts.master')

@section('title')
    {{ trans('booking_management.title') }}
@endsection

@section('page_level_css')

    {!! $BookingDataTableObj->css() !!}
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">

        <div class="row" id="div_user_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("booking_management.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
                        {!! $BookingDataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .wrapper -->

@endsection

@section('page_level_js')
    {!! $BookingDataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
@endsection

@section('page_document_ready')
    {!! $BookingDataTableObj->ready() !!}
@endsection