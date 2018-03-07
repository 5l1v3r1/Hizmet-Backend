@extends('layouts.master')

@section('title')
    {{ trans('client_management.title') }}
@endsection

@section('page_level_css')
    {!! $UserDataTableObj->css() !!}


@endsection

@section('content')
    <?php
    $user_logo_hdn_value = "not_changed";
    ?>
    <div class="wrapper wrapper-content animated fadeInRight">


        <div class="row" id="div_user_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("user_management.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
                        {!! $UserDataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .wrapper -->
@endsection

@section('page_level_js')
    {!! $UserDataTableObj->js() !!}
@endsection

@section('page_document_ready')
    {!! $UserDataTableObj->ready() !!}
@endsection