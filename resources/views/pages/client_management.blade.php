@extends('layouts.master')

@section('title')
    {{ trans('client_management.title') }}
@endsection

@section('page_level_css')
    {!! $DataTableObj->css() !!}
    <link rel="stylesheet" type="text/css" href="/js/plugins/jsTree/themes/default/style.min.css" />

@endsection

@section('content')
    <?php
        $client_logo_hdn_value = "not_changed";
    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        @if (Helper::has_right(Auth::user()->operations, "add_new_client"))
            <div class="row" id="div_add_new_client" style="display:none;">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5 id="modal_title"> {{ trans('client_management.add_new_client') }}</h5>
                            <div class="ibox-tools">
                                <a class="" onclick="cancel_add_new_form();">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <form class="m-t form-horizontal"  method="POST" action="{{ url('/client_management/add') }}" id="add_new_client_form">
                                {{ csrf_field() }}

                                <!-- get selectable distributors according to user type -->
                                {!!  Helper::get_distributors_select("new_client_distributor") !!}


                                <div class="form-group" id="div_org_tree" style="display:none;">
                                    <label class="col-sm-3 control-label"> </label>
                                    <div class="col-sm-6">
                                        <div id="org_tree">

                                        </div>
                                        <span class="help-block" id="org_tree_error" style="color:red;"></span>
                                    </div>
                                    <input type="hidden" value="0" id="hdn_org_tree_id" name="hdn_org_tree_id"/>
                                </div>


                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('client_management.name') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_client_name" name="new_client_name" required minlength="3" maxlength="255"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('client_management.authorized_name') }} <span style="color:red;">*</span> </label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_client_authorized_name" name="new_client_authorized_name" minlength="3" maxlength="255" required />

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('client_management.email') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="email" placeholder="" class="form-control" id="new_client_email" name="new_client_email" minlength="3" maxlength="255" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('client_management.gsm_phone') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_client_gsm_phone" name="new_client_gsm_phone" minlength="10" maxlength="20" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('client_management.phone') }} </label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_client_phone" name="new_client_phone" minlength="7" maxlength="20">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('client_management.fax') }} </label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_client_fax" name="new_client_fax" minlength="7" maxlength="20">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('client_management.tax_administration') }} </label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_client_tax_administration" name="new_client_tax_administration" minlength="3" maxlength="255">

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('client_management.tax_no') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" placeholder="" class="form-control" id="new_client_tax_no" name="new_client_tax_no" minlength="3" maxlength="30">

                                    </div>
                                </div>




                                <div class="form-group" style="margin-bottom: 5px;">
                                    <label class="col-sm-3 control-label"> {{ trans('modem_management.location') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="new_client_location_text" name="new_client_location_text" required minlength="3" maxlength="255">
                                    </div>
                                </div>
                                <input type="hidden" name="new_client_location_latitude" id="new_client_location_latitude" value=""/>
                                <input type="hidden" name="new_client_location_longitude" id="new_client_location_longitude" value=""/>

                                <div class="form-group">
                                    <div class="col-sm-6 col-sm-offset-3">
                                        <div id="new_client_location" style="width: 100%; height: 300px;"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">{{ trans('client_management.logo') }}</label>
                                    <div class="col-lg-6">
                                        <input class="file-loading" type="file" id="new_client_logo" name="new_client_logo">
                                        <input type="hidden" value="<?=$client_logo_hdn_value;?>" name="hidden_client_logo" id="hidden_client_logo" />
                                        <input type="hidden" value="" name="uploaded_client_image_name" id="uploaded_client_image_name" />
                                        <input type="hidden" value="/img/avatar/client/no_avatar.png" name="edit_client_image_name" id="edit_client_image_name" />
                                        <div id="new_client_logo_error" class="help-block"></div>
                                    </div>
                                </div>

                                <input type="hidden" value="new" id="client_op_type" name="client_op_type">
                                <input type="hidden" value="" id="client_edit_id" name="client_edit_id">

                                <div class="form-group">
                                    <div class="col-lg-4 col-lg-offset-3">
                                        <button type="button" class="btn btn-white" onclick="cancel_add_new_form();">
                                            <i class="fa fa-times"></i> {{ trans('client_management.cancel') }} </button>
                                        <button type="submit" class="btn btn-primary" id="save_client_button" name="save_client_button" onclick="return validate_save_op();"><i class="fa fa-thumbs-o-up"></i> {{ trans('client_management.save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row" id="div_client_dataTable">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("client_management.title") }}</h5>
                        <div class="ibox-tools"></div>
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

    @if (Helper::has_right(Auth::user()->operations, "add_new_client"))
        <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
        <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>
        <script type="text/javascript" language="javascript" src="/js/plugins/jsTree/jstree.min.js"></script>

        <script type="text/javascript" language="javascript" src='http://maps.google.com/maps/api/js?key=AIzaSyDAhJzAfuGq9J9-f_NGriGvs_8c2BWfRqc&libraries=places'></script>
        <script type="text/javascript" language="javascript" src="/js/plugins/locationPicker/locationpicker.jquery.min.js"></script>

        <script>

            var selected_org_schema_id = false;

            function validate_save_op(){
                $("#add_new_client_form").parsley();

                //check if org tree is selected
                if( (("{{ Auth::user()->user_type }}" == "1" || "{{ Auth::user()->user_type }}" == "2") && $("#new_client_distributor").val() != "0") || "{{ Auth::user()->user_type }}" == "3" ){
                    selected_tree_id = $('#org_tree').jstree('get_selected');
                    if(!($.isNumeric(selected_tree_id[0]))){
                        pop_up_source_error("org_tree_error","{{ trans("distributor_detail.required_field") }}",false);
                        $('html,body').animate({
                                scrollTop: $("#org_tree").offset().top},
                            'slow');
                        return false;
                    }
                    else{
                        $("#hdn_org_tree_id").val(selected_tree_id[0]);
                        $("#org_tree_error").hide();
                    }
                }
            }

            function cancel_add_new_form(){
                $("#add_new_client_form .form-control").val("");
                $('#new_client_location_text').val('19 Mayıs, Akdeniz Sk. No:6, 34736 Kadıköy/İstanbul, Türkiye');

                $(".parsley-errors-list").remove();

                $("#new_client_logo").fileinput('refresh');
                $("#div_add_new_client").hide();
                $("#div_client_dataTable").show();
            }

            function show_add_new_form(){
                $("#modal_title").html("{{ trans('client_management.add_new_client') }}");
                $("#client_op_type").val("new");
                $("#client_edit_id").val("");
                selected_org_schema_id = false;

                @if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 )
                    $("#new_client_distributor").val("0").trigger("change");
                @elseif( Auth::user()->user_type == 3 )
                    load_org_tree({{ Auth::user()->org_id }});
                @endif

                $('#new_client_location').locationpicker({
                    location: {
                        latitude: 40.980568,
                        longitude: 29.0887487
                    },
                    inputBinding: {
                        locationNameInput: $('#new_client_location_text'),
                        latitudeInput: $('#new_client_location_latitude'),
                        longitudeInput: $('#new_client_location_longitude'),
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

                $('#save_client_button').html('<i class="fa fa-thumbs-o-up"></i> {{trans("client_management.save")}}');

                $("#div_add_new_client").show();
                $("#div_client_dataTable").hide();
            }

            function edit_client(id){
                $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

                $("#client_op_type").val("edit");
                $("#client_edit_id").val(id);
                $("#modal_title").html("{{ trans('client_management.update_title') }}");

                $.ajax({
                    method:"POST",
                    url:"/client_management/get_info",
                    data:"id="+id,
                    async:false,
                    success:function(return_value){
                        if( $.trim(return_value) != 'NEXIST' && return_value.search("ERROR") == -1 ){
                            the_info = JSON.parse(return_value);
                            the_location = JSON.parse(the_info.location);

                            selected_org_schema_id = the_info["org_schema_id"];
                            @if (Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                                $("#new_client_distributor").val(the_info["distributor_id"]).trigger("change");
                            @elseif( Auth::user()->user_type == 3 )
                                load_org_tree(the_info["distributor_id"]);
                                load_additional_info(the_info["distributor_id"]);
                            @endif


                            $("#new_client_name").val(the_info["name"]);
                            $("#new_client_authorized_name").val(the_info["authorized_name"]);
                            $("#new_client_email").val(the_info["email"]);
                            $("#new_client_gsm_phone").val(the_info["gsm_phone"]);
                            $("#new_client_phone").val(the_info["phone"]);
                            $("#new_client_fax").val(the_info["fax"]);
                            $("#new_client_tax_administration").val(the_info["tax_administration"]);
                            $("#new_client_tax_no").val(the_info["tax_no"]);

                            $("#new_client_location_text").val(the_location.text);
                            $("#new_client_location_latitude").val(the_location.latitude);
                            $("#new_client_location_longitude").val(the_location.longitude);

                            $('#new_client_location').locationpicker({
                                location: {
                                    latitude: the_location.latitude,
                                    longitude: the_location.longitude
                                },
                                inputBinding: {
                                    locationNameInput: $('#new_client_location_text'),
                                    latitudeInput: $('#new_client_location_latitude'),
                                    longitudeInput: $('#new_client_location_longitude'),
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

                            $('#edit_client_logo').attr('src', '/img/avatar/client/' + the_info["logo"]);
                            $('#edit_client_image_name').val('/img/avatar/client/' + the_info["logo"]);

                            $('#save_client_button').html('<i class="fa fa-refresh"></i> {{trans("client_management.update")}}');

                            $("#div_client_dataTable").hide();
                            $("#div_add_new_client").show();
                            $("#bg_block_screen").remove();
                            $("#new_client_name").focus();
                        }
                        else{
                            alertBox("Oops...","{{ trans('global.unexpected_error') }}","error");
                        }
                    }
                });

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
                unexpected_error: '{{ trans('global.unexpected_error') }}'
            };

            function load_org_tree(id){
                $('#org_tree').jstree('destroy');
                $('#div_org_tree').show();
                $.ajax({
                    method: "POST",
                    url: "/organization_schema/get_organization_schema",
                    data: "distributor_id="+id,
                    async: false,
                    success: function(return_text){
                        if( return_text != "" && return_text != "ERROR" ){
                            // data, distributor_id,div, lang, contextMenu, checkbox ,multiple
                            createJsTree(return_text,id, 'org_tree', lang_obj, false, false, false,selected_org_schema_id);

                        }
                        else{
                            alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
                        }
                    }
                });
            }
        </script>
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_client"))
        <script>
            function delete_client(id){
                confirmBox('','{{ trans('client_management.delete_client_warning') }}','warning',function(){
                    $.ajax({
                        method:"POST",
                        url:"/client_management/delete",
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

    @if (Helper::has_right(Auth::user()->operations, "add_new_client"))
        @if (session()->has('new_client_insert_success') && session('new_client_insert_success'))
            {{ session()->forget('new_client_insert_success') }}

            custom_toastr('{{ trans('client_management.add_new_success') }}');
        @endif

        @if (session()->has('client_update_success') && session('client_update_success'))
            {{ session()->forget('client_update_success') }}

            custom_toastr('{{ trans('client_management.update_success') }}');
        @endif

        $("#new_client_logo").fileinput({
            uploadUrl: "/client_management/upload_image",
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
            elErrorContainer: '#new_client_logo_error',
            msgErrorClass: 'alert alert-block alert-danger',
            initialPreview: [
                "<img id='edit_client_logo' src='/img/avatar/client/no_avatar.png' class='file-preview-image' style='height:160px'>",
                ],
            initialCaption: " {{ trans('client_management.select_client_logo') }}"
        }).on("filebatchselected", function(event, files) {// trigger upload method immediately after files are selected
            $("#new_client_logo").fileinput("upload");
            $('#uploaded_client_image_name').val($('#new_client_logo').prop("files")[0]['name']);
        }).on("filecleared", function(event) {
            $("#hidden_client_logo").val("not_changed");
            $("#uploaded_client_image_name").val("");
            $('#new_client_logo').fileinput('refresh');
            $('#edit_client_logo').attr('src',$('#edit_client_image_name').val());
        }).on('filebatchuploadcomplete', function(event, files, extra) {
            $("#hidden_client_logo").val("changed");
            $(".file-thumbnail-footer").hide();
        });

        @if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 )
            $("#new_client_distributor").select2({
                minimumResultsForSearch: 10,
                placeholder: ""
            }).change(function(){
                the_val = $(this).val();
                if(the_val == 0)//meaning that this client is bound direct to system
                {
                    $("#div_org_tree").hide();

                    return;
                }
                else{
                    load_org_tree(the_val);
                }
            });
        @endif
    @endif

    @if (Helper::has_right(Auth::user()->operations, "delete_client"))
        @if (session()->has('client_delete_success') && session('client_delete_success'))
            {{ session()->forget('client_delete_success') }}

            custom_toastr('{{ trans('client_management.delete_success') }}');
        @endif
    @endif
@endsection