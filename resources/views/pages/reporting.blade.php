@extends('layouts.master')

@section('title')
    {{ trans('reporting.title') }}
@endsection

@section('page_level_css')
    {!! $ReportsTableObj->css() !!}

    <link rel="stylesheet" href="/js/plugins/bootstrap-toggle/css/bootstrap-toggle.min.css">
    <link rel="stylesheet" href="/css/plugins/iCheck/custom.css">
    <link rel="stylesheet" href="/css/plugins/iCheck/skins/flat/orange.css">
    <link rel="stylesheet" href="/css/plugins/iCheck/skins/flat/blue.css">
    <link rel="stylesheet" type="text/css" href="/js/plugins/jsTree/themes/default/style.min.css" />

    <style>
        .slow .toggle-group { transition: left 0.7s; -webkit-transition: left 0.7s; }
    </style>

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

        .detail-backgorund{
            background-color: #fAfAf8;
        }
    </style>
@endsection

@section('content')
    <?php
        $yesterday = date('d/m/Y', strtotime("-1 days"));
        $previous_day = date('d/m/Y', strtotime("-2 days"));
        $last_week = date('d/m/Y', strtotime("-2 week Monday"));
        $previous_week = date('d/m/Y', strtotime("-3 week Monday"));
        $last_month = date('m/Y', strtotime("-1 month"));
        $previous_month = date('m/Y', strtotime("-2 month"));
        $last_year = date('Y', strtotime("-1 year"));
        $previous_year = date('Y', strtotime("-2 year"));
    ?>

    <form method="POST" action="/reporting/download_report_file"  id="download_report_form">
        {{ csrf_field() }}
        <input type="hidden" id="report_id" name="report_id" value=""/>
        <input type="hidden" id="download_token" name="download_token" value="" />
    </form>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs" id="reporting_tabs">
                        <li class="" tab="#tab-1">
                            <a data-toggle="tab" href="#tab-1" aria-expanded="false">
                                <i class="fa fa-check-square-o fa-lg" aria-hidden="true"></i>
                                {{ trans('reporting.completed_reports') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-2">
                            <a data-toggle="tab" href="#tab-2" aria-expanded="false">
                                <i class="fa fa-star-half-o fa-lg" aria-hidden="true"></i>
                                {{ trans('reporting.report_templates') }}
                            </a>
                        </li>
                        <li class="" tab="#tab-3">
                            <a data-toggle="tab" href="#tab-3" aria-expanded="false">
                                <i class="fa fa-plus-square-o fa-lg" aria-hidden="true"></i>
                                {{ trans('reporting.create_report_tab') }}
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row">
                                    <div class="col-lg-12">
                                        {!! $ReportsTableObj->html() !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-2" class="tab-pane">
                            <div class="panel-body tooltip-demo" data-html="true">
                                <div class="row" id="div_templates_dataTable">
                                    <div class="col-lg-12">
                                        {!! $TemplatesTableObj->html() !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (Helper::has_right(Auth::user()->operations, "create_new_report"))
                            <div id="tab-3" class="tab-pane">
                                <div class="panel-body tooltip-demo" data-html="true">
                                    <div class="row" id="div_create_report">
                                        <div class="col-lg-12">
                                            <form class="m-t form-horizontal" role="form" method="POST"
                                                  action="{{ url('/reporting/add') }}" id="create_report_form">

                                                {{ csrf_field() }}

                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label"> </label>
                                                    <div class="col-sm-6">
                                                        <input id="purpose" type="checkbox" checked name="purpose">
                                                    </div>
                                                </div>

                                                <div class="form-group template" style="display: none;">
                                                    <label class="col-sm-3 control-label"> {{ trans('reporting.template_name') }} <span style="color:red;">*</span></label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control" id="template_name" name="template_name" minlength="3" maxlength="255" required>
                                                    </div>
                                                </div>

                                                <div class="form-group report">
                                                    <label class="col-sm-3 control-label"> {{ trans('reporting.report_name') }} <span style="color:red;">*</span></label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control" id="report_name" name="report_name" minlength="3" maxlength="255" required>
                                                    </div>
                                                </div>

                                                <!-- working type (instant | periodic) -->
                                                <div class="form-group template" id="div_working_type">
                                                    <label class="col-sm-3 control-label"> {{ trans('reporting.working_type') }} <span style="color:red;">*</span></label>
                                                    <div class="col-sm-6">
                                                        <select name="working_type" id="working_type" style="width:100%;" class="form-control" required>
                                                            <option value="instant"> {{ trans('reporting.instant') }} </option>
                                                            <option value="periodic"> {{ trans('reporting.periodic') }} </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label"> {{ trans('reporting.report_type') }} <span style="color:red;">*</span></label>
                                                    <div class="col-sm-6" style="min-height: 34px; padding-top: 5px; padding-bottom: 5px;">
                                                        <div class="i-checks i-checks-rt col-sm-6">
                                                            <input type="radio" id="rt_statistic" value="0" name="report_type_options" checked>
                                                            <label for="rt_statistic"> {{ trans('reporting.summary_statistic') }} </label>
                                                        </div>
                                                        <div class="i-checks i-checks-rt col-sm-6">
                                                            <input type="radio" id="rt_comparison" value="1" name="report_type_options">
                                                            <label for="rt_comparison"> {{ trans('reporting.consumption_comparison') }} </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- working period -->
                                                <div class="form-group periodic" id="div_working_period_1" style="display: none;">
                                                    <label class="col-sm-3 control-label"> {{ trans('reporting.working_period') }} <span style="color:red;">*</span></label>
                                                    <div class="col-sm-6">
                                                        <select name="working_period" id="working_period" style="width:100%;" required class="form-control">
                                                            <option value="daily"> {{ trans('reporting.daily_with_exp') }} </option>
                                                            <option value="weekly"> {{ trans('reporting.weekly_with_exp') }} </option>
                                                            <option value="monthly"> {{ trans('reporting.monthly_with_exp') }} </option>
                                                            <option value="yearly"> {{ trans('reporting.yearly_with_exp') }} </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group" id="div_working_period_2" style="display: none;">
                                                    <label class="col-sm-3 control-label"></label>
                                                    <div class="col-sm-6" id="div_working_period_weekly" style="display: none;">
                                                        <select class="form-control focus-class" name="working_period_weekly" id="working_period_weekly" style="width:100%;" required>
                                                            <option value="monday"> {{ trans('alerts.daily_day', array('day' => trans('devices.monday'))) }} </option>
                                                            <option value="tuesday"> {{ trans('alerts.daily_day', array('day' => trans('devices.tuesday'))) }} </option>
                                                            <option value="wednesday"> {{ trans('alerts.daily_day', array('day' => trans('devices.wednesday'))) }} </option>
                                                            <option value="thursday"> {{ trans('alerts.daily_day', array('day' => trans('devices.thirsday'))) }} </option>
                                                            <option value="friday"> {{ trans('alerts.daily_day', array('day' => trans('devices.friday'))) }} </option>
                                                            <option value="saturday"> {{ trans('alerts.daily_day', array('day' => trans('devices.saturday'))) }} </option>
                                                            <option value="sunday"> {{ trans('alerts.daily_day', array('day' => trans('devices.sunday'))) }} </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-6" id="div_working_period_monthly" style="display: none;">
                                                        <select class="form-control focus-class" name="working_period_monthly" id="working_period_monthly" style="width:100%;" required>
                                                            <?php
                                                                for($i=1; $i<29;$i++){
                                                                    echo '<option value="'.$i.'">'.trans("alerts.month_day",array("day"=>$i)).'</option>';
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!--
                                                <div class="form-group statistic">
                                                    <label class="col-sm-3 control-label"> \{\{ trans('reporting.data_range') }} <span style="color:red;">*</span></label>
                                                    <div class="col-sm-6 tooltip-demo">
                                                        <select class="form-control focus-class" name="data_range" id="data_range" style="width:100%;" required>
                                                            <?php /*
                                                            for($i=1; $i<31;$i++){
                                                                echo '<option value="'.$i.'">'.trans("alerts.since_day",array("day"=>$i)).'</option>';
                                                            } */
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                -->

                                                <div class="form-group statistic" id="div_stats_data_range">
                                                    <label class="col-sm-3 control-label"> {{ trans('reporting.data_range') }} <span style="color:red;">*</span></label>
                                                    <div class="col-sm-6 tooltip-demo">
                                                        <select class="form-control focus-class" name="data_range" id="data_range" style="width:100%;" required>
                                                            <option value="daily" selected> {{ trans('reporting.daily') }} </option>
                                                            <option value="weekly"> {{ trans('reporting.weekly') }} </option>
                                                            <option value="monthly"> {{ trans('reporting.monthly') }} </option>
                                                            <option value="yearly"> {{ trans('reporting.yearly') }} </option>

                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- stats dates (instant) -->
                                                <div class="form-group statistic" id="div_stats_dates_i">
                                                    <label class="col-sm-3 control-label"> </label>
                                                    <div class="col-sm-6">
                                                        <div class="stats_daterange input-daterange input-group" id="datepicker">
                                                            <input id="stats_start" type="text" class="input-sm form-control" name="stats_start" value="{{ $previous_day }}" required />
                                                            <span class="input-group-addon">-</span>
                                                            <input id="stats_end" type="text" class="input-sm form-control" name="stats_end" value="{{ $yesterday }}" required />
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- data_type (consumption | reactive | current | voltage | cosfi) -->
                                                <div class="form-group statistic">
                                                    <label class="col-lg-3 control-label">{{ trans('reporting.data_type') }} <span style="color:red;">*</span></label>
                                                    <div class="col-lg-6">
                                                        <select name="content_types[]" id="content_types" class="form-control" style="width:100%;" multiple="multiple" data-parsley-errors-container="#content_types_error" data-placeholder="{{ trans('reporting.multiple_selectable') }}" required>
                                                            <option value="consumption"> {{ trans('reporting.consumption') }} </option>
                                                            <option value="reactive_rates"> {{ trans('reporting.reactive_rates') }} </option>
                                                            <option value="current"> {{ trans('reporting.current') }} </option>
                                                            <option value="voltage"> {{ trans('reporting.voltage') }} </option>
                                                            <option value="cosfi"> {{ trans('reporting.cosfi') }} </option>
                                                        </select>
                                                        <span class="help-block" id="content_types_error" style="color:red;"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group comparison" style="display: none;">
                                                    <label class="col-sm-3 control-label"> {{ trans('reporting.comparison_type') }} <span style="color:red;">*</span></label>
                                                    <div class="col-sm-6">
                                                        <select name="comparison_type" id="comparison_type" style="width:100%;" required class="form-control">
                                                            <option value="daily" selected> {{ trans('reporting.daily_comparison') }} </option>
                                                            <option value="weekly"> {{ trans('reporting.weekly_comparison') }} </option>
                                                            <option value="monthly"> {{ trans('reporting.monthly_comparison') }} </option>
                                                            <option value="yearly"> {{ trans('reporting.yearly_comparison') }} </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- comparison dates (instant) -->
                                                <div class="form-group" id="div_comparison_dates_i" style="display: none;">
                                                    <label class="col-sm-3 control-label"> </label>
                                                    <div class="col-sm-6">
                                                        <div class="comparison_daterange input-daterange input-group" id="datepicker">
                                                            <input id="comparison_start" type="text" class="input-sm form-control" name="comparison_start" value="{{ $previous_day }}" required />
                                                            <span class="input-group-addon">-</span>
                                                            <input id="comparison_end" type="text" class="input-sm form-control" name="comparison_end" value="{{ $yesterday }}" required />
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- comparison dates (periodic daily) -->
                                                <div class="form-group" id="div_comparison_dates_p" style="display: none;">
                                                    <label class="col-sm-3 control-label"> </label>

                                                    <div class="col-sm-6 comparison_dates_p" id="div_comparison_dates_pd">
                                                        <select name="comparison_dates_pd" id="comparison_dates_pd" style="width:100%;" required class="form-control">
                                                            <option value="ypdd" selected> {{ trans("reporting.ypdd") }}</option>
                                                            <option value="ypwd"> {{ trans("reporting.ypwd") }} </option>
                                                            <option value="ypmd"> {{ trans("reporting.ypmd") }} </option>
                                                            <option value="ypyd"> {{ trans("reporting.ypyd") }} </option>
                                                        </select>
                                                    </div>

                                                    <div class="col-sm-6 comparison_dates_p" id="div_comparison_dates_pw" style="display: none;">
                                                        <select name="comparison_dates_pw" id="comparison_dates_pw" style="width:100%;" required class="form-control">
                                                            <option value="lwpw" selected> {{ trans("reporting.lwpw") }} </option>
                                                            <option value="lwpm"> {{ trans("reporting.lwpm") }} </option>
                                                            <option value="lwpy"> {{ trans("reporting.lwpy") }} </option>
                                                        </select>
                                                    </div>

                                                    <div class="col-sm-6 comparison_dates_p" id="div_comparison_dates_pm" style="display: none;">
                                                        <select name="comparison_dates_pm" id="comparison_dates_pm" style="width:100%;" required class="form-control">
                                                            <option value="lmpm" selected> {{ trans("reporting.lmpm") }} </option>
                                                            <option value="lmpy"> {{ trans("reporting.lmpy") }} </option>
                                                        </select>
                                                    </div>

                                                    <div class="col-sm-6 comparison_dates_p" id="div_comparison_dates_py" style="display: none;">
                                                        <select name="comparison_dates_py" id="comparison_dates_py" style="width:100%;" required class="form-control">
                                                            <option value="lypy" selected> {{ trans("reporting.lypy") }} </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- get selectable distributors according to user type -->
                                                {!!  Helper::get_distributors_select("report_distributor") !!}

                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label"> </label>
                                                    <div class="col-sm-6" style="min-height: 34px; padding-top: 5px; padding-bottom: 5px;">
                                                        <div class="i-checks i-checks-filter col-sm-6" >
                                                            <input type="radio" id="org_schema_filter_c" value="0" name="org_schema_filter" checked>
                                                            <label for="org_schema_filter_c"> {{ trans('reporting.modem_based') }} </label>
                                                        </div>
                                                        <div class="i-checks i-checks-filter col-sm-6">
                                                            <input type="radio" id="org_schema_filter_b" value="1" name="org_schema_filter">
                                                            <label for="org_schema_filter_b"> {{ trans('reporting.break_based') }} </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group" id="div_org_schema" style="display: none;">
                                                    <label class="col-sm-3 control-label"> </label>
                                                    <div class="col-sm-6">
                                                        <div id="org_schema"></div>
                                                        <span class="help-block" id="org_schema_error" style="color:red;"></span>
                                                    </div>

                                                    <input type="hidden" value="" id="hdn_org_schema_values" name="hdn_org_schema_values">
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

                                                <div class="form-group" id="div_report_emails">
                                                    <label class="col-lg-3 control-label">{{ trans('reporting.email') }} </label>
                                                    <div class="col-lg-6">
                                                        <select name="report_emails[]" id="report_emails" class="form-control" style="width:100%;" data-parsley-errors-container="#report_emails_error" minlength="3" maxlength="500">
                                                        </select>
                                                        <span class="help-block" id="report_emails_error" style="color:red;"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-3 control-label"> {{ trans('reporting.explanation') }} </label>
                                                    <div class="col-sm-6">
                                                        <textarea type="text" class="form-control" id="report_explanation" name="report_explanation" placeholder="{{ trans('reporting.exp_placeholder') }}" minlength="3" maxlength="500"></textarea>
                                                    </div>
                                                </div>

                                                <input type="hidden" value="new" id="report_op_type" name="report_op_type">
                                                <input type="hidden" value="" id="report_edit_id" name="report_edit_id">

                                                <br />
                                                <div class="form-group">
                                                    <div class="col-sm-6 col-sm-offset-3">
                                                        <button type="submit" class="btn btn-primary btn-block" id="save_report_button" name="save_report_button" onclick="return validate_save_op();">
                                                            <i class="fa fa-thumbs-o-up"></i> {{ trans('reporting.create') }}
                                                        </button>
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
            </div>
        </div>
    </div>
@endsection

@section('page_level_js')
    {!! $ReportsTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/jsTree/jstree.min.js"></script>

    <!-- iCheck -->
    <script src="/js/plugins/iCheck/icheck.min.js"></script>

    <script>
        org_schema_filter_var = true;
        org_schema;
        var is_edit_click = false;
        var org_schema_checked_ids = false;

        @if (Helper::has_right(Auth::user()->operations, "create_new_report"))
            function validate_save_op(){
                $("#create_report_form").parsley().reset();
                // $("#create_report_form").parsley();

                $("#org_schema_error").html('');

                $('#create_report_form').parsley({
                    excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], input:hidden"
                });

                // validate to org_schema tree
                checked_elements = $("#org_schema").jstree('get_checked');

                if( !(checked_elements != "" && checked_elements.length > 0) ){
                    pop_up_source_error("org_schema_error","{{ trans("distributor_detail.required_field") }}", false);
                    return false;
                }

                $("#hdn_org_schema_values").val(checked_elements);

                // Show the template tab after the page reload
                if( $('#report_op_type').val() == "edit" || $('#working_type').val() == "periodic" ){
                    $('a[href="#tab-2"]').trigger('click');
                }
                else if( $('#working_type').val() == "instant" ){
                    $('a[href="#tab-1"]').trigger('click');
                }
            }

            function initialize_form(){
                $("#report_op_type").val("new");
                $("#report_edit_id").val("");
                org_schema_checked_ids = false;

                $('#purpose').bootstrapToggle('enable');
                $('#purpose').bootstrapToggle('on');

                $('#report_name, #template_name, #report_explanation').val('');

                $('#rt_comparison').iCheck('uncheck');
                $('#rt_statistic').iCheck('check');

                $('#data_range').val('daily').trigger('change');
                $('#content_types').val(null).trigger('change');

                $('#report_distributor').val($('#report_distributor option:first-child').val()).trigger('change');

                $('#org_schema_filter_c').iCheck('check');

                $('#comparison_type').val('daily').trigger('change');
                $('#working_type').val('instant').trigger('change');

                $('#report_emails').val(null).trigger('change');

                $('#save_report_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("reporting.create")}}');
            }

            function edit_template(id) {
                $('#purpose').bootstrapToggle('enable');

                $("#report_op_type").val("edit");
                $("#report_edit_id").val(id);
                is_edit_click = true;

                $('body').prepend("<div id='bg_block_screen'><div class='loader'></div>{{ trans("global.preparing") }}...</div>");

                // get the template data to fill the form
                $.ajax({
                    method:"POST",
                    url:"/reporting/get_info",
                    data:"id="+id,
                    async:false,
                    success:function(return_value){
                        if( $.trim(return_value) != 'NEXIST' && return_value.search("ERROR") == -1 ){
                            // open the tab-3 to show form
                            $('a[href="#tab-3"]').trigger('click');

                            the_info = JSON.parse(return_value);
                            the_detail = JSON.parse(the_info.detail);
                            the_ainfo = JSON.parse(the_info.additional_info);
                            the_org_schema = JSON.parse(the_info.org_schema_detail);
                            the_email = JSON.parse(the_info.email);

                            $('#purpose').bootstrapToggle('off');

                            $("#report_name").val("");
                            $("#template_name").val(the_info.template_name);
                            $("#working_type").val(the_detail.working_type).trigger('change');

                            if( the_detail.report_type == "stats" ){
                                $('#rt_statistic').iCheck('check');

                                if( "" + the_detail.working_type == "instant" ){
                                    $("#data_range").val(the_detail.data_range).trigger('change');
                                    $("#stats_start").datepicker("setDate", "" + the_detail.stats_start);
                                    $("#stats_end").datepicker("setDate", "" + the_detail.stats_end);
                                }
                                else{ // periodic

                                    $("#working_period").val(the_detail.working_period).trigger('change');

                                    if( the_detail.working_period == "weekly" ){
                                        $('#working_period_weekly').val(the_detail.working_period_weekly).trigger('change');
                                    }
                                    else if( the_detail.working_period == "monthly" ){
                                        $('#working_period_monthly').val(the_detail.working_period_monthly).trigger('change');
                                    }
                                    else{
                                        $('#working_period_weekly').val("monday").trigger('change');
                                        $('#working_period_monthly').val(1).trigger('change');
                                    }

                                    if( the_detail.working_period != "yearly" ){
                                        $("#data_range").val(the_detail.data_range).trigger('change');
                                    }
                                }

                                $('#content_types').val( the_detail.content_types ).trigger('change');
                            }
                            else{ // comparison
                                $('#rt_comparison').iCheck('check');

                                $("#comparison_type").val(the_detail.comparison_type).trigger('change');

                                if( "" + the_detail.working_type == "instant" ){
                                    $("#comparison_start").datepicker("setDate", "" + the_detail.comparison_start);
                                    $("#comparison_end").datepicker("setDate", "" + the_detail.comparison_end);
                                }
                                else{ // periodic
                                    if( the_detail.comparison_type == "daily" ){
                                        $("#comparison_dates_pd").val(the_detail.comparison_dates_pd).trigger('change');
                                    }
                                    else if( the_detail.comparison_type == "weekly" ){
                                        $("#comparison_dates_pw").val(the_detail.comparison_dates_pw).trigger('change');
                                    }
                                    else if( the_detail.comparison_type == "monthly" ){
                                        $("#comparison_dates_pm").val(the_detail.comparison_dates_pm).trigger('change');
                                    }
                                    else if( the_detail.comparison_type == "yearly" ){
                                        $("#comparison_dates_py").val(the_detail.comparison_dates_py).trigger('change');
                                    }
                                }
                            }

                            if( the_org_schema.filter == "modem_based" ){
                                org_schema_checked_ids = the_org_schema.values;
                                checked_ids = [];

                                $.each(org_schema_checked_ids, function(index,value){
                                    checked_ids.push(value+"_client_modem");
                                });

                                org_schema_checked_ids = checked_ids;
                                $("#hdn_org_schema_values").val(org_schema_checked_ids);


                            }
                            else{
                                org_schema_checked_ids = the_org_schema.values;
                                $("#hdn_org_schema_values").val(the_org_schema.values);


                            }

                            @if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 )
                                $("#report_distributor").val(the_info.distributor_id).trigger('change');
                            @elseif( Auth::user()->user_type == 3 )
                                $("#hdn_report_distributor").val({{ Auth::user()->org_id }});
                                load_additional_info({{ Auth::user()->org_id }});
                            @endif

                            $("#report_emails").select2({
                                minimumResultsForSearch: Infinity,
                                multiple:true,
                                placeholder:'{{ trans('devices.type_emails') }}',
                                tags:true,
                                data:JSON.parse(the_info.email)
                            }).val(JSON.parse(the_info.email)).trigger('change');

                            $('#report_explanation').val(the_info.explanation);

                            if( the_org_schema.filter == "modem_based" ){
                                $('#org_schema_filter_c').iCheck('check');
                            }
                            else{
                                $('#org_schema_filter_b').iCheck('check');
                            }

                            $('#purpose').bootstrapToggle('disable');

                            $('#save_report_button').html('<i class="fa fa-refresh"></i> {{trans("modem_management.update")}}');
                        }
                        else{
                            alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                        }
                    }
                });

                $("#bg_block_screen").remove();
            }

            function load_org_tree(id){
                $('#org_schema').jstree('destroy');
                $('#div_org_schema').show();

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
                    unexpected_error: '{{ trans('global.unexpected_error') }}'
                };

                $.ajax({
                    method: "POST",
                    url: "/organization_schema/get_organization_schema",
                    data: "distributor_id="+id+"&show_clients=true",
                    async: false,
                    success: function(return_text){
                        if( return_text != "" && return_text != "ERROR" ){
                            // data, distributor_id, div, lang, contextMenu, checkbox ,multiple
                            org_schema = createJsTree(
                                return_text,
                                id,
                                'org_schema',
                                lang_obj,
                                false,
                                true,
                                true,
                                false,
                                org_schema_filter_var,
                                org_schema_checked_ids
                            );
                        }
                        else{
                            alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
                        }
                    }
                });
            }

            function load_additional_info(id){
                $('#div_ainfo').hide();
                $("#div_ainfo_elements").html("");
                type = $('#report_op_type').val();
                template_id = $('#report_edit_id').val();

                $.ajax({
                    method: "POST",
                    url: "/reporting/get_add_infos",
                    data: "distributor_id="+id+"&op_type="+type+"&template_id="+template_id,
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

            function start_stop(id, warning_message, status){
                confirmBox(
                    '',
                    warning_message,
                    'warning',
                    function(){
                        $.ajax({
                            method:"POST",
                            url:"/reporting/startstop",
                            data:"id="+id+"&status="+status,
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

            function rerun(id){
                confirmBox(
                    '',
                    '{{ trans('reporting.rerun_warning') }}',
                    'warning',
                    function(){
                        $.ajax({
                            method:"POST",
                            url:"/reporting/rerun",
                            data:"id="+id,
                            success:function(return_text){
                                if(return_text == "SUCCESS"){
                                    $('a[href="#tab-1"]').trigger('click');
                                    r_dt.ajax.reload();
                                    //location.reload();
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
        @endif

        @if (Helper::has_right(Auth::user()->operations, "view_reporting"))
            //For tracking download operation
            var downloadTimer;
            var downloadAttempts = 120;

            function blockResubmit() {

                downloadAttempts = 120;

                $('body').prepend("<div id='bg_block_screen'><div class='loader'></div>{{ trans("global.preparing") }}...</div>");
                var downloadToken = new Date().getTime();
                $("#download_token").val(downloadToken);

                downloadTimer = window.setInterval( function() {

                    var token = getCookie( "DownloadToken" );
                    if( (token == downloadToken) || (downloadAttempts == 0) ) {
                        window.clearInterval( downloadTimer );
                        expireCookie( "DownloadToken" );
                        $("#bg_block_screen").remove();
                    }

                    downloadAttempts--;
                }, 500 );
            }

            function download_report_file(id){
                blockResubmit();
                $("#report_id").val(id);
                $("#download_report_form").submit();
            }
        @endif

        @if (Helper::has_right(Auth::user()->operations, "delete_report"))
            function delete_report(id, type){
                confirmBox(
                    '',
                    '{{ trans('reporting.delete_report_warning') }}',
                    'warning',
                    function(){
                        $.ajax({
                            method:"POST",
                            url:"/reporting/delete",
                            data:"id="+id+"&type="+type,
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
        @endif

        var previous_report_type = 0;
    </script>
@endsection

@section('page_document_ready')
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            custom_toastr('{{ $error }}', 'error');
        @endforeach
    @endif

    @if ( Helper::has_right(Auth::user()->operations,'create_new_report') )
        @if (session()->has('create_template_success') && session('create_template_success'))
            {{ session()->forget('create_template_success') }}

            custom_toastr('{{ trans('reporting.create_template_success') }}');
        @endif

        @if (session()->has('same_template_name') && session('same_template_name'))
            {{ session()->forget('same_template_name') }}

            custom_toastr('{{ trans('reporting.same_template_name') }}','error');
        @endif

        @if (session()->has('preparing_report_success') && session('preparing_report_success'))
            {{ session()->forget('preparing_report_success') }}

            custom_toastr('{{ trans('reporting.preparing_report_success') }}');
        @endif

        @if (session()->has('update_template_success') && session('update_template_success'))
            {{ session()->forget('update_template_success') }}

            custom_toastr('{{ trans('reporting.update_template_success') }}');
        @endif

        @if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 )
            $("#report_distributor").select2({
                minimumResultsForSearch: 10,
                placeholder: ""
            }).change(function(){

                if($(this).val() == 0){

                }


                load_org_tree($(this).val());

                if( $(this).val() == 0 ){
                    $("#div_ainfo").hide();
                    $('#org_schema_filter_c').iCheck('check');
                    $("#org_schema_filter_b").closest(".i-checks").hide();

                }
                else{
                    $("#org_schema_filter_b").closest(".i-checks").show();
                    load_additional_info($(this).val());
                }
            });
        @elseif( Auth::user()->user_type == 3 )
            load_org_tree({{ Auth::user()->org_id }});
            load_additional_info({{ Auth::user()->org_id }});
        @endif

        $("#report_emails").select2({
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
                    pop_up_source_error("report_emails_error","{{ trans("devices.unproper_email") }}");
                    title_val = jquery_escape(isNew.val());
                    $(this).parent().find("[title="+title_val+"]").find(".select2-selection__choice__remove").click();
                    isNew.remove();
                }
            }
        });

        $('#rt_statistic').on('ifChecked', function(event){
            $('#create_report_form').find('.statistic, .comparison').hide(600);
            $('#div_comparison_dates_p, #div_comparison_dates_i').hide(600);
            $('#content_types').removeAttr('required');

            $('#content_types').attr('required','required');

            $('#create_report_form').find('.statistic').show(600);

            if( $('#working_type').val() == "periodic" ){
                $('#working_period').val('daily').trigger('change');
                $('#div_working_period_1').show(600);
                $('#div_stats_data_range, #div_stats_dates_i').hide(600);
            }

            $('#content_types').attr('required','required');

        });

        $('#rt_comparison').on('ifChecked', function(event){
            $('#create_report_form').find('.statistic, .comparison').hide(600);
            $('#div_comparison_dates_p, #div_comparison_dates_i').hide(600);
            $('#content_types').removeAttr('required');

            if( $('#working_type').val() == "periodic" ){
                $('#div_working_period_1, #div_working_period_2').hide(600);
                $('#div_comparison_dates_p').show(600);
            }
            else{
                $('#div_comparison_dates_i').show(600);
            }

            $('#create_report_form').find('.comparison').show(600);
        });

        $('.i-checks-rt').iCheck({
            checkboxClass: 'icheckbox_flat-orange',
            radioClass: 'iradio_flat-orange',
            cursor: true
        }).on('ifClicked', function(event){
            // Since the Click event can not be triggered,
            // the changes that have been done here have moved to the ifChecked funstion above
            return false;

            the_val = $(this).val();

            if( the_val == previous_report_type )
                return;

            $('#create_report_form').find('.statistic, .comparison').hide(600);
            $('#div_comparison_dates_p, #div_comparison_dates_i').hide(600);
            $('#content_types').removeAttr('required');

            if( the_val == 0 ){ // statistic
                $('#create_report_form').find('.statistic').show(600);

                if( $('#working_type').val() == "periodic" ){

                    alert(123);
                    $('#working_period').val('daily').trigger('change');
                    $('#div_working_period_1').show(600);
                    $('#div_stats_data_range, #div_stats_dates_i').hide(600);
        alert(345);
                }

                $('#content_types').attr('required','required');

                previous_report_type = 0;
            }
            else if( the_val == 1 ){ // comparison
                if( $('#working_type').val() == "periodic" ){
                    $('#div_working_period_1, #div_working_period_2').hide(600);
                    $('#div_comparison_dates_p').show(600);
                }
                else{
                    $('#div_comparison_dates_i').show(600);
                }

                $('#create_report_form').find('.comparison').show(600);
                previous_report_type = 1;
            }
            else{
                return;
            }
        });

        $('#org_schema_filter_c').on('ifChecked', function(event){
            org_schema_filter_var = true;

            @if( Auth::user()->user_type == 3 )
                load_org_tree({{ Auth::user()->org_id }});
            @else
                $("#report_distributor").val($("#report_distributor").val()).trigger("change");
            @endif
        });

        $('#org_schema_filter_b').on('ifChecked', function(event){

            org_schema_filter_var = false;

            @if( Auth::user()->user_type == 3 )
                load_org_tree({{ Auth::user()->org_id }});
            @else
                $("#report_distributor").val($("#report_distributor").val()).trigger("change");
            @endif
        });

        $('.i-checks-filter').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue',
            cursor: true
        }).on('ifClicked', function(event){
            return false;

            the_val = $(this).val();

            if(the_val == 0){
                org_schema_filter_var = true;
            }
            else{
                org_schema_filter_var = false;
            }
        });

        $("#data_range, #working_period_weekly, #working_period_monthly, #comparison_dates_pd, #comparison_dates_pw, #comparison_dates_pm, #comparison_dates_py").select2({
            minimumResultsForSearch: Infinity
        });

        $('.stats_daterange').datepicker({
            format: "dd/mm/yyyy",
            language: "{{ App::getLocale() }}",
            todayHighlight: true,
            autoclose: true,
            endDate: '-1d',
            immediateUpdates: true
        });

        $("#data_range").change(function(){
            the_val = $(this).val();

            $('.stats_daterange').datepicker('destroy');

            if( the_val == "daily" ){
                $('.stats_daterange').datepicker({
                    format: "dd/mm/yyyy",
                    language: "{{ App::getLocale() }}",
                    todayHighlight: true,
                    autoclose: true,
                    endDate: '-1d',
                    immediateUpdates: true
                });

                $("#stats_start").datepicker("setDate", "{{ $previous_day }}");
                $("#stats_end").datepicker("setDate", "{{ $yesterday }}");
            }
            else if( the_val == "weekly" ){
                $('.stats_daterange').datepicker({
                    format: "dd/mm/yyyy",
                    language: "{{ App::getLocale() }}",
                    todayHighlight: true,
                    autoclose: true,
                    endDate: '-1d',
                    immediateUpdates: true,
                    daysOfWeekDisabled: "0,2,3,4,5,6",
                    daysOfWeekHighlighted: "1",
                    calendarWeeks: true,
                });

                $("#stats_start").datepicker("setDate", "{{ $previous_week }}");
                $("#stats_end").datepicker("setDate", "{{ $last_week }}");
            }
            else if( the_val == "monthly" ){
                $('.stats_daterange').datepicker({
                    format: "mm/yyyy",
                    minViewMode: 1,
                    language: "{{ App::getLocale() }}",
                    autoclose: true,
                    endDate: '-1m'
                });

                $("#stats_start").datepicker("setDate", "{{ $previous_month }}");
                $("#stats_end").datepicker("setDate", "{{ $last_month }}");
            }
            else if( the_val == "yearly" ){
                $('.stats_daterange').datepicker({
                    format: "yyyy",
                    minViewMode: 2,
                    language: "{{ App::getLocale() }}",
                    autoclose: true,
                    endDate: '-1y'
                });

                $("#stats_start").datepicker("setDate", "{{ $previous_year }}");
                $("#stats_end").datepicker("setDate", "{{ $last_year }}");
            }
            else{
                return alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
            }

        });

        $('#purpose').bootstrapToggle({
            on: '{{ trans('reporting.create_report') }}',
            off: '{{ trans('reporting.create_template') }}',
            width: '100%',
            onstyle: 'success',
            offstyle: 'primary',
            style: 'slow'
        }).change(function() {
            the_val = $(this).prop('checked');

            $('#create_report_form').find('.report, .template').hide(600);

            if( the_val == true ){ // report
                $('#create_report_form').find('.periodic, .instant').hide(600);
                $('#working_type').val('instant').trigger('change');
                $('#working_period').val('daily').trigger('change');
                $('#create_report_form').find('.report').show(600);
            }
            else if( the_val == false ){ // template
                $('#create_report_form').find('.template').show(600);
            }
            else{
                return;
            }
        });

        $('#comparison_type').select2({
            minimumResultsForSearch: Infinity
        }).on('change', function (evt) {
            the_val = $(this).val();

            $('.comparison_daterange').datepicker('destroy');
            $('.comparison_dates_p').hide(600);

            if( the_val == "daily" ){
                $('.comparison_daterange').datepicker({
                    format: "dd/mm/yyyy",
                    language: "{{ App::getLocale() }}",
                    todayHighlight: true,
                    autoclose: true,
                    endDate: '-1d',
                    immediateUpdates: true
                });

                $("#comparison_start").datepicker("setDate", "{{ $previous_day }}");
                $("#comparison_end").datepicker("setDate", "{{ $yesterday }}");

                $('#div_comparison_dates_pd').show(600);
            }
            else if( the_val == "weekly" ){
                $('.comparison_daterange').datepicker({
                    format: "dd/mm/yyyy",
                    language: "{{ App::getLocale() }}",
                    todayHighlight: true,
                    autoclose: true,
                    endDate: '-1d',
                    immediateUpdates: true,
                    daysOfWeekDisabled: "0,2,3,4,5,6",
                    daysOfWeekHighlighted: "1",
                    calendarWeeks: true,
                });

                $("#comparison_start").datepicker("setDate", "{{ $previous_week }}");
                $("#comparison_end").datepicker("setDate", "{{ $last_week }}");

                $('#div_comparison_dates_pw').show(600);
            }
            else if( the_val == "monthly" ){
                $('.comparison_daterange').datepicker({
                    format: "mm/yyyy",
                    minViewMode: 1,
                    language: "{{ App::getLocale() }}",
                    autoclose: true,
                    endDate: '-1m'
                });

                $("#comparison_start").datepicker("setDate", "{{ $previous_month }}");
                $("#comparison_end").datepicker("setDate", "{{ $last_month }}");

                $('#div_comparison_dates_pm').show(600);
            }
            else if( the_val == "yearly" ){
                $('.comparison_daterange').datepicker({
                    format: "yyyy",
                    minViewMode: 2,
                    language: "{{ App::getLocale() }}",
                    autoclose: true,
                    endDate: '-1y'
                });

                $("#comparison_start").datepicker("setDate", "{{ $previous_year }}");
                $("#comparison_end").datepicker("setDate", "{{ $last_year }}");

                $('#div_comparison_dates_py').show(600);
            }
            else{
                return alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
            }
        });

        $('.comparison_daterange').datepicker({
            format: "dd/mm/yyyy",
            language: "{{ App::getLocale() }}",
            todayHighlight: true,
            autoclose: true,
            endDate: '-1d',
            immediateUpdates: true
        });

        $('#working_type').select2({
            minimumResultsForSearch: Infinity
        }).on('change', function (evt) {
            the_val = $(this).val();
            $('#create_report_form').find('.instant, .periodic').hide(600);
            $('#div_comparison_dates_p, #div_comparison_dates_i').hide(600);

            if( the_val == "instant" ){
                $('#working_period').val('daily').trigger('change');
                $('#create_report_form').find('.instant').show(600);

                if( $('input[name=report_type_options]:checked').val() == 1 ){
                    $('#div_stats_data_range, #div_stats_dates_i').hide(600);
                    $('#div_comparison_dates_i').show(600);
                }
                else{
                    $('#div_stats_data_range, #div_stats_dates_i').show(600);
                }
            }
            else if( the_val == "periodic" ){
                $('#create_report_form').find('.periodic').show(600);
                $('#div_stats_data_range, #div_stats_dates_i').hide(600);

                if( $('input[name=report_type_options]:checked').val() == 1 ){
                    $('#div_working_period_1, #div_working_period_2').hide();
                    $('#div_comparison_dates_p').show(600);
                }
            }
            else{
                return;
            }
        });

        $('#working_period').select2({
            minimumResultsForSearch: Infinity
        }).on('change', function (evt) {
            the_val = $(this).val();
            $('#div_working_period_2, #div_working_period_weekly, #div_working_period_monthly').hide(600);

            if( the_val == "daily" ){
                /*if( $('input[name=report_type_options]:checked').val() == 0 )
                    $('#data_range').closest('.form-group').show(600);*/
            }
            else if( the_val == "weekly" ){
                $('#div_working_period_weekly').show();
                $('#div_working_period_2').show(600);

                /*if( $('input[name=report_type_options]:checked').val() == 0 )
                    $('#data_range').closest('.form-group').show(600);*/
            }
            else if( the_val == "monthly" ){
                $('#div_working_period_monthly').show();
                $('#div_working_period_2').show(600);

                /*if( $('input[name=report_type_options]:checked').val() == 0 )
                    $('#data_range').closest('.form-group').show(600);*/
            }
            else if( the_val == "yearly" ){
                $('#data_range').closest('.form-group').hide(600);
            }
            else{
                return;
            }
        });

        $("#content_types").select2({
            placeholder:'{{ trans('reporting.multiple_selectable') }}',
            minimumResultsForSearch: Infinity,
            multiple: true,
            allowClear: true
        });

        // placeholder width
        $('.select2-search__field').css('width', '100%');

        @if (session()->has('create_report_success') && session('create_report_success'))
            {{ session()->forget('create_report_success') }}

            custom_toastr('{{ trans('reporting.create_report_success') }}');
        @endif

        @if (session()->has('report_update_success') && session('report_update_success'))
            {{ session()->forget('report_update_success') }}

            custom_toastr('{{ trans('reporting.report_update_success') }}');
        @endif
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_report"))
        @if (session()->has('report_delete_success') && session('report_delete_success'))
            {{ session()->forget('report_delete_success') }}

            custom_toastr('{{ trans('reporting.report_delete_success') }}');
        @endif

        @if (session()->has('template_delete_success') && session('template_delete_success'))
            {{ session()->forget('template_delete_success') }}

            custom_toastr('{{ trans('reporting.template_delete_success') }}');
        @endif
    @endif

    var tab_1 = false,
        tab_2 = false,
        tab_3 = false;

    // Keep the current tab active after page reload
    rememberTabSelection('#reporting_tabs', !localStorage);

    if(document.location.hash){
        $("#reporting_tabs a[href='"+document.location.hash+"']").trigger('click');
    }

    function load_tab_content(selectedTab){
        if(selectedTab == "#tab-1" && tab_1 == false){
            {!! $ReportsTableObj->ready() !!}
            tab_1 = true;

            setInterval(function(){
                r_dt.ajax.reload();

            }, 30000);
        }
        else if(selectedTab == "#tab-2" && tab_2 == false){
            tab_2 = true;
            {!! $TemplatesTableObj->ready() !!}
        }
        else if(selectedTab == "#tab-3"){
            if( is_edit_click == false && $("#report_op_type").val() == "edit" ){
                confirmBox(
                    '',
                    '{{ trans('reporting.clear_form_warning') }}',
                    'info',
                    function(isConfirm) {
                        if(!isConfirm){
                            initialize_form();
                        }
                    },
                    true
                );
            }
            else if( $("#report_op_type").val() == "new" && tab_3 == false ){
                initialize_form();
                tab_3 = true;
            }

            is_edit_click = false;
        }
        else{
            return;
        }
    }

    // Load the selected tab content When the tab is changed
    $('#reporting_tabs a').on('shown.bs.tab', function(event){
        var selectedTab = $(event.target).attr("href");
        load_tab_content(selectedTab);

        // clear hash and parameter values from URL
        history.pushState('', document.title, window.location.pathname);
    });

    // Just install the related tab content When the page is first loaded
    active_tab = $('#reporting_tabs li.active').attr("tab");
    if( !(active_tab == "" || active_tab == null) )
        load_tab_content(active_tab);
    else
        $("#reporting_tabs a:first").trigger('click');
@endsection