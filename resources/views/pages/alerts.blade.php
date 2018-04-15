@extends('layouts.master')

@section('title')
    {{ trans('alerts.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}

    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css" />

    <style>
        .detail-backgorund{
            background-color: #fAfAf8;
        }
    </style>
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="row" id="div_support_dataTable">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Bildirimler</h5>
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
    </div>
@endsection

@section('page_level_js')
    {!! $DataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script>
    function read_alert(id){
        $.ajax({
            method:"POST",
            url:"/alerts/update_read",
            data:"id="+id,
            success: function(return_text){


            }
    })}


</script>

@endsection

@section('page_document_ready')

    {!! $DataTableObj->ready() !!}
@endsection