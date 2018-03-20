@extends('layouts.master')

@section('title')
    {{ trans('client_detail.title') }}
@endsection

@section('page_level_css')
    {!! $UserDataTableObj->css() !!}
@endsection

@section('content')
    <?php
    $the_client = json_decode($the_client);
    $country_list = DB::table('cities')->get();
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_client_summary" style="margin-bottom:20px;">
            <div class="col-md-6">
                <div class="profile-image">
                </div>
                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_client->name }}
                        </h2>

                        <p style="margin: 10px 0 0;">
                            {{ trans('client_management.gsm_phone') }}: <strong> {{ $the_client->gsm_phone}} </strong>

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('client_management.phone') }}: <strong> {{ $the_client->phone}} </strong>

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('client_management.address') }}:
                            <strong> {{ $the_client->location_text }} </strong>

                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <table class="table small m-b-xs">
                    <tbody>
                    <tr>
                        <td>
                            <strong>{{ trans('client_management.email') }}</strong>
                        </td>
                        <td>
                            {{ $the_client->email }}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <strong>{{ trans('client_detail.created_by') }}</strong>
                        </td>
                        <td>
                            {{ $the_client->created_by }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('client_management.created_at') }}</strong>
                        </td>
                        <td>
                            {{ date('d/m/Y H:i:s',strtotime($the_client->created_at)) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- #div_client_summary -->

        <div class="row" id="div_modem_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="client_detail_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.bookings') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-podcast fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.orders') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-cogs fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.information') }}
                            </a>
                        </li>

                        <li class="" tab="#tab-4">
                            <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                <i class="fa fa-pie-chart fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.statistics') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-5">
                            <a data-toggle="tab" href="#tab-5" aria-expanded="false">
                                <i class="fa fa-bell-o fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.messages') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-6">
                            <a data-toggle="tab" href="#tab-6" aria-expanded="false">
                                <i class="fa fa-tasks fa-lg" aria-hidden="true"></i>
                                {{ trans('client_detail.event_logs') }}
                            </a>
                        </li>
                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!!  $BookingDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-1 -->

                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!!  $OrderDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-2 -->

                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="panel-body">
                                    <form class="m-t form-horizontal" role="form" method="POST"
                                          action="{{ url('/client_management/add') }}" id="add_new_client_client_form">
                                    {{ csrf_field() }}

                                    <!-- get selectable client_type according to user type -->


                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('client_management.name') }}
                                                <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <input type="text" value="{{ $the_client->name }}" class="form-control"
                                                       id="new_client_name" name="new_client_name" required
                                                       minlength="3" maxlength="255"/>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('client_management.email') }}
                                                <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <input type="email" value="{{$the_client->email}}" class="form-control"
                                                       id="new_client_email" name="new_client_email" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label" id="password_label_for_edit">
                                                {{ trans('client_management.password') }}
                                                <a id="change_pass_icon" onclick="open_edit_password();"
                                                   href="javascript:void(1);">
                                                    <i class="fa fa-lock fa-lg" aria-hidden="true"></i>
                                                </a>
                                                <a id="cancel_pass_icon" style="display:none;"
                                                   onclick="cancel_edit_password()" href="javascript:void(1);">
                                                    <i class="fa fa-unlock fa-lg" aria-hidden="true"></i>
                                                </a>
                                            </label>

                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" value=""
                                                       id="new_client_password" name="new_client_password" minlength="6"
                                                       maxlength="20" disabled/>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('client_detail.gsm_number') }}
                                                <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <input type="number" value="{{$the_client->gsm_phone}}"
                                                       class="form-control" id="new_client_gsm_phone"
                                                       name="new_client_gsm_phone" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('client_detail.phone') }} </label>
                                            <div class="col-sm-6">
                                                <input type="number" value="{{$the_client->phone}}" class="form-control"
                                                       id="new_client_phone" name="new_client_phone">
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('booking_detail.province') }}
                                                <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <select id="new_client_province" name="new_client_province"
                                                        class="form-control" style="width: 100%;">

                                                    <option></option>
                                                    @foreach($country_list as $one_country)
                                                        <option value="{{ $one_country->id}}">{{ $one_country->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('client_detail.district') }} </label>
                                            <div class="col-sm-6">
                                                <input type="number" value="{{$the_client->district}}"
                                                       class="form-control" id="new_client_district"
                                                       name="new_client_district" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('client_detail.location') }}
                                                <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <textarea class="form-control" id="new_client_location"
                                                          name="new_client_location">{{$the_client->location}}</textarea>
                                            </div>
                                        </div>
                                        <input type="hidden" value="edit" id="client_op_type" name="client_op_type">
                                        <input type="hidden" value="{{$the_client->id}}" id="client_edit_id"
                                               name="client_edit_id">

                                        <div class="form-group">
                                            <div class="col-lg-4 col-lg-offset-3">
                                                <button type="submit" class="btn btn-primary" id="save_client_button"
                                                        name="save_client_button">
                                                    <i class="fa fa-refresh"></i> {{ trans('client_management.update') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div> <!-- .tab-3 -->


                        <div id="tab-4" class="tab-pane">
                            <div class="panel-body">
                                Geliştirme devam ediyor...
                            </div>
                        </div>

                        <div id="tab-5" class="tab-pane">
                            <div class="panel-body">
                                Geliştirme devam ediyor...
                            </div>
                        </div> <!-- .tab-5 -->

                        <div id="tab-6" class="tab-pane">
                            <div class="panel-body">
                                Geliştirme devam ediyor...
                            </div>
                        </div> <!-- .tab-6 -->
                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_modem_tabs -->

    </div>
@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/fileinput/fileinput_locale_tr.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
    <script type="text/javascript" language="javascript"
            src="/js/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>

    {!! $UserDataTableObj->js() !!}
    <script>

        function open_edit_password() {
            $('#new_client_password').removeAttr('disabled');
            $('#new_client_password').attr('required', '');
            $('#change_pass_icon').hide();
            $('#cancel_pass_icon').show();
        }

        function cancel_edit_password() {
            $('#new_client_password').attr('disabled', '');
            $('#new_client_password').removeAttr('required');
            $('#cancel_pass_icon').hide();
            $('#change_pass_icon').show();
        }
    </script>
@endsection

@section('page_document_ready')

    @if (session()->has('client_update_success') && session('client_update_success'))
        {{ session()->forget('client_update_success') }}

        custom_toastr('{{ trans('client_management.update_success') }}');
    @endif

    $("#change_client_status").bootstrapSwitch();

    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        $('#change_client_status').on('switchChange.bootstrapSwitch', function(event, state) {
        if( state === false ){
        $('#change_client_status_helper').show(600);
        new_status = "2";
        }
        else{
        $('#change_client_status_helper').hide(600);
        new_status = "1";
        }
        });

        $('#change_client_status_button').click(function(){
        if( "{{ $the_client->status }}" == new_status ){
        return alertBox('','{{trans('client_detail.same_status')}}','info');
        }

        message = "{{ trans('client_detail.activate_to_user') }}";
        if( new_status == "2" ){
        message = "{{ trans('client_detail.deactivate_to_user') }}";
        }

        confirmBox("",message,"warning",function(){
        $.ajax({
        method:"POST",
        url:"/client_management/change/{{$the_client->id}}/changeStatus",
        data:"id="+{{$the_client->id}}+"&status="+new_status,
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
    rememberTabSelection('#client_detail_tabs', !localStorage);

    var tab_1 = false,
    tab_2 = false,
    tab_3 = false,
    tab_4 = false,
    tab_5 = false,
    tab_6 = false;

    function load_tab_content(selectedTab){
    if(selectedTab == "#tab-1" && tab_1 == false){
    {!! $BookingDataTableObj->ready() !!}
    tab_1 = true;
    }
    else if(selectedTab == "#tab-2" && tab_2 == false){
    {!! $OrderDataTableObj->ready() !!}
    tab_2 = true;
    }
    else if(selectedTab == "#tab-3" && tab_3 == false){

    tab_3 = true;
    }
    else if(selectedTab == "#tab-4" && tab_4 == false){

    tab_4 = true;
    }
    else if(selectedTab == "#tab-5" && tab_5 == false){
    tab_5 = true;

    }
    else if(selectedTab == "#tab-6" && tab_6 == false){
    tab_6 = true;


    }
    else{
    return;
    }
    }

    // Load the selected tab content When the tab is changed
    $('#client_detail_tabs a').on('shown.bs.tab', function(event){
    var selectedTab = $(event.target).attr("href");
    load_tab_content(selectedTab);

    // clear hash and parameter values from URL
    history.pushState('', document.title, window.location.pathname);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#client_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
    load_tab_content(active_tab);
    else
    $("#client_detail_tabs a:first").trigger('click');
    province = {{$the_client->province}}
    $( "#new_client_province" ).val(province);


@endsection