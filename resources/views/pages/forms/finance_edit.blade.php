<?php

$booking_name = DB::table('booking')
    ->orderBy('id', 'asc')
    ->get();

?>
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

    .div_editor_custom {
        border: 1px solid;
        border-radius: 0px 0px 10px 10px;
        background-color: white;
        min-height: 137px;
    }
</style>
<div class="modal inmodal" id="financeFormModal" tabindex="0" role="dialog" aria-hidden="true" style="display: none;">
    <form method="POST" action="{{ url('/finance/addFinance') }}" id="formFinanceFormModal">
        {{ csrf_field() }}
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span
                                class="sr-only">{{ trans("global.close") }}</span></button>
                    <i class="fa fa-list-alt modal-icon"></i>
                    <h4 class="modal-title" id="payment_modal_title">{{trans("finance.add_new_payment")}}</h4>
                    <small class="font-bold">{{trans("finance.add_new_payment_exp")}}</small>
                </div>
                <div class="modal-body">




                    <hr class="hr-text" data-content="{{ trans("finance.payment_status") }}">
                    {!!  Helper::get_status("payment_status", false) !!}
                    <br><br>
                    <hr class="hr-text" data-content="{{ trans("finance.payment_client_name") }}">
                    <div class="form-group">
                        {!!  Helper::get_clients_select("payment_client_name", false) !!}
                        <br>
                    </div><br>

                    <hr class="hr-text" data-content="{{ trans("finance.booking_name") }}">
                    <div class="form-group">
                        {!!  Helper::get_booking_select("booking_name_select", false) !!}
                        <br>
                    </div>
                    <br>
                    <hr class="hr-text" data-content="{{ trans("finance.amount") }}">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{trans('finance.amount')}} <span style="color:red;">*</span></label>
                        <div class="col-sm-6">
                            <input id="amount" placeholder="{{trans("finance.amount")}}" name="amount"
                               class="form-control" type="number" required minlength="3" maxlength="255">
                        </div><br>
                    </div>
                    <br>
                    <hr class="hr-text" data-content="{{ trans("finance.tax_rate") }}">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{trans('finance.tax_rate')}} <span style="color:red;">*</span></label>
                        <div class="col-sm-6">
                            <input id="tax_rate" placeholder="{{trans("finance.tax_rate")}}" name="tax_rate"
                                   class="form-control" type="number" required minlength="3" maxlength="255">
                        </div><br>
                    </div><br>
                    <hr class="hr-text" data-content="{{ trans("finance.net_amount") }}">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{trans('finance.net_amount')}} <span style="color:red;">*</span></label>
                        <div class="col-sm-6">
                            <input id="net_amount" placeholder="{{trans("finance.net_amount")}}" name="net_amount"
                                   class="form-control" type="number" required minlength="3" maxlength="255">
                        </div><br>
                    </div><br>

                    <div class="form-group" id="type_hidden">
                        <hr class="hr-text" data-content="{{ trans("finance.net_amount") }}">
                        <label class="col-sm-3 control-label">{{trans('finance.net_amount')}} <span style="color:red;">*</span></label>
                        <div class="col-sm-6">
                            <select class="form-control" name="type" id="type" style="width:100%;">
                                <option value="1">Gelen ödeme</option>
                                <option value="2">Giden Ödeme</option>
                            </select>
                        </div><br>
                    </div><br>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">{{trans("global.close")}}</button>
                    <button id="dinance_modal_submit_button" type="submit" class="btn btn-primary"
                            onclick="return validate_save_advert_op();">{{trans("global.save")}}</button>
                </div>
            </div>
        </div>


        <input type="hidden" id="payment_mode" name="payment_mode" value="new"/>
        <input type="hidden" id="payment_id" name="payment_id" value="0"/>

    </form>
</div>

<script>
    function show_add_new_form() {
        $("#payment_modal_title").html("{{ trans("finance.add_new_payment") }}");
        $("#payment_status").val("").trigger("change");
        $("#payment_client_name").val("").trigger("change");
        $("#booking_name_select").val("").trigger("change");
        $("#amount").val(" ");
        $("#tax_rate").val(" ");
        $("#net_amount").val(" ");
        $("#payment_mode").val("new");
        $("#type_hidden").show();


        $("#financeFormModal").modal('show');
    }

    function validate_save_advert_op() {

        $("#description_hidden").val($("#description").code());
        $("#requirements_hidden").val($("#requirements").code());
        $("#benefits_hidden").val($("#benefits").code());
        $("#formAdvertFormModal").parsley();
    }

    function edit_payment(id) {
        $("#payment_modal_title").html("{{ trans("finance.edit_payment") }}");
        $.ajax({

            method: "GET",
            url: "/finance/getFinanceInfo",
            data: "id=" + id,
            success: function (return_text) {

                the_obj = JSON.parse(return_text);

                //firstly load all data into modal form area

                $("#payment_status").val(the_obj.status).trigger("change");
                $("#payment_client_name").val(the_obj.client_id).trigger("change");
                $("#booking_name_select").val(the_obj.booking_id).trigger("change");
                $("#amount").val(the_obj.amount);
                $("#tax_rate").val(the_obj.tax);
                $("#net_amount").val(the_obj.net_amount);
                $("#payment_mode").val("edit");
                $("#payment_id").val(the_obj.id);
                $("#type_hidden").hide();


                //open modal box
                $("#financeFormModal").modal('show');
            }
        });
    }

    function pause_advert(unique_id) {

        $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("advert.pausing") }}...</div>");
        $.ajax({

            method: "POST",
            url: "/advert/pauseAdvert",
            data: "unique_id=" + unique_id,
            success: function (return_text) {


                $("#bg_block_screen").remove();
                location.reload();
            }
        });
    }
</script>

