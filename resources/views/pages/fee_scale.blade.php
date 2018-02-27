@extends('layouts.master')

@section('title')
    {{ trans('fee_scale.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @if (Helper::has_right(Auth::user()->operations, "add_new_fee_scale"))
            <div class="row" id="div_add_new_fee" style="display:none;">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5 id="modal_title"> {{ trans('fee_scale.add_new_fee') }}</h5>
                            <div class="ibox-tools">
                                <a class="" onclick="cancel_add_new_form('#div_fee_dataTable','#div_add_new_fee');">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/fee_scale/add') }}" id="add_new_fee_form">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.name') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_name" name="new_fee_name" required minlength="3" maxlength="255">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.active_unit_price') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_aup" name="new_fee_aup" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.reactive_unit_price') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_raup" name="new_fee_raup" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.t1_unit_price') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_t1" name="new_fee_t1" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.t2_unit_price') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_t2" name="new_fee_t2" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.t3_unit_price') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_t3" name="new_fee_t3" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.distribution_cost') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_dc" name="new_fee_dc" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.energy_fund') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_ef" name="new_fee_ef" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.trt_share') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_ts" name="new_fee_ts" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.consumption_tax') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_ect" name="new_fee_ect" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.transformer_loss_unit_price') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_tlup" name="new_fee_tlup" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.transformer_power') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <select class="form-horizontal" id="new_fee_tp" name="new_fee_tp" style="width: 100%;" required minlength="1" maxlength="5">
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="160">160</option>
                                            <option value="250">250</option>
                                            <option value="400">400</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label"> {{ trans('fee_scale.power_unit_price') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="new_fee_pup" name="new_fee_pup" required minlength="1" maxlength="10" data-parsley-pattern="^[0-9]+(\.[0-9]{1,6}$)?">
                                    </div>
                                </div>

                                <input type="hidden" value="new" id="fee_op_type" name="fee_op_type">
                                <input type="hidden" value="" id="fee_edit_id" name="fee_edit_id">

                                <div class="form-group">
                                    <div class="col-lg-4 col-lg-offset-3">
                                        <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                            <i class="fa fa-times"></i> {{ trans('fee_scale.cancel') }} </button>
                                        <button type="submit" class="btn btn-primary" id="save_fee_button" name="save_fee_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('fee_scale.save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row" id="div_fee_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("fee_scale.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
                        {!! $DataTableObj->html() !!}
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .wrapper -->
@endsection

@section('page_level_js')
    {!! $DataTableObj->js() !!}

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>

    <script>
        @if (Helper::has_right(Auth::user()->operations, "add_new_fee_scale"))
            function validate_save_op(){
                $("#add_new_fee_form").parsley().reset();
                $("#add_new_fee_form").parsley();
            }

            $("#new_fee_tp").select2({
                minimumResultsForSearch: Infinity
            });

            function cancel_add_new_form(){
                $("#add_new_fee_form .form-control").val("");
                $(".parsley-errors-list").remove();

                $("#new_fee_tp").select2({
                    minimumResultsForSearch: Infinity
                }).val(50).trigger("change");

                $("#div_add_new_fee").hide();
                $("#div_fee_dataTable").show();
            }

            function show_add_new_form(){
                $("#fee_op_type").val("new");
                $("#fee_edit_id").val("");
                $("#modal_title").html("{{ trans('fee_scale.add_new_fee') }}");

                $('#save_fee_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("fee_scale.save")}}');
                $("#div_add_new_fee").show();
                $("#div_fee_dataTable").hide();
            }

            function edit_fee(id){
                $('body').prepend("<div id='bg_block_screen'><div class='loader'></div>{{ trans("global.preparing") }}...</div>");

                $("#fee_op_type").val("edit");
                $("#fee_edit_id").val(id);
                $("#modal_title").html("{{ trans('fee_scale.update_title') }}");

                $.ajax({
                    method:"POST",
                    url:"/fee_scale/get_info",
                    data:"id="+id,
                    async:false,
                    success:function(return_value){
                        if( $.trim(return_value) != 'NEXIST' && return_value.search("ERROR") == -1 ){
                            the_info = JSON.parse(return_value);

                            $("#new_fee_name").val(the_info["name"]);
                            $("#new_fee_aup").val(the_info["active_unit_price"]);
                            $("#new_fee_raup").val(the_info["reactive_unit_price"]);
                            $("#new_fee_t1").val(the_info["t1_unit_price"]);
                            $("#new_fee_t2").val(the_info["t2_unit_price"]);
                            $("#new_fee_t3").val(the_info["t3_unit_price"]);
                            $("#new_fee_dc").val(the_info["distribution_cost"]);
                            $("#new_fee_ef").val(the_info["energy_fund"]);
                            $("#new_fee_ts").val(the_info["trt_share"]);
                            $("#new_fee_ect").val(the_info["consumption_tax"]);
                            $("#new_fee_tlup").val(the_info["transformer_loss_unit_price"]);
                            $("#new_fee_pup").val(the_info["power_unit_price"]);

                            $("#new_fee_tp").select2({
                                minimumResultsForSearch: Infinity
                            }).val(the_info["transformer_power"]).trigger("change");


                            $('#save_fee_button').html('<i class="fa fa-refresh"></i> {{trans("fee_scale.update")}}');
                            $("#div_fee_dataTable").hide();
                            $("#div_add_new_fee").show();
                            $("#new_fee_name").focus();
                        }
                        else{
                            alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                        }
                    }
                });

                $("#bg_block_screen").remove();
            }
        @endif

        @if( Helper::has_right(Auth::user()->operations, "delete_fee_scale") )
            function delete_fee(id){

                confirmBox('','{{ trans('fee_scale.delete_warning') }}','warning',function(){

                    $.ajax({
                        method:"POST",
                        url:"/fee_scale/delete",
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

    @if (Helper::has_right(Auth::user()->operations, "add_new_fee_scale"))
        @if (session()->has('fee_update_success') && session('fee_update_success'))
            {{ session()->forget('fee_update_success') }}

            custom_toastr('{{ trans('fee_scale.fee_update_success') }}');
        @endif

        @if (session()->has('new_fee_insert_success') && session('new_fee_insert_success'))
            {{ session()->forget('new_fee_insert_success') }}

            custom_toastr('{{ trans('fee_scale.new_fee_insert_success') }}');
        @endif
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_fee_scale"))
        @if (session()->has('fee_scale_delete_success') && session('fee_scale_delete_success'))
            {{ session()->forget('fee_scale_delete_success') }}

            custom_toastr('{{ trans('fee_scale.fee_scale_delete_success') }}');
        @endif
    @endif

@endsection