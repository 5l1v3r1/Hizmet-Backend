@extends('layouts.master')

@section('title')
    {{ trans('organization_schema.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/select2-bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/jsTree/themes/default/style.min.css" />
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>{{ trans("organization_schema.title") }}</h5>
                        <div class="ibox-tools">

                        </div>
                    </div>
                    <div class="ibox-content tooltip-demo">
                        @if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 )
                            <div class="form-horizontal" style="margin-bottom: 20px;">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> {{ trans('organization_schema.distributor') }} <span style="color:red;">*</span></label>
                                    <div class="col-sm-6" id="div_distributor_loading" style="padding-top: 6px;">
                                        <i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i> {{ trans('organization_schema.distributor_loading') }}
                                    </div>

                                    <div class="col-sm-6" id="div_distributor_list" style="display: none;">
                                        <select name="distributor_list" id="distributor_list" style="width:100%;" required class="form-control" data-placeholder="{{ trans('organization_schema.select_distributor') }}">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        @endif

                        <br />
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
                                        <button type="button" class="btn btn-primary" onclick="update_node_info();">
                                            <i class="fa fa-check-square-o"></i> {{ trans('organization_schema.save_update') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/jsTree/jstree.min.js"></script>

    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/parsley.min.js"></script>
    <script type="text/javascript" language="javascript" src="/js/plugins/parsley/{{App::getLocale()}}.js"></script>

    <script>

        function load_node_detail(id, distributor_id){
            $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.loading_info") }}...</div>");

            $("#show_node_info .form-control").val("");

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
            unexpected_error: '{{ trans('global.unexpected_error') }}'
        };
    </script>
@endsection

@section('page_document_ready')
    @if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 )
        $.ajax({
            method:"POST",
            url:"/organization_schema/get_distributors",
            data:"",
            async:false,
            success:function(return_text){
                if(return_text == "NEXIST"){
                    alertBox('', '{{ trans('organization_schema.nexist_distributor') }}', 'info');
                }
                else if( return_text == "ERROR" ){
                    alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
                }
                else{
                    data = JSON.parse(return_text);

                    $("#distributor_list").select2({
                        minimumResultsForSearch: 10,
                        placeholder: "{{ trans('organization_schema.select_distributor') }}",
                        data: data,
                        templateResult: function(distributor){
                            distributor_logo = "no_avatar.png";

                            if( !(distributor.logo == undefined || distributor.logo == "") )
                                distributor_logo =  distributor.logo;

                            return $('<div style="float:left;">'+
                                    '<img style="width:55px;height:55px;" src="/img/avatar/distributor/'+distributor_logo+'" />'+
                                    '</div>'+
                                    '<div style="float:left;margin-left:10px;font-size:14px;font-weight:bold;">' +
                                    ''+ distributor.name + '<br/>'+
                                    '<div style="font-size:12px;font-weight:normal;">{{ trans("organization_schema.authorized_person") }}: '+distributor.authorized_name+'</div>'+
                                    '<div style="font-size:12px;font-weight:normal;">{{ trans("organization_schema.created_at") }}: ' + distributor.created_at + '</div>'+
                                    '</div>'+
                                    '<div style="clear:both;"></div>');
                        },
                        templateSelection:function(distributor){
                            return distributor.name;
                        }
                    });

                    $('#div_distributor_loading').hide();
                    $('#div_distributor_list').show();
                }

                //to trigger placeholder
                $("#distributor_list").parent().find(".select2-selection__placeholder").html("{{ trans('organization_schema.select_distributor') }}");
            }
        });

        $('#distributor_list').change(function(){
            $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

            $('#div_schema').jstree('destroy');
            the_val = $(this).val();

            $.ajax({
                method: "POST",
                url: "/organization_schema/get_organization_schema",
                data: "distributor_id="+the_val,
                async: false,
                success: function(return_text){
                    if( return_text != "" && return_text != "ERROR" ){
                        // data, div, lang, contextMenu, checkbox ,multiple
                        createJsTree(return_text, the_val, 'div_schema', lang_obj);

                        /*
                        $('#div_schema').jstree({
                            'core' : {
                                'check_callback' : true,
                                'themes' : {
                                    'variant' : 'large',
                                    'responsive': false
                                },
                                'animation': 500,
                                'strings' : {
                                    'loading' : '{{ trans('global.loading') }} ...',
                                    'New node' : '{{ trans('organization_schema.new_element') }}'
                                },
                                'multiple' : false, // if checkbox enabled, multiple select can be enable|disabled
                                'data' : JSON.parse(return_text),
                                error : function(err) {
                                    if(err.id === 'unique_01') {
                                        alertBox('', '{{ trans('organization_schema.same_name_warning') }}', 'warning');
                                    }
                                }
                            },
                            'types' : {
                                'default' : {
                                    'icon': false
                                }
                            },
                            'checkbox' : {
                                'keep_selected_style' : false
                            },
                            'contextmenu':{
                                'items': function(node) {
                                    if (node.parents.length < 2) { // if the selected node is master node, than return only create option
                                        return {
                                            'Create': {
                                                'icon': 'fa fa-plus-square-o',
                                                'separator_before': false,
                                                'separator_after': false,
                                                'label': '{{ trans('organization_schema.add_node') }}',
                                                'action': function (data) {

                                                    parent_id = $('#div_schema').jstree('get_selected');
                                                    $.ajax({
                                                        method:'POST',
                                                        url:'/organization_schema/add_node',
                                                        data:'parent_id='+parent_id+'&distributor_id='+the_val,
                                                        success:function(return_text){
                                                            if( $.isNumeric(return_text) && Math.floor(return_text) == return_text && Math.abs(return_text) == return_text ){
                                                                // CREATE client side too
                                                                var ref = $.jstree.reference(data.reference);
                                                                sel = ref.get_selected();
                                                                if(!sel.length) {
                                                                    return false;
                                                                }
                                                                sel = sel[0];
                                                                sel = ref.create_node(sel, {"id":return_text, "parent":parent_id});
                                                                if(sel) {
                                                                    ref.edit(sel);
                                                                }
                                                            }
                                                            else{
                                                                alertBox('','{{ trans('global.unexpected_error') }}', 'error');
                                                            }
                                                        }
                                                    });

                                                }
                                            }
                                        };
                                    }

                                    return {
                                        'Create': {
                                            'icon': 'fa fa-plus-square-o',
                                            'separator_before': false,
                                            'separator_after': false,
                                            'label': '{{ trans('organization_schema.add_node') }}',
                                            'action': function (data) {
                                                parent_id = $('#div_schema').jstree('get_selected');

                                                $.ajax({
                                                    method:'POST',
                                                    url:'/organization_schema/add_node',
                                                    data:'parent_id='+parent_id+'&distributor_id='+the_val,
                                                    success:function(return_text){
                                                        if( $.isNumeric(return_text) && Math.floor(return_text) == return_text && Math.abs(return_text) == return_text ){
                                                            // CREATE client side too
                                                            var ref = $.jstree.reference(data.reference);
                                                            sel = ref.get_selected();
                                                            if(!sel.length) {
                                                                return false;
                                                            }
                                                            sel = sel[0];
                                                            sel = ref.create_node(sel, {"id":return_text, "parent":parent_id});
                                                            if(sel) {
                                                                ref.edit(sel);
                                                            }
                                                        }
                                                        else{
                                                            alertBox('','{{ trans('global.unexpected_error') }}', 'error');
                                                        }
                                                    }
                                                });
                                            }
                                        },
                                        'Rename': {
                                            'icon': 'fa fa-edit',
                                            'separator_before': false,
                                            'separator_after': false,
                                            'label': '{{ trans('organization_schema.rename') }}',
                                            'action': function (data) {

                                                var inst = $.jstree.reference(data.reference);
                                                obj = inst.get_node(data.reference);
                                                inst.edit(obj);
                                            }
                                        },
                                        'Remove': {
                                            'icon': 'fa fa-trash-o',
                                            'separator_before': true,
                                            'separator_after': false,
                                            'label': '{{ trans('organization_schema.delete_node') }}',
                                            'action': function (data) {

                                                return alert("yapım aşamasında");
                                                node_id = $('#div_schema').jstree('get_selected');

                                                confirmBox(
                                                    '',
                                                    '{{ trans('organization_schema.delete_node_warning') }}',
                                                    'warning',
                                                    function(){
                                                        $.ajax({
                                                            method:'POST',
                                                            url:'/organization_schema/delete_node',
                                                            data:'id='+node_id,
                                                            success:function(return_text){
                                                                if(return_text == 'SUCCESS'){
                                                                    // DELETE client side too
                                                                    var ref = $.jstree.reference(data.reference),
                                                                        sel = ref.get_selected();
                                                                    if(!sel.length) {
                                                                        return false;
                                                                    }
                                                                    ref.delete_node(sel);
                                                                }
                                                                else{
                                                                    alertBox('','{{ trans('global.unexpected_error') }}', 'error');
                                                                }
                                                            }
                                                        });
                                                    },
                                                    true
                                                );
                                            }
                                        }
                                    };
                                }
                            },
                            'plugins' : [
                                'themes',
                                'contextmenu',
                                'sort', // Automatically sorts all siblings in the tree
                                'unique', // Enforces that no nodes with the same name can coexist as siblings
                                'types'
                            ]
                        }).on('after_close.jstree', function (e, data) {
                            alert('closed');
                        }).on('select_node.jstree', function (e, data) {

                                load_node_detail(data.node.id,the_val);
                        }).on('rename_node.jstree', function (e, data) {
                            // This event runs after the rename operation is complete.
                            var parent = data.node.parent;
                            var node_id = data.node.id;
                            var node_name = data.node.text;

                            $.ajax({
                                method:'POST',
                                url:'/organization_schema/add_node',
                                data:'node_id='+node_id+"&text="+node_name,
                                success:function(return_text){

                                    if( return_text != 'SUCCESS' ){
                                        alertBox('','{{ trans('global.unexpected_error') }}', 'error');

                                    }
                                }
                            });

                        }).on('delete_node.jstree', function (e, data) {
                            // This event runs after the delete operation is complete.
                            alertBox('', '[' +data.node.text + '] {{ trans('organization_schema.node_deleted') }}', 'success');
                        })
                        .on('create_node.jstree', function (e, data) {
                            // This event runs after the create operation is complete.
                        }).on('ready.jstree',function (e,data){

                            load_node_detail(0,the_val);
                        }); */

                        $('#div_organization_schema').show();
                    }
                    else{
                        alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
                    }
                }
            });

            $("#bg_block_screen").remove();
        });

    @elseif( Auth::user()->user_type == 3 )
        <!-- get organization_schema according to Auth user org_id -->
        $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

        $.ajax({
            method: "POST",
            url: "/organization_schema/get_organization_schema",
            data: "distributor_id={{ Auth::user()->org_id }}",
            async: false,
            success: function(return_text){
                if( return_text != "" && return_text != "ERROR" ){
                    // data, div, lang, contextMenu, checkbox ,multiple
                    createJsTree(return_text, {{ Auth::user()->org_id }}, 'div_schema', lang_obj);
                    $('#div_organization_schema').show();
                }
                else{
                    alertBox('', '{{ trans('global.unexpected_error') }}', 'error');
                }
            }
        });

        $("#bg_block_screen").remove();

    @endif
@endsection