@extends('layouts.master')

@section('title')
    Blog Detayları
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css" />
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
    <style>


        .chat
        {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .chat li
        {
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #B3A9A9;
        }

        .chat li.left .chat-body
        {
            margin-left: 60px;
        }

        .chat li.right .chat-body
        {
            margin-right: 60px;
        }


        .chat li .chat-body p
        {
            margin: 0;
            color: #777777;
        }

        .panel .slidedown .glyphicon, .chat .glyphicon
        {
            margin-right: 5px;
        }

        .panel-body
        {
            overflow-y: scroll;
            height: 250px;
        }

        ::-webkit-scrollbar-track
        {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
            background-color: #F5F5F5;
        }

        ::-webkit-scrollbar
        {
            width: 12px;
            background-color: #F5F5F5;
        }

        ::-webkit-scrollbar-thumb
        {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
            background-color: #555;
        }
    </style>

@endsection

@section('content')
    <?php
    $the_blog = json_decode($the_blog);
    $the_users = json_decode($the_users);

    $user_data = DB::table('users')
        ->where("status",'<>', 0)
        ->orderBy('name')
        ->get();

    $blog_category = DB::table('blog_category')
        ->orderBy('id')
        ->get();

    ?>

    <div class="row" id="div_user_summary">
        <div class="col-md-6">

            <div class="profile-info">
                <div>
                    <h2 class="no-margins">
                        <strong> {{ $the_blog->title}}</strong>
                    </h2>
                    <p style="margin: 10px 0 0;">
                        Blog Kategorisi:  <strong>  {{ $the_blog->c_name}} </strong>

                    </p>

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <table class="table small m-b-xs">
                <tbody>
                <tr>
                    <td>
                        <strong>Blog Yazarı</strong>
                    </td>
                    <td>
                        {{$the_users->name}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong> Oluşturulma Tarihi</strong>
                    </td>
                    <td>
                        {{$the_blog->created_at}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong> Son Güncelleme</strong>
                    </td>
                    <td>
                        {{$the_blog->updated_at}}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_user_summary">
            <div class="col-md-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5 id="modal_title"> Talebi Düzenle</h5>
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
                                    <input type="text" placeholder="" class="form-control" id="new_blog_title" name="new_blog_title" required minlength="3" maxlength="255" value="{{$the_blog->title}}">
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
                                    <textarea type="text" placeholder="" class="form-control" id="summary" name="summary">{{$the_blog->summary}}</textarea>
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
                            <textarea hidden id="content_hidden" name="content_hidden">{{$the_blog->content}}</textarea>
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

                            <input type="hidden" value="edit" id="blog_op_type" name="blog_op_type">
                            <input type="hidden" value="{{$the_blog->bid}}" id="blog_edit_id" name="blog_edit_id">

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
    </div>



@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script src="/js/plugins/summernote/summernote.min.js"></script>
    <script>
        $(document).ready(function(){
            $("#blog_selected_category").val("<?php echo $the_blog->category_id;?>").trigger("change");
            $("#selected_user_id").val("<?php echo $the_blog->created_by;?>").trigger("change");
            $("#blog_status").val("<?php echo $the_blog->status;?>").trigger("change");

            $("#content").summernote({

                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['color', ['color']],
                    ['insert', ['picture']]
                ]
            });

            vs2=jQuery("#content_hidden").text();
            $("#content").code(vs2);
        });
        function validate_save_op(){

            $("#content_hidden").text($("#content").code());
            $("#add_new_blog_form").parsley();
        }
    </script>

@endsection

@section('page_document_ready')

    @if (session()->has('new_blog_message_insert_success') && session('new_blog_message_insert_success'))
        {{ session()->forget('new_blog_message_insert_success') }}

        custom_toastr('Mesaj Gönderme Başarılı');
    @endif
@endsection