@extends('layouts.master')

@section('title')
    {{ trans('distributor_management.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}
@endsection

@section('content')
    <?php
        $distributor_logo_hdn_value = "not_changed";
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        @if (Helper::has_right(Auth::user()->operations, "add_new_distributor"))
            <div class="row" id="div_add_new_distributor" style="display:none;">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5 id="modal_title"> {{ trans('distributor_management.add_new_distributor') }}</h5>
                            <div class="ibox-tools">
                                <a class="" onclick="cancel_add_new_form();">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <form class="m-t form-horizontal"  method="POST" action="{{ url('/distributor_management/add') }}" id="add_new_distributor_form">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.name') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_distributor_name" name="new_distributor_name" required minlength="3" maxlength="255"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.authorized_name') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_distributor_authorized_name" name="new_distributor_authorized_name" minlength="3" maxlength="255" required />

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.email') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="email" placeholder="" class="form-control" id="new_distributor_email" name="new_distributor_email" minlength="3" maxlength="255" required>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.gsm_phone') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_distributor_gsm_phone" name="new_distributor_gsm_phone" minlength="10" maxlength="20" required>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.phone') }} </label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_distributor_phone" name="new_distributor_phone" minlength="7" maxlength="20">

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.fax') }} </label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_distributor_fax" name="new_distributor_fax" minlength="7" maxlength="20">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.tax_administration') }} </label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_distributor_tax_administration" name="new_distributor_tax_administration" minlength="3" maxlength="255">

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.tax_no') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_distributor_tax_no" name="new_distributor_tax_no" minlength="3" maxlength="30">

                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom: 5px;">
                                    <label class="col-sm-3 control-label"> {{ trans('distributor_management.address') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="new_distributor_location_text" name="new_distributor_location_text" required minlength="3" maxlength="255">
                                    </div>
                                </div>
                                <input type="hidden" name="new_distributor_location_latitude" id="new_distributor_location_latitude" value=""/>
                                <input type="hidden" name="new_distributor_location_longitude" id="new_distributor_location_longitude" value=""/>

                                <div class="form-group">
                                    <div class="col-sm-6 col-sm-offset-3">
                                        <div id="new_distributor_location" style="width: 100%; height: 300px;"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">{{ trans('distributor_management.logo') }}</label>
                                    <div class="col-lg-6">
                                        <input class="file-loading" type="file" id="new_distributor_logo" name="new_distributor_logo">
                                        <input type="hidden" value="<?=$distributor_logo_hdn_value;?>" name="hidden_distributor_logo" id="hidden_distributor_logo" />
                                        <input type="hidden" value="" name="uploaded_distributor_image_name" id="uploaded_distributor_image_name" />
                                        <input type="hidden" value="/img/avatar/distributor/no_avatar.png" name="edit_distributor_image_name" id="edit_distributor_image_name" />
                                        <div id="new_distributor_logo_error" class="help-block"></div>
                                    </div>
                                </div>

                                <input type="hidden" value="new" id="distributor_op_type" name="distributor_op_type">
                                <input type="hidden" value="" id="distributor_edit_id" name="distributor_edit_id">

                                <div class="form-group">
                                    <div class="col-lg-4 col-lg-offset-3">
                                        <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                            <i class="fa fa-times"></i> {{ trans('distributor_management.cancel') }} </button>
                                        <button type="submit" class="btn btn-primary" id="save_distributor_button" name="save_distributor_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('distributor_management.save') }}</button>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row" id="div_distributor_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("distributor_management.title") }}</h5>
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

    @if (Helper::has_right(Auth::user()->operations, "add_new_distributor"))
        <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
        <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
        <script type="text/javascript" language="javascript" src='http://maps.google.com/maps/api/js?key=AIzaSyDAhJzAfuGq9J9-f_NGriGvs_8c2BWfRqc&libraries=places'></script>
        <script type="text/javascript" language="javascript" src="/js/plugins/locationPicker/locationpicker.jquery.min.js"></script>

        <script>
            function validate_save_op(){
                $("#add_new_distributor_form").parsley();
            }

            function cancel_add_new_form(){
                $("#add_new_distributor_form .form-control").val("");
                $('#new_distributor_location_text').val('19 Mayıs, Akdeniz Sk. No:6, 34736 Kadıköy/İstanbul, Türkiye');

                $(".parsley-errors-list").remove();

                $("#new_distributor_logo").fileinput('refresh');

                $("#div_add_new_distributor").hide();
                $("#div_distributor_dataTable").show();
            }

            function show_add_new_form(){
                $("#modal_title").html("{{ trans('distributor_management.add_new_distributor') }}");
                $("#distributor_op_type").val("new");
                $("#distributor_edit_id").val("");

                $('#new_distributor_location').locationpicker({
                    location: {
                        latitude: 40.980568,
                        longitude: 29.0887487
                    },
                    inputBinding: {
                        locationNameInput: $('#new_distributor_location_text'),
                        latitudeInput: $('#new_distributor_location_latitude'),
                        longitudeInput: $('#new_distributor_location_longitude'),
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

                $('#save_distributor_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("distributor_management.save")}}');

                $("#div_add_new_distributor").show();
                $("#div_distributor_dataTable").hide();
            }

            function edit_distributor(id){
                $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

                $("#distributor_op_type").val("edit");
                $("#distributor_edit_id").val(id);
                $("#modal_title").html("{{ trans('distributor_management.update_title') }}");

                $.ajax({
                    method:"POST",
                    url:"/distributor_management/get_info",
                    data:"id="+id,
                    async:false,
                    success:function(return_value){
                        if( $.trim(return_value) != 'NEXIST' && return_value.search("ERROR") == -1 ){
                            the_info = JSON.parse(return_value);
                            the_location = JSON.parse(the_info.location);

                            $("#new_distributor_name").val(the_info["name"]);
                            $("#new_distributor_authorized_name").val(the_info["authorized_name"]);
                            $("#new_distributor_email").val(the_info["email"]);
                            $("#new_distributor_gsm_phone").val(the_info["gsm_phone"]);
                            $("#new_distributor_phone").val(the_info["phone"]);
                            $("#new_distributor_fax").val(the_info["fax"]);
                            $("#new_distributor_tax_administration").val(the_info["tax_administration"]);
                            $("#new_distributor_tax_no").val(the_info["tax_no"]);

                            $("#new_distributor_location_text").val(the_location.text);
                            $("#new_distributor_location_latitude").val(the_location.latitude);
                            $("#new_distributor_location_longitude").val(the_location.longitude);

                            $('#new_distributor_location').locationpicker({
                                location: {
                                    latitude: the_location.latitude,
                                    longitude: the_location.longitude
                                },
                                inputBinding: {
                                    locationNameInput: $('#new_distributor_location_text'),
                                    latitudeInput: $('#new_distributor_location_latitude'),
                                    longitudeInput: $('#new_distributor_location_longitude'),
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

                            $('#edit_distributor_logo').attr('src', '/img/avatar/distributor/' + the_info["logo"]);
                            $('#edit_distributor_image_name').val('/img/avatar/distributor/' + the_info["logo"]);

                            $('#save_distributor_button').html('<i class="fa fa-refresh"></i> {{trans("distributor_management.update")}}');

                            $("#div_distributor_dataTable").hide();
                            $("#div_add_new_distributor").show();
                            $("#bg_block_screen").remove();
                            $("#new_distributor_name").focus();
                        }
                        else{
                            alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                        }
                    }
                });
            }
        </script>
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_distributor"))
        <script>
            function delete_distributor(id){
                confirmBox('','{{ trans('distributor_management.delete_distributor_warning') }}','warning',function(){
                    $.ajax({
                        method:"POST",
                        url:"/distributor_management/delete",
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

    @if (Helper::has_right(Auth::user()->operations, "add_new_distributor"))
        @if (session()->has('new_distributor_insert_success') && session('new_distributor_insert_success'))
            {{ session()->forget('new_distributor_insert_success') }}

            custom_toastr('{{ trans('distributor_management.add_new_success') }}');
        @endif

        @if (session()->has('distributor_update_success') && session('distributor_update_success'))
            {{ session()->forget('distributor_update_success') }}

            custom_toastr('{{ trans('distributor_management.update_success') }}');
        @endif

        $("#new_distributor_logo").fileinput({
            uploadUrl: "/distributor_management/upload_image",
            language: '{{App::getLocale()}}',
            showUpload: false,
            showClose: false,
            uploadClass: false,
            showUploadedThumbs: false,
            browseClass: "btn btn-success",
            removeClass: "btn btn-danger",
            maxFileCount: 1,
            maxFileSize: 2048, // 2 MB
            allowedFileExtensions: ["jpg", "gif", "png", "jpeg"],
            elErrorContainer: '#new_distributor_logo_error',
            msgErrorClass: 'alert alert-block alert-danger',
            initialPreview: [
            "<img id='edit_distributor_logo' src='/img/avatar/distributor/no_avatar.png' class='file-preview-image' style='height:160px'>",
            ],
            initialCaption: " {{ trans('distributor_management.select_distributor_logo') }}"
        }).on("filebatchselected", function(event, files) {// trigger upload method immediately after files are selected
            $("#new_distributor_logo").fileinput("upload");
            $('#uploaded_distributor_image_name').val($('#new_distributor_logo').prop("files")[0]['name']);
        }).on("filecleared", function(event) {
            $("#hidden_distributor_logo").val("not_changed");
            $("#uploaded_distributor_image_name").val("");
            $('#new_distributor_logo').fileinput('refresh');
            $('#edit_distributor_logo').attr('src',$('#edit_distributor_image_name').val());
        }).on('filebatchuploadcomplete', function(event, files, extra) {
            $("#hidden_distributor_logo").val("changed");
            $(".file-thumbnail-footer").hide();
        });
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_distributor"))
        @if (session()->has('distributor_delete_success') && session('distributor_delete_success'))
            {{ session()->forget('distributor_delete_success') }}

            custom_toastr('{{ trans('distributor_management.delete_success') }}');
        @endif
    @endif
@endsection