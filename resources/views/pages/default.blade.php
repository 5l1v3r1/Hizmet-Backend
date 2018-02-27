@extends('layouts.master')

@section('title')
    {{ trans('global.title') }}
@endsection

@section('page_level_css')

@endsection

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">

        <!-- This div will be removed in other pages-->
        <div class="under_construction" style="text-align:center;">
            <img src="/img/Uc.jpg"/>
        </div>
    </div>
</div>
@endsection

@section('page_level_js')
@endsection

@section('page_document_ready')
@endsection