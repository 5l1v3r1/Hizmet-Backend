@extends('layouts.master')

@section('title')
    {{ trans('user_detail.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css" />

    {!! $EventDataTableObj->css() !!}
@endsection

@section('content')
    <?php
        $the_user = json_decode($the_user);
        $user_logo_hdn_value = "not_changed";
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
                            {{ trans('user_detail.user_type') }}:  <strong> {{ trans("global.".$the_user->type) }} </strong>

                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('user_detail.organization') }}:
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
                                <strong>{{ trans('user_detail.email') }}</strong>
                            </td>
                            <td>
                                {{ $the_user->email }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('user_detail.created_by') }}</strong>
                            </td>
                            <td>
                                {{ $the_user->created_by }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('user_detail.created_at') }}</strong>
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
                    <ul class="nav nav-tabs" id="user_detail_tabs">
                        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="true">
                                <i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i>
                                {{ trans('user_detail.edit_info') }}
                            </a>
                        </li>
                        @endif
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('user_detail.manage_account') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-bolt fa-lg" aria-hidden="true"></i>
                                {{ trans('user_detail.edit_authorizations') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-4">
                            <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                <i class="fa fa-history fa-lg" aria-hidden="true"></i>
                                {{ trans('user_detail.event_logs') }}
                            </a>
                        </li>
                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
                            <div id="tab-1" class="tab-pane">
                                <div class="panel-body">
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
                        @endif <!-- Check Auth user has 'add_new_user' authorization -->

                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body">
                                <div class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-md-3 col-xs-7 control-label"> {{ trans('user_detail.current_status') }}: </label>
                                        <label class="col-md-8 col-xs-5 control-label" style="text-align: left;">{!! trans('global.status_'.$the_user->status) !!}</label>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-3 col-xs-7 control-label">{{ trans('user_detail.change_status') }}:</label>
                                        <div class="col-md-8 col-xs-5" style="padding-top: 7px;">
                                            <input
                                                    class="form-control"
                                                    id="change_user_status"
                                                    type="checkbox"
                                                    data-on-text="{{trans('user_detail.active')}}"
                                                    data-off-text="{{trans('user_detail.inactive')}}"
                                                    data-on-color="success"
                                                    data-off-color="danger"
                                                    data-size="mini"
                                                    {{ $the_user->status == 1?"checked":"" }}
                                            >
                                            <span id="change_user_status_helper" class="help-block" style="display:none;color:darkred;">{{ trans('user_detail.deactive_user_warning') }}</span>
                                        </div>
                                    </div>


                                        <div class="form-group">

                                                <div class="col-md-3 col-xs-7 control-label">
                                                    @if (Helper::has_right(Auth::user()->operations,'delete_user'))
                                                        <button type="button" class="btn btn-sm btn-white" id="delete_user_button" name="delete_user_button">
                                                            <i class="fa fa-trash-o"></i> {{ trans('user_detail.delete_user') }}
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="col-md-8 col-xs-5" style="padding-top: 7px;">
                                                    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
                                                    <button type="button" class="btn btn-sm btn-info" id="change_user_status_button" name="change_user_status_button">
                                                        <i class="fa fa-retweet"></i> {{ trans('user_detail.change') }}
                                                    </button>
                                                    @endif
                                                </div>

                                        </div>
                                </div>
                            </div>
                        </div> <!-- .tab-2 -->
                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body">
                                {!! Helper::authorization_edit_form($the_user) !!}
                            </div>
                        </div> <!-- .tab-3 -->
                        <div id="tab-4" class="tab-pane">
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
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput_locale_tr.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>

    {!! $EventDataTableObj->js() !!}

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

            function adjust_checkbox(type,check_element){

                if(type=="op"){
                    if(check_element.hasClass("fa-check-square-o")){
                        check_element.removeClass("fa-check-square-o");
                        check_element.addClass("fa-square-o");
                    }
                    else{
                        check_element.addClass("fa-check-square-o");
                        check_element.removeClass("fa-square-o");
                    }
                }
                else if(type=="sub"){

                    if(check_element.hasClass("fa-check-square-o")){

                        check_element.closest(".the_sub_menu").find(".the_operation_icon").removeClass("fa-check-square-o");
                        check_element.closest(".the_sub_menu").find(".the_operation_icon").addClass("fa-square-o");
                    }
                    else{
                        check_element.closest(".the_sub_menu").find(".the_operation_icon").addClass("fa-check-square-o");
                        check_element.closest(".the_sub_menu").find(".the_operation_icon").removeClass("fa-square-o");
                        check_element.closest(".the_sub_menu").find(".the_operation_icon").removeClass("fa-square");
                    }
                }

                parent_element = check_element.closest(".the_parent");
                dd_list = parent_element.find(".the_sub_menu");

                var checked_operation_counter = 0;
                var total_operation_counter = 0;
                $.each( dd_list, function() {

                    one_menu = $(this);

                    var checked_menu_counter = 0;
                    $.each(one_menu.find(".the_operation"), function(){
                        total_operation_counter++;
                        if($(this).find("i").first().hasClass("fa-check-square-o")){
                            checked_menu_counter++;
                            checked_operation_counter++;
                        }
                    });

                    if(checked_menu_counter == 0){
                        one_menu.find(".the_sub_menu_icon").first().removeClass("fa-check-square-o");
                        one_menu.find(".the_sub_menu_icon").first().addClass("fa-square-o");
                        one_menu.find(".the_sub_menu_icon").first().removeClass("fa-square");
                    }
                    else if(one_menu.find(".the_operation").length == checked_menu_counter){

                        one_menu.find(".the_sub_menu_icon").first().addClass("fa-check-square-o");
                        one_menu.find(".the_sub_menu_icon").first().removeClass("fa-square-o");
                        one_menu.find(".the_sub_menu_icon").first().removeClass("fa-square");
                    }
                    else{
                        one_menu.find(".the_sub_menu_icon").first().removeClass("fa-check-square-o");
                        one_menu.find(".the_sub_menu_icon").first().removeClass("fa-square-o");
                        one_menu.find(".the_sub_menu_icon").first().addClass("fa-square");
                    }
                });

                if(checked_operation_counter == 0){
                    parent_element.find(".the_parent_icon").first().removeClass("fa-check-square-o");
                    parent_element.find(".the_parent_icon").first().addClass("fa-square-o");
                    parent_element.find(".the_parent_icon").first().removeClass("fa-square");
                    parent_element.find(".the_parent_icon").first().parent().css("color","#676a6c");
                }
                else if(total_operation_counter == checked_operation_counter){
                    parent_element.find(".the_parent_icon").first().addClass("fa-check-square-o");
                    parent_element.find(".the_parent_icon").first().removeClass("fa-square-o");
                    parent_element.find(".the_parent_icon").first().removeClass("fa-square");
                    parent_element.find(".the_parent_icon").first().parent().css("color","#23B613");
                }
                else{
                    parent_element.find(".the_parent_icon").first().removeClass("fa-check-square-o");
                    parent_element.find(".the_parent_icon").first().removeClass("fa-square-o");
                    parent_element.find(".the_parent_icon").first().addClass("fa-square");
                    parent_element.find(".the_parent_icon").first().parent().css("color","#4E5698");
                }
            }

            function save_authorizations(){
                confirmBox("","{{ trans("user_detail.change_authorization_warning") }}","warning",function(){

                    operations = "";
                    $.each($(".the_operation_icon"),function(){
                        if($(this).hasClass("fa-check-square-o")){
                            operations += $(this).closest(".the_operation").attr("id");
                        }
                    });

                    $.ajax({
                        method:"POST",
                        url:"/user_management/detail/{{$the_user->id}}/changeAuthorization",
                        data:"id={{$the_user->id}}&operations="+operations,
                        success:function(return_text){
                            if($.trim(return_text)=="SUCCESS"){
                                //alertBox("","{{ trans("user_detail.change_authorization_success") }}","success");
                                location.reload();
                            }else{
                                alertBox("Oops...", "{{trans('global.unexpected_error')}}", "error");
                            }
                        }
                    });
                }, true);
            }
        @endif

        function collapse_item(element){

            the_li = element.closest("li");
            the_li.children('[data-action="collapse"]').hide();
            the_li.children('[data-action="expand"]').show();
            the_li.children('.dd-list').hide();

        }
        function expand_item(element){

            the_li = element.closest("li");
            the_li.children('[data-action="expand"]').hide();
            the_li.children('[data-action="collapse"]').show();
            the_li.children('.dd-list').show();

        }

        var new_status = "{{ $the_user->status }}";
    </script>

@endsection

@section('page_document_ready')
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (session()->has('user_update_success') && session('user_update_success'))
        {{ session()->forget('user_update_success') }}

        custom_toastr('{{ trans('user_management.update_success') }}');
    @endif

    @if (session()->has('user_status_activated') && session('user_status_activated'))
        {{ session()->forget('user_status_activated') }}

        custom_toastr('{{ trans('user_detail.user_status_activated') }}');
    @endif

    @if (session()->has('user_status_deactivated') && session('user_status_deactivated'))
        {{ session()->forget('user_status_deactivated') }}

        custom_toastr('{{ trans('user_detail.user_status_deactivated') }}','warning');
    @endif

    @if (session()->has('user_change_authorization') && session('user_change_authorization'))
        {{ session()->forget('user_change_authorization') }}

        custom_toastr('{{ trans('user_detail.change_authorization_success') }}');
    @endif


    @if (Helper::has_right(Auth::user()->operations,'delete_user'))
        $('#delete_user_button').click(function(){
            confirmBox("","{{trans('user_detail.delete_user_warning')}}","warning",function(){
                $.ajax({
                    method:"POST",
                    url:"/user_management/detail/{{$the_user->id}}/deleteUser",
                    data:"id="+{{$the_user->id}},
                    success:function(return_text){
                        if($.trim(return_text)=="SUCCESS"){
                            window.location ='/user_management';
                        }
                        else{
                            alertBox("Oops...", "{{trans('global.unexpected_error')}}", "error");
                        }
                    }
                });
            }, true);
        });
    @endif


    $("#change_user_status").bootstrapSwitch();

    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        $('#change_user_status').on('switchChange.bootstrapSwitch', function(event, state) {
            if( state === false ){
                $('#change_user_status_helper').show(600);
                new_status = "2";
            }
            else{
                $('#change_user_status_helper').hide(600);
                new_status = "1";
            }
        });

        $('#change_user_status_button').click(function(){
            if( "{{ $the_user->status }}" == new_status ){
                return alertBox('','{{trans('user_detail.same_status')}}','info');
            }

            message = "{{ trans('user_detail.activate_to_user') }}";
            if( new_status == "2" ){
                message = "{{ trans('user_detail.deactivate_to_user') }}";
            }

            confirmBox("",message,"warning",function(){
                $.ajax({
                    method:"POST",
                    url:"/user_management/detail/{{$the_user->id}}/changeStatus",
                    data:"id="+{{$the_user->id}}+"&status="+new_status,
                    success:function(return_text){
                        if($.trim(return_text)=="SUCCESS"){
                            location.reload();
                        }else{
                            alertBox("Oops...", "{{trans('global.unexpected_error')}}", "error");
                        }
                    }
                });
            }, true);
        });


        $("#new_user_clients").select2({
            minimumResultsForSearch: 10
        });

        $("#new_user_distributors").select2({
            minimumResultsForSearch: 10
        });

        $("#new_user_type").select2({
            minimumResultsForSearch: Infinity
        }).val({{$the_user->user_type}}).trigger("change");

        if( {{$the_user->user_type}} == 3 ){
            $("#new_user_distributors").show();
            $('#new_user_clients').removeAttr('required');
            $('#new_user_distributors').attr('required', '');
            $("#new_user_distributors").select2({
                minimumResultsForSearch: 10
            }).val({{$the_user->org_id}}).trigger("change");
        }
        else if( {{$the_user->user_type}} == 4 ){
            $("#new_user_clients").show();
            $('#new_user_distributors').removeAttr('required');
            $('#new_user_clients').attr('required', '');
            $("#new_user_clients").select2({
                minimumResultsForSearch: 10
            }).val({{$the_user->org_id}}).trigger("change");
        }
        else{
            $("#new_user_clients").hide();
            $("#new_user_distributors").hide();
            $('#new_user_distributors').removeAttr('required');
            $('#new_user_clients').removeAttr('required');
        }

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
    @endif

    // Keep the current tab active after page reload
    rememberTabSelection('#user_detail_tabs', !localStorage);

    if(document.location.hash){
        $("#user_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
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
            // user_authorizations();
            tab_2 = true;
        }
        else if(selectedTab == "#tab-3" && tab_3 == false){
            // user_login_history();
            tab_3 = true;
        }
        else if(selectedTab == "#tab-4" && tab_4 == false){
            tab_4 = true;
            {!! $EventDataTableObj->ready() !!}
        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#user_detail_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#user_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#user_detail_tabs a:first").trigger('click');


@endsection