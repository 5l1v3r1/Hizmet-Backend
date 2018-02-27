@extends('layouts.master')

@section('title')
    {{ trans('alerts.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}

    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css" />

    <style>
        .detail-backgorund{
            background-color: #fAfAf8;
        }
    </style>
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="alerts_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-bell-o fa-lg" aria-hidden="true"></i>
                                {{ trans('alerts.title') }}
                            </a>
                        </li>
                        @if (Helper::has_right(Auth::user()->operations, "add_new_alert_definition"))
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-bullseye fa-lg" aria-hidden="true"></i>
                                {{ trans('alerts.definitions') }}
                            </a>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        {!! $DataTableObj->html() !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (Helper::has_right(Auth::user()->operations, "add_new_alert_definition"))
                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        @if (Helper::has_right(Auth::user()->operations, "add_new_alert_definition"))
                                            <div class="row" id="div_add_new_definition" style="display:none;">
                                                <div class="col-lg-12">
                                                    <div class="ibox float-e-margins">
                                                        <div class="ibox-title">
                                                            <h5 id="modal_title"> {{ trans('alerts.add_new_definition') }}</h5>
                                                            <div class="ibox-tools">
                                                                <a class="" onclick="cancel_add_new_form('#div_definition_dataTable','#div_add_new_definition');">
                                                                    <i class="fa fa-times"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="ibox-content tooltip-demo" data-html="true" style="border-top-width: 0px;">
                                                            <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/alerts/add') }}" id="add_new_definition_form">
                                                                {{ csrf_field() }}

                                                                <div class="form-group">
                                                                    <label class="col-sm-3 control-label"> {{ trans('alerts.definition_type') }} <span style="color:red;">*</span></label>
                                                                    <div class="col-sm-6">
                                                                        <select class="form-horizontal" id="new_definition_type" name="new_definition_type" style="width: 100%;" required minlength="1" maxlength="15">
                                                                            <option value="reactive">{{ trans("alerts.reactive") }}</option>
                                                                            <option value="connection">{{ trans("alerts.connection") }}</option>
                                                                            <option value="voltage">{{ trans("alerts.voltage") }}</option>
                                                                            <option value="current">{{ trans("alerts.current") }}</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label class="col-sm-3 control-label"> {{ trans('alerts.name') }} <span style="color:red;">*</span></label>
                                                                    <div class="col-sm-6">
                                                                        <input type="text" class="form-control" id="new_definition_name" name="new_definition_name" required minlength="3" maxlength="255">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label class="col-sm-3 control-label"> {{ trans('alerts.notification') }} </label>
                                                                    <div class="col-sm-6" position="relative;">
                                                                        <i onclick="manage_fieldset($(this),$('#notification_policy_div'));" class="fa fa-square-o fa-2x" id="notification_checkbox" style="color:#676a6c;cursor:pointer;"></i> <span style="color:#676a6c;position:absolute;padding-left:8px;top:3px;">{{ trans("alerts.notification_exp") }}</span>
                                                                        <div id="notification_policy_div" style="display:none;">
                                                                            <div class="definition_reactive_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.inductive_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.inductive_limit_exp") }}"></i>
                                                                                            </span>
                                                                                            <input type="text" class="form-control" id="n_inductive_limit" name="n_inductive_limit" value="10" required minlength="1" maxlength="3" data-parsley-type="digits">
                                                                                            <span class="input-group-addon">
                                                                                                %
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.capacitive_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.capacitive_limit_exp") }}"></i>
                                                                                            </span>
                                                                                            <input type="text" class="form-control" id="n_capacitive_limit" name="n_capacitive_limit" value="8" required minlength="1" maxlength="3" data-parsley-type="digits">

                                                                                            <span class="input-group-addon">
                                                                                                %
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.consumption_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.consumption_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="n_consumption_limit" name="n_consumption_limit" value="0" required minlength="1" maxlength="10" data-parsley-type="digits">

                                                                                            <span class="input-group-addon">
                                                                                                kW
                                                                                            </span>
                                                                                        </div>

                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.calculation_period') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.calculation_period_exp") }}"></i>
                                                                                            </span>

                                                                                            <select class="form-control focus-class" name="n_calculation_period" id="n_calculation_period" style="width:100%;" required>
                                                                                                <option value="0">{{ trans("alerts.since_invoice_day") }}</option>
                                                                                                <?php
                                                                                                    for($i=1; $i<31;$i++){
                                                                                                        echo '<option value="'.$i.'">'.trans("alerts.since_day",array("day"=>$i)).'</option>';
                                                                                                    }
                                                                                                ?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_reactive_inputs -->

                                                                            <div style="display: none;" class="definition_current_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.unbalanced_current') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="n_unbalanced_current_status"
                                                                                                name="n_unbalanced_current_status"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.higher_than_5A') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="n_5A_current_status"
                                                                                                name="n_5A_current_status"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_current_inputs -->

                                                                            <div style="display: none;" class="definition_voltage_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.lower_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.voltage_lower_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="n_voltage_lower_limit" name="n_voltage_lower_limit" value="200" required minlength="1" maxlength="5" data-parsley-type="digits">

                                                                                            <span class="input-group-addon">
                                                                                                V
                                                                                            </span>
                                                                                        </div>

                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.upper_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.voltage_upper_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="n_voltage_upper_limit" name="n_voltage_upper_limit" value="240" required minlength="1" maxlength="5" data-parsley-type="digits">

                                                                                            <span class="input-group-addon">
                                                                                                V
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_voltage_inputs -->

                                                                            <div style="display: none;" class="definition_connection_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.device_connection') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="n_device_connection"
                                                                                                name="n_device_connection"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.modem_connection') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="n_modem_connection"
                                                                                                name="n_modem_connection"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.duration') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.duration_exp") }}"></i>
                                                                                            </span>
                                                                                            <select class="form-control focus-class" name="n_duration" id="n_duration" style="width:100%;">
                                                                                                <option value="6"> {{  trans("alerts.duration_hour",array("hour"=>6)) }} </option>
                                                                                                <option value="12"> {{  trans("alerts.duration_hour",array("hour"=>12)) }} </option>
                                                                                                <option value="24" selected> {{  trans("alerts.duration_day",array("day"=>1)) }} </option>
                                                                                                <option value="48"> {{  trans("alerts.duration_day",array("day"=>2)) }} </option>
                                                                                                <option value="72"> {{  trans("alerts.duration_day",array("day"=>3)) }} </option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_connection_inputs -->

                                                                            <div class="form-group">
                                                                                <label class="col-sm-5 control-label"> {{ trans('alerts.notification_period') }} <span style="color:red;">*</span></label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="n_notification_period" id="n_notification_period" style="width:100%;" required>
                                                                                            <option value="periodic"> {{ trans('alerts.periodic') }} </option>
                                                                                            <option value="daily"> {{ trans('alerts.daily') }} </option>
                                                                                            <option value="definite_day"> {{ trans('alerts.definite_day') }} </option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_periodic_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="n_notification_period_periodic" id="n_notification_period_periodic" style="width:100%;" required>
                                                                                            <?php

                                                                                            for($i=1; $i<31;$i++){
                                                                                                echo '<option value="'.$i.'">'.trans("alerts.notification_day",array("day"=>$i)).'</option>';
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group" style="display:none;">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_daily_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="n_notification_period_daily" id="n_notification_period_daily" style="width:100%;" required >
                                                                                            <option value="monday"> {{ trans('alerts.daily_day', array('day' => trans('devices.monday'))) }} </option>
                                                                                            <option value="tuesday"> {{ trans('alerts.daily_day', array('day' => trans('devices.tuesday'))) }} </option>
                                                                                            <option value="wednesday"> {{ trans('alerts.daily_day', array('day' => trans('devices.wednesday'))) }} </option>
                                                                                            <option value="thursday"> {{ trans('alerts.daily_day', array('day' => trans('devices.thirsday'))) }} </option>
                                                                                            <option value="friday"> {{ trans('alerts.daily_day', array('day' => trans('devices.friday'))) }} </option>
                                                                                            <option value="saturday"> {{ trans('alerts.daily_day', array('day' => trans('devices.saturday'))) }} </option>
                                                                                            <option value="sunday"> {{ trans('alerts.daily_day', array('day' => trans('devices.sunday'))) }} </option>
                                                                                        </select>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group" style="display:none;">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_definite_day_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="n_notification_period_definite_day" id="n_notification_period_definite_day" style="width:100%;" required >
                                                                                            <?php

                                                                                            for($i=1; $i<31;$i++){
                                                                                                echo '<option value="'.$i.'">'.trans("alerts.month_day",array("day"=>$i)).'</option>';
                                                                                            }
                                                                                            ?>
                                                                                        </select>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- form-group (notification period) -->
                                                                        </div>
                                                                    </div> <!-- .col-sm-6 -->
                                                                </div> <!-- form-group (notification) -->

                                                                <div class="form-group">
                                                                    <label class="col-sm-3 control-label"> {{ trans('alerts.email') }} </label>
                                                                    <div class="col-sm-6">
                                                                        <i onclick="manage_fieldset($(this),$('#email_policy_div'));" class="fa fa-square-o fa-2x" id="email_checkbox" style="color:#676a6c;cursor:pointer;"></i>
                                                                        <span style="color:#676a6c;position:absolute;padding-left:8px;top:3px;">{{ trans("alerts.email_exp") }}</span>
                                                                        <div id="email_policy_div" style="display:none;">
                                                                            <div class="definition_reactive_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.inductive_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.inductive_limit_exp") }}"></i>
                                                                                            </span>
                                                                                            <input type="text" class="form-control" id="e_inductive_limit" name="e_inductive_limit" value="15" required minlength="1" maxlength="3" data-parsley-type="number">
                                                                                            <span class="input-group-addon">
                                                                                                %
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.capacitive_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.capacitive_limit_exp") }}"></i>
                                                                                            </span>
                                                                                            <input type="text" class="form-control" id="e_capacitive_limit" name="e_capacitive_limit" value="10" required minlength="1" maxlength="3" data-parsley-type="number">

                                                                                            <span class="input-group-addon">
                                                                                                %
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.consumption_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.consumption_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="e_consumption_limit" name="e_consumption_limit" value="0" required minlength="1" maxlength="3" data-parsley-type="number">

                                                                                            <span class="input-group-addon">
                                                                                                kW
                                                                                            </span>
                                                                                        </div>

                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.calculation_period') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.calculation_period_exp") }}"></i>
                                                                                            </span>

                                                                                            <select class="form-control focus-class" name="e_calculation_period" id="e_calculation_period" style="width:100%;">
                                                                                                <option value="0">{{ trans("alerts.since_invoice_day") }}</option>
                                                                                                <?php
                                                                                                for($i=1; $i<31;$i++){
                                                                                                    echo '<option value="'.$i.'">'.trans("alerts.since_day",array("day"=>$i)).'</option>';
                                                                                                }
                                                                                                ?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_reactive_inputs -->

                                                                            <div style="display: none;" class="definition_current_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.unbalanced_current') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="e_unbalanced_current_status"
                                                                                                name="e_unbalanced_current_status"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.higher_than_5A') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="e_5A_current_status"
                                                                                                name="e_5A_current_status"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_current_inputs -->

                                                                            <div style="display: none;" class="definition_voltage_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.lower_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.voltage_lower_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="e_voltage_lower_limit" name="e_voltage_lower_limit" value="190" required minlength="1" maxlength="3" data-parsley-type="number">

                                                                                            <span class="input-group-addon">
                                                                                                V
                                                                                            </span>
                                                                                        </div>

                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.upper_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.voltage_upper_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="e_voltage_upper_limit" name="e_voltage_upper_limit" value="250" required minlength="1" maxlength="3" data-parsley-type="number">

                                                                                            <span class="input-group-addon">
                                                                                                V
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_voltage_inputs -->

                                                                            <div style="display: none;" class="definition_connection_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.device_connection') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="e_device_connection"
                                                                                                name="e_device_connection"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.modem_connection') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="e_modem_connection"
                                                                                                name="e_modem_connection"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.duration') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.duration_exp") }}"></i>
                                                                                            </span>
                                                                                            <select class="form-control focus-class" name="e_duration" id="e_duration" style="width:100%;">
                                                                                                <option value="6"> {{  trans("alerts.duration_hour",array("hour"=>6)) }} </option>
                                                                                                <option value="12"> {{  trans("alerts.duration_hour",array("hour"=>12)) }} </option>
                                                                                                <option value="24" selected> {{  trans("alerts.duration_day",array("day"=>1)) }} </option>
                                                                                                <option value="48"> {{  trans("alerts.duration_day",array("day"=>2)) }} </option>
                                                                                                <option value="72"> {{  trans("alerts.duration_day",array("day"=>3)) }} </option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_connection_inputs -->


                                                                            <div class="form-group">
                                                                                <label class="col-sm-5 control-label"> {{ trans('alerts.notification_period') }} <span style="color:red;">*</span></label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="e_notification_period" id="e_notification_period" style="width:100%;" required>
                                                                                            <option value="periodic"> {{ trans('alerts.periodic') }} </option>
                                                                                            <option value="daily"> {{ trans('alerts.daily') }} </option>
                                                                                            <option value="definite_day"> {{ trans('alerts.definite_day') }} </option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_periodic_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="e_notification_period_periodic" id="e_notification_period_periodic" style="width:100%;" required>
                                                                                            <?php

                                                                                            for($i=1; $i<31;$i++){
                                                                                                echo '<option value="'.$i.'">'.trans("alerts.notification_day",array("day"=>$i)).'</option>';
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group" style="display:none;">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_daily_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="e_notification_period_daily" id="e_notification_period_daily" style="width:100%;" required >
                                                                                            <option value="monday"> {{ trans('alerts.daily_day', array('day' => trans('devices.monday'))) }} </option>
                                                                                            <option value="tuesday"> {{ trans('alerts.daily_day', array('day' => trans('devices.tuesday'))) }} </option>
                                                                                            <option value="wednesday"> {{ trans('alerts.daily_day', array('day' => trans('devices.wednesday'))) }} </option>
                                                                                            <option value="thursday"> {{ trans('alerts.daily_day', array('day' => trans('devices.thirsday'))) }} </option>
                                                                                            <option value="friday"> {{ trans('alerts.daily_day', array('day' => trans('devices.friday'))) }} </option>
                                                                                            <option value="saturday"> {{ trans('alerts.daily_day', array('day' => trans('devices.saturday'))) }} </option>
                                                                                            <option value="sunday"> {{ trans('alerts.daily_day', array('day' => trans('devices.sunday'))) }} </option>
                                                                                        </select>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group" style="display:none;">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_definite_day_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="e_notification_period_definite_day" id="e_notification_period_definite_day" style="width:100%;" required >
                                                                                            <?php

                                                                                            for($i=1; $i<31;$i++){
                                                                                                echo '<option value="'.$i.'">'.trans("alerts.month_day",array("day"=>$i)).'</option>';
                                                                                            }
                                                                                            ?>
                                                                                        </select>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- form-group (notification period) -->
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label class="col-sm-3 control-label"> {{ trans('alerts.sms') }} </label>
                                                                    <div class="col-sm-6">
                                                                        <i onclick="manage_fieldset($(this),$('#sms_policy_div'));" class="fa fa-square-o fa-2x" id="sms_checkbox" style="color:#676a6c;cursor:pointer;"></i><span style="color:#676a6c;position:absolute;padding-left:8px;top:3px;">{{ trans("alerts.sms_exp") }}</span>
                                                                        <div id="sms_policy_div" style="display:none;">
                                                                            <div class="definition_reactive_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.inductive_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.inductive_limit_exp") }}"></i>
                                                                                            </span>
                                                                                            <input type="text" class="form-control" id="s_inductive_limit" name="s_inductive_limit" value="18" required minlength="1" maxlength="3" data-parsley-type="number">
                                                                                            <span class="input-group-addon">
                                                                                                %
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.capacitive_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.capacitive_limit_exp") }}"></i>
                                                                                            </span>
                                                                                            <input type="text" class="form-control" id="s_capacitive_limit" name="s_capacitive_limit" value="18" required minlength="1" maxlength="3" data-parsley-type="number">

                                                                                            <span class="input-group-addon">
                                                                                                %
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.consumption_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.consumption_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="s_consumption_limit" name="s_consumption_limit" value="0" required minlength="1" maxlength="3" data-parsley-type="number">

                                                                                            <span class="input-group-addon">
                                                                                                kW
                                                                                            </span>
                                                                                        </div>

                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.calculation_period') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.calculation_period_exp") }}"></i>
                                                                                            </span>

                                                                                            <select class="form-control focus-class" name="s_calculation_period" id="s_calculation_period" style="width:100%;">
                                                                                                <option value="0">{{ trans("alerts.since_invoice_day") }}</option>
                                                                                                <?php
                                                                                                for($i=1; $i<31;$i++){
                                                                                                    echo '<option value="'.$i.'">'.trans("alerts.since_day",array("day"=>$i)).'</option>';
                                                                                                }
                                                                                                ?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_reactive_inputs -->

                                                                            <div style="display: none;" class="definition_current_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.unbalanced_current') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="s_unbalanced_current_status"
                                                                                                name="s_unbalanced_current_status"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.higher_than_5A') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="s_5A_current_status"
                                                                                                name="s_5A_current_status"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_current_inputs -->

                                                                            <div style="display: none;" class="definition_voltage_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.lower_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.voltage_lower_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="s_voltage_lower_limit" name="s_voltage_lower_limit" value="180" required minlength="1" maxlength="3" data-parsley-type="number">

                                                                                            <span class="input-group-addon">
                                                                                                V
                                                                                            </span>
                                                                                        </div>

                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.upper_limit') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.voltage_upper_limit_exp") }}"></i>
                                                                                            </span>

                                                                                            <input type="text" class="form-control" id="s_voltage_upper_limit" name="s_voltage_upper_limit" value="270" required minlength="1" maxlength="3" data-parsley-type="number">

                                                                                            <span class="input-group-addon">
                                                                                                V
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_voltage_inputs -->

                                                                            <div style="display: none;" class="definition_connection_inputs">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.device_connection') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="s_device_connection"
                                                                                                name="s_device_connection"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.modem_connection') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo" style="padding-top: 7px;">
                                                                                        <input
                                                                                                class="form-control"
                                                                                                id="s_modem_connection"
                                                                                                name="s_modem_connection"
                                                                                                type="checkbox"
                                                                                                data-on-text="{{trans('user_detail.active')}}"
                                                                                                data-off-text="{{trans('user_detail.inactive')}}"
                                                                                                data-on-color="success"
                                                                                                data-off-color="danger"
                                                                                                data-size="mini"
                                                                                                checked
                                                                                        >
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label class="col-sm-5 control-label"> {{ trans('alerts.duration') }} <span style="color:red;">*</span></label>
                                                                                    <div class="col-sm-7 tooltip-demo">
                                                                                        <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.duration_exp") }}"></i>
                                                                                            </span>
                                                                                            <select class="form-control focus-class" name="s_duration" id="s_duration" style="width:100%;">
                                                                                                <option value="6"> {{  trans("alerts.duration_hour",array("hour"=>6)) }} </option>
                                                                                                <option value="12"> {{  trans("alerts.duration_hour",array("hour"=>12)) }} </option>
                                                                                                <option value="24" selected> {{  trans("alerts.duration_day",array("day"=>1)) }} </option>
                                                                                                <option value="48"> {{  trans("alerts.duration_day",array("day"=>2)) }} </option>
                                                                                                <option value="72"> {{  trans("alerts.duration_day",array("day"=>3)) }} </option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> <!-- .definition_connection_inputs -->

                                                                            <div class="form-group">
                                                                                <label class="col-sm-5 control-label"> {{ trans('alerts.notification_period') }} <span style="color:red;">*</span></label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="s_notification_period" id="s_notification_period" style="width:100%;" required>
                                                                                            <option value="periodic"> {{ trans('alerts.periodic') }} </option>
                                                                                            <option value="daily"> {{ trans('alerts.daily') }} </option>
                                                                                            <option value="definite_day"> {{ trans('alerts.definite_day') }} </option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_periodic_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="s_notification_period_periodic" id="s_notification_period_periodic" style="width:100%;" required>
                                                                                            <?php

                                                                                            for($i=1; $i<31;$i++){
                                                                                                echo '<option value="'.$i.'">'.trans("alerts.notification_day",array("day"=>$i)).'</option>';
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group" style="display:none;">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_daily_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="s_notification_period_daily" id="s_notification_period_daily" style="width:100%;" required >
                                                                                            <option value="monday"> {{ trans('alerts.daily_day', array('day' => trans('devices.monday'))) }} </option>
                                                                                            <option value="tuesday"> {{ trans('alerts.daily_day', array('day' => trans('devices.tuesday'))) }} </option>
                                                                                            <option value="wednesday"> {{ trans('alerts.daily_day', array('day' => trans('devices.wednesday'))) }} </option>
                                                                                            <option value="thursday"> {{ trans('alerts.daily_day', array('day' => trans('devices.thirsday'))) }} </option>
                                                                                            <option value="friday"> {{ trans('alerts.daily_day', array('day' => trans('devices.friday'))) }} </option>
                                                                                            <option value="saturday"> {{ trans('alerts.daily_day', array('day' => trans('devices.saturday'))) }} </option>
                                                                                            <option value="sunday"> {{ trans('alerts.daily_day', array('day' => trans('devices.sunday'))) }} </option>
                                                                                        </select>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group" style="display:none;">
                                                                                <label class="col-sm-5 control-label"> </label>
                                                                                <div class="col-sm-7 tooltip-demo">
                                                                                    <div class="input-group">
                                                                                            <span class="input-group-addon">
                                                                                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="auto" title="{{ trans("alerts.notification_period_definite_day_exp") }}"></i>
                                                                                            </span>
                                                                                        <select class="form-control focus-class" name="s_notification_period_definite_day" id="s_notification_period_definite_day" style="width:100%;" required >
                                                                                            <?php

                                                                                            for($i=1; $i<31;$i++){
                                                                                                echo '<option value="'.$i.'">'.trans("alerts.month_day",array("day"=>$i)).'</option>';
                                                                                            }
                                                                                            ?>
                                                                                        </select>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- form-group (notification period) -->
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <input type="hidden" value="0" name="hdn_notification_checkbox" id="hdn_notification_checkbox">
                                                                <input type="hidden" value="0" name="hdn_email_checkbox" id="hdn_email_checkbox">
                                                                <input type="hidden" value="0" name="hdn_sms_checkbox" id="hdn_sms_checkbox">
                                                                <input type="hidden" value="new" id="definition_op_type" name="definition_op_type">
                                                                <input type="hidden" value="" id="definition_edit_id" name="definition_edit_id">

                                                                <div class="form-group">
                                                                    <div class="col-lg-4 col-lg-offset-3">
                                                                        <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                                                            <i class="fa fa-times"></i> {{ trans('alerts.cancel') }} </button>

                                                                        <button type="submit" class="btn btn-primary" id="save_definition_button" name="save_definition_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('alerts.save') }}</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>

                                <div class="row" id="div_definitions_dataTable">
                                    <div class="col-lg-12">
                                        {!! $DefinitionDataTableObj->html() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
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
    <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_alert_definition"))
            function manage_fieldset(element,div){

                element_id = element.attr("id");
                hdn_element = $("#hdn_"+element_id);

                if(div.css("display")=="none"){

                    div.show();
                    element.removeClass("fa-square-o");
                    element.addClass("fa-check-square-o");
                    element.css("color","green");

                    hdn_element.val(1);


                }
                else{
                    div.hide();
                    element.removeClass("fa-check-square-o");
                    element.addClass("fa-square-o");
                    element.css("color","#676a6c");
                    hdn_element.val(0);
                }

            }

            function validate_save_op(){
                return_value = true;

                if( $('#notification_checkbox').hasClass("fa-square-o") && $('#email_checkbox').hasClass("fa-square-o") && $('#sms_checkbox').hasClass("fa-square-o") ){
                    alertBox('', '{{ trans('alerts.select_definition_warning') }}', 'warning');
                    return_value = false;
                }

                $("#add_new_definition_form").parsley().reset();
                // $("#add_new_definition_form").parsley();

                $('#add_new_definition_form').parsley({
                    excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], input:hidden"
                });

                return return_value;
            }

            $("#new_definition_type").select2({
                minimumResultsForSearch: Infinity
            });

            function cancel_add_new_form(){
                //$("#add_new_definition_form .form-control").val("");
                $(".parsley-errors-list").remove();

                $("#new_definition_type").select2({
                    minimumResultsForSearch: Infinity
                }).val("reactive").trigger("change");

                $('#new_definition_name').val("");

                if( $('#notification_checkbox').hasClass( "fa-check-square-o" ) ){
                    $('#notification_checkbox').click();
                }

                if( $('#email_checkbox').hasClass( "fa-check-square-o" ) ){
                    $('#email_checkbox').click();
                }

                if( $('#sms_checkbox').hasClass( "fa-check-square-o" ) ){
                    $('#sms_checkbox').click();
                }

                $("#n_notification_period, #e_notification_period, #s_notification_period").select2({
                    minimumResultsForSearch: Infinity
                }).val(1).trigger("change");

                $("#div_add_new_definition").hide();
                $("#div_definitions_dataTable").show();
            }

            function show_add_new_form(){
                $("#definition_op_type").val("new");
                $("#definition_edit_id").val("");
                $("#modal_title").html("{{ trans('alerts.add_new_definition') }}");
                $("#new_definition_type").select2({
                    minimumResultsForSearch: Infinity
                }).val("reactive").trigger("change");

                $('#save_definition_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("alerts.save")}}');
                $("#div_add_new_definition").show();
                $("#div_definitions_dataTable").hide();
            }

            function fill_definition_fields(type,prefix,group){

                if(type == "reactive"){
                    $("#"+prefix+"_inductive_limit").val(the_policy[group].inductive_limit);
                    $("#"+prefix+"_capacitive_limit").val(the_policy[group].capacitive_limit);
                    $("#"+prefix+"_consumption_limit").val(the_policy[group].consumption_limit);
                    $("#"+prefix+"_calculation_period").select2({
                        minimumResultsForSearch: Infinity
                    }).val(the_policy[group].calculation_period).trigger("change");

                }
                else if(type == "current"){

                    if(the_policy[group].unbalanced_current_status == 0)
                        $("#"+prefix+"_unbalanced_current_status").bootstrapSwitch('state',false);
                    else
                        $("#"+prefix+"_unbalanced_current_status").bootstrapSwitch('state',true);


                    if(the_policy[group]["5A_current_status"] == 0)
                        $("#"+prefix+"_5A_current_status").bootstrapSwitch('state',false);
                    else
                        $("#"+prefix+"_5A_current_status").bootstrapSwitch('state',true);

                }
                else if(type == "voltage"){

                    $("#"+prefix+"_voltage_lower_limit").val(the_policy[group].voltage_lower_limit);
                    $("#"+prefix+"_voltage_upper_limit").val(the_policy[group].voltage_upper_limit);

                }
                else if(type == "connection"){
                    if(the_policy[group].device_connection == 0)
                        $("#"+prefix+"_device_connection").bootstrapSwitch('state',false);
                    else
                        $("#"+prefix+"_device_connection").bootstrapSwitch('state',true);


                    if(the_policy[group]["modem_connection"] == 0)
                        $("#"+prefix+"_modem_connection").bootstrapSwitch('state',false);
                    else
                        $("#"+prefix+"_modem_connection").bootstrapSwitch('state',true);

                    $("#"+prefix+"_duration").select2({
                        minimumResultsForSearch: Infinity
                    }).val(the_policy[group].duration).trigger("change");
                }

                $("#"+prefix+"_notification_period").select2({
                    minimumResultsForSearch: Infinity
                }).val(the_policy[group].notification_period.type).trigger("change");

                $("#"+prefix+"_notification_period_"+the_policy[group].notification_period.type).select2({
                    minimumResultsForSearch: Infinity
                }).val(the_policy[group].notification_period.period).trigger("change");
            }

            function edit_alert_definition(id){
                $('body').prepend("<div id='bg_block_screen'><div class='loader'></div>{{ trans("global.preparing") }}...</div>");

                $("#definition_op_type").val("edit");
                $("#definition_edit_id").val(id);
                $("#modal_title").html("{{ trans('alerts.update_definition') }}");

                $.ajax({
                    method:"POST",
                    url:"/alerts/get_definition_info",
                    data:"id="+id,
                    async:false,
                    success:function(return_value){
                        if( $.trim(return_value) != 'NEXIST' && return_value.search("ERROR") == -1 ){

                            the_info = JSON.parse(return_value);
                            the_policy = JSON.parse(the_info.policy);

                            $("#new_definition_type").select2({
                                minimumResultsForSearch: Infinity
                            }).val(the_info["type"]).trigger("change");
                            $("#new_definition_name").val(the_info["name"]);

                            if (typeof the_policy.notification !== 'undefined') {

                                fill_definition_fields(the_info["type"],"n","notification");
                                $("#notification_checkbox").click();
                            }

                            if (typeof the_policy.email !== 'undefined') {

                                fill_definition_fields(the_info["type"],"e","email");
                                $("#email_checkbox").click();
                            }

                            if (typeof the_policy.sms !== 'undefined') {

                                fill_definition_fields(the_info["type"],"s","sms");
                                $("#sms_checkbox").click();
                            }

                            $('#save_definition_button').html('<i class="fa fa-refresh"></i> {{trans("alerts.update")}}');
                            $("#div_definitions_dataTable").hide();
                            $("#div_add_new_definition").show();
                            $("#new_definition_name").focus();
                        }
                        else{
                            alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                        }
                    }
                });

                $("#bg_block_screen").remove();
            }
        @endif

        @if( Helper::has_right(Auth::user()->operations, "delete_alert_definition") )
            function delete_alert_definition(id){
            confirmBox('','{{ trans('alerts.delete_warning') }}','warning',function(){
                $.ajax({
                    method:"POST",
                    url:"/alerts/delete",
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

        @if (Helper::has_right(Auth::user()->operations, "change_alert_status"))

            function delete_alert(id){
                confirmBox('','{{ trans('alerts.delete_alert_warning') }}','warning',function(){
                    $.ajax({
                        method:"POST",
                        url:"/alerts/delete",
                        data:"id="+id+"&type=one_alert",
                        success:function(return_text){
                            if(return_text == "SUCCESS"){
                                al_dt.ajax.reload();
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
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if (Helper::has_right(Auth::user()->operations, "add_new_alert_definition"))
        @if (session()->has('new_alert_definition_insert_success') && session('new_alert_definition_insert_success'))
            {{ session()->forget('new_alert_definition_insert_success') }}

            custom_toastr('{{ trans('alerts.new_alert_definition_insert_success') }}');
        @endif

        @if (session()->has('alert_definition_update_success') && session('alert_definition_update_success'))
            {{ session()->forget('alert_definition_update_success') }}

            custom_toastr('{{ trans('alerts.alert_definition_update_success') }}');
        @endif
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_alert_definition"))
        @if (session()->has('alert_definition_delete_success') && session('alert_definition_delete_success'))
            {{ session()->forget('alert_definition_delete_success') }}

            custom_toastr('{{ trans('alerts.alert_definition_delete_success') }}');
        @endif
    @endif

    @if (Helper::has_right(Auth::user()->operations, "add_new_alert_definition"))
        $("#n_unbalanced_current_status, #n_5A_current_status, #n_modem_connection, #n_device_connection, #e_unbalanced_current_status, #e_5A_current_status,#e_modem_connection, #e_device_connection, #s_unbalanced_current_status, #s_5A_current_status, #s_modem_connection, #s_device_connection").bootstrapSwitch();

        $("#n_calculation_period, #e_calculation_period, #s_calculation_period, #n_notification_period, #n_notification_period_periodic, #n_notification_period_daily, #n_notification_period_definite_day, #e_notification_period, #e_notification_period_periodic, #e_notification_period_daily, #e_notification_period_definite_day, #s_notification_period, #s_notification_period_periodic, #s_notification_period_daily, #s_notification_period_definite_day, #n_duration, #e_duration, #s_duration").select2({
            minimumResultsForSearch: Infinity
        });

        $("#n_notification_period, #e_notification_period, #s_notification_period").select2({
            minimumResultsForSearch: Infinity
        }).on('change', function(){

            the_id = $(this).attr("id");
            prefix = the_id.charAt(0);

            $("#"+prefix+"_notification_period_periodic,#"+prefix+"_notification_period_daily,#"+prefix+"_notification_period_definite_day").closest(".form-group").hide();
            if( $(this).val() == "periodic" ){
                $("#"+prefix+"_notification_period_periodic").closest(".form-group").show();
            }
            else if( $(this).val() == "daily" ){
                $("#"+prefix+"_notification_period_daily").closest(".form-group").show();
            }
            else if( $(this).val() == "definite_day" ){
                $("#"+prefix+"_notification_period_definite_day").closest(".form-group").show();
            }

        });

        $('#new_definition_type').change(function(){
            the_val = $(this).val();

            $('.definition_reactive_inputs').hide();
            $('.definition_current_inputs').hide();
            $('.definition_voltage_inputs').hide();
            $('.definition_connection_inputs').hide();

            if( the_val == "reactive" ){
                $('.definition_reactive_inputs').show();
            }
            else if( the_val == "current" ){
                $('.definition_current_inputs').show();
            }
            else if( the_val == "voltage" ){
                $('.definition_voltage_inputs').show();
            }
            else if( the_val == "connection" ){
                $('.definition_connection_inputs').show();
            }
            else{
                return alertBox('', "{{ trans('global.unexpected_error') }}" , 'error');
            }
        });
    @endif

    // Keep the current tab active after page reload
    rememberTabSelection('#alerts_tabs', !localStorage);

    if (document.location.hash && document.location.hash == '#alarms') {
        $("#alerts_tabs a[href='#tab-1']").trigger('click');
    }
    else if(document.location.hash){
        $("#alerts_tabs a[href='"+document.location.hash+"']").trigger('click');
    }

    var tab_1 = false,
        tab_2 = false,
        tab_3 = false;

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            tab_1 = true;
            {!! $DataTableObj->ready() !!}
        }
        @if (Helper::has_right(Auth::user()->operations, "add_new_alert_definition"))
        else if(selectedTab == "#tab-2" && tab_2 == false){
            tab_2 = true;
            {!! $DefinitionDataTableObj->ready() !!}
        }
        @endif
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#alerts_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);

        // clear hash and parameter values from URL
        history.pushState('', document.title, window.location.pathname);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#alerts_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#alerts_tabs a:first").trigger('click');
@endsection