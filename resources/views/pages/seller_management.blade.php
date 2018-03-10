@extends('layouts.master')

@section('title')
    {{ trans('seller_management.title') }}
@endsection

@section('page_level_css')
    {!! $UserDataTableObj->css() !!}


@endsection

@section('content')
    <?php
    $seller_logo_hdn_value = "not_changed";
    ?>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_add_new_user" style="display:none;">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5 id="modal_title"> {{ trans('seller_management.add_new_user') }}</h5>
                        <div class="ibox-tools">
                            <a class="" onclick="cancel_add_new_form('#div_seller_dataTable','#div_add_new_user');">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">


                        <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/seller_management/add') }}" id="add_new_seller_form">
                            {{ csrf_field() }}



                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('seller_management.name') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_seller_name" name="new_seller_name" required minlength="3" maxlength="255">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('seller_management.email') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="email" placeholder="" class="form-control" id="new_seller_email" name="new_seller_email" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label id="password_label_for_new" class="col-sm-3 control-label"> {{ trans('seller_management.password') }} <span style="color:red;">*</span></label>

                                <label class="col-sm-3 control-label" id="password_label_for_edit" style="display: none;">
                                    {{ trans('seller_management.password') }}
                                    <a id="change_pass_icon" onclick="open_edit_password();" href="javascript:void(1);">
                                        <i class="fa fa-lock fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a id="cancel_pass_icon" style="display:none;" onclick="cancel_edit_password()" href="javascript:void(1);">
                                        <i class="fa fa-unlock fa-lg" aria-hidden="true"></i>
                                    </a>
                                </label>

                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_seller_password" name="new_seller_password" minlength="6" maxlength="20" required />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('seller_management.gsm_phone') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="number" placeholder="" class="form-control" id="new_seller_gsm_phone" name="new_seller_gsm_phone" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('seller_detail.phone') }} </label>
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" id="new_seller_phone" name="new_seller_phone">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('seller_detail.province') }} </label>
                                <div class="col-sm-6">
                                    <input type="number"  class="form-control" id="new_seller_province" name="new_seller_province" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('seller_detail.district') }} </label>
                                <div class="col-sm-6">
                                    <input type="number"  class="form-control" id="new_seller_district" name="new_seller_district" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"> {{ trans('seller_detail.location') }} <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                     <textarea class="form-control" id="new_seller_location" name="new_seller_location">

                                    </textarea>
                                </div>
                            </div>


                            <input type="hidden" value="new" id="seller_op_type" name="seller_op_type">
                            <input type="hidden" value="" id="seller_edit_id" name="seller_edit_id">

                            <div class="form-group">
                                <div class="col-lg-4 col-lg-offset-3">
                                    <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                        <i class="fa fa-times"></i> {{ trans('seller_management.cancel') }} </button>
                                    <button type="submit" class="btn btn-primary" id="save_seller_button" name="save_seller_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('seller_management.save') }}</button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="div_seller_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("seller_management.title") }}</h5>
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
            $("#add_new_seller_form").parsley().reset();
            $("#add_new_seller_form").parsley();
        }

        function open_edit_password(){
            $('#new_seller_password').removeAttr('disabled');
            $('#new_seller_password').attr('required', '');
            $('#change_pass_icon').hide();
            $('#cancel_pass_icon').show();
        }

        function cancel_edit_password(){
            $('#new_seller_password').attr('disabled', '');
            $('#new_seller_password').removeAttr('required');
            $('#cancel_pass_icon').hide();
            $('#change_pass_icon').show();
        }

        function cancel_add_new_form(){
            $("#add_new_seller_form .form-control").val("");
            $(".parsley-errors-list").remove();

            $("#new_seller_logo").fileinput('refresh');
            $("#div_add_new_user").hide();
            $("#div_seller_dataTable").show();
        }

        function show_add_new_form(){
            $("#modal_title").html("{{ trans('seller_management.add_new_user') }}");
            $("#seller_op_type").val("new");
            $("#seller_edit_id").val("");



            $("#password_label_for_edit").hide();
            $("#password_label_for_new").show();

            $("#new_seller_password").removeAttr('disabled');
            $('#new_seller_password').attr('required', '');

            $("#new_seller_type").select2({
                minimumResultsForSearch: Infinity
            }).val(4).trigger("change");

            $('#save_seller_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("seller_management.save")}}');

            $("#div_add_new_user").show();
            $("#div_seller_dataTable").hide();
        }

        function edit_seller(id){
            $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

            $("#modal_title").html("{{ trans('seller_management.update_title') }}");
            $("#seller_op_type").val("edit");
            $("#seller_edit_id").val(id);

            $.ajax({
                method:"POST",
                url:"/seller_management/get_info",
                data:"id="+id,
                async:false,
                success:function(return_value){
                    if( $.trim(return_value) != 'NEXIST' || $.trim(return_value) != "ERROR" ){
                        the_info = JSON.parse(return_value);

                        $("#new_seller_name").val(the_info["name"]);
                        $("#new_seller_email").val(the_info["email"]);
                        $("#new_seller_phone").val(the_info["phone"]);
                        $("#new_seller_gsm_phone").val(the_info["gsm_phone"]);
                        $("#new_seller_province").val(the_info["province"]);
                        $("#new_seller_district").val(the_info["district"]);
                        $("#new_seller_location").val(the_info["location"]);

                        $("#new_seller_password").val("");
                        $('#new_seller_password').removeAttr('required');
                        $("#new_seller_password").attr('disabled','disabled');



                        $("#password_label_for_new").hide();
                        $("#password_label_for_edit").show();

                        $("#cancel_pass_icon").hide();
                        $("#change_pass_icon").show();

                        $('#save_seller_button').html('<i class="fa fa-refresh"></i> {{trans("seller_management.update")}}');
                        $("#div_seller_dataTable").hide();
                        $("#div_add_new_user").show();

                        $("#bg_block_screen").remove();

                        $("#new_seller_name").focus();
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
        @if (session()->has('new_seller_insert_success') && session('new_seller_insert_success'))
            {{ session()->forget('new_seller_insert_success') }}

            custom_toastr('{{ trans('seller_management.add_new_success') }}');
        @endif

        @if (session()->has('seller_update_success') && session('seller_update_success'))
            {{ session()->forget('seller_update_success') }}

            custom_toastr('{{ trans('seller_management.update_success') }}');
        @endif

        $("#new_seller_type").select2({
        minimumResultsForSearch: Infinity
        });

        $("#new_seller_type").change(function(){
        the_val = $.trim($(this).val());

        if( the_val == 3 ){
        $('#new_seller_clients').closest('.form-group').hide();
        $('#new_seller_distributors').closest('.form-group').show(600);
        $('#new_seller_clients').removeAttr('required');
        $('#new_seller_distributors').attr('required', '');
        }
        else if( the_val == 4 ){
        $('#new_seller_distributors').closest('.form-group').hide();
        $('#new_seller_clients').closest('.form-group').show(600);
        $('#new_seller_distributors').removeAttr('required');
        $('#new_seller_clients').attr('required', '');
        }
        else{
        $('#new_seller_distributors').closest('.form-group').hide();
        $('#new_seller_clients').closest('.form-group').hide();
        $('#new_seller_clients').removeAttr('required');
        $('#new_seller_distributors').removeAttr('required');
        }
        });

        $("#new_seller_clients").select2({
        minimumResultsForSearch: 10
        });

        $("#new_seller_distributors").select2({
        minimumResultsForSearch: 10
        });

        $("#new_seller_logo").fileinput({
        uploadUrl: "/seller_management/upload_image",
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
        elErrorContainer: '#new_seller_logo_error',
        msgErrorClass: 'alert alert-block alert-danger',
        initialPreview: [
        "<img id='edit_seller_logo' src='/img/avatar/user/no_avatar.png' class='file-preview-image' style='height:160px'>",
        ],
        initialCaption: " {{ trans('seller_management.select_seller_logo') }}"
        }).on("filebatchselected", function(event, files) {// trigger upload method immediately after files are selected
        $("#new_seller_logo").fileinput("upload");
        $('#uploaded_image_name').val($('#new_seller_logo').prop("files")[0]['name']);
        }).on("filecleared", function(event) {
        $("#hidden_seller_logo").val("not_changed");
        $("#uploaded_image_name").val("");
        $('#new_seller_logo').fileinput('refresh');
        $('#edit_seller_logo').attr('src',$('#edit_image_name').val());
        }).on('filebatchuploadcomplete', function(event, files, extra) {
        $("#hidden_seller_logo").val("changed");
        $(".file-thumbnail-footer").hide();
        });
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_user"))
        @if (session()->has('seller_delete_success') && session('seller_delete_success'))
            {{ session()->forget('seller_delete_success') }}

            custom_toastr('{{ trans('seller_management.seller_delete_success') }}');
        @endif
    @endif
@endsection