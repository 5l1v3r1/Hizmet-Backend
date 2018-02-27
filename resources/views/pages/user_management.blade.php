@extends('layouts.master')

@section('title')
    {{ trans('user_management.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}
@endsection

@section('content')
    <?php
        $user_logo_hdn_value = "not_changed";
    ?>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_add_new_user" style="display:none;">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5 id="modal_title"> {{ trans('user_management.add_new_user') }}</h5>
                        <div class="ibox-tools">
                            <a class="" onclick="cancel_add_new_form('#div_user_dataTable','#div_add_new_user');">
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

                        <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/user_management/add') }}" id="add_new_user_form">
                            {{ csrf_field() }}

                            <!-- get selectable user_type according to user type -->
                            {!!  Helper::get_user_type_select("new_user_type") !!}

                            <!-- get selectable clients according to user type -->
                            {!!  Helper::get_clients_select("new_user_clients", false) !!}

                            <!-- get selectable distributors according to user type -->
                            {!!  Helper::get_distributors_select("new_user_distributors",true) !!}

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('user_management.name') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_user_name" name="new_user_name" required minlength="3" maxlength="255">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('user_management.email') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="email" placeholder="" class="form-control" id="new_user_email" name="new_user_email" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label id="password_label_for_new" class="col-sm-3 control-label"> {{ trans('user_management.password') }} <span style="color:red;">*</span></label>

                                <label class="col-sm-3 control-label" id="password_label_for_edit" style="display: none;">
                                    {{ trans('user_management.password') }}
                                    <a id="change_pass_icon" onclick="open_edit_password();" href="javascript:void(1);">
                                        <i class="fa fa-lock fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a id="cancel_pass_icon" style="display:none;" onclick="cancel_edit_password()" href="javascript:void(1);">
                                        <i class="fa fa-unlock fa-lg" aria-hidden="true"></i>
                                    </a>
                                </label>

                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_user_password" name="new_user_password" minlength="6" maxlength="20" required />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label">{{ trans('user_management.logo') }}</label>
                                <div class="col-lg-6">
                                    <input class="file-loading" type="file" id="new_user_logo" name="new_user_logo">
                                    <input type="hidden" value="<?=$user_logo_hdn_value;?>" name="hidden_user_logo" id="hidden_user_logo" />
                                    <input type="hidden" value="" name="uploaded_image_name" id="uploaded_image_name" />
                                    <input type="hidden" value="/img/avatar/user/no_avatar.png" name="edit_image_name" id="edit_image_name" />
                                    <div id="new_user_logo_error" class="help-block"></div>
                                </div>
                            </div>

                            <input type="hidden" value="new" id="user_op_type" name="user_op_type">
                            <input type="hidden" value="" id="user_edit_id" name="user_edit_id">

                            <div class="form-group">
                                <div class="col-lg-4 col-lg-offset-3">
                                    <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                        <i class="fa fa-times"></i> {{ trans('user_management.cancel') }} </button>
                                    <button type="submit" class="btn btn-primary" id="save_user_button" name="save_user_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('user_management.save') }}</button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="div_user_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("user_management.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
                        {!! $DataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .wrapper -->
@endsection

@section('page_level_js')
    {!! $DataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
            function validate_save_op(){
                $("#add_new_user_form").parsley().reset();
                $("#add_new_user_form").parsley();
            }

            function open_edit_password(){
                $('#new_user_password').removeAttr('disabled');
                $('#new_user_password').attr('required', '');
                $('#change_pass_icon').hide();
                $('#cancel_pass_icon').show();
            }

            function cancel_edit_password(){
                $('#new_user_password').attr('disabled', '');
                $('#new_user_password').removeAttr('required');
                $('#cancel_pass_icon').hide();
                $('#change_pass_icon').show();
            }

            function cancel_add_new_form(){
                $("#add_new_user_form .form-control").val("");
                $(".parsley-errors-list").remove();

                $("#new_user_logo").fileinput('refresh');
                $("#div_add_new_user").hide();
                $("#div_user_dataTable").show();
            }

            function show_add_new_form(){
                $("#modal_title").html("{{ trans('user_management.add_new_user') }}");
                $("#user_op_type").val("new");
                $("#user_edit_id").val("");

                @if(Auth::user()->user_type == 3)
                    if( !($('#new_user_clients > option').length>0)){
                        $('#add_new_user_form').hide();
                        $('#add_new_user_warning').show();
                        $("#div_add_new_user").show();
                        $("#div_user_dataTable").hide();
                        return;
                    }
                @elseif(Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                    if(!($('#new_user_clients > option').length>0 || $('#new_user_distributors > option').length>0)){
                        $('#add_new_user_form').hide();
                        $('#add_new_user_warning').show();
                        $("#div_add_new_user").show();
                        $("#div_user_dataTable").hide();
                        return;
                    }
                @endif

                $("#password_label_for_edit").hide();
                $("#password_label_for_new").show();

                $("#new_user_password").removeAttr('disabled');
                $('#new_user_password').attr('required', '');

                $("#new_user_type").select2({
                    minimumResultsForSearch: Infinity
                }).val(4).trigger("change");

                $('#save_user_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("user_management.save")}}');

                $("#div_add_new_user").show();
                $("#div_user_dataTable").hide();
            }

            function edit_user(id){
                $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

                $("#modal_title").html("{{ trans('user_management.update_title') }}");
                $("#user_op_type").val("edit");
                $("#user_edit_id").val(id);

                $.ajax({
                    method:"POST",
                    url:"/user_management/get_info",
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
                            $("#div_user_dataTable").hide();
                            $("#div_add_new_user").show();

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
    {!! $DataTableObj->ready() !!}

    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        @if (session()->has('new_user_insert_success') && session('new_user_insert_success'))
            {{ session()->forget('new_user_insert_success') }}

            custom_toastr('{{ trans('user_management.add_new_success') }}');
        @endif

        @if (session()->has('user_update_success') && session('user_update_success'))
            {{ session()->forget('user_update_success') }}

            custom_toastr('{{ trans('user_management.update_success') }}');
        @endif

        $("#new_user_type").select2({
            minimumResultsForSearch: Infinity
        });

        $("#new_user_type").change(function(){
            the_val = $.trim($(this).val());

            if( the_val == 3 ){
                $('#new_user_clients').closest('.form-group').hide();
                $('#new_user_distributors').closest('.form-group').show(600);
                $('#new_user_clients').removeAttr('required');
                $('#new_user_distributors').attr('required', '');
            }
            else if( the_val == 4 ){
                $('#new_user_distributors').closest('.form-group').hide();
                $('#new_user_clients').closest('.form-group').show(600);
                $('#new_user_distributors').removeAttr('required');
                $('#new_user_clients').attr('required', '');
            }
            else{
                $('#new_user_distributors').closest('.form-group').hide();
                $('#new_user_clients').closest('.form-group').hide();
                $('#new_user_clients').removeAttr('required');
                $('#new_user_distributors').removeAttr('required');
            }
        });

        $("#new_user_clients").select2({
            minimumResultsForSearch: 10
        });

        $("#new_user_distributors").select2({
            minimumResultsForSearch: 10
        });

        $("#new_user_logo").fileinput({
            uploadUrl: "/user_management/upload_image",
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
            elErrorContainer: '#new_user_logo_error',
            msgErrorClass: 'alert alert-block alert-danger',
            initialPreview: [
                "<img id='edit_user_logo' src='/img/avatar/user/no_avatar.png' class='file-preview-image' style='height:160px'>",
            ],
            initialCaption: " {{ trans('user_management.select_user_logo') }}"
        }).on("filebatchselected", function(event, files) {// trigger upload method immediately after files are selected
            $("#new_user_logo").fileinput("upload");
            $('#uploaded_image_name').val($('#new_user_logo').prop("files")[0]['name']);
        }).on("filecleared", function(event) {
            $("#hidden_user_logo").val("not_changed");
            $("#uploaded_image_name").val("");
            $('#new_user_logo').fileinput('refresh');
            $('#edit_user_logo').attr('src',$('#edit_image_name').val());
        }).on('filebatchuploadcomplete', function(event, files, extra) {
            $("#hidden_user_logo").val("changed");
            $(".file-thumbnail-footer").hide();
        });
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_user"))
        @if (session()->has('user_delete_success') && session('user_delete_success'))
            {{ session()->forget('user_delete_success') }}

            custom_toastr('{{ trans('user_management.user_delete_success') }}');
        @endif
    @endif
@endsection