@extends('layouts.master')

@section('title')
    {{ trans('client_management.title') }}
@endsection

@section('page_level_css')
    {!! $UserDataTableObj->css() !!}


@endsection

@section('content')
    <?php
    $client_logo_hdn_value = "not_changed";
    ?>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_add_new_user" style="display:none;">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5 id="modal_title"> {{ trans('client_management.add_new_user') }}</h5>
                        <div class="ibox-tools">
                            <a class="" onclick="cancel_add_new_form('#div_client_dataTable','#div_add_new_user');">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">


                        <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/client_management/add') }}" id="add_new_client_form">
                        {{ csrf_field() }}



                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('client_management.name') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_client_name" name="new_client_name" required minlength="3" maxlength="255">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('client_management.email') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="email" placeholder="" class="form-control" id="new_client_email" name="new_client_email" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label id="password_label_for_new" class="col-sm-3 control-label"> {{ trans('client_management.password') }} <span style="color:red;">*</span></label>

                                <label class="col-sm-3 control-label" id="password_label_for_edit" style="display: none;">
                                    {{ trans('client_management.password') }}
                                    <a id="change_pass_icon" onclick="open_edit_password();" href="javascript:void(1);">
                                        <i class="fa fa-lock fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a id="cancel_pass_icon" style="display:none;" onclick="cancel_edit_password()" href="javascript:void(1);">
                                        <i class="fa fa-unlock fa-lg" aria-hidden="true"></i>
                                    </a>
                                </label>

                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_client_password" name="new_client_password" minlength="6" maxlength="20" required />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('client_management.gsm_phone') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="number" placeholder="" class="form-control" id="new_client_gsm_phone" name="new_client_gsm_phone" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('client_detail.phone') }} </label>
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" id="new_client_phone" name="new_client_phone">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('client_detail.province') }} </label>
                                <div class="col-sm-6">
                                    <input type="number"  class="form-control" id="new_client_province" name="new_client_province" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('client_detail.district') }} </label>
                                <div class="col-sm-6">
                                    <input type="number"  class="form-control" id="new_client_district" name="new_client_district" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('client_detail.location') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                     <textarea class="form-control" id="new_client_location" name="new_client_location">

                                    </textarea>
                                </div>
                            </div>


                            <input type="hidden" value="new" id="client_op_type" name="client_op_type">
                            <input type="hidden" value="" id="client_edit_id" name="client_edit_id">

                            <div class="form-group">
                                <div class="col-lg-4 col-lg-offset-3">
                                    <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                        <i class="fa fa-times"></i> {{ trans('client_management.cancel') }} </button>
                                    <button type="submit" class="btn btn-primary" id="save_client_button" name="save_client_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('client_management.save') }}</button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="div_client_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("client_management.title") }}</h5>
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
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        function validate_save_op(){
            $("#add_new_client_form").parsley().reset();
            $("#add_new_client_form").parsley();
        }

        function open_edit_password(){
            $('#new_client_password').removeAttr('disabled');
            $('#new_client_password').attr('required', '');
            $('#change_pass_icon').hide();
            $('#cancel_pass_icon').show();
        }

        function cancel_edit_password(){
            $('#new_client_password').attr('disabled', '');
            $('#new_client_password').removeAttr('required');
            $('#cancel_pass_icon').hide();
            $('#change_pass_icon').show();
        }

        function cancel_add_new_form(){
            $("#add_new_client_form .Form-control").val("");
            $(".parsley-errors-list").remove();

            $("#new_client_logo").fileinput('refresh');
            $("#div_add_new_user").hide();
            $("#div_client_dataTable").show();
        }

        function show_add_new_form(){
            $("#modal_title").html("{{ trans('client_management.add_new_user') }}");
            $("#client_op_type").val("new");
            $("#client_edit_id").val("");



            $("#password_label_for_edit").hide();
            $("#password_label_for_new").show();

            $("#new_client_password").removeAttr('disabled');
            $('#new_client_password').attr('required', '');

            $("#new_client_type").select2({
                minimumResultsForSearch: Infinity
            }).val(4).trigger("change");

            $('#save_client_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("client_management.save")}}');

            $("#div_add_new_user").show();
            $("#div_client_dataTable").hide();
        }

        function edit_client(id){
            $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

            $("#modal_title").html("{{ trans('client_management.update_title') }}");
            $("#client_op_type").val("edit");
            $("#client_edit_id").val(id);

            $.ajax({
                method:"POST",
                url:"/client_management/get_info",
                data:"id="+id,
                async:false,
                success:function(return_value){
                    if( $.trim(return_value) != 'NEXIST' || $.trim(return_value) != "ERROR" ){
                        the_info = JSON.parse(return_value);

                        $("#new_client_name").val(the_info["name"]);
                        $("#new_client_email").val(the_info["email"]);
                        $("#new_client_name").val(the_info["name"]);
                        $("#new_client_email").val(the_info["email"]);
                        $("#new_client_phone").val(the_info["phone"]);
                        $("#new_client_gsm_phone").val(the_info["gsm_phone"]);
                        $("#new_client_province").val(the_info["province"]);
                        $("#new_client_district").val(the_info["district"]);
                        $("#new_client_location").val(the_info["location"]);

                        $("#new_client_password").val("");
                        $('#new_client_password').removeAttr('required');
                        $("#new_client_password").attr('disabled','disabled');

                        $("#new_client_type").select2({
                            minimumResultsForSearch: Infinity
                        }).val(the_info["client_type"]).trigger("change");


                        $("#password_label_for_new").hide();
                        $("#password_label_for_edit").show();

                        $("#cancel_pass_icon").hide();
                        $("#change_pass_icon").show();

                        $('#save_client_button').html('<i class="fa fa-refresh"></i> {{trans("client_management.update")}}');
                        $("#div_client_dataTable").hide();
                        $("#div_add_new_user").show();

                        $("#bg_block_screen").remove();

                        $("#new_client_name").focus();
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
    {!! $UserDataTableObj->ready() !!}
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        @if (session()->has('new_client_insert_success') && session('new_client_insert_success'))
            {{ session()->forget('new_client_insert_success') }}

            custom_toastr('{{ trans('client_management.add_new_success') }}');
        @endif

        @if (session()->has('client_update_success') && session('client_update_success'))
            {{ session()->forget('client_update_success') }}

            custom_toastr('{{ trans('client_management.update_success') }}');
        @endif

        $("#new_client_type").select2({
        minimumResultsForSearch: Infinity
        });

        $("#new_client_type").change(function(){
        the_val = $.trim($(this).val());

        if( the_val == 3 ){
        $('#new_client_clients').closest('.form-group').hide();
        $('#new_client_distributors').closest('.form-group').show(600);
        $('#new_client_clients').removeAttr('required');
        $('#new_client_distributors').attr('required', '');
        }
        else if( the_val == 4 ){
        $('#new_client_distributors').closest('.form-group').hide();
        $('#new_client_clients').closest('.form-group').show(600);
        $('#new_client_distributors').removeAttr('required');
        $('#new_client_clients').attr('required', '');
        }
        else{
        $('#new_client_distributors').closest('.form-group').hide();
        $('#new_client_clients').closest('.form-group').hide();
        $('#new_client_clients').removeAttr('required');
        $('#new_client_distributors').removeAttr('required');
        }
        });

        $("#new_client_clients").select2({
        minimumResultsForSearch: 10
        });

        $("#new_client_distributors").select2({
        minimumResultsForSearch: 10
        });

        $("#new_client_logo").fileinput({
        uploadUrl: "/client_management/upload_image",
        language: '{{App::getLocale()}}',
        showUpload: false,
        showClose: false,
        uploadClass: false,
        showUploadedThumbs: false,
        browseClass: "btn btn-success",
        removeClass: "btn btn-danger",
        maxFileCount: 1,
        maxFileSize: 2048, // 2 MB
        allowedFileExtensions: ["jpg", "gif", "png", "jpeg"],
        elErrorContainer: '#new_client_logo_error',
        msgErrorClass: 'alert alert-block alert-danger',
        initialPreview: [
        "<img id='edit_client_logo' src='/img/avatar/user/no_avatar.png' class='file-preview-image' style='height:160px'>",
        ],
        initialCaption: " {{ trans('client_management.select_client_logo') }}"
        }).on("filebatchselected", function(event, files) {// trigger upload method immediately after files are selected
        $("#new_client_logo").fileinput("upload");
        $('#uploaded_image_name').val($('#new_client_logo').prop("files")[0]['name']);
        }).on("filecleared", function(event) {
        $("#hidden_client_logo").val("not_changed");
        $("#uploaded_image_name").val("");
        $('#new_client_logo').fileinput('refresh');
        $('#edit_client_logo').attr('src',$('#edit_image_name').val());
        }).on('filebatchuploadcomplete', function(event, files, extra) {
        $("#hidden_client_logo").val("changed");
        $(".file-thumbnail-footer").hide();
        });
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_user"))
        @if (session()->has('client_delete_success') && session('client_delete_success'))
            {{ session()->forget('client_delete_success') }}

            custom_toastr('{{ trans('client_management.client_delete_success') }}');
        @endif
    @endif
@endsection