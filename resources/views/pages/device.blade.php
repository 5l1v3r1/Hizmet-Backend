@extends('layouts.master')

@section('title')
    {{ trans('devices.'.$device_type.'_title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @if (Helper::has_right(Auth::user()->operations, "add_new_device"))
            <div class="row" id="div_add_new_device" style="display:none;">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5 id="modal_title"> {{ trans('devices.add_new_'.$device_type) }}</h5>
                            <div class="ibox-tools">
                                <a class="" onclick="cancel_add_new_form('#div_device_dataTable','#div_add_new_device');">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/device_management/add') }}" id="add_new_device_form" data-parsley-excluded="[disabled=disabled]">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.device_type') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_type" id="device_type" style="width:100%;" required>
                                            <option value="meter"> {{ trans('global.meter') }} </option>
                                            <option value="relay"> {{ trans('global.relay') }} </option>
                                            <option value="analyzer"> {{ trans('global.analyzer') }} </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.device_model') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_model" id="device_model" class="form-control" style="width:100%;" required>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.device_modem') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_modem" id="device_modem" class="form-control" style="width:100%;" required>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.serial_no') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="device_serial_no" name="device_serial_no" required minlength="3" maxlength="50" >
                                    </div>
                                </div>

                                <div class="form-group meter analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.current_transformer_ratio') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_current_transformer_ratio" id="device_current_transformer_ratio" style="width:100%;" required class="form-control">
                                            <option value="1"> 5/5 </option>
                                            <option value="2"> 10/5 </option>
                                            <option value="3"> 15/5 </option>
                                            <option value="4"> 20/5 </option>
                                            <option value="5"> 25/5 </option>
                                            <option value="6"> 30/5 </option>
                                            <option value="8"> 40/5 </option>
                                            <option value="10"> 50/5 </option>
                                            <option value="12"> 60/5 </option>
                                            <option value="15"> 75/5 </option>
                                            <option value="16"> 80/5 </option>
                                            <option value="20"> 100/5 </option>
                                            <option value="24"> 120/5 </option>
                                            <option value="25"> 125/5 </option>
                                            <option value="30"> 150/5 </option>
                                            <option value="32"> 160/5 </option>
                                            <option value="40"> 200/5 </option>
                                            <option value="50"> 250/5 </option>
                                            <option value="60"> 300/5 </option>
                                            <option value="70"> 350/5 </option>
                                            <option value="80"> 400/5 </option>
                                            <option value="100"> 500/5 </option>
                                            <option value="120"> 600/5 </option>
                                            <option value="150"> 750/5 </option>
                                            <option value="160"> 800/5 </option>
                                            <option value="200"> 1000/5 </option>
                                            <option value="240"> 1200/5 </option>
                                            <option value="250"> 1250/5 </option>
                                            <option value="300"> 1500/5 </option>
                                            <option value="320"> 1600/5 </option>
                                            <option value="360"> 1800/5 </option>
                                            <option value="400"> 2000/5 </option>
                                            <option value="500"> 2500/5 </option>
                                            <option value="600"> 3000/5 </option>
                                            <option value="640"> 3200/5 </option>
                                            <option value="800"> 4000/5 </option>
                                            <option value="1000"> 5000/5 </option>
                                            <option value="1200"> 6000/5 </option>
                                            <option value="1500"> 7500/5 </option>
                                            <option value="1600"> 8000/5 </option>
                                            <option value="2000"> 10000/5 </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.voltage_transformer_ratio') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_voltage_transformer_ratio" id="device_voltage_transformer_ratio" style="width:100%;" required class="form-control">
                                            <option value="1"> 1 </option>
                                            <option value="63"> 63 </option>
                                            <option value="105"> 105 </option>
                                            <option value="150"> 150 </option>
                                            <option value="158"> 158 </option>
                                            <option value="300"> 300 </option>
                                            <option value="315"> 315 </option>
                                            <option value="330"> 330 </option>
                                            <option value="345"> 345 </option>
                                            <option value="360"> 360 </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.multiplier') }} </label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="device_multiplier" name="device_multiplier" value="1" disabled required>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.data_send_period') }} (dk) <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_data_period" id="device_data_period" style="width:100%;" required class="form-control">
                                            <option value="1"> 1 </option>
                                            <option value="5"> 5 </option>
                                            <option value="15"> 15 </option>
                                            <option value="30"> 30 </option>
                                            <option value="45"> 45 </option>
                                            <option value="60"> 60 </option>
                                            <option value="120"> 120 </option>
                                            <option value="180"> 180 </option>
                                            <option value="240"> 240 </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.invoice_fee_scale') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_invoice_fee_scale" id="device_invoice_fee_scale" class="form-control" style="width:100%;" required>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.fee_scale_type') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_invoice_fee_scale_type" id="device_invoice_fee_scale_type" style="width:100%;" required class="form-control">
                                            <option value="single_rate_tariff"> {{ trans('devices.single_rate_tariff') }} </option>
                                            <option value="time_of_use_tariff"> {{ trans('devices.time_of_use_tariff') }} </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.invoice_day') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_invoice_day" id="device_invoice_day" style="width:100%;" required class="form-control">
                                            <?php
                                                for($i=1;$i<29;$i++){
                                                    echo '<option value="'.$i.'"> '.$i.' </option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.contract_power') }} (kVA) <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" value="60" class="form-control" id="device_contract_power" name="device_contract_power" required minlength="1" maxlength="11" >
                                    </div>
                                </div>

                                <div class="form-group relay analyzer device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.modbus_address') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" value="1" class="form-control" id="device_modbus_address" name="device_modbus_address" required minlength="1" maxlength="11">
                                    </div>
                                </div>

                                <div class="form-group meter device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.connection_type') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <select name="device_connection_type" id="device_connection_type" style="width:100%;" required class="form-control">
                                            <option value="optic"> {{ trans('global.optic') }} </option>
                                            <option value="rs485"> {{ trans('global.rs485') }} </option>
                                            <option value="rs232"> {{ trans('global.rs232') }} </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter device_input">
                                    <label class="col-sm-3 control-label"> {{ trans('devices.type_code') }} <span style="color:red;"></span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" value="" class="form-control" id="device_type_code" name="device_type_code" minlength="1" maxlength="100">
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-lg-3 control-label">{{ trans('devices.alerts') }} </label>
                                    <div class="col-lg-6">
                                        <select name="device_alert_definitions[]" id="device_alert_definitions" class="form-control" style="width:100%;" >
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input" id="div_device_alert_emails">
                                    <label class="col-lg-3 control-label">{{ trans('devices.alert_emails') }} <span style="color:red;">*</span></label>
                                    <div class="col-lg-6">
                                        <select name="device_alert_emails[]" id="device_alert_emails" class="form-control" style="width:100%;" data-parsley-errors-container="#device_alert_emails_error">
                                        </select>
                                        <span class="help-block" id="device_alert_emails_error" style="color:red;"></span>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input" id="div_device_alert_sms">
                                    <label class="col-lg-3 control-label">{{ trans('devices.alert_sms') }} <span style="color:red;">*</span></label>
                                    <div class="col-lg-6">
                                        <select name="device_alert_sms[]" id="device_alert_sms" class="form-control" style="width:100%;" data-parsley-errors-container="#device_alert_sms_error">
                                        </select>
                                        <span class="help-block" id="device_alert_sms_error" style="color:red;"></span>
                                    </div>
                                </div>

                                <div class="form-group meter relay analyzer device_input">
                                    <label class="col-lg-3 control-label">{{ trans('devices.explanation') }} </label>
                                    <div class="col-lg-6">
                                        <textarea placeholder="{{ trans('devices.explanation_placeholder') }}" class="form-control" rows="3" id="device_explanation" name ="device_explanation" minlength="3" maxlength="500"></textarea>
                                    </div>
                                </div>

                                <input type="hidden" value="new" id="device_op_type" name="device_op_type">
                                <input type="hidden" value="" id="device_edit_id" name="device_edit_id">

                                <br />
                                <div class="form-group">
                                    <div class="col-lg-4 col-lg-offset-3">
                                        <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                            <i class="fa fa-times"></i> {{ trans('devices.cancel') }} </button>
                                        <button type="submit" class="btn btn-primary" id="save_device_button" name="save_device_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('devices.save') }}</button>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row" id="div_device_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("devices.".$device_type."_title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo" data-html="true">
                        {!! $DataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_level_js')
    {!! $DataTableObj->js() !!}

    @if (Helper::has_right(Auth::user()->operations, "add_new_device"))
        <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
        <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
        <script>
            var device_type = "{{ $device_type }}";
            var old_device_type = "";
            var alert_definition_types = [];
            var alert_definition_change_by_auto = false;
            var first_time = true;

            function validate_save_op(){
                $("#add_new_device_form").parsley().reset();
                //$("#add_new_device_form").parsley();

                $('#add_new_device_form').parsley({
                    excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], input:hidden"
                });
            }

            function cancel_add_new_form(){
                $("#device_serial_no").val("");
                $("#device_contract_power").val("");
                $("#device_type_code").val("");
                $("#device_explanation").val("");
                $("#device_modbus_address").val("");

                $(".parsley-errors-list").remove();

                $("#device_current_transformer_ratio").val(1);
                $('#device_current_transformer_ratio').trigger('change');

                $("#device_voltage_transformer_ratio").val(1);
                $('#device_voltage_transformer_ratio').trigger('change');

                $("#device_data_period").val(1);
                $('#device_data_period').trigger('change');

                $("#device_connection_type").val("optic");
                $('#device_connection_type').trigger('change');

                $("#device_invoice_day").val(1);
                $('#device_invoice_day').trigger('change');

                $("#div_add_new_device").hide();
                $("#div_device_dataTable").show();
            }

            function prepare_modem_options(){
                // prepare modem options
                $.ajax({
                    method:"POST",
                    url:"/device_management/get_modems",
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
                            $("#device_modem").select2({
                                minimumResultsForSearch: 5,
                                data: JSON.parse(return_text),
                                templateResult: function(modem){
                                    modem_logo = "";

                                    if(modem.logo==undefined || modem.logo=="")
                                        modem_logo =  "no_avatar.png";
                                    else
                                        modem_logo = modem.logo;

                                    return $('<div style="float:left;">'+
                                            '<img style="width:55px;height:55px;" src="/img/avatar/client/'+modem_logo+'" />'+
                                            '</div>'+
                                            '<div style="float:left;margin-left:10px;font-size:14px;font-weight:bold;">'+modem.serial_no+'<br/>'+
                                            '<div style="font-size:12px;font-weight:normal;">{{ trans("devices.trademark_model") }}: '+modem.trademark+'/'+modem.model+'</div>'+
                                            '<div style="font-size:12px;font-weight:normal;">{{ trans("devices.client_distributor") }}: '+modem.client_name+'/'+modem.distributor+'</div>'+
                                            '</div>'+
                                            '<div style="clear:both;"></div>');
                                },
                                templateSelection:function(modem){
                                    return modem.serial_no;
                                }
                            });
                        }
                    }
                });
            }

            // prepare fee_scale options
            function prepare_fee_scale_options(){
                $.ajax({
                    method:"POST",
                    url:"/device_management/get_fee_scales",
                    async:false,
                    data:"",
                    success:function(return_text){
                        if(return_text == "NEXIST"){
                            alertBox('','{{ trans('devices.nexist_fee_scale') }}','info');
                        }
                        else if( return_text == "ERROR" ){
                            alertBox('','{{ trans('global.unexpected_error') }}','error');
                        }
                        else{
                            $("#device_invoice_fee_scale").select2({
                                minimumResultsForSearch: 5,
                                data: JSON.parse(return_text),
                                templateResult: function(fee_scale){
                                    return $('<div style="font-size:14px;font-weight:bold;">'+fee_scale.text+'<br/>'+
                                            '<div style="font-size:12px;font-weight:normal;">{{ trans("devices.created_by") }}: '+fee_scale.created_by+'</div>'+
                                            '</div>'+
                                            '<div style="clear:both;"></div>');
                                },
                                templateSelection:function(fee_scale){
                                    return fee_scale.text;
                                }
                            });
                        }
                    }
                });
            }

            function prepare_alert_definition_options(){
                if(first_time==false)
                    return;

                first_time = false;
                $.ajax({
                    method:"POST",
                    url:"/device_management/get_alert_definitions",
                    async:false,
                    data:"",
                    success:function(return_text){
                        if(return_text == "NEXIST"){
                            alertBox('','{{ trans('devices.nexist_alert_definitions') }}','info');
                        }
                        else if( return_text == "ERROR" ){
                            alertBox('','{{ trans('global.unexpected_error') }}','error');
                        }
                        else{

                            tmp_array = [];
                            the_data = JSON.parse(return_text);

                            $.each(the_data,function(index,value){

                                policy = JSON.parse(value.policy);
                                tmp_obj = {
                                    id:value.id,
                                    type:value.type,
                                    notification:false,
                                    email:false,
                                    sms:false,
                                    selected:false,
                                    new:false
                                };

                                if (typeof policy.notification !== 'undefined') {
                                    tmp_obj.notification = true;
                                }
                                if (typeof policy.email !== 'undefined') {
                                    tmp_obj.email = true;
                                }
                                if (typeof policy.sms !== 'undefined') {
                                    tmp_obj.sms = true;
                                }

                                tmp_array.push(tmp_obj);
                            });

                            //lookup table is being prepared
                            alert_definition_types = tmp_array;

                            $("#device_alert_definitions").select2({
                                minimumResultsForSearch: 5,
                                multiple:true,
                                placeholder:'{{ trans('devices.select_alert_definitions') }}',
                                data: JSON.parse(return_text),
                                templateResult: function(definition){

                                    icon = '';
                                    if(definition.type == "reactive"){

                                        icon = '<i class="fa fa-bolt fa-lg" style="color: #ff0000;padding-right:8px;"></i>';

                                    }
                                    else if(definition.type == "current"){

                                        icon = '<i class="fa fa-random fa-lg" style="color:#666633;padding-right:8px;"></i>';

                                    }
                                    else if(definition.type == "voltage"){

                                        icon = '<i class="fa fa-exclamation-triangle fa-lg" style="color: #ff9900;padding-right:8px;"></i>';

                                    }
                                    else if(definition.type == "connection"){

                                        icon = '<i class="fa fa-chain-broken fa-lg" style="color: #000099;padding-right:8px;"></i>';

                                    }
                                    return $('<div style="font-size:14px;font-weight:bold;">'+icon+definition.text+'<br/>'+
                                        '<div style="font-size:12px;font-weight:normal;">{{ trans("devices.created_by") }}: '+definition.created_by+'</div>'+
                                        '</div>'+
                                        '<div style="clear:both;"></div>');
                                },
                                templateSelection:function(definition){
                                    return definition.text;
                                }
                            }).on("change",function(e) {
                                if(alert_definition_change_by_auto == false){
                                    //alert(1234);
                                    selected_array = $(this).val();
                                    new_item_type = false;
                                    new_selected_array = [];
                                    $.each(alert_definition_types,function(index,value){

                                        if($.inArray(value.id+"",selected_array)>= 0){

                                            //this means this item is the new element
                                            if(alert_definition_types[index].selected == false){

                                                alert_definition_types[index].selected = true;
                                                alert_definition_types[index].new = true;
                                                new_item_type = alert_definition_types[index].type;

                                            }
                                            else{
                                                alert_definition_types[index].new = false;
                                            }

                                        }
                                        else{
                                            alert_definition_types[index].selected = false;

                                        }

                                    });

                                    $("#div_device_alert_emails, #div_device_alert_sms").hide();
                                    $("#device_alert_emails, #device_alert_sms").removeAttr('required');

                                    $.each(alert_definition_types,function(index,value){
                                        if(value.selected==true){
                                            if(value.new !=true && value.type==new_item_type){
                                                value.selected = false;
                                            }
                                            else{
                                                new_selected_array.push(value.id);
                                                if(value.sms == true) {
                                                    $("#div_device_alert_sms").show();
                                                    $("#device_alert_sms").attr('required', '');
                                                }

                                                if(value.email == true){
                                                    $("#div_device_alert_emails").show();
                                                    $("#device_alert_emails").attr('required', '');
                                                }
                                            }
                                        }

                                    });

                                    alert_definition_change_by_auto = true;

                                    //alert(new_selected_array);
                                    $(this).val(new_selected_array).trigger("change");

                                }
                                else{
                                    //alert(123);
                                    alert_definition_change_by_auto = false;
                                }

                            });
                        }
                    }
                });

                $("#device_alert_sms").select2({
                    minimumResultsForSearch: Infinity,
                    multiple:true,
                    placeholder:'{{ trans('devices.type_sms_phone') }}',
                    tags:true
                }).on("change", function(e) {
                    var isNew = $(this).find('option:not([tag_checked])');
                    if(isNew.length){
                        if(isNew.val().length==10 && Math.floor(isNew.val()) == isNew.val() && $.isNumeric(isNew.val())){
                            isNew.attr("tag_checked","");
                        }
                        else{
                            pop_up_source_error("device_alert_sms_error","{{ trans("devices.unproper_phone") }}");
                            title_val = jquery_escape(isNew.val());
                            $(this).parent().find("[title="+title_val+"]").find(".select2-selection__choice__remove").click();
                            isNew.remove();
                        }
                    }
                });

                $("#device_alert_emails").select2({
                    minimumResultsForSearch: Infinity,
                    multiple:true,
                    placeholder:'{{ trans('devices.type_emails') }}',
                    tags:true
                }).on("change", function(e) {
                    var isNew = $(this).find('option:not([tag_checked])');
                    if(isNew.length){
                        if(validateEmail(isNew.val())){
                            isNew.attr("tag_checked","");
                        }
                        else{
                            pop_up_source_error("device_alert_emails_error","{{ trans("devices.unproper_email") }}");
                            title_val = jquery_escape(isNew.val());
                            $(this).parent().find("[title="+title_val+"]").find(".select2-selection__choice__remove").click();
                            isNew.remove();
                        }
                    }
                });

            }

            function show_add_new_form(){
                $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");
                $("#modal_title").html("{{ trans('devices.add_new_'.$device_type) }}");
                $('#save_device_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("devices.save")}}');
                $("#device_op_type").val("new");
                $("#device_edit_id").val("");

                $("#div_add_new_device").show();
                $("#div_device_dataTable").hide();

                if("{{ $device_type }}" == "all_devices"){
                    $("#device_type").closest(".form-group").show();
                    device_type = "meter";
                }
                else{
                    $("#device_type").closest(".form-group").hide();
                }

                $("#device_type").select2({
                    minimumResultsForSearch: Infinity
                }).val(device_type).trigger("change");

                prepare_modem_options();
                prepare_fee_scale_options();

                old_device_type = "";
                alert_definition_change_by_auto = true;
                prepare_alert_definition_options();
                $('#device_alert_definitions').val(null).trigger('change');
                $('#device_alert_emails').val(null).trigger('change');
                $('#device_alert_sms').val(null).trigger('change');
                $("#div_device_alert_emails,#div_device_alert_sms").hide();

                $("#bg_block_screen").remove();
            }

            function edit_device(id){
                $("#device_op_type").val("edit");
                $("#device_edit_id").val(id);
                $("#modal_title").html("{{ trans('devices.update_title_'.$device_type) }}");

                $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

                if("{{ $device_type }}" == "all_devices"){
                    $("#device_type").closest(".form-group").show();
                    device_type = "meter";
                }
                else{
                    $("#device_type").closest(".form-group").hide();
                }


                // The form is filled with selected device data
                $.ajax({
                    method:"POST",
                    url:"/device_management/get_device_info",
                    data:"id="+id+"&type="+device_type,
                    async:false,
                    success:function(return_value){
                        if( $.trim(return_value) != 'NEXIST' && return_value.search("ERROR") == -1 ){

                            prepare_modem_options();
                            prepare_fee_scale_options();

                            the_info = JSON.parse(return_value);

                            $("#device_type").select2({
                                minimumResultsForSearch: Infinity
                            }).val(the_info["device_type"]).trigger("change");

                            $("#device_model").val(the_info["device_type_id"]);
                            $("#device_model").trigger('change');

                            $("#device_modem").val(the_info["modem_id"]);
                            $('#device_modem').trigger('change');

                            $('#device_serial_no').val(the_info["device_no"]);

                            $("#device_current_transformer_ratio").val(the_info["current_transformer_ratio"]);
                            $('#device_current_transformer_ratio').trigger('change');

                            $("#device_voltage_transformer_ratio").val(the_info["voltage_transformer_ratio"]);
                            $('#device_voltage_transformer_ratio').trigger('change');

                            $('#device_multiplier').val(the_info["multiplier"]);

                            $("#device_data_period").val(the_info["data_period"]);
                            $('#device_data_period').trigger('change');


                            $("#device_invoice_fee_scale").val(the_info["fee_scale_id"]);
                            $('#device_invoice_fee_scale').trigger('change');


                            $("#device_invoice_fee_scale_type").val(the_info["fee_scale_type"]);
                            $('#device_invoice_fee_scale_type').trigger('change');

                            $("#device_connection_type").val(the_info["connection_type"]);
                            $('#device_connection_type').trigger('change');

                            $("#device_invoice_day").val(the_info["invoice_day"]);
                            $('#device_invoice_day').trigger('change');

                            $('#device_modbus_address').val(the_info["modbus_address"]);
                            $('#device_contract_power').val(the_info["contract_power"]);
                            $('#device_type_code').val(the_info["type_code"]);
                            $('#device_explanation').val(the_info["explanation"]);

                            $('#save_device_button').html('<i class="fa fa-refresh"></i> {{trans("devices.update")}}');
                            $("#div_device_dataTable").hide();
                            $("#div_add_new_device").show();

                            prepare_alert_definition_options();

                            $("#div_device_alert_emails, #div_device_alert_sms").hide();
                            $('#device_alert_definitions').val(the_info["alert_definitions"].split(',')).trigger('change');


                            $("#device_alert_emails").select2({
                                minimumResultsForSearch: Infinity,
                                multiple:true,
                                placeholder:'{{ trans('devices.type_emails') }}',
                                tags:true,
                                data:JSON.parse(the_info["alert_emails"])
                            });
                            $('#device_alert_emails').val(JSON.parse(the_info["alert_emails"])).trigger('change');

                            $("#device_alert_sms").select2({
                                minimumResultsForSearch: Infinity,
                                multiple:true,
                                placeholder:'{{ trans('devices.type_sms_phone') }}',
                                tags:true,
                                data:JSON.parse(the_info["alert_phones"])
                            });
                            $('#device_alert_sms').val(JSON.parse(the_info["alert_phones"])).trigger('change');


                            //resize select2 options to beautify placeholders
                            /*$("#device_alert_sms").parent().find('.select2-search__field').css("width","100%");
                            $("#device_alert_sms").parent().find('.select2-search__field').parent().css("width","100%");

                            $("#device_alert_emails").parent().find('.select2-search__field').css("width","100%");
                            $("#device_alert_emails").parent().find('.select2-search__field').parent().css("width","100%");*/


                            $("#new_device_name").focus();
                        }
                        else{
                            alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                        }
                    }
                });

                $("#bg_block_screen").remove();
            }
        </script>
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_device"))
        <script>
            function delete_device(id,status,expired_date){
                if(status == 1){
                    ddw = "<b>" + expired_date + "</b> {{ trans('devices.active_device_delete_warning') }}";
                }
                else if(status ==2){
                    ddw = "{{ trans('devices.passive_device_delete_warning') }}";
                }

                confirmBox('',ddw,'warning',function(){
                    $.ajax({
                        method:"POST",
                        url:"/device_management/delete",
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

        </script>
    @endif
@endsection

@section('page_document_ready')
    {!! $DataTableObj->ready() !!}

    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (Helper::has_right(Auth::user()->operations, "add_new_device"))
        @if (session()->has('new_meter_insert_success') && session('new_meter_insert_success'))
            {{ session()->forget('new_meter_insert_success') }}

            custom_toastr('{{ trans('devices.add_new_meter_success') }}');
        @endif

        @if (session()->has('new_relay_insert_success') && session('new_relay_insert_success'))
            {{ session()->forget('new_relay_insert_success') }}

            custom_toastr('{{ trans('devices.add_new_relay_success') }}');
        @endif

        @if (session()->has('new_analyzer_insert_success') && session('new_analyzer_insert_success'))
            {{ session()->forget('new_analyzer_insert_success') }}

            custom_toastr('{{ trans('devices.add_new_analyzer_success') }}');
        @endif

        @if (session()->has('meter_update_success') && session('meter_update_success'))
            {{ session()->forget('meter_update_success') }}

            custom_toastr('{{ trans('devices.meter_update_success') }}');
        @endif

        @if (session()->has('relay_update_success') && session('relay_update_success'))
            {{ session()->forget('relay_update_success') }}

            custom_toastr('{{ trans('devices.relay_update_success') }}');
        @endif

        @if (session()->has('analyzer_update_success') && session('analyzer_update_success'))
            {{ session()->forget('analyzer_update_success') }}

            custom_toastr('{{ trans('devices.analyzer_update_success') }}');
        @endif

        $('#device_type').on('change', function (evt) {
            if( old_device_type == $(this).val()){
                return;
            }
            else{
                old_device_type = $(this).val();
            }

            $("#device_model").empty();

            $.ajax({
                method:"POST",
                url:"/device_management/get_devices",
                async: false,
                //data:"device_type="+($(this).val()=="all_devices"?device_type:$(this).val()),
                data:"device_type="+$(this).val(),
                success:function(return_text){
                    if(return_text == "NEXIST"){
                        alertBox('','{{ trans('devices.nexist_devices') }}','info');
                    }
                    else if( return_text == "ERROR" ){
                        alertBox('','{{ trans('global.unexpected_error') }}','error');
                    }
                    else{
                        $("#device_model").select2({
                            minimumResultsForSearch: 10,
                            data: JSON.parse(return_text)
                        });
                    }
                }
            });

            //hide/show input fields according to device_type
            $(".device_input").hide();
            $(".device_input").find(".form-control").attr("disabled","disabled");

            $("."+$(this).val()).show();
            $("."+$(this).val()).find(".form-control").removeAttr("disabled");
            $("#device_multiplier").attr("disabled","disabled");
        });


        $("#device_current_transformer_ratio, #device_voltage_transformer_ratio").select2({
            minimumResultsForSearch: 5
        }).change(function(){
            $("#device_multiplier").val($("#device_current_transformer_ratio").val() * $("#device_voltage_transformer_ratio").val());
        }).val(1).trigger("change");

        $("#device_data_period").select2({
            minimumResultsForSearch: 5
        });

        $("#device_invoice_fee_scale_type").select2({
            minimumResultsForSearch: Infinity
        });

        $("#device_invoice_day").select2({
            minimumResultsForSearch: 5
        });

        $("#device_connection_type").select2({
            minimumResultsForSearch: 5
        });
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_device"))
        @if (session()->has('device_delete_success') && session('device_delete_success'))
            {{ session()->forget('device_delete_success') }}

            custom_toastr('{{ trans('devices.device_delete_success') }}');
        @endif
    @endif

@endsection