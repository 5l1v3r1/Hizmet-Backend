@extends('layouts.master')

@section('title')
    {{ trans('system_summary.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/select2-bootstrap.min.css" />
@endsection

@section('content')


@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>

    <script>
        function get_last_reactives(type){
            if( typeof type !== undefined && type != ""){
                $.ajax({
                    method: "POST",
                    url: "/system_summary/get_reactives",
                    data: "type="+type,
                    success:function(return_text){
                        $('#reactives_table').html(return_text);
                    }
                });
            }
        }

        function get_last_alerts(type){
            if( typeof type !== undefined && type != "") {
                $.ajax({
                    method: "POST",
                    url: "/system_summary/get_alerts",
                    data: "type=" + type,
                    success: function (return_text) {
                        $('#alerts_table').html(return_text);
                    }
                });
            }
        }

        function get_last_devices(type){
            if( typeof type !== undefined && type != "") {
                $.ajax({
                    method: "POST",
                    url: "/system_summary/get_devices",
                    data: "type=" + type,
                    success: function (return_text) {
                        $('#devices_table').html(return_text);
                    }
                });
            }
        }

        function get_last_ucds(type){
            if( typeof type !== undefined && type != "") {
                $.ajax({
                    method: "POST",
                    url: "/system_summary/get_ucds",
                    data: "type=" + type,
                    success: function (return_text) {
                        $('#ucds_table').html(return_text);
                    }
                });
            }
        }


    </script>
@endsection

@section('page_document_ready')
    $.fn.select2.defaults.set( "theme", "default" );

    $("#last_reactive_dt").select2({
        minimumResultsForSearch: Infinity,
        width: 'auto',
        dropdownAutoWidth : true
    })
    .change(function(){
        the_val = $(this).val();
        //alert(the_val);
        get_last_reactives(the_val);
    })
    .val('all_devices')
    .trigger('change');

    $("#last_alerts_dt").select2({
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth : true,
        width: 'auto'
    })
    .change(function(){
        the_val = $(this).val();
        //alert(the_val);
        get_last_alerts(the_val);
    })
    .val('reactive')
    .trigger('change');

    $("#last_devices_dt").select2({
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth : true,
        width: 'auto'
    })
    .change(function(){
        the_val = $(this).val();
        //alert(the_val);
        get_last_devices(the_val);
    })
    .val('modem')
    .trigger('change');

    $("#last_ucd_dt").select2({
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth : true,
        width: 'auto'
    })
    .change(function(){
        the_val = $(this).val();
        //alert(the_val);
        get_last_ucds(the_val);
    })
    .val('users')
    .trigger('change');

@endsection