@extends('layouts.master')

@section('title')
    {{ trans('booking_detail.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css"/>


@endsection

@section('content')
    <?php
    $the_booking = json_decode($the_booking);
    $user_logo_hdn_value = "not_changed";
    $country_list = DB::table('status')->get();
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_user_summary">
            <div class="col-md-6">

                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_booking->booking_title}}
                        </h2>
                        <p style="margin: 10px 0 0;">
                            {{ trans('booking_detail.assigned_name') }}:
                            <strong> {{ $the_booking->assigned_name}} </strong>

                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('booking_detail.name') }}:
                            <strong>{{ $the_booking->client_name }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <table class="table small m-b-xs">
                    <tbody>
                    <tr>
                        <td>
                            <strong>{{ trans('booking_detail.offer_date') }}</strong>
                        </td>
                        <td>
                            {{ $the_booking->offer_date }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('booking_detail.offer_status') }}</strong>
                        </td>
                        <td>
                            {!! trans('global.status_'.$the_booking->offer_status) !!}
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div> <!-- #div_booking_summary -->
        <br><br>

        <div class="row" id="div_booking_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="booking_detail_tabs">

                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('booking_detail.show_offer') }}
                            </a>
                        </li>


                    </ul> <!-- .nav -->

                    <div class="tab-content">

                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="panel-body">
                                    <form class="m-t form-horizontal" role="form" method="POST"
                                          action="{{ url('/offer_management/change') }}" id="add_new_client_client_form">
                                    {{ csrf_field() }}

                                    <!-- get selectable client_type according to user type -->


                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('booking_detail.prices') }}
                                                <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <input type="text" value="{{ $the_booking->prices }}"
                                                       class="form-control" id="new_prices" name="new_prices"
                                                       required minlength="2" maxlength="255"/>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('booking_detail.note') }}
                                                <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                 <textarea class="form-control" id="new_note" name="new_note" required minlength="2">{{$the_booking->note}}</textarea>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"> {{ trans('booking_detail.status') }}
                                                <span style="color:red;">*</span></label>
                                            <div class="col-sm-6">
                                                <select id="new_status" name="new_status" class="form-control" style="width: 100%;">

                                                    <option></option>
                                                    @foreach($country_list as $one_country)
                                                        <option value="{{ $one_country->id}}">{{ $one_country->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>



                                        <input type="hidden" value="edit" id="offer_op_type" name="client_op_type">
                                        <input type="hidden" value="{{$the_booking->id}}" id="offer_edit_id"
                                               name="offer_edit_id">

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
                        </div> <!-- .tab-1 -->


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
    <script type="text/javascript" language="javascript"
            src="/js/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>



    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        function validate_save_op() {
            $("#add_new_booking_form").parsley().reset();
            $("#add_new_booking_form").parsley();
        }




        @endif



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