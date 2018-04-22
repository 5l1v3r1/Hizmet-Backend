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

    $blog_etiket = DB::table('blog_tag')
        ->select('*','blog_etiket.id as eid')
        ->LeftJoin('blog_etiket','blog_etiket.tag_id','blog_tag.id')
        ->where('blog_id',$the_blog->bid)
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


    <div class="row" id="div_blog_tabs2">
        <div class="col-lg-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs" id="blog_detail_tabs2">
                    <li class="" tab="#tab-1">
                        <a data-toggle="tab" href="#tab-1" aria-expanded="true">
                            <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                            Blog Detayları
                        </a>
                    </li>
                    <li class="" tab="#tab-2">
                        <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                            <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                            Etiketler
                        </a>
                    </li>
                </ul> <!-- .nav -->

                <div class="tab-content">
                    <div id="tab-1" class="tab-pane">
                        <div class="panel-body">
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
                    </div> <!-- .tab-1 -->
                    <div id="tab-2" class="tab-pane">
                        <div class="panel-body">

                            <div class="col-md-4">
                                <form  class="m-t form-horizontal" role="form" method="POST" action="{{ url('/blog/tag/del') }}" id="blog_category_del">
                                    {{ csrf_field() }}
                                    <select name="del_tag" id="del_tag" size="5" class="form-control">
                                        @foreach($blog_etiket as $o_cat)
                                            <option value="{{$o_cat->eid}}">{{$o_cat->name}}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary" id="category_delete" name="category_delete" >Sil</button>
                                </form>
                            </div>
                            <div class="col-md-8">
                                <form  class="m-t form-horizontal" role="form" method="POST" action="{{ url('/blog/tag/add') }}" id="blog_category_add">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> Etiket Adı <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" placeholder="" class="form-control" id="new_tag_name" name="new_tag_name" required minlength="3" maxlength="255">
                                        </div>
                                    </div>
                                    <input type="hidden" id="blog_id" name="blog_id" value="{{$the_blog->bid}}">
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
            @if (session()->has('blog_cetiket_delete_success') && session('blog_cetiket_delete_success'))
            {{ session()->forget('blog_cetiket_delete_success') }}

            custom_toastr('Blog Etiket silme başarılı.');
            @endif

            // Keep the current tab active after page reload
            rememberTabSelection('#blog_detail_tabs2', !localStorage);

            if(document.location.hash){
                $("#blog_detail_tabs2 a[href='"+document.location.hash+"']").trigger('click');
            }

            var tab_1 = false,
                tab_2 = false,
                tab_3 = false,
                tab_4 = false;

            function load_tab_content(selectedTab){
                if(selectedTab == "#tab-1" && tab_1 == false){
                    tab_1 = true;

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
            $('#blog_detail_tabs2 a').on('shown.bs.tab', function(event){
                var selectedTab = $(event.target).attr("href");
                load_tab_content(selectedTab);
            });

            // Just install the related tab content When the page is first loaded
            active_tab = $('#blog_detail_tabs2 li.active').attr("tab");
            if( !(active_tab == "" || active_tab == null) )
                load_tab_content(active_tab);
            else
                $("#blog_detail_tabs2 a:first").trigger('click');

        });
        function validate_save_op(){

            $("#content_hidden").text($("#content").code());
            $("#add_new_blog_form").parsley();
        }
    </script>

@endsection

@section('page_document_ready')





@endsection