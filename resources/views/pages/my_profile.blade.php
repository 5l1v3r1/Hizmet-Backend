@extends('layouts.master')

@section('title')
    {{ trans('my_profile.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all" />
    {!! $EventDataTableObj->css() !!}
@endsection

@section('content')
    <?php
        $the_user = json_decode($the_user);

        if( $the_user->user_type == 3 || $the_user->user_type == 4 ){
            $the_organization = json_decode($the_organization);
        }

        $user_logo_hdn_value = "not_changed";
        $distributor_logo_hdn_value = "not_changed";
        $client_logo_hdn_value = "not_changed";
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_user_summary">
            <div class="col-md-6">
                <div class="profile-image">
                    <img src="/img/avatar/user/{{$the_user->avatar}}" class="img-circle circle-border m-b-md" alt="profile">
                </div>
                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_user->name }}
                        </h2>
                        <p style="margin: 10px 0 0;">
                            {{ trans('my_profile.user_type') }}:  <strong> {{ trans("global.".$the_user->type) }} </strong>

                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('my_profile.organization') }}:
                            <strong>{{ $the_user->org_name }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <table class="table small m-b-xs">
                    <tbody>
                    <tr>
                        <td>
                            <strong>{{ trans('my_profile.email') }}</strong>
                        </td>
                        <td>
                            {{ $the_user->email }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('my_profile.created_by') }}</strong>
                        </td>
                        <td>
                            {{ $the_user->created_by }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('my_profile.created_at') }}</strong>
                        </td>
                        <td>
                            {{ $the_user->created_at }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- #div_user_summary -->

        <div class="row" id="div_user_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="my_profile_tabs">
                        @if (Helper::has_right(Auth::user()->operations, "edit_profile_info"))
                            <li class="" tab="#tab-1">
                                <a data-toggle="tab" href="#tab-1" aria-expanded="true">
                                    <i class="fa fa-user-circle-o fa-lg" aria-hidden="true"></i>
                                    {{ trans('my_profile.account_info') }}
                                </a>
                            </li>
                            @if( $the_user->user_type != 1 && $the_user->user_type != 2 )
                                <li class="" tab="#tab-2">
                                    <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                        <i class="fa fa-building-o fa-lg" aria-hidden="true"></i>
                                        {{ trans('my_profile.organization_info') }}
                                    </a>
                                </li>
                            @endif
                        @endif
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-history fa-lg" aria-hidden="true"></i>
                                {{ trans('my_profile.event_logs') }}
                            </a>
                        </li>
                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        @if( Helper::has_right(Auth::user()->operations, "edit_profile_info")  )
                            <div id="tab-1" class="tab-pane">
                                <div class="panel-body">
                                    <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/my_profile/edit/account') }}" id="add_new_user_form">
                                        {{ csrf_field() }}

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('user_management.name') }} <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <input type="text" value="{{ $the_user->name }}" class="form-control" id="new_user_name" name="new_user_name" required minlength="3" maxlength="255" />
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('user_management.email') }} <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <input type="email" value="{{$the_user->email}}" class="form-control" id="new_user_email" name="new_user_email" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label" id="password_label_for_edit">
                                                {{ trans('user_management.password') }}
                                                <a id="change_pass_icon" onclick="open_edit_password();" href="javascript:void(1);">
                                                    <i class="fa fa-lock fa-lg" aria-hidden="true"></i>
                                                </a>
                                                <a id="cancel_pass_icon" style="display:none;" onclick="cancel_edit_password()" href="javascript:void(1);">
                                                    <i class="fa fa-unlock fa-lg" aria-hidden="true"></i>
                                                </a>
                                            </label>

                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" value="" id="new_user_password" name="new_user_password" minlength="6" maxlength="20" disabled />
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-lg-3 control-label">{{ trans('user_management.logo') }}</label>
                                            <div class="col-lg-6">
                                                <input class="file-loading" type="file" id="new_user_logo" name="new_user_logo">
                                                <input type="hidden" value="{{$user_logo_hdn_value}}" name="hidden_user_logo" id="hidden_user_logo" />
                                                <input type="hidden" value="" name="uploaded_image_name" id="uploaded_image_name" />
                                                <input type="hidden" value="/img/avatar/user/{{$the_user->avatar}}" name="edit_image_name" id="edit_image_name" />
                                                <div id="new_user_logo_error" class="help-block"></div>
                                            </div>
                                        </div>

                                        <input type="hidden" value="edit" id="user_op_type" name="user_op_type">
                                        <input type="hidden" value="{{$the_user->id}}" id="user_edit_id" name="user_edit_id">

                                        <div class="form-group">
                                            <div class="col-lg-4 col-lg-offset-3">
                                                <button type="submit" class="btn btn-primary" id="save_user_button" name="save_user_button" onclick="return validate_save_op();">
                                                    <i class="fa fa-refresh"></i> {{ trans('user_management.update') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div> <!-- .tab-1 -->
                        @endif

                        @if( $the_user->user_type != 1 && $the_user->user_type != 2 && Helper::has_right(Auth::user()->operations, "edit_profile_info")  )
                            <div id="tab-2" class="tab-pane">
                                <div class="panel-body">
                                    @if( $the_user->user_type == 3)
                                        <div class="row" id="div_add_new_distributor" style="">
                                            <div class="col-lg-12">
                                                <form class="m-t form-horizontal"  method="POST" action="{{ url('/distributor_management/edit_profile') }}" id="add_new_distributor_form">
                                                    {{ csrf_field() }}

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.name') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->name }}" class="form-control" id="new_distributor_name" name="new_distributor_name" required minlength="3" maxlength="255"/>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.authorized_name') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->authorized_name }}" class="form-control" id="new_distributor_authorized_name" name="new_distributor_authorized_name" minlength="3" maxlength="255" required />
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.email') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="email" value="{{ $the_organization->email }}" class="form-control" id="new_distributor_email" name="new_distributor_email" minlength="3" maxlength="255" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.gsm_phone') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->gsm_phone }}" class="form-control" id="new_distributor_gsm_phone" name="new_distributor_gsm_phone" minlength="10" maxlength="20" required>

                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.phone') }} </label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->phone }}" class="form-control" id="new_distributor_phone" name="new_distributor_phone" minlength="7" maxlength="20">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.fax') }} </label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->fax }}" class="form-control" id="new_distributor_fax" name="new_distributor_fax" minlength="7" maxlength="20">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.tax_administration') }} </label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->tax_administration }}" class="form-control" id="new_distributor_tax_administration" name="new_distributor_tax_administration" minlength="3" maxlength="255">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.tax_no') }}</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->tax_no }}" class="form-control" id="new_distributor_tax_no" name="new_distributor_tax_no" minlength="3" maxlength="30">

                                                        </div>
                                                    </div>

                                                    <div class="form-group" style="margin-bottom: 5px;">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_management.address') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="text" class="form-control" value="{{ $the_organization->location_text }}" id="new_distributor_location_text" name="new_distributor_location_text" required minlength="3" maxlength="255">
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="new_distributor_location_latitude" id="new_distributor_location_latitude" value="{{ $the_organization->location_latitude }}" />
                                                    <input type="hidden" name="new_distributor_location_longitude" id="new_distributor_location_longitude" value="{{ $the_organization->location_longitude }}"/>

                                                    <div class="form-group">
                                                        <div class="col-sm-6 col-sm-offset-3">
                                                            <div id="new_distributor_location" style="width: 100%; height: 300px;"></div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-lg-3 control-label">{{ trans('distributor_management.logo') }}</label>
                                                        <div class="col-lg-6">
                                                            <input class="file-loading" type="file" id="new_distributor_logo" name="new_distributor_logo">
                                                            <input type="hidden" value="<?=$distributor_logo_hdn_value;?>" name="hidden_distributor_logo" id="hidden_distributor_logo" />
                                                            <input type="hidden" value="" name="uploaded_distributor_image_name" id="uploaded_distributor_image_name" />
                                                            <input type="hidden" value="/img/avatar/distributor/{{ $the_organization->logo }}" name="edit_distributor_image_name" id="edit_distributor_image_name" />
                                                            <div id="new_distributor_logo_error" class="help-block"></div>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" value="edit" id="distributor_op_type" name="distributor_op_type">
                                                    <input type="hidden" value="yes" id="distributor_edit_profile" name="distributor_edit_profile">
                                                    <input type="hidden" value="{{ $the_organization->id }}" id="distributor_edit_id" name="distributor_edit_id">

                                                    <div class="form-group">
                                                        <div class="col-lg-4 col-lg-offset-3">
                                                            <button type="submit" class="btn btn-primary" id="save_distributor_button" name="save_distributor_button" onclick="return validate_distributor();"><i class="fa fa-thumbs-o-up"></i> {{ trans('distributor_management.update') }}</button>

                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    @if( $the_user->user_type == 4)
                                        <div class="row" id="div_add_new_client" style=";">
                                            <div class="col-lg-12">
                                                <form class="m-t form-horizontal"  method="POST" action="{{ url('/client_management/edit_profile') }}" id="add_new_client_form">
                                                {{ csrf_field() }}

                                                    <input type="hidden" name="new_client_distributor" id="new_client_distributor" value="{{ $the_organization->distributor_id }}" />

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('client_management.name') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->name }}" class="form-control" id="new_client_name" name="new_client_name" required minlength="3" maxlength="255"/>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('client_management.authorized_name') }} <span style="color:red;">*</span> </label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->authorized_name }}" class="form-control" id="new_client_authorized_name" name="new_client_authorized_name" minlength="3" maxlength="255" required />
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('client_management.email') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="email" value="{{ $the_organization->email }}" class="form-control" id="new_client_email" name="new_client_email" minlength="3" maxlength="255" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('client_management.gsm_phone') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->gsm_phone }}" class="form-control" id="new_client_gsm_phone" name="new_client_gsm_phone" minlength="10" maxlength="20" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('client_management.phone') }} </label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->phone }}" class="form-control" id="new_client_phone" name="new_client_phone" minlength="7" maxlength="20">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('client_management.fax') }} </label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->fax}}" class="form-control" id="new_client_fax" name="new_client_fax" minlength="7" maxlength="20">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('client_management.tax_administration') }} </label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->tax_administration}}" class="form-control" id="new_client_tax_administration" name="new_client_tax_administration" minlength="3" maxlength="255">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('client_management.tax_no') }}</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" value="{{ $the_organization->tax_no}}" class="form-control" id="new_client_tax_no" name="new_client_tax_no" minlength="3" maxlength="30">
                                                        </div>
                                                    </div>

                                                    <div class="form-group" style="margin-bottom: 5px;">
                                                        <label class="col-sm-3 control-label"> {{ trans('modem_management.location') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="text" class="form-control" id="new_client_location_text" name="new_client_location_text" required minlength="3" maxlength="255">
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="new_client_location_latitude" id="new_client_location_latitude" value=""/>
                                                    <input type="hidden" name="new_client_location_longitude" id="new_client_location_longitude" value=""/>

                                                    <div class="form-group">
                                                        <div class="col-sm-6 col-sm-offset-3">
                                                            <div id="new_client_location" style="width: 100%; height: 300px;"></div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-lg-3 control-label">{{ trans('client_management.logo') }}</label>
                                                        <div class="col-lg-6">
                                                            <input class="file-loading" type="file" id="new_client_logo" name="new_client_logo">
                                                            <input type="hidden" value="<?=$client_logo_hdn_value;?>" name="hidden_client_logo" id="hidden_client_logo" />
                                                            <input type="hidden" value="" name="uploaded_client_image_name" id="uploaded_client_image_name" />
                                                            <input type="hidden" value="/img/avatar/client/{{ $the_organization->logo}}" name="edit_client_image_name" id="edit_client_image_name" />
                                                            <div id="new_client_logo_error" class="help-block"></div>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" value="edit" id="client_op_type" name="client_op_type">
                                                    <input type="hidden" value="yes" id="client_edit_profile" name="client_edit_profile">
                                                    <input type="hidden" value="{{ $the_organization->id }}" id="client_edit_id" name="client_edit_id">

                                                    <div class="form-group">
                                                        <div class="col-lg-4 col-lg-offset-3">
                                                            <button type="submit" class="btn btn-primary" id="save_client_button" name="save_client_button" onclick="return validate_client();"><i class="fa fa-thumbs-o-up"></i> {{ trans('client_management.update') }}</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div> <!-- .tab-2 -->
                        @endif
                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body">
                                {!! $EventDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-3 -->
                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_user_tabs -->
    </div> <!-- .wrapper -->
@endsection

@section('page_level_js')
    {!! $EventDataTableObj->js() !!}

    @if (Helper::has_right(Auth::user()->operations, "edit_profile_info"))
        <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
        <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
        <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput.min.js"></script>
        <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput_locale_tr.js"></script>

        <script>
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
        </script>
    @endif

    @if (Helper::has_right(Auth::user()->operations, "edit_profile_info") && $the_user->user_type != 1 && $the_user->user_type != 2)
        <script type="text/javascript" language="javascript" src='http://maps.google.com/maps/api/js?key=AIzaSyDAhJzAfuGq9J9-f_NGriGvs_8c2BWfRqc&libraries=places'></script>
        <script type="text/javascript" language="javascript" src="/js/plugins/locationPicker/locationpicker.jquery.min.js"></script>

        <script>
            function validate_distributor(){
                $("#add_new_distributor_form").parsley().reset();
                $("#add_new_distributor_form").parsley();
            }

            function validate_client(){
                $("#add_new_client_form").parsley().reset();
                $("#add_new_client_form").parsley();
            }
        </script>
    @endif
@endsection

@section('page_document_ready')
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (Helper::has_right(Auth::user()->operations, "edit_profile_info"))
        @if (session()->has('account_update_success') && session('account_update_success'))
            {{ session()->forget('account_update_success') }}

            custom_toastr('{{ trans('my_profile.account_update_success') }}');
        @endif

        $("#new_user_logo").fileinput({
            uploadUrl: "/user_management/edit_image",
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
                "<img id='edit_user_logo' src='/img/avatar/user/{{ $the_user->avatar}}' class='file-preview-image' style='height:160px' />",
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

        @if( Auth::user()->user_type == 3 )
            @if (session()->has('distributor_update_success') && session('distributor_update_success'))
                {{ session()->forget('distributor_update_success') }}

                custom_toastr('{{ trans('distributor_management.update_success') }}');
            @endif

            $("#new_distributor_logo").fileinput({
                uploadUrl: "/distributor_management/edit_image",
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
                elErrorContainer: '#new_distributor_logo_error',
                msgErrorClass: 'alert alert-block alert-danger',
                initialPreview: [
                "<img id='edit_distributor_logo' src='/img/avatar/distributor/{{ $the_organization->logo }}' class='file-preview-image' style='height:160px'>",
                ],
                initialCaption: " {{ trans('distributor_management.select_distributor_logo') }}"
            }).on("filebatchselected", function(event, files) {// trigger upload method immediately after files are selected
                $("#new_distributor_logo").fileinput("upload");
                $('#uploaded_distributor_image_name').val($('#new_distributor_logo').prop("files")[0]['name']);
            }).on("filecleared", function(event) {
                $("#hidden_distributor_logo").val("not_changed");
                $("#uploaded_distributor_image_name").val("");
                $('#new_distributor_logo').fileinput('refresh');
                $('#edit_distributor_logo').attr('src',$('#edit_distributor_image_name').val());
            }).on('filebatchuploadcomplete', function(event, files, extra) {
                $("#hidden_distributor_logo").val("changed");
                $(".file-thumbnail-footer").hide();
            });
        @endif

        @if( Auth::user()->user_type == 4 )
            @if (session()->has('client_update_success') && session('client_update_success'))
                {{ session()->forget('client_update_success') }}

                custom_toastr('{{ trans('client_management.update_success') }}');
            @endif

            $("#new_client_logo").fileinput({
                uploadUrl: "/client_management/edit_image",
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
                    "<img id='edit_client_logo' src='/img/avatar/client/{{ $the_organization->logo }}' class='file-preview-image' style='height:160px'>",
                    ],
                initialCaption: " {{ trans('client_management.select_client_logo') }}"
            }).on("filebatchselected", function(event, files) {// trigger upload method immediately after files are selected
                $("#new_client_logo").fileinput("upload");
                $('#uploaded_client_image_name').val($('#new_client_logo').prop("files")[0]['name']);
            }).on("filecleared", function(event) {
                $("#hidden_client_logo").val("not_changed");
                $("#uploaded_client_image_name").val("");
                $('#new_client_logo').fileinput('refresh');
                $('#edit_client_logo').attr('src',$('#edit_client_image_name').val());
            }).on('filebatchuploadcomplete', function(event, files, extra) {
                $("#hidden_client_logo").val("changed");
                $(".file-thumbnail-footer").hide();
            });
        @endif
    @endif

    // Keep the current tab active after page reload
    rememberTabSelection('#my_profile_tabs', !localStorage);

    var tab_1 = false,
        tab_2 = false,
        tab_3 = false;

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            @if (Helper::has_right(Auth::user()->operations, "edit_profile_info"))
                // user_edit_form();
                tab_1 = true;
            @endif
        }
        else if(selectedTab == "#tab-2" && tab_2 == false){
            @if ( Auth::user()->user_type == 3 )
                 $('#new_distributor_location').locationpicker({
                    location: {
                        latitude: {{ $the_organization->location_latitude }},
                        longitude: {{ $the_organization->location_longitude }}
                    },
                    inputBinding: {
                        locationNameInput: $('#new_distributor_location_text'),
                        latitudeInput: $('#new_distributor_location_latitude'),
                        longitudeInput: $('#new_distributor_location_longitude'),
                    },
                    radius: null,
                    zoom: 14,
                    addressFormat: 'street_address',
                    enableAutocomplete: true,
                    enableReverseGeocode: true,
                    autocompleteOptions: {
                        componentRestrictions: {country: 'tr'}
                    }
                });
            @endif

            @if ( Auth::user()->user_type == 4 )
                 $('#new_client_location').locationpicker({
                    location: {
                        latitude: {{ $the_organization->location_latitude }},
                        longitude: {{ $the_organization->location_longitude }}
                    },
                    inputBinding: {
                        locationNameInput: $('#new_client_location_text'),
                        latitudeInput: $('#new_client_location_latitude'),
                        longitudeInput: $('#new_client_location_longitude'),
                    },
                    radius: null,
                    zoom: 14,
                    addressFormat: 'street_address',
                    enableAutocomplete: true,
                    enableReverseGeocode: true,
                    autocompleteOptions: {
                        componentRestrictions: {country: 'tr'}
                    }
                });
            @endif

            tab_2 = true;
        }
        else if(selectedTab == "#tab-3" && tab_3 == false){

            tab_3 = true;
            {!! $EventDataTableObj->ready() !!}
        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#my_profile_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#my_profile_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#my_profile_tabs a:first").trigger('click');

@endsection