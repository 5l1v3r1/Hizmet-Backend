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
$blog_top_category = DB::table('blog_category')
    ->where('top_category', '0')
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


        <div class="row" id="div_blog_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="blog_detail_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                Blog Listesi
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                Kategoriler
                            </a>
                        </li>


                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body">
                                {!! $BlogDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-1 -->
                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body">

                                <div class="col-md-4">
                                <form  class="m-t form-horizontal" role="form" method="POST" action="{{ url('/blog/category/del') }}" id="blog_category_del">
                                    {{ csrf_field() }}
                                <select name="del_category" size="5" class="form-control">
                                    @foreach($blog_category as $o_cat)
                                    <option value="{{$o_cat->id}}">{{$o_cat->c_name}}</option>
                                    @endforeach
                                </select>
                                    <button type="submit" class="btn btn-primary" id="category_delete" name="category_delete" >Sil</button>
                                </form>
                                </div>
                                <div class="col-md-8">
                                    <form  class="m-t form-horizontal" role="form" method="POST" action="{{ url('/blog/category/add') }}" id="blog_category_add">
                                        {{ csrf_field() }}
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> Kategori Adı <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <input type="text" placeholder="" class="form-control" id="new_category_name" name="new_category_name" required minlength="3" maxlength="255">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> Sıralama <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <input type="text" placeholder="" class="form-control" id="new_category_rank" name="new_category_rank" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label">Üst Kategori <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <select id="new_category_top" name="new_category_top" class="form-control" style="width: 100%;">
                                                    <option value="0"></option>
                                                    @foreach($blog_top_category as $one_list)
                                                        <option value="{{ $one_list->id }}">{{ $one_list->c_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <br>

                                        </div>
                                        <div class="col-md-4" >
                                        </div><div class="col-md-4" >
                                            <input type="hidden" placeholder="" class="form-control" id="op_type" name="op_type" value="new">
                                            <button type="submit" class="btn btn-primary" id="category_delete" name="category_delete">Ekle</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div> <!-- .tab-2 -->


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
            $("#div_blog_tabs").show();
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
            $("#div_blog_tabs").hide();
        }

        function validate_save_op(){

            $("#content_hidden").val($("#content").code());
            $("#add_new_blog_form").parsley();
        }

        @endif
    </script>
@endsection

@section('page_document_ready')

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
        @if (session()->has('new_blog_category_insert_success') && session('new_blog_category_insert_success'))
            {{ session()->forget('new_blog_category_insert_success') }}

            custom_toastr('Blog kategori ekleme başarılı.');
        @endif
        @if (session()->has('blog_category_delete_success') && session('blog_category_delete_success'))
            {{ session()->forget('blog_category_delete_success') }}

            custom_toastr('Blog kategori silme başarılı.');
        @endif

    @endif



    // Keep the current tab active after page reload
    rememberTabSelection('#blog_detail_tabs', !localStorage);

    if(document.location.hash){
    $("#blog_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
    }

    var tab_1 = false,
    tab_2 = false,
    tab_3 = false,
    tab_4 = false;

    function load_tab_content(selectedTab){
    if(selectedTab == "#tab-1" && tab_1 == false){
    tab_1 = true;
    {!! $BlogDataTableObj->ready() !!}
    }
    else if(selectedTab == "#tab-2" && tab_2 == false){
    tab_2 = true;
    }
    else if(selectedTab == "#tab-3" && tab_3 == false){
    tab_3 = true;
    }
    else if(selectedTab == "#tab-4" && tab_4 == false){
    tab_4 = true;


    }
    else{
    return;
    }
    }

    // Load the selected tab content When the tab is changed
    $('#blog_detail_tabs a').on('shown.bs.tab', function(event){
    var selectedTab = $(event.target).attr("href");
    load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#blog_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
    load_tab_content(active_tab);
    else
    $("#blog_detail_tabs a:first").trigger('click');




@endsection