@extends('layouts.master')

@section('title')
    {{ trans('modem_management.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}

    <style>
        .hr-text {
            line-height: 1em;
            position: relative;
            outline: 0;
            border: 0;
            color: black;
            text-align: center;
            height: 1.5em;
            opacity: .5;
        }

        .hr-text::before {
            content: '';
            background: linear-gradient(to right, transparent, #818078, transparent);
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
        }
        .hr-text::after {
            content: attr(data-content);
            position: relative;
            display: inline-block;
            color: black;

            padding: 0 .5em;
            line-height: 1.5em;

            color: #818078;
            background-color: #fcfcfa;
        }

    </style>
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @if (Helper::has_right(Auth::user()->operations, "add_new_modem"))
            <div class="row" id="div_add_new_modem" style="display:none;">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5 id="modal_title"> {{ trans('modem_management.add_new_modem') }}</h5>
                            <div class="ibox-tools">
                                <a class="" onclick="cancel_add_new_form('#div_modem_dataTable','#div_add_new_modem');">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/modem_management/add') }}" id="add_new_modem_form">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('modem_management.serial_no') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="new_modem_serial_no" name="new_modem_serial_no" required minlength="3" maxlength="255">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('modem_management.distinctive_identifier') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="new_modem_distinctive_identifier" name="new_modem_distinctive_identifier" placeholder="{{ trans('modem_management.distinctive_identifier_plh') }}" required minlength="3" maxlength="255">
                                    </div>
                                </div>

                                {!!  Helper::get_modem_type("new_modem_type") !!}


                                <!--
                                \{\!!  Helper::get_clients_select("new_modem_client") !!}
                                -->

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.device_modem') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="new_modem_client" id="new_modem_client" class="form-control" style="width:100%;" required>
                                        </select>
                                    </div>
                                </div>

                                <div id="div_ainfo" style="display:none;">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <hr class="hr-text" data-content="{{ trans("client_management.additional_info") }}">
                                        </div>
                                    </div>

                                    <div id="div_ainfo_elements">

                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <hr class="hr-text" data-content="">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom: 5px;">
                                    <label class="col-sm-3 control-label"> {{ trans('modem_management.location') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="new_modem_location_text" name="new_modem_location_text" required minlength="3" maxlength="255">
                                    </div>
                                </div>
                                <input type="hidden" name="new_modem_location_latitude" id="new_modem_location_latitude" value=""/>
                                <input type="hidden" name="new_modem_location_longitude" id="new_modem_location_longitude" value=""/>

                                <div class="form-group">
                                    <div class="col-sm-6 col-sm-offset-3">
                                        <div id="new_modem_location" style="width: 100%; height: 300px;"></div>
                                    </div>
                                </div>

                                <div class="form-group">

                                    <label class="col-sm-3 control-label"> {{ trans('modem_management.temperature_center')
                                    }}</label>

                                    <div class="col-sm-6">
                                        <select name="airports" id="airports" class="form-control" style="width:100%;" >

                                        </select>
                                    </div>

                                </div>

                                <div class="form-group" >
                                    <label class="col-sm-3 control-label"> {{ trans('modem_management.explanation') }} </label>
                                    <div class="col-sm-6">
                                        <textarea type="text" class="form-control" id="new_modem_explanation" name="new_modem_explanation"  minlength="3" maxlength="500"></textarea>
                                    </div>
                                </div>

                                <input type="hidden" value="new" id="modem_op_type" name="modem_op_type">
                                <input type="hidden" value="" id="modem_edit_id" name="modem_edit_id">

                                <div class="form-group">
                                    <div class="col-lg-4 col-lg-offset-3">
                                        <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                            <i class="fa fa-times"></i> {{ trans('modem_management.cancel') }} </button>
                                        <button type="submit" class="btn btn-primary" id="save_modem_button" name="save_modem_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('modem_management.save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row" id="div_modem_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("modem_management.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
                        {!! $DataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_level_js')
    {!! $DataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>

    <script type="text/javascript" language="javascript" src='http://maps.google.com/maps/api/js?key=AIzaSyDAhJzAfuGq9J9-f_NGriGvs_8c2BWfRqc&libraries=places'></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/locationPicker/locationpicker.jquery.min.js"></script>

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_modem"))
                function validate_save_op(){
                    $("#add_new_modem_form").parsley().reset();
                    $("#add_new_modem_form").parsley();
                }

                function cancel_add_new_form(){
                    //$("#add_new_modem_form .form-control").not('#new_modem_location_text').val("");
                    $('#new_modem_serial_no').val("");
                    $('#new_modem_explanation').val("");
                    $(".parsley-errors-list").remove();

                    $("#div_add_new_modem").hide();
                    $("#div_modem_dataTable").show();
                }

                function show_add_new_form(){
                    $("#modem_op_type").val("new");
                    $("#modem_edit_id").val("");
                    $("#modal_title").html("{{ trans('modem_management.add_new_modem') }}");

                    $('#save_modem_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("modem_management.save")}}');
                    $("#div_add_new_modem").show();
                    $("#div_modem_dataTable").hide();

                    prepare_client_options();

                    $('#new_modem_location').locationpicker({
                        location: {
                            latitude: 40.980568,
                            longitude: 29.0887487
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
                        },
                        onchanged:function(currentLocation, radius, isMarkerDropped){
                            $.ajax({
                                method:"POST",
                                url:"/modem_management/get_airports",
                                data:"data_obj="+JSON.stringify(currentLocation),
                                success:function(return_text){

                                    $("#airports").select2().empty();
                                    $("#airports").select2({
                                        minimumResultsForSearch: 10,
                                        data: JSON.parse(return_text)
                                    }).change(function(){

                                    });
                                }
                            });

                        },
                        oninitialized: function(currentLocation, component){
                            $.ajax({
                                method:"POST",
                                url:"/modem_management/get_airports",
                                data:"data_obj="+JSON.stringify({"latitude":40.980568,"longitude":29.0887487}),
                                success:function(return_text){
                                    $("#airports").select2().empty();
                                    $("#airports").select2({
                                        minimumResultsForSearch: 10,
                                        data: JSON.parse(return_text)
                                    }).change(function(){

                                    });
                                }
                            });
                        }
                    });
                }

                function edit_modem(id) {
                    $("#modem_op_type").val("edit");
                    $("#modem_edit_id").val(id);
                    $("#modal_title").html("{{ trans('modem_management.update_title') }}");

                    $('body').prepend("<div id='bg_block_screen'><div class='loader'></div>{{ trans("global.preparing") }}...</div>");

                    prepare_client_options();

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
                                $("#new_modem_distinctive_identifier").val(the_info.distinctive_identifier);

                                $("#new_modem_type").select2({
                                    minimumResultsForSearch: 10
                                }).val(the_info.modem_type_id).trigger("change");

                                $("#new_modem_client").select2({
                                    minimumResultsForSearch: 10
                                }).val(the_info.client_id).trigger("change");

                                $("#new_modem_location_text").val(the_location.text);
                                $("#new_modem_location_latitude").val(the_location.latitude);
                                $("#new_modem_location_longitude").val(the_location.longitude);

                                $.ajax({
                                    method:"POST",
                                    url:"/modem_management/get_airports",
                                    async: false,
                                    data:"data_obj="+JSON.stringify({"latitude":the_location.latitude,"longitude":the_location.longitude}),
                                    success:function(return_text){
                                        $("#airports").select2().empty();
                                        $("#airports").select2({
                                            minimumResultsForSearch: 10,
                                            data: JSON.parse(return_text)
                                        }).change(function(){

                                        });
                                    }
                                });

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
                                    },
                                    onchanged:function(currentLocation, radius, isMarkerDropped){
                                        $.ajax({
                                            method:"POST",
                                            url:"/modem_management/get_airports",
                                            data:"data_obj="+JSON.stringify(currentLocation),
                                            success:function(return_text){

                                                $("#airports").select2().empty();
                                                $("#airports").select2({
                                                    minimumResultsForSearch: 10,
                                                    data: JSON.parse(return_text)
                                                }).change(function(){

                                                });
                                            }
                                        });
                                    },
                                    oninitialized: function(component){

                                    }
                                });

                                if( the_info.airport_id != 0 ){
                                    $("#airports").val(the_info.airport_id).trigger('change');
                                }

                                $('#save_modem_button').html('<i class="fa fa-refresh"></i> {{trans("modem_management.update")}}');
                                $("#div_modem_dataTable").hide();
                                $("#div_add_new_modem").show();
                            }
                            else{
                                alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                            }
                        }
                    });

                    $("#bg_block_screen").remove();
                }

                function prepare_client_options(){
                    $.ajax({
                        method:"POST",
                        url:"/modem_management/get_clients",
                        data:"",
                        async:false,
                        success:function(return_text){
                            if(return_text == "NEXIST"){
                                alertBox('','{{ trans('devices.nexist_modems') }}','info');
                            }
                            else if( return_text == "ERROR" ){
                                alertBox('','{{ trans('global.unexpected_error') }}','error');
                            }
                            else{
                                $("#new_modem_client").select2({
                                    minimumResultsForSearch: 10,
                                    data: JSON.parse(return_text),
                                    templateResult: function(client){
                                        client_logo = "";

                                        if(client.logo==undefined || client.logo=="")
                                            client_logo =  "no_avatar.png";
                                        else
                                            client_logo = client.logo;

                                        return $(
                                            '<div style="float:left;">'+
                                                '<img style="width:55px;height:55px;" src="/img/avatar/client/'+client_logo+'" />'+
                                            '</div>'+
                                            '<div style="float:left;margin-left:10px;font-size:14px;font-weight:bold;">'+
                                                client.text + '<br/>' +
                                                '<div style="font-size:12px;font-weight:normal;"> ' +
                                                    '{{ trans("modem_management.location") }}: '+client.location_verbal+
                                                '</div>'+
                                                '<div style="font-size:12px;font-weight:normal;"> ' +
                                                    ' {{ trans("modem_management.distributor") }}: '+client.distributor +
                                                '</div>'+
                                            '</div>'+
                                            '<div style="clear:both;"></div>');
                                    },
                                    templateSelection:function(client){
                                        return client.text;
                                    }
                                });

                                $('#new_modem_client').val($('#new_modem_client option:first-child').val()).trigger('change');
                            }
                        }
                    });
                }

                function load_additional_info(id){
                    $('#div_ainfo').hide();
                    $("#div_ainfo_elements").html("");
                    op_type = $('#modem_op_type').val();
                    modem_id = $('#modem_edit_id').val();

                    $.ajax({
                        method: "POST",
                        url: "/modem_management/get_add_infos",
                        data: "client_id="+id+"&op_type="+op_type+"&modem_id="+modem_id,
                        async: false,
                        success: function(return_text){
                            if( return_text != "" && return_text != "ERROR" ){
                                if(return_text !="EMPTY"){
                                    $('#div_ainfo').show();
                                    $("#div_ainfo_elements").html(return_text);
                                }
                            }
                            else{
                                alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
                            }
                        }
                    });
                }
        @endif

        @if (Helper::has_right(Auth::user()->operations, "delete_modem"))
            function delete_modem(id){
                confirmBox('','{{ trans('modem_management.delete_modem_warning') }}','warning',function(){

                        $.ajax({
                            method:"POST",
                            url:"/modem_management/delete",
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

                    },true);
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

    @if ( Helper::has_right(Auth::user()->operations,'add_new_modem') )
        $("#new_modem_type").select2({
            minimumResultsForSearch: 10
        });

        $("#new_modem_client").select2({
            minimumResultsForSearch: 10
        }).on('change', function(){
            the_val = $(this).val();

            load_additional_info(the_val);

        });

        <!--REMEMBER!!!-->
        <!--This portion of code must be located in the modem details page-->
        @if (session()->has('new_modem_insert_success') && session('new_modem_insert_success'))
            {{ session()->forget('new_modem_insert_success') }}

            custom_toastr('{{ trans('modem_management.add_new_success') }}');
        @endif

        @if (session()->has('modem_update_success') && session('modem_update_success'))
            {{ session()->forget('modem_update_success') }}

            custom_toastr('{{ trans('modem_management.update_success') }}');
        @endif
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_modem"))
        @if (session()->has('modem_delete_success') && session('modem_delete_success'))
            {{ session()->forget('modem_delete_success') }}

            custom_toastr('{{ trans('modem_management.delete_success') }}');
        @endif
    @endif
@endsection