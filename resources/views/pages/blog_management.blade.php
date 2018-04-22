<?php

$client_name = DB::table('clients')
    ->where("status",'<>', 0)
    ->where("type",'=', 1)
    ->orderBy('id')
    ->get();
$user_data = DB::table('users')
    ->where("status",'<>', 0)
    ->orderBy('name')
    ->get();
$client_data = DB::table('clients')
    ->where("status",'<>', 0)
    ->orderBy('name')
    ->get();
$blog_category = DB::table('blog_category')
    ->orderBy('id')
    ->get();
?>

@extends('layouts.master')

@section('title')
    {{ trans('booking_management.title') }}
@endsection

@section('page_level_css')

    {!! $BlogDataTableObj->css() !!}
    <link href="/css/plugins/summernote/summernote.css" rel="stylesheet">
    <link href="/css/plugins/summernote/summernote-bs3.css" rel="stylesheet">
    <style>
        .hr-text {
            line-height: 1em;
            position: relative;
            outline: 0;
            border: 0;
            color: black;
            text-align: center;
            height: 1.5em;
            opacity: .5;
        }

        .hr-text::before {
            content: '';
            background: linear-gradient(to right, transparent, #818078, transparent);
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
        }
        .hr-text::after {
            content: attr(data-content);
            position: relative;
            display: inline-block;
            color: black;

            padding: 0 .5em;
            line-height: 1.5em;

            color: #818078;
            background-color: #fcfcfa;
        }

        .div_editor_custom{
            border: 1px solid;
            border-radius: 0px 0px 10px 10px;
            background-color: white;
            min-height: 137px;
        }</style>
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">

        <div class="row" id="div_add_new_blog" style="display:none;">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5 id="modal_title"> {{ trans('user_management.add_new_user') }}</h5>
                        <div class="ibox-tools">
                            <a class="" onclick="cancel_add_new_form('#div_blog_dataTable','#div_add_new_blog');">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">

                        <div class="row" id="add_new_user_warning" style="display: none;">
                            <div class="col-lg-10 col-lg-offset-1">
                                <div class="alert alert-danger">
                                    <i class="fa fa-exclamation-circle fa-lg" aria-hidden="true"></i>
                                    @if(Auth::user()->user_type == 3)
                                        {{ trans('user_management.add_new_user_d_warning') }}
                                    @elseif(Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                                        {{ trans('user_management.add_new_user_a_warning') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/blog/add') }}" id="add_new_blog_form">
                            {{ csrf_field() }}


                            <hr class="hr-text" data-content="Blog Başlığı">
                            <div class="form-group">
                                <label class="col-sm-3 control-label"> Blog Başlığı <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_blog_title" name="new_blog_title" required minlength="3" maxlength="255">
                                </div>
                            </div>

                            <hr class="hr-text" data-content="Blog Kategorisi">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Kategori <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="blog_selected_category" name="blog_selected_category" class="form-control" style="width: 100%;">
                                        <option value="0"></option>
                                        @foreach($blog_category as $one_list)
                                            <option value="{{ $one_list->id }}">{{ $one_list->c_name }}</option>
                                        @endforeach
                                    </select></div>

                                <br>
                            </div>
                            <hr class="hr-text" data-content="Blog Yazarı">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Yazar <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="selected_user_id" name="selected_user_id" class="form-control" style="width: 100%;">
                                        <option value="0"></option>
                                        @foreach($user_data as $one_list)
                                            <option value="{{ $one_list->id }}">{{ $one_list->name }}</option>
                                        @endforeach
                                    </select></div>

                                <br>
                            </div>
                            <hr class="hr-text" data-content="Blog Özeti">
                            <div class="form-group">
                                <label class="col-sm-3 control-label"> Blog Özeti <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <textarea type="text" placeholder="" class="form-control" id="summary" name="summary"></textarea>
                                </div>
                            </div>
                            <hr class="hr-text" data-content="Blog İçeriği">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">İçerik <span style="color:red;">*</span></label>
                                <div class="col-sm-6 div_editor_custom">
                                <div id="content" name="content">

                                </div>
                                </div>
                            </div>
                            <input type="hidden" value="" name="content_hidden" id="content_hidden"/>
                            <hr class="hr-text" data-content="Blog Durumu">


                            <div class="form-group">
                                <label class="col-sm-3 control-label">Yazar <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="blog_status" name="blog_status" class="form-control" style="width: 100%;">
                                        <option value="0"></option>

                                        <option value="1">Taslak</option>
                                        <option value="2">Yayında</option>
                                        <option value="3">Kaldırıldı</option>

                                    </select></div>

                                <br>
                            </div>

                            <input type="hidden" value="new" id="blog_op_type" name="blog_op_type">
                            <input type="hidden" value="" id="blog_edit_id" name="blog_edit_id">

                            <div class="form-group">
                                <div class="col-lg-4 col-lg-offset-3">
                                    <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                        <i class="fa fa-times"></i> {{ trans('user_management.cancel') }} </button>
                                    <button type="submit" class="btn btn-primary" id="save_blogt_button" name="save_blog_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('user_management.save') }}</button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>



        <div class="row" id="div_blog_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("booking_management.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
                        {!! $BlogDataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .wrapper -->

@endsection

@section('page_level_js')
    {!! $BlogDataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script src="/js/plugins/summernote/summernote.min.js"></script>

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        function validate_save_op(){
            $("#add_new_blog_form").parsley().reset();
            $("#add_new_blog_form").parsley();
        }



        function cancel_add_new_form(){
            $("#add_new_blog_form .form-control").val("");
            $(".parsley-errors-list").remove();


            $("#div_add_new_blog").hide();
            $("#div_blog_dataTable").show();
        }

        function show_add_new_form(){
            $("#modal_title").html("Yeni Blog Oluştur");
            $("#blog_op_type").val("new");
            $("#blog_edit_id").val("");


            $("#content").summernote({

                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['color', ['color']],
                    ['insert', ['picture']]
                ]
            });

            $("#content").code('');



            $("#password_label_for_edit").hide();
            $("#password_label_for_new").show();

            $("#new_user_password").removeAttr('disabled');
            $('#new_user_password').attr('required', '');

            $("#new_user_type").select2({
                minimumResultsForSearch: Infinity
            }).val(4).trigger("change");

            $('#save_blog_button').html('<i class="fa fa-thumbs-o-up"></i> Kaydet');

            $("#div_add_new_blog").show();
            $("#div_blog_dataTable").hide();
        }

        function validate_save_op(){

            $("#content_hidden").val($("#content").code());
            $("#add_new_blog_form").parsley();
        }

        @endif
    </script>
@endsection

@section('page_document_ready')
    {!! $BlogDataTableObj->ready() !!}
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        @if (session()->has('new_blog_insert_success') && session('new_blog_insert_success'))
            {{ session()->forget('new_blog_insert_success') }}

            custom_toastr('Blog oluşturma başarılı');
        @endif

        @if (session()->has('blog_update_success') && session('blog_update_success'))
            {{ session()->forget('blog_update_success') }}

            custom_toastr('Blog güncelleme başarılı.');
        @endif

    @endif


@endsection