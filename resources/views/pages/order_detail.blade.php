@extends('layouts.master')

@section('title')
    {{ trans('order_detail.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css"/>
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css"/>

    {!! $OfferDataTableObj->css() !!}
@endsection

@section('content')
    <?php
    $the_order = json_decode($the_order);
    $user_logo_hdn_value = "not_changed";
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_user_summary">
            <div class="col-md-6">

                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_order->name }}
                        </h2>
                        <p style="margin: 10px 0 0;">
                            {{ trans('order_detail.order_title') }}: <strong> {{ $the_order->booking_title}} </strong>

                        </p>
                        <p style="margin: 5px 0 0;">
                            {{ trans('order_detail.name') }}:
                            <strong>{{ $the_order->name }}</strong>
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
                            {{ $the_order->email }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('order_detail.created_by') }}</strong>
                        </td>
                        <td>
                            {{ $the_order->created_by }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ trans('order_detail.created_at') }}</strong>
                        </td>
                        <td>
                            {{ $the_order->created_at }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- #div_order_summary -->

        <div class="row" id="div_order_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="order_detail_tabs">

                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('order_detail.show_booking') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="true">
                                <i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i>
                                {{ trans('order_detail.edit_booking') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('order_detail.order_state') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-4">
                            <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                <i class="fa fa-unlock-alt fa-lg" aria-hidden="true"></i>
                                {{ trans('order_detail.manage_offer') }}
                            </a>
                        </li>

                    </ul> <!-- .nav -->

                    <div class="tab-content">

                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body">
                                İlan burda gösterilecek
                            </div>
                        </div> <!-- .tab-1 -->


                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body">
                                <form class="m-t form-horizontal" role="form" method="POST"
                                      action="{{ url('/order_management/add') }}" id="add_new_order_form">
                                    {{ csrf_field() }}



                                    <!-- get selectable clients according to user type -->
                                    {!!  Helper::get_clients_select("new_user_clients", false) !!}


                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('order_detail.order_name') }}
                                            <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{$the_order->booking_title}}"
                                                   class="form-control" id="new_order_name" name="new_order_name"
                                                   required>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('order_detail.date') }}
                                            <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{$the_order->order_date}}"
                                                   class="form-control" id="new_order_date" name="new_order_date"
                                                   required>
                                        </div>
                                    </div>
                                        <!-- get selectable assigned  -->
                                        {!!  Helper::get_assigned_select("new_assigned_id", false) !!}



                                        <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('order_detail.service_name') }}
                                            <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{$the_order->s_name}}" class="form-control"
                                                   id="new_order_s_name" name="new_order_s_name" disabled>
                                        </div>
                                    </div>

                                        {!!  Helper::get_status("new_order_status", false) !!}

                                    <input type="hidden" value="edit_order" id="order_op_type" name="order_op_type">
                                    <input type="hidden" value="{{$the_order->order_id}}" id="order_edit_id"
                                           name="order_edit_id">

                                    <div class="form-group">
                                        <div class="col-lg-4 col-lg-offset-3">
                                            <button type="submit" class="btn btn-primary" id="save_order_button"
                                                    name="save_order_button" onclick="return validate_save_op();">
                                                <i class="fa fa-refresh"></i> {{ trans('order_detail.update') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div> <!-- .tab-2 -->

                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body">
                                <form class="m-t form-horizontal" role="form" method="POST"
                                      action="{{ url('/order_management/add') }}" id="add_new_offer_form">
                                    {{ csrf_field() }}




                                    <!-- get selectable assigned  -->
                                    {!!  Helper::get_booking_select("new_offfer_booking_id", false) !!}


                                <!-- get selectable assigned  -->
                                    {!!  Helper::get_assigned_select("new_offer_assigned_id", false) !!}


                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"> {{ trans('order_detail.prices') }}
                                            <span style="color:red;">*</span></label>
                                        <div class="col-sm-6">
                                            <input type="number" value="{{ $the_order->offer_prices }}"
                                                   class="form-control"
                                                   id="new_prices" name="new_prices" required/>
                                        </div>
                                    </div>


                                    <input type="hidden" value="edit_offer" id="order_op_type" name="order_op_type">
                                    <input type="hidden" value="{{$the_order->offer_id}}" id="offer_edit_id"
                                           name="offer_edit_id">

                                    <div class="form-group">
                                        <div class="col-lg-4 col-lg-offset-3">
                                            <button type="submit" class="btn btn-primary" id="save_order_button"
                                                    name="save_order_button" onclick="return validate_save_op2();">
                                                <i class="fa fa-refresh"></i> {{ trans('order_detail.update') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
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
        </div> <!-- #div_order_tabs -->
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

    {!! $OfferDataTableObj->js() !!}

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        function validate_save_op() {
            $("#add_new_order_form").parsley().reset();
            $("#add_new_order_form").parsley();
        }
        function validate_save_op2() {
            $("#add_new_offer_form").parsley().reset();
            $("#add_new_offer_form").parsley();
        }



        @endif

        function collapse_item(element) {

            the_li = element.closest("li");
            the_li.children('[data-action="collapse"]').hide();
            the_li.children('[data-action="expand"]').show();
            the_li.children('.dd-list').hide();

        }

        function expand_item(element) {

            the_li = element.closest("li");
            the_li.children('[data-action="expand"]').hide();
            the_li.children('[data-action="collapse"]').show();
            the_li.children('.dd-list').show();

        }

        var new_status = "{{ $the_order->status }}";
    </script>

@endsection

@section('page_document_ready')



    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (session()->has('order_update_success') && session('order_update_success'))
        {{ session()->forget('order_update_success') }}

        custom_toastr('{{ trans('order_management.update_success') }}');
    @endif

    @if (session()->has('offer_update_success') && session('offer_update_success'))
        {{ session()->forget('offer_update_success') }}

        custom_toastr('{{ trans('order_management.offer_update_success') }}');
    @endif

    @if (session()->has('order_status_activated') && session('order_status_activated'))
        {{ session()->forget('order_status_activated') }}

        custom_toastr('{{ trans('order_detail.order_status_activated') }}');
    @endif

    @if (session()->has('order_status_deactivated') && session('order_status_deactivated'))
        {{ session()->forget('order_status_deactivated') }}

        custom_toastr('{{ trans('order_detail.order_status_deactivated') }}','warning');
    @endif

    @if (session()->has('order_change_authorization') && session('order_change_authorization'))
        {{ session()->forget('order_change_authorization') }}

        custom_toastr('{{ trans('order_detail.change_authorization_success') }}');
    @endif


    @if (Helper::has_right(Auth::user()->operations,'delete_user'))
        $('#delete_order_button').click(function(){
        confirmBox("","{{trans('order_detail.delete_order_warning')}}","warning",function(){
        $.ajax({
        method:"POST",
        url:"/order_management/detail/{{$the_order->id}}/deleteUser",
        data:"id="+{{$the_order->id}},
        success:function(return_text){
        if($.trim(return_text)=="SUCCESS"){
        window.location ='/order_management';
        }
        else{
        alertBox("Oops...", "{{trans('global.unexpected_error')}}", "error");
        }
        }
        });
        }, true);
        });
    @endif


    $("#change_order_status").bootstrapSwitch();

    @if (Helper::has_right(Auth::user()->operations, "add_new_user"))
        $('#change_order_status').on('switchChange.bootstrapSwitch', function(event, state) {
        if( state === false ){
        $('#change_order_status_helper').show(600);
        new_status = "2";
        }
        else{
        $('#change_order_status_helper').hide(600);
        new_status = "1";
        }
        });

        $('#change_order_status_button').click(function(){
        if( "{{ $the_order->status }}" == new_status ){
        return alertBox('','{{trans('order_detail.same_status')}}','info');
        }

        message = "{{ trans('order_detail.activate_to_user') }}";
        if( new_status == "2" ){
        message = "{{ trans('order_detail.deactivate_to_user') }}";
        }

        confirmBox("",message,"warning",function(){
        $.ajax({
        method:"POST",
        url:"/order_management/detail/{{$the_order->id}}/changeStatus",
        data:"id="+{{$the_order->id}}+"&status="+new_status,
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
    rememberTabSelection('#order_detail_tabs', !localStorage);

    if(document.location.hash){
    $("#order_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
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
    // order_authorizations();
    tab_2 = true;
    }
    else if(selectedTab == "#tab-3" && tab_3 == false){
    // order_login_history();
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
    $('#order_detail_tabs a').on('shown.bs.tab', function(event){
    var selectedTab = $(event.target).attr("href");
    load_tab_content(selectedTab);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#order_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
    load_tab_content(active_tab);
    else
    $("#order_detail_tabs a:first").trigger('click');

    $("#new_offer_assigned_id").select2({
    minimumResultsForSearch: 1
    }).val({{$the_order->offer_assigned_id}}).trigger("change");

    $("#new_user_clients").select2({
    minimumResultsForSearch: 5
    }).val({{$the_order->order_client_id}}).trigger("change");

    $("#new_assigned_id").select2({
    minimumResultsForSearch: 5
    }).val({{$the_order->assigned_id}}).trigger("change");

    $("#new_offfer_booking_id").select2({
    minimumResultsForSearch: 5
    }).val({{$the_order->booking_id}}).trigger("change");

    $("#new_order_status").select2({
    minimumResultsForSearch: 5
    }).val({{$the_order->order_status}}).trigger("change");





@endsection