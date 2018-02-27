@extends('layouts.master')

@section('title')
    {{ trans('asset_map.title') }}
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/map/ammap.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/js/map/plugins/export/export.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/css/plugins/iCheck/custom.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />

    <style>
        .select2-results__message{
            display: none;
        }

        body.fullscreen-mode {
            overflow-y: hidden;
            overflow-x: hidden;
        }

        .wrapper.fullscreen {
            bottom: 0;
            left: 0;
            margin-bottom: 0;
            overflow: auto;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 2030;
            background-color: #f3f3f4;
        }

        .map-marker {
            /* adjusting for the marker dimensions
            so that it is centered on coordinates */
            margin-left: -8px;
            margin-top: -8px;
        }

        .map-marker.map-clickable {
            cursor: pointer;
        }

        .the_pulse {
            width: 10px;
            height: 10px;
            border: 5px solid #0033cc;
            -webkit-border-radius: 30px;
            -moz-border-radius: 30px;
            border-radius: 30px;
            background-color: #716f42;
            z-index: 10;
            position: absolute;
            cursor:pointer;
        }

        .the_pulse:hover {
            margin-left: -5px;
            margin-top: -5px;
            border: 10px solid #0033cc;
        }

        .map-marker .dot_danger, .map-marker .dot_warning {
            background: transparent;
            -webkit-border-radius: 60px;
            -moz-border-radius: 60px;
            border-radius: 60px;
            height: 50px;
            width: 50px;
            -webkit-animation: the_pulse 3s ease-out;
            -moz-animation: the_pulse 3s ease-out;
            animation: the_pulse 3s ease-out;
            -webkit-animation-iteration-count: infinite;
            -moz-animation-iteration-count: infinite;
            animation-iteration-count: infinite;
            position: absolute;
            top: -20px;
            left: -20px;
            z-index: 1;
            opacity: 0;
        }

        .map-marker .dot_danger{
            border: 10px solid red;
        }

        .map-marker .dot_warning{
            border: 10px solid #000000;
        }

        @-moz-keyframes the_pulse {
            0% {
                -moz-transform: scale(0);
                opacity: 0.0;
            }
            25% {
                -moz-transform: scale(0);
                opacity: 0.1;
            }
            50% {
                -moz-transform: scale(0.2);
                opacity: 0.3;
            }
            75% {
                -moz-transform: scale(0.5);
                opacity: 0.5;
            }
            100% {
                -moz-transform: scale(1);
                opacity: 0.0;
            }
        }

        @-webkit-keyframes the_pulse {
            0% {
                -webkit-transform: scale(0);
                opacity: 0.0;
            }
            25% {
                -webkit-transform: scale(0);
                opacity: 0.1;
            }
            50% {
                -webkit-transform: scale(0.2);
                opacity: 0.3;
            }
            75% {
                -webkit-transform: scale(0.5);
                opacity: 0.5;
            }
            100% {
                -webkit-transform: scale(1);
                opacity: 0.0;
            }
        }
    </style>
@endsection

@section('content')
    <?php
        $where_for_modems = array(
                array("M.status","<>",0) // Daha sonra sadece aktif cihazları getir diye düzenlenecek
        );

        $where_for_devices = array(
                array("D.status","<>",0) // Daha sonra sadece aktif cihazları getir diye düzenlenecek
        );

        if( Auth::user()->user_type == 4 ){
            $where_for_modems[] = array("M.client_id",Auth::user()->org_id);
            $where_for_devices[] = array("M.client_id",Auth::user()->org_id);
        }
        else if( Auth::user()->user_type == 3 ){
            $where_for_modems[] = array("C.distributor_id",Auth::user()->org_id);
            $where_for_devices[] = array("C.distributor_id",Auth::user()->org_id);
        }
        else if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){

        }
        else{
            abort(404);
        }

        $total_modem_count = 0;
        $total_meter_count = 0;
        $total_relay_count = 0;
        $total_analyzer_count = 0;

        $total_modem_count = DB::table('modems as M')
                ->select('M.id as id')
                ->join('clients as C', 'M.client_id', 'C.id')
                ->where($where_for_modems)
                ->count();

        $devices = DB::table('devices as D')
                ->select('D.id as device_id', 'DT.type as device_type')
                ->join('device_type as DT', 'DT.id', 'D.device_type_id')
                ->join('modems as M', 'M.id', 'D.modem_id')
                ->join('clients as C', 'M.client_id', 'C.id')
                ->where($where_for_devices)
                ->get();

         foreach ($devices as $one_device){
            switch ($one_device->device_type){
                case "meter":
                    $total_meter_count++; break;
                case "relay":
                    $total_relay_count++; break;
                case "analyzer":
                    $total_analyzer_count++; break;
            }
         }

    ?>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" style="margin: 0px;">
            <div class="col-lg-6 col-md-7 col-xs-12 form-group text-center">
                <label class="checkbox-inline i-checks col-lg-3" style="margin: 0px;padding:6px 5px;color:#0033cc;font-weight:bold;">
                    <input type="radio" value="modem" name="devices" checked> {{ trans('asset_map.modem') }}
                    <span class="badge"> {{ $total_modem_count }} </span>
                </label>
                <label class="checkbox-inline i-checks col-lg-3" style="margin: 0px;padding:6px 5px;color:#408000;font-weight:bold;">
                    <input type="radio" value="meter" name="devices"> {{ trans('asset_map.meter') }}
                    <span class="badge"> {{ $total_meter_count }} </span>
                </label>
                <label class="checkbox-inline i-checks col-lg-3" style="margin: 0px;padding:6px 5px;color:#664400;font-weight:bold;">
                    <input type="radio" value="relay" name="devices"> {{ trans('asset_map.relay') }}
                    <span class="badge"> {{ $total_relay_count }} </span>
                </label>
                <label class="checkbox-inline i-checks col-lg-3" style="margin: 0px;padding:6px 5px;color:#000;font-weight:bold;">
                    <input type="radio" value="analyzer" name="devices"> {{ trans('asset_map.analyzer') }}
                    <span class="badge"> {{ $total_analyzer_count }} </span>
                </label>
            </div>

            <div class="col-lg-4 col-md-5 col-xs-12 form-group">
                <select name="filter_type" id="filter_type" class="form-control" style="width:100%;">
                    <option value="normal" id="normal_filter"> {{ trans('asset_map.error_free_devices') }} </option>
                    <option value="connection"  id="connection_filter"> {{ trans('asset_map.connection_error_devices') }} </option>
                </select>
            </div>

            <div class="col-lg-1 col-lg-offset-1 col-md-1 col-xs-12 form-group text-center">
                <button id="fullscreen_button" title="" class="btn btn-white btn-circle" type="button">
                    <i class="fa fa-expand"></i>
                </button>
            </div>
        </div>

        <div id="chartdiv" style="width: auto;">
        </div>
    </div>
@endsection

@section('page_level_js')
    <script type="text/javascript" language="javascript" src="/js/map/ammap.js"></script>
    <script type="text/javascript" language="javascript" src="/js/map/turkeyHigh.js"></script>
    <script type='text/javascript' language="javascript" src='/js/map/plugins/export/export.min.js'></script>
    <script type='text/javascript' language="javascript" src='/js/map/plugins/export/lang/tr.js'></script>
    <!-- iCheck -->
    <script src="/js/plugins/iCheck/icheck.min.js"></script>
    <!-- Select2 -->
    <script type="text/javascript" language="javascript" src="/js/plugins/select2/dist/js/new.min.js"></script>

    <script type="text/javascript">
        var map = "";

        // this function will take current images on the map and create HTML elements for them
        function updateCustomMarkers( event ) {
            // get map object
            var map = event.chart;
            var used_location = [];

            // go through all of the images
            for ( var x in map.dataProvider.images ) {
                // get MapImage object
                var image = map.dataProvider.images[ x ];

                // check if it has corresponding HTML element
                if ( 'undefined' == typeof image.externalElement )
                    image.externalElement = createCustomMarker( image );

                // reposition the element accoridng to coordinates
                var xy = map.coordinatesToStageXY( image.longitude, image.latitude );
                lo = image.longitude;
                la = image.latitude;

                lo = lo.substring(0,7);
                la = la.substring(0,7);

                c = lo+la;

                if( used_location.indexOf(c) != -1){
                    image.externalElement.style.top = xy.y + 'px';
                    image.externalElement.style.left = (xy.x+5) + 'px';
                }
                else{
                    used_location.push(c);
                    image.externalElement.style.top = xy.y + 'px';
                    image.externalElement.style.left = xy.x + 'px';
                }
            }
        }

        // this function creates and returns a new marker element
        function createCustomMarker( image ) {
            // create holder
            var holder = document.createElement( 'div' );
            holder.className = 'map-marker tooltip-demo';
            //holder.title = image.title;
            holder.style.position = 'absolute';

            // maybe add a link to it?
            if ( undefined != image.url ) {
                holder.onclick = function() {
                    window.location.href = image.url;
                };
                holder.className += ' map-clickable';
            }

            // create dot
            if( image.alert_type != "normal"){
                var dot = document.createElement( 'div' );
                dot.className = 'dot_'+image.alert_type;
                holder.appendChild( dot );
            }


            // create pulse
            var pulse = document.createElement( 'div' );
            pulse.className = 'the_pulse';
            pulse.setAttribute('data-toggle','popover');
            pulse.style.borderColor = image.color;
            holder.appendChild( pulse );

            $(pulse).popover({
                html:true,
                title:'<b>'+image.client+'</b>',
                content: image.content,
                trigger: 'click focus',
                container: 'body'
            });

            $(pulse).tooltip({
                title:'<b>'+image.title+'</b>',
                html:true
            });

            // append the marker to the map container
            image.chart.chartDiv.appendChild( holder );

            return holder;
        }

        function map_size(){
            var w = Math.round($('#page-wrapper').width());
            var h = Math.round($(window).height()) - 125;
            $('#chartdiv').css("height", h + 'px');
            $('#chartdiv').css("max-height", w + 'px');
        }

        function get_map_data( device_type = 'modem', filter = 'normal' ){
            $('body').prepend("<div id='bg_block_screen'> <div class='loader'></div>{{ trans("global.preparing") }}...</div>");

            $.ajax({
                method:"POST",
                url:"asset_map/get_data",
                async: false,
                data:"type="+device_type+"&filter="+filter,
                success:function(return_text){
                    return_text = JSON.parse(return_text);

                    map.dataProvider.images = return_text;
                    map.validateData();

                    $("#bg_block_screen").remove();
                }
            });
        }

        /*
        function create_city_description(city){
            $.ajax({
                method: "POST",
                data: "city="+city,
                url: "/asset_map/get_city_data",
                success: function(return_text){
                    the_info = JSON.parse(return_text);

                    alert(the_info);
                }
            });

            return `
                    <table class='table' style='margin-bottom: 0px;'>
                        <tbody>
                            <tr>
                                <td style="border: none;">Modem Sayısı</td>
                                <td style="border: none;">60</td>
                            </tr>
                            <tr>
                                <td>Sayaç Sayısı</td>
                                <td>30</td>
                            </tr>
                            <tr>
                                <td>Röle Sayısı</td>
                                <td>20</td>
                            </tr>
                            <tr>
                                <td>Analizör Sayısı</td>
                                <td>10</td>
                            </tr>
                        </tbody>
                    </table>
                `;
        } */

    </script>
@endsection

@section('page_document_ready')
    $("#filter_type").select2({
        minimumResultsForSearch: Infinity
    });

    $('body').on('click', function (e) {
        $('[data-toggle="popover"],[data-original-title]').each(function () {
            //the 'is' for buttons that trigger popups
            //the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                (($(this).popover('hide').data('bs.popover')||{}).inState||{}).click = false  // fix for BS 3.3.6
            }

        });
    });

    $('input[name=devices]:radio').on('ifChecked', function(event){
        // To close all popup messages which are open
        $('body').click();

        the_val = $(this).val();
        filter = $('#filter_type').val();

        if( the_val == "modem"){
            $("#reactive_filter, #current_filter, #voltage_filter").remove();
        }
        else{
            if( $('#reactive_filter').length == 0 ){
                $('#filter_type').append('<option value="reactive" id="reactive_filter"> {{ trans('asset_map.reactive_devices') }}</option>');
            }
        }

        get_map_data( the_val , filter );
    });

    $("#filter_type").change(function(){
        the_val = $(this).val();
        device_type = $('input[name=devices]:checked', '.wrapper').val();

        get_map_data( device_type , the_val );

    });

    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
    });

    // Fullscreen function
    $('#fullscreen_button').on('click', function () {
        var button = $(this).find('i');
        button.toggleClass('fa-expand').toggleClass('fa-compress');

        $('body').toggleClass('fullscreen-mode');
        $('.wrapper').toggleClass('fullscreen');
        setTimeout(function () {
            $(window).trigger('resize');
        }, 100);
    });

    map_size();
    $( window ).resize(function() {
        map_size();
    });

    /*
    var areas = [];
    for (i=1; i<82; i++){
        first = false;

        if(i<10)
            i = "0"+i;

        areas.push({
                "id": "TR-"+i,
                "description": create_city_description(i)
            }
        );
    }
    */

    map = AmCharts.makeChart( "chartdiv", {
            "type": "map",
            "projection": "miller",
            "addClassNames": true,
            "zoomOnDoubleClick": true,
            "allowClickOnSelectedObject": false,

            /* the default settings for all MapArea objects */
            "areasSettings": {
                "autoZoom": true,
                "color": "#FFCC00",
                "selectable": true,
                "selectedColor": "#FFFFFF",
                "selectedOutlineColor": "#000000",
                "unlistedAreasColor": "#15A892",
                "outlineThickness": 0.1,
                "rollOverOutlineColor": "#FF0000",
                "rollOverBrightness": 20,
                "descriptionWindowTop": 0,
                "descriptionWindowRight": 0
            },

            /* the default settings for all MapImage objects */
            "imagesSettings": {
                "rollOverColor": "#089282",
                "rollOverScale": 3,
                "selectedScale": 3,
                "selectedColor": "#089282",
                "color": "#13564e"
            },

            "zoomControl": {
                "maxZoomLevel": 1024
            },

            "dataProvider": {
                "map": "turkeyHigh",
                "getAreasFromMap": true,
                // "areas": areas,
                "images": []
            }
        } );

        // add events to recalculate map position when the map is moved or zoomed
        map.addListener( "positionChanged", updateCustomMarkers );

        get_map_data();
@endsection