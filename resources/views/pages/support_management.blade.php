<?php

$client_name = DB::table('clients')
    ->where("status",'<>', 0)
    ->where("type",'=', 1)
    ->orderBy('id')
    ->get();
$user_date = DB::table('users')
    ->where("status",'<>', 0)
    ->orderBy('name')
    ->get();
$client_date = DB::table('clients')
    ->where("status",'<>', 0)
    ->orderBy('name')
    ->get();
$support_category = DB::table('support_category')
    ->orderBy('id')
    ->get();
?>

@extends('layouts.master')

@section('title')
    {{ trans('booking_management.title') }}
@endsection

@section('page_level_css')

    {!! $SupportDataTableObj->css() !!}
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">

        <div class="row" id="div_add_new_support" style="display:none;">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5 id="modal_title"> {{ trans('user_management.add_new_user') }}</h5>
                        <div class="ibox-tools">
                            <a class="" onclick="cancel_add_new_form('#div_support_dataTable','#div_add_new_support');">
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

                        <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/support/add') }}" id="add_new_support_form">
                        {{ csrf_field() }}



                            <div class="form-group">
                                <label class="col-sm-3 control-label"> Destek Başlığı <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_support_title" name="new_support_title" required minlength="3" maxlength="255">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Kategori <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="support_selected_category" name="support_selected_category" class="form-control" style="width: 100%;">
                                        <option value="0"></option>
                                        @foreach($support_category as $one_list)
                                            <option value="{{ $one_list->id }}">{{ $one_list->name }}</option>
                                        @endforeach
                                    </select></div>

                                <br>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Müşteri <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="selected_client_id" name="selected_client_id" class="form-control" style="width: 100%;">
                                        <option value="0"></option>
                                        @foreach($client_date as $one_list)
                                            <option value="{{ $one_list->id }}">{{ $one_list->name }}</option>
                                        @endforeach
                                    </select></div>

                                <br>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Atanan <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="selected_interested_id" name="selected_interested_id" class="form-control" style="width: 100%;">
                                        <option value="0"></option>
                                        @foreach($user_date as $one_list)
                                            <option value="{{ $one_list->id }}">{{ $one_list->name }}</option>
                                        @endforeach
                                    </select></div>

                                <br>
                            </div>
                            {!!  Helper::get_status("support_status", false) !!}


                            <input type="hidden" value="new" id="support_op_type" name="support_op_type">
                            <input type="hidden" value="" id="support_edit_id" name="support_edit_id">

                            <div class="form-group">
                                <div class="col-lg-4 col-lg-offset-3">
                                    <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                        <i class="fa fa-times"></i> {{ trans('user_management.cancel') }} </button>
                                    <button type="submit" class="btn btn-primary" id="save_support_button" name="save_support_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('user_management.save') }}</button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </div>



            <div class="row" id="div_support_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("booking_management.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
                        {!! $SupportDataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .wrapper -->

@endsection

@section('page_level_js')
    {!! $SupportDataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        function validate_save_op(){
            $("#add_new_support_form").parsley().reset();
            $("#add_new_suppoer_form").parsley();
        }



        function cancel_add_new_form(){
            $("#add_new_support_form .form-control").val("");
            $(".parsley-errors-list").remove();


            $("#div_add_new_support").hide();
            $("#div_support_dataTable").show();
        }

        function show_add_new_form(){
            $("#modal_title").html("Yeni Talep Oluştur");
            $("#user_op_type").val("new");
            $("#user_edit_id").val("");



            $("#password_label_for_edit").hide();
            $("#password_label_for_new").show();

            $("#new_user_password").removeAttr('disabled');
            $('#new_user_password').attr('required', '');

            $("#new_user_type").select2({
                minimumResultsForSearch: Infinity
            }).val(4).trigger("change");

            $('#save_user_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("user_management.save")}}');

            $("#div_add_new_support").show();
            $("#div_support_dataTable").hide();
        }

        function edit_user(id){
            $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

            $("#modal_title").html("Talebi düzenle");
            $("#user_op_type").val("edit");
            $("#user_edit_id").val(id);

            $.ajax({
                method:"POST",
                url:"/support/get_info",
                data:"id="+id,
                async:false,
                success:function(return_value){
                    if( $.trim(return_value) != 'NEXIST' || $.trim(return_value) != "ERROR" ){
                        the_info = JSON.parse(return_value);

                        $("#new_user_name").val(the_info["name"]);
                        $("#new_user_email").val(the_info["email"]);

                        $("#new_user_password").val("");
                        $('#new_user_password').removeAttr('required');
                        $("#new_user_password").attr('disabled','disabled');

                        $("#new_user_type").select2({
                            minimumResultsForSearch: Infinity
                        }).val(the_info["user_type"]).trigger("change");

                        if( the_info["user_type"] == 3 ){
                            $("#new_user_distributors").show();
                            $('#new_user_clients').removeAttr('required');
                            $('#new_user_distributors').attr('required', '');
                            $("#new_user_distributors").select2({
                                minimumResultsForSearch: 10
                            }).val(the_info["org_id"]).trigger("change");
                        }
                        else if( the_info["user_type"] == 4 ){
                            $("#new_user_clients").show();
                            $('#new_user_distributors').removeAttr('required');
                            $('#new_user_clients').attr('required', '');
                            $("#new_user_clients").select2({
                                minimumResultsForSearch: 10
                            }).val(the_info["org_id"]).trigger("change");
                        }
                        else{
                            $("#new_user_clients").hide();
                            $('#new_user_clients').removeAttr('required');
                            $("#new_user_distributors").hide();
                            $('#new_user_distributors').removeAttr('required');
                        }

                        $('#edit_user_logo').attr('src', '/img/avatar/user/' + the_info["avatar"]);
                        $('#edit_image_name').val('/img/avatar/user/' + the_info["avatar"]);

                        $("#password_label_for_new").hide();
                        $("#password_label_for_edit").show();

                        $("#cancel_pass_icon").hide();
                        $("#change_pass_icon").show();

                        $('#save_user_button').html('<i class="fa fa-refresh"></i> {{trans("user_management.update")}}');
                        $("#div_support_dataTable").hide();
                        $("#div_add_new_support").show();

                        $("#bg_block_screen").remove();

                        $("#new_user_name").focus();
                    }
                    else{
                        $("#bg_block_screen").remove();

                        alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                    }
                }
            });
        }
        @endif
    </script>
@endsection

@section('page_document_ready')
    {!! $SupportDataTableObj->ready() !!}
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        @if (session()->has('new_support_insert_success') && session('new_support_insert_success'))
            {{ session()->forget('new_support_insert_success') }}

            custom_toastr('Talep oluşturma başarılı');
        @endif

        @if (session()->has('support_update_success') && session('support_update_success'))
            {{ session()->forget('support_update_success') }}

            custom_toastr('Talep güncelleme başarılı.');
        @endif

    @endif


@endsection