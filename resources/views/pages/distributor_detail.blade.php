@extends('layouts.master')

@section('title')
    {{ trans('distributor_detail.title') }}
@endsection

@section('page_level_css')
    {!! $UserDataTableObj->css() !!}

    <link rel="stylesheet" type="text/css" href="/js/plugins/jsTree/themes/default/style.min.css" />
    <link rel="stylesheet" href="/css/plugins/iCheck/custom.css">
    <link rel="stylesheet" href="/css/plugins/iCheck/skins/flat/orange.css">
    <link rel="stylesheet" href="/css/plugins/iCheck/skins/flat/blue.css">
@endsection

@section('content')
    <?php
        $the_distributor = json_decode($the_distributor);
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_distributor_summary" style="margin-bottom:20px;">
            <div class="col-md-6">
                <div class="profile-image">
                    <img src="/img/avatar/distributor/{{$the_distributor->logo}}" class="img-circle circle-border m-b-md" alt="profile">
                </div>
                <div class="profile-info">
                    <div>
                        <h2 class="no-margins">
                            {{ $the_distributor->name }}
                        </h2>
                        <p style="margin: 10px 0 0;">
                            {{ trans('distributor_detail.authorized_name') }}:  <strong> {{ $the_distributor->authorized_name}} </strong>
                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('distributor_detail.email') }}:  <strong> {{ $the_distributor->email}} </strong>
                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('distributor_detail.gsm_phone') }}:  <strong> {{ $the_distributor->gsm_phone}} </strong>

                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('distributor_detail.phone') }}:  <strong> {{ $the_distributor->phone}} </strong>
                        </p>
                        <p style="margin: 10px 0 0;">
                            {{ trans('distributor_detail.address') }}:  <strong> {{ $the_distributor->location_text }} </strong>

                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <table class="table small m-b-xs">
                    <tbody>
                        <tr>
                            <td>
                                <strong>{{ trans('distributor_detail.fax') }}</strong>
                            </td>
                            <td>
                                {{ $the_distributor->fax }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('distributor_detail.tax_administration') }}</strong>
                            </td>
                            <td>
                                {{ $the_distributor->tax_administration }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('distributor_detail.tax_no') }}</strong>
                            </td>
                            <td>
                                {{ $the_distributor->tax_no }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('distributor_detail.created_by') }}</strong>
                            </td>
                            <td>
                                {{ $the_distributor->created_by }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>{{ trans('distributor_detail.created_at') }}</strong>
                            </td>
                            <td>
                                {{ date('d/m/Y H:i:s',strtotime($the_distributor->created_at)) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- #div_distributor_summary -->

        <div class="row" id="div_distributor_tabs">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="distributor_detail_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                                {{ trans('distributor_detail.users') }}
                            </a>
                        </li>
                        <li tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-handshake-o fa-lg" aria-hidden="true"></i>
                                {{ trans('distributor_detail.clients') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-podcast fa-lg" aria-hidden="true"></i>
                                {{ trans('distributor_detail.modems') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-4">
                            <a data-toggle="tab" href="#tab-4" aria-expanded="false">
                                <i class="fa fa-cogs fa-lg" aria-hidden="true"></i>
                                {{ trans('distributor_detail.devices') }}
                            </a>
                        </li>
                        <!--
                        <li class="" tab="#tab-5">
                            <a data-toggle="tab" href="#tab-5" aria-expanded="false">
                                <i class="fa fa-pie-chart fa-lg" aria-hidden="true"></i>
                                \{\{ trans('distributor_detail.statistics') }}
                            </a>
                        </li> -->
                        <li class="" tab="#tab-6">
                            <a data-toggle="tab" href="#tab-6" aria-expanded="false">
                                <i class="fa fa-bell-o fa-lg" aria-hidden="true"></i>
                                {{ trans('distributor_detail.alarms') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-7">
                            <a data-toggle="tab" href="#tab-7" aria-expanded="false">
                                <i class="fa fa-tasks fa-lg" aria-hidden="true"></i>
                                {{ trans('distributor_detail.event_logs') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-8">
                            <a data-toggle="tab" href="#tab-8" aria-expanded="false">
                                <i class="fa fa-sitemap fa-lg" aria-hidden="true"></i>
                                {{ trans('distributor_detail.organization_schema') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-9">
                            <a data-toggle="tab" href="#tab-9" aria-expanded="false">
                                <i class="fa fa-puzzle-piece fa-lg" aria-hidden="true"></i>
                                {{ trans('distributor_detail.additional_infos') }}
                            </a>
                        </li>
                    </ul> <!-- .nav -->

                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!!  $UserDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-1 -->

                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!! $ClientDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-2 -->

                        <div id="tab-3" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!!  $ModemDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-3 -->

                        <div id="tab-4" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!!  $DeviceDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-4 -->

                        <!--
                        <div id="tab-5" class="tab-pane">
                            <div class="panel-body tooltip-demo">
                                Geliştirme devam ediyor...
                            </div>
                        </div> <!-- .tab-5 -->

                        <div id="tab-6" class="tab-pane">
                            <div class="panel-body">
                                {!! $AlertsDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-6 -->

                        <div id="tab-7" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                {!! $EventDataTableObj->html() !!}
                            </div>
                        </div> <!-- .tab-7 -->

                        <div id="tab-8" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row" id="div_organization_schema" style="display: none;">
                                    <div class="col-lg-7" id="div_schema" style="margin-bottom: 30px;">

                                    </div>

                                    <div class="col-lg-5">
                                        <form class="m-t form-horizontal" id="show_node_info">
                                            {{ csrf_field() }}

                                            <div class="form-group">
                                                <label for="authorized_person"> {{ trans('organization_schema.authorized_person') }}</label>
                                                <input type="text" class="form-control" id="authorized_person" name="authorized_person" minlength="3" maxlength="100">
                                            </div>

                                            <div class="form-group">
                                                <label for="email"> {{ trans('organization_schema.email') }}</label>
                                                <input type="email" class="form-control" id="email" name="email" minlength="3" maxlength="100">
                                            </div>

                                            <div class="form-group">
                                                <label for="phone"> {{ trans('organization_schema.phone_1') }}</label>
                                                <input type="text" class="form-control" id="phone_1" name="phone_1" minlength="3" maxlength="100">
                                            </div>

                                            <div class="form-group">
                                                <label for="phone"> {{ trans('organization_schema.phone_2') }}</label>
                                                <input type="text" class="form-control" id="phone_2" name="phone_2" minlength="3" maxlength="100">
                                            </div>

                                            <div class="form-group">
                                                <button type="button" class="btn btn-primary btn-block" onclick="update_node_info();">
                                                    <i class="fa fa-check-square-o"></i> {{ trans('organization_schema.save_update') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- .tab-8 -->

                        <div id="tab-9" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">


                                <div class="row" id="div_add_new_ainfo" style="display:none;">
                                    <div class="col-lg-12">
                                        <div class="ibox float-e-margins">
                                            <div class="ibox-title">
                                                <h5 id="ainfo_title"> {{ trans('distributor_detail.add_new_ainfo') }}</h5>
                                                <div class="ibox-tools">
                                                    <a class="" onclick="cancel_add_new_form('#ainfo_table_div','#div_add_new_ainfo');">
                                                        <i class="fa fa-times"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="ibox-content">
                                                <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/distributor_management/detail/add/ainfo/'.$the_distributor->id) }}" id="add_new_ainfo">
                                                    {{ csrf_field() }}

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_detail.info_name') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6">
                                                            <input type="text" class="form-control" id="new_ainfo_name" name="new_ainfo_name" required minlength="3" maxlength="100">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-sm-3 control-label">  </label>
                                                        <div class="col-sm-6" style="padding-top: 6px;">
                                                            <div class="i-checks">
                                                                <input type="checkbox" id="ainfo_is_category" name="ainfo_is_category">
                                                                <label for="ainfo_is_category"> {{ trans('distributor_detail.is_category') }} </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group" style="display:none;" id="div_ainfo_options">
                                                        <label class="col-sm-3 control-label"> {{ trans('distributor_detail.options') }} <span style="color:red;">*</span></label>
                                                        <div class="col-sm-6" >
                                                            <select name="ainfo_options[]" id="ainfo_options" class="form-control" style="width:100%;" >
                                                            </select>
                                                            <span class="help-block" id="ainfo_options_error" style="color:red;"></span>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" value="new" id="ainfo_op_type" name="ainfo_op_type">
                                                    <input type="hidden" value="" id="ainfo_edit_id" name="ainfo_edit_id">

                                                    <div class="form-group">
                                                        <div class="col-lg-4 col-lg-offset-3">
                                                            <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                                                <i class="fa fa-times"></i> {{ trans('distributor_detail.cancel') }} </button>
                                                            <button type="submit" class="btn btn-primary" id="save_ainfo_button" name="save_ainfo_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('distributor_detail.save') }}</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="ainfo_table_div">
                                    {!! $AddInfoDataTableObj->html() !!}
                                </div>

                            </div>
                        </div> <!-- .tab-9 -->
                    </div> <!-- .tab-content -->
                </div>
            </div>
        </div> <!-- #div_distributor_tabs -->

    </div>
@endsection

@section('page_level_js')
    {!! $UserDataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/jsTree/jstree.min.js"></script>

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>

    <!-- iCheck -->
    <script src="/js/plugins/iCheck/icheck.min.js"></script>

    <script>
        @if (Helper::has_right(Auth::user()->operations, "change_alert_status"))
            function delete_alert(id){
            confirmBox('','{{ trans('alerts.delete_alert_warning') }}','warning',function(){
                $.ajax({
                    method:"POST",
                    url:"/alerts/delete",
                    data:"id="+id+"&type=one_alert",
                    success:function(return_text){
                        if(return_text == "SUCCESS"){
                            ddal_dt.ajax.reload();
                        }
                        else{
                            alertBox('','{{ trans("global.unexpected_error") }}', 'warning');
                        }
                    }
                });

            },true);
        }
        @endif

        function load_node_detail(id, distributor_id){
            $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.loading_info") }}...</div>");

            $("#show_node_info .form-control").val("");
            $("#show_node_info").find(".form-control").removeAttr("disabled");
            $("#show_node_info").find("button").removeAttr("disabled");

            $.ajax({
                method:"POST",
                url:"/organization_schema/node_detail",
                data:"id="+id+"&distributor_id="+distributor_id,
                async:false,
                success:function(return_text){
                    if( return_text != "EMPTY" && return_text != "ERROR" ){
                        the_info = JSON.parse(return_text);

                        $('#authorized_person').val(the_info.authorized_person);
                        $('#email').val(the_info.email);
                        $('#phone_1').val(the_info.phone_1);
                        $('#phone_2').val(the_info.phone_2);
                    }
                }
            });

            $("#bg_block_screen").remove();
        }

        function update_node_info(){
            if( $("#show_node_info").parsley().validate() ){
                node_id = $('#div_schema').jstree('get_selected');
                node_id = node_id[0];

                the_obj = {
                    node_id:node_id,
                    authorized_person:$("#authorized_person").val(),
                    email:$("#email").val(),
                    phone_1:$("#phone_1").val(),
                    phone_2:$("#phone_2").val(),
                };

                $.ajax({
                    method:"POST",
                    url:"/organization_schema/update_node_info",
                    data:"data="+JSON.stringify(the_obj),
                    success:function(return_text){
                        if( return_text == "SUCCESS" ){
                            alertBox('','{{ trans('organization_schema.node_updated') }}','success');               }
                        }
                });
            }
        }

        var lang_obj = {
            add: '{{ trans("organization_schema.add_node") }}',
            delete: '{{ trans("organization_schema.delete_node") }}',
            rename: '{{ trans("organization_schema.rename") }}',
            error: '{{ trans("global.unexpected_error") }}',
            node_deleted: '{{ trans("organization_schema.node_deleted") }}',
            loading: '{{ trans('global.loading') }}',
            new_node: '{{ trans('organization_schema.new_element') }}',
            delete_node_warning: '{{ trans("organization_schema.delete_node_warning") }}',
            same_name_warning: '{{ trans('organization_schema.same_name_warning') }}',
            unexpected_error: '{{ trans('global.unexpected_error') }}',
            not_deletable_warning: '{{ trans('organization_schema.not_deletable_b') }}'
        };

        function validate_save_op(){
            $("#add_new_ainfo").parsley().reset();
            $("#add_new_ainfo").parsley();

            var isChecked = $("#ainfo_is_category").prop("checked");
            if(isChecked){
                if($("#ainfo_options").val() == ""){
                    pop_up_source_error("ainfo_options_error","{{ trans("distributor_detail.required_field") }}");
                    return false;
                }
            }
        }

        function cancel_add_new_form(){
            //$("#add_new_modem_form .form-control").not('#new_modem_location_text').val("");
            $('#new_ainfo_name').val("");
            $(".parsley-errors-list").remove();

            $("#div_add_new_ainfo").hide();
            $("#ainfo_table_div").show();
        }

        function show_add_new_form(){
            $("#ainfo_op_type").val("new");
            $("#ainfo_edit_id").val("");

            $(".parsley-errors-list").remove();
            $('#new_ainfo_name').val("");
            $("#ainfo_title").html("{{ trans('distributor_detail.add_new_ainfo') }}");

            $('#ainfo_is_category').iCheck('uncheck');

            $("#ainfo_options").val(null).trigger('change');
            $("#div_ainfo_options").hide();

            $('#save_ainfo_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("distributor_detail.save")}}');
            $("#div_add_new_ainfo").show();
            $("#ainfo_table_div").hide();
        }

        /*
        function edit_modem(id) {
            $("#modem_op_type").val("edit");
            $("#modem_edit_id").val(id);
            $("#modal_title").html("\{\{ trans('modem_management.update_title') }}");

            $('body').prepend("<div id='bg_block_screen'><div class='loader'></div>\{\{ trans("global.preparing") }}...</div>");
            $.ajax({
                method:"POST",
                url:"/modem_management/get_info",
                data:"id="+id,
                async:false,
                success:function(return_value){
                    if( $.trim(return_value) != 'NEXIST' && return_value.search("ERROR") == -1 ){
                        the_info = JSON.parse(return_value);
                        the_location = JSON.parse(the_info.location);

                        $("#new_modem_serial_no").val(the_info.serial_no);

                        $("#new_modem_type").select2({
                            minimumResultsForSearch: 10
                        }).val(the_info.modem_type_id).trigger("change");

                        $("#new_modem_client").select2({
                            minimumResultsForSearch: 10
                        }).val(the_info.client_id).trigger("change");

                        $("#new_modem_location_text").val(the_location.text);
                        $("#new_modem_location_latitude").val(the_location.latitude);
                        $("#new_modem_location_longitude").val(the_location.longitude);
                        $('#new_modem_location').locationpicker({
                            location: {
                                latitude: the_location.latitude,
                                longitude: the_location.longitude
                            },
                            inputBinding: {
                                locationNameInput: $('#new_modem_location_text'),
                                latitudeInput: $('#new_modem_location_latitude'),
                                longitudeInput: $('#new_modem_location_longitude'),
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

                        $('#save_modem_button').html('<i class="fa fa-refresh"></i> \{\{trans("modem_management.update")}}');
                        $("#div_modem_dataTable").hide();
                        $("#div_add_new_modem").show();
                    }
                    else{
                        alertBox("Oops...","\{\{ trans('global.unexpected_error') }}","error");
                    }
                }
            });

            $("#bg_block_screen").remove();
        }
        */

        function delete_ainfo(id){
            confirmBox(
                '',
                '{{ trans('distributor_detail.ainfo_delete_warning') }}',
                'warning',
                function(){
                    $.ajax({
                        method:"POST",
                        url:"/distributor_management/detail/ainfo_delete",
                        data:"id="+id,
                        success:function(return_text){
                            if(return_text == "SUCCESS"){
                                location.reload();
                            }
                            else{
                                alertBox('','{{ trans("global.unexpected_error") }}', 'warning');
                            }
                        }
                    });
                },
                true
            );
        }

        function edit_ainfo(id){
            $("#ainfo_op_type").val("edit");
            $("#ainfo_edit_id").val(id);

            $('body').prepend("<div id='bg_block_screen'><div class='loader'></div>{{ trans("global.preparing") }}...</div>");

            // get the ainfo data to fill the form
            $.ajax({
                method:"POST",
                url:"/distributor_management/detail/get_ainfo",
                data:"id="+id,
                async:false,
                success:function(return_value){
                    if( $.trim(return_value) != 'NEXIST' && return_value.search("ERROR") == -1 ){
                        the_info = JSON.parse(return_value);

                        $("#new_ainfo_name").val(the_info.name);

                        if( the_info.is_category == 1 ){
                            $('#ainfo_is_category').iCheck('check');

                            $("#div_ainfo_options").show();

                            $("#ainfo_options").select2({
                                placeholder:'{{ trans('reporting.multiple_selectable') }}',
                                minimumResultsForSearch: Infinity,
                                multiple:true,
                                tags:true,
                                data:JSON.parse(the_info.options)
                            }).val(JSON.parse(the_info.options)).trigger('change');
                        }
                        else{
                            $('#ainfo_is_category').iCheck('uncheck');
                            $("#ainfo_options").val(null).trigger('change');
                            $("#div_ainfo_options").hide();
                        }

                        $('#save_ainfo_button').html('<i class="fa fa-refresh"></i> {{trans("modem_management.update")}}');

                        $("#ainfo_table_div").hide();
                        $("#div_add_new_ainfo").show();
                    }
                    else{
                        alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                    }
                }
            });

            $("#bg_block_screen").remove();
        }
    </script>
@endsection

@section('page_document_ready')
    @if (session()->has('new_ainfo_insert_success') && session('new_ainfo_insert_success'))
        {{ session()->forget('new_ainfo_insert_success') }}

        custom_toastr('{{ trans('distributor_detail.new_ainfo_insert_success') }}');
    @endif

    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_flat-orange',
        radioClass: 'iradio_flat-orange',
        cursor: true
    }).on('ifChecked', function(event){
        $("#div_ainfo_options").show();

        $("#ainfo_options").select2({
            minimumResultsForSearch: Infinity,
            multiple:true,
            tags:true,
            placeholder:'{{ trans('reporting.multiple_selectable') }}',
        });
    }).on('ifUnchecked', function(event){
        $("#div_ainfo_options").hide();
    });

    // Keep the current tab active after page reload
    rememberTabSelection('#distributor_detail_tabs', !localStorage);

    if (document.location.hash && document.location.hash == '#alarms') {
        $("#distributor_detail_tabs a[href='#tab-6']").trigger('click');
    }
    else if(document.location.hash){
        $("#distributor_detail_tabs a[href='"+document.location.hash+"']").trigger('click');
    }

    var tab_1 = false,
        tab_2 = false,
        tab_3 = false,
        tab_4 = false,
        tab_5 = false,
        tab_6 = false,
        tab_7 = false,
        tab_8 = false,
        tab_9 = false;

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            {!! $UserDataTableObj->ready() !!}
            tab_1 = true;
        }
        else if(selectedTab == "#tab-2" && tab_2 == false){
            {!! $ClientDataTableObj->ready() !!}
            tab_2 = true;
        }
        else if(selectedTab == "#tab-3" && tab_3 == false){
            {!! $ModemDataTableObj->ready() !!}
            tab_3 = true;
        }
        else if(selectedTab == "#tab-4" && tab_4 == false){
            {!! $DeviceDataTableObj->ready() !!}
            tab_4 = true;
        }
        else if(selectedTab == "#tab-5" && tab_5 == false){

            tab_5 = true;
        }
        else if(selectedTab == "#tab-6" && tab_6 == false){
            {!! $AlertsDataTableObj->ready() !!}
            tab_6 = true;
        }
        else if(selectedTab == "#tab-7" && tab_7 == false){

            tab_7 = true;
            {!! $EventDataTableObj->ready() !!}
        }
        else if(selectedTab == "#tab-8" && tab_8 == false){
            tab_8 = true;

            <!-- get organization_schema according to Auth user org_id -->
            $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

            $.ajax({
                method: "POST",
                url: "/organization_schema/get_organization_schema",
                data: "distributor_id={{ $the_distributor->id }}&show_clients=true",
                async: false,
                success: function(return_text){
                    if( return_text != "" && return_text != "ERROR" ){
                        // data, div, lang, contextMenu, checkbox ,multiple
                        createJsTree(return_text, {{ $the_distributor->id }}, 'div_schema', lang_obj);
                        $('#div_organization_schema').show();
                    }
                    else{
                        alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
                    }
                }
            });

            $("#bg_block_screen").remove();

        }
        else if(selectedTab == "#tab-9" && tab_9 == false){
            tab_9 = true;
            {!! $AddInfoDataTableObj->ready() !!}
        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#distributor_detail_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);

        // clear hash and parameter values from URL
        history.pushState('', document.title, window.location.pathname);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#distributor_detail_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#distributor_detail_tabs a:first").trigger('click');

@endsection