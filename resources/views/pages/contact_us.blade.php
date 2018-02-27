@extends('layouts.master')

@section('title')
    {{ trans('contact_us.title') }}
@endsection

@section('page_level_css')

    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <style>
        select option[disabled]:first-child {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-6" style="margin-bottom: 30px;">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> {{ trans('contact_us.contact_us_info') }}
                </div>

                <form class="form-horizontal m-t-md" id="message_form_div">
                    <div class="form-group">
                        <label class="col-lg-3 control-label">{{ trans('contact_us.message_type') }} <span style="color:red;">*</span></label>
                        <div class="col-lg-9">
                            <select class="form-control" id="contact_us_message_type" name="contact_us_message_type" style="width:100%;">
                                <option value="suggestion">{{ trans('contact_us.suggestion') }}</option>
                                <option value="bug">{{ trans('contact_us.bug') }}</option>
                                <option value="complaint">{{ trans('contact_us.complaint') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label">{{ trans('contact_us.your_message') }} <span style="color:red;">*</span></label>
                        <div class="col-lg-9">
                            <textarea placeholder="{{ trans('contact_us.write_your_message') }}..." class="form-control" rows="6" id="contact_us_message" name ="contact_us_message" required minlength="20" maxlength="500"></textarea>
                            <span class="help-block" id="contact_us_message_error" style="color:red;"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-9 col-lg-offset-3" style="text-align: right;">
                            <button onclick="return send_message();" type="submit" class="btn btn-primary" id="the_send_button" name="the_send_button">
                                <i class="fa fa-paper-plane-o"></i> {{ trans('contact_us.send') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-6" style="margin-bottom: 30px;">
                <h1 style="margin-top: 0px; text-align: center;">2M Enerji Toptan Elektirik TİC. A.Ş.</h1>

                <br />

                <div class="col-xs-10 col-xs-offset-1" style="margin-bottom: 30px;">
                    <address>
                        <i class="fa fa-map-marker fa-lg" aria-hidden="true"></i>
                        <strong>
                                                    <span class="navy">
                                                        İstanbul Merkez Ofis
                                                    </span>
                        </strong>
                        <br />
                        Vera Plaza - 19 Mayıs Mah. İnönü Cad. Esin sk. No: 1 D: 3 <br />
                        Kozyatağı-Kadıköy / İstanbul
                    </address>

                    <address>
                        <i class="fa fa-phone fa-lg" aria-hidden="true"></i>
                        +90 216 414 64 00 | +90 216 360 44 51
                    </address>
                    <address>
                        <i class="fa fa-fax fa-lg" aria-hidden="true"></i> 	+90 216 345 08 72
                    </address>
                    <address>
                        <i class="fa fa-envelope-o fa-lg" aria-hidden="true"></i> enerjitakip@2menerji.com
                    </address>

                    <a href="https://www.facebook.com/2MEnerji">
                        <i class="fa fa-facebook-square fa-2x"></i>
                    </a>

                    <a href="https://tr.linkedin.com/company/2m-enerji" style="margin-left: 10px;">
                        <i class="fa fa-linkedin-square fa-2x"></i>
                    </a>
                </div>
            </div>
        </div>

        <br>
        <div class="row">
            <div class="col-lg-12">
                <div class="map" id="map" style="height: 300px;z-index: 1;"></div>
            </div>
        </div>
        <br>
    </div>
@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDAhJzAfuGq9J9-f_NGriGvs_8c2BWfRqc" async defer></script>

    <script>
        var map;

        function initMap() {
            var myLatLng = {lat: 40.980526, lng: 29.090039};

            // Create a map object and specify the DOM element for display.
            var map = new google.maps.Map(document.getElementById('map'), {
                center: myLatLng,
                scrollwheel: false,
                zoom: 16
            });

            // Create a marker and set its position.
            var marker = new google.maps.Marker({
                map: map,
                position: myLatLng,
                title: '2M Enerji'
            });
        }

        function send_message(){
            //$("#message_form_div").parsley();

            the_obj = $('#contact_us_message').parsley().validate();

            if(the_obj ==true){

                $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.sending") }}...</div>");

                type = $("#contact_us_message_type").val();
                message = $("#contact_us_message").val();


                $.ajax({

                    url:"/contact_us/send_message",
                    method:"POST",
                    data:"type="+type+"&message="+message,
                    success:function(return_text){

                        $("#bg_block_screen").remove();
                        if(return_text == "SUCCESS"){

                            alertBox("","{{ trans("contact_us.mail_success_message")}}","success");

                            $("#contact_us_message").val("");
                            $("#contact_us_message_type").val("suggestion").trigger("change");
                        }
                        else{
                            alertBox("","{{ trans("global.unexpected_error")}}","error");
                        }

                    }
                });

            }
            return false;
        }

        setTimeout(function() {
            initMap();
        }, 1000);
    </script>
@endsection

@section('page_document_ready')
    $("#contact_us_message_type").select2({
        minimumResultsForSearch: Infinity
    });
@endsection