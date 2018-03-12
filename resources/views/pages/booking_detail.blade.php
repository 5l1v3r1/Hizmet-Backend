@extends('layouts.master')

@section('title')
    {{ trans('booking_detail.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css" />

    {!! $OfferDataTableObj->css() !!}
@endsection

@section('content')
    <?php
    $the_booking = json_decode($the_booking);
    $user_logo_hdn_value = "not_changed";
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_user_summary">
            <div class="col-md-6">

                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_booking->name }}
                        </h2>
                        <p style="margin: 10px 0 0;">
                            {{ trans('booking_detail.booking_title') }}:  <strong> {{ $the_booking->booking_title}} </strong>

                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('booking_detail.name') }}:
                            <strong>{{ $the_booking->name }}</strong>
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
                            {{ $the_booking->name }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('booking_detail.created_by') }}</strong>
                        </td>
                        <td>
                            {{ $the_booking->name }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('booking_detail.created_at') }}</strong>
                        </td>
                        <td>
                            {{ $the_booking->name }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- #div_booking_summary -->

        <div class="row" id="div_booking_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="booking_detail_tabs">

                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('booking_detail.show_booking') }}
                            </a>
                        </li>
                        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
                            <li class="" tab="#tab-2">
                                <a data-toggle="tab" href="#tab-2" aria-expanded="true">
                                    <i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i>
                                    {{ trans('booking_detail.edit_booking') }}
                                </a>
                            </li>
                        @endif
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('booking_detail.manage_booking') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-4">
                            <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('booking_detail.manage_offer') }}
                            </a>
                        </li>

                    </ul> <!-- .nav -->

                    <div class="tab-content">

                            <div id="tab-1" class="tab-pane">
                                <div class="panel-body">
                                    İlan burda gösterilecek
                                </div>
                            </div> <!-- .tab-1 -->

                        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body">
                                <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/booking_management/add') }}" id="add_new_booking_form">
                                    {{ csrf_field() }}




                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('booking_detail.name') }} <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{ $the_booking->name }}" class="form-control" id="new_booking_name" name="new_booking_name" required minlength="3" maxlength="255" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('booking_detail.booking_name') }} <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{$the_booking->booking_title}}" class="form-control" id="new_booking_email" name="new_booking_email" required>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('booking_detail.date') }} <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{$the_booking->booking_date}}" class="form-control" id="new_booking_email" name="new_booking_email" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('booking_detail.assigned_id') }} <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{$the_booking->assigned_id}}" class="form-control" id="new_booking_email" name="new_booking_email" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('booking_detail.service_name') }} <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{$the_booking->s_name}}" class="form-control" id="new_booking_email" name="new_booking_email" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('booking_detail.booking_detail') }} <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <textarea rows="4" cols="50" class="form-control" id="new_booking_email" name="new_booking_email">
                                            At w3schools.com you will learn how to make a website. We offer free tutorials in all web development technologies.
                                            </textarea>
                                        </div>
                                    </div>





                                    <input type="hidden" value="edit" id="booking_op_type" name="booking_op_type">
                                    <input type="hidden" value="{{$the_booking->id}}" id="booking_edit_id" name="booking_edit_id">

                                    <div class="form-group">
                                        <div class="col-lg-4 col-lg-offset-3">
                                            <button type="submit" class="btn btn-primary" id="save_booking_button" name="save_booking_button" onclick="return validate_save_op();">
                                                <i class="fa fa-refresh"></i> {{ trans('booking_detail.update') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div> <!-- .tab-2 -->
                        @endif <!-- Check Auth user has 'add_new_user' authorization -->
                            <div id="tab-3" class="tab-pane">
                                <div class="panel-body">
                                    <div class="form-horizontal">
                                        <div class="form-group">
                                            <label class="col-md-3 col-xs-7 control-label"> {{ trans('booking_detail.current_status') }}: </label>
                                            <label class="col-md-8 col-xs-5 control-label" style="text-align: left;">{!! trans('global.status_'.$the_booking->status) !!}</label>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-3 col-xs-7 control-label">{{ trans('booking_detail.change_status') }}:</label>
                                            <div class="col-md-8 col-xs-5" style="padding-top: 7px;">
                                                <input
                                                        class="form-control"
                                                        id="change_booking_status"
                                                        type="checkbox"
                                                        data-on-text="{{trans('booking_detail.active')}}"
                                                        data-off-text="{{trans('booking_detail.inactive')}}"
                                                        data-on-color="success"
                                                        data-off-color="danger"
                                                        data-size="mini"
                                                        {{ $the_booking->status == 1?"checked":"" }}
                                                >
                                                <span id="change_booking_status_helper" class="help-block" style="display:none;color:darkred;">{{ trans('booking_detail.deactive_booking_warning') }}</span>
                                            </div>
                                        </div>


                                        <div class="form-group">

                                            <div class="col-md-3 col-xs-7 control-label">

                                            </div>
                                            <div class="col-md-8 col-xs-5" style="padding-top: 7px;">
                                                @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
                                                    <button type="button" class="btn btn-sm btn-info" id="change_booking_status_button" name="change_booking_status_button">
                                                        <i class="fa fa-retweet"></i> {{ trans('booking_detail.change') }}
                                                    </button>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- .tab-3 -->

                        <div id="tab-4" class="tab-pane">
                            <div class="panel-body">
                                {!!  $OfferDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-4  -->

                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_booking_tabs -->
    </div> <!-- .wrapper -->
@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput_locale_tr.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>

    {!! $OfferDataTableObj->js() !!}

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        function validate_save_op(){
            $("#add_new_booking_form").parsley().reset();
            $("#add_new_booking_form").parsley();
        }

        function open_edit_password(){
            $('#new_booking_password').removeAttr('disabled');
            $('#new_booking_password').attr('required', '');
            $('#change_pass_icon').hide();
            $('#cancel_pass_icon').show();
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

        var new_status = "{{ $the_booking->status }}";
    </script>

@endsection

@section('page_document_ready')
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (session()->has('booking_update_success') && session('booking_update_success'))
        {{ session()->forget('booking_update_success') }}

        custom_toastr('{{ trans('booking_management.update_success') }}');
    @endif

    @if (session()->has('booking_status_activated') && session('booking_status_activated'))
        {{ session()->forget('booking_status_activated') }}

        custom_toastr('{{ trans('booking_detail.booking_status_activated') }}');
    @endif

    @if (session()->has('booking_status_deactivated') && session('booking_status_deactivated'))
        {{ session()->forget('booking_status_deactivated') }}

        custom_toastr('{{ trans('booking_detail.booking_status_deactivated') }}','warning');
    @endif

    @if (session()->has('booking_change_authorization') && session('booking_change_authorization'))
        {{ session()->forget('booking_change_authorization') }}

        custom_toastr('{{ trans('booking_detail.change_authorization_success') }}');
    @endif


    @if (Helper::has_right(Auth::user()->operations,'delete_user'))
        $('#delete_booking_button').click(function(){
        confirmBox("","{{trans('booking_detail.delete_booking_warning')}}","warning",function(){
        $.ajax({
        method:"POST",
        url:"/booking_management/detail/{{$the_booking->id}}/deleteUser",
        data:"id="+{{$the_booking->id}},
        success:function(return_text){
        if($.trim(return_text)=="SUCCESS"){
        window.location ='/booking_management';
        }
        else{
        alertBox("Oops...", "{{trans('global.unexpected_error')}}", "error");
        }
        }
        });
        }, true);
        });
    @endif


    $("#change_booking_status").bootstrapSwitch();

    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        $('#change_booking_status').on('switchChange.bootstrapSwitch', function(event, state) {
        if( state === false ){
        $('#change_booking_status_helper').show(600);
        new_status = "2";
        }
        else{
        $('#change_booking_status_helper').hide(600);
        new_status = "1";
        }
        });

        $('#change_booking_status_button').click(function(){
        if( "{{ $the_booking->status }}" == new_status ){
        return alertBox('','{{trans('booking_detail.same_status')}}','info');
        }

        message = "{{ trans('booking_detail.activate_to_user') }}";
        if( new_status == "2" ){
        message = "{{ trans('booking_detail.deactivate_to_user') }}";
        }

        confirmBox("",message,"warning",function(){
        $.ajax({
        method:"POST",
        url:"/booking_management/detail/{{$the_booking->id}}/changeStatus",
        data:"id="+{{$the_booking->id}}+"&status="+new_status,
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




    @endif

    // Keep the current tab active after page reload
    rememberTabSelection('#booking_detail_tabs', !localStorage);

    if(document.location.hash){
    $("#booking_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
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
    // booking_authorizations();
    tab_2 = true;
    }
    else if(selectedTab == "#tab-3" && tab_3 == false){
    // booking_login_history();
    tab_3 = true;
    }
    else if(selectedTab == "#tab-4" && tab_4 == false){
    tab_4 = true;
    {!! $OfferDataTableObj->ready() !!}
    }
    else{
    return;
    }
    }

    // Load the selected tab content When the tab is changed
    $('#booking_detail_tabs a').on('shown.bs.tab', function(event){
    var selectedTab = $(event.target).attr("href");
    load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#booking_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
    load_tab_content(active_tab);
    else
    $("#booking_detail_tabs a:first").trigger('click');


@endsection