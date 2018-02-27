<!DOCTYPE html>
<html>
    <head>
        <!-- Basic Page Needs ================================================== -->
        <meta charset="utf-8">
        <meta name="description" content="{{ trans('global.description') }}">
        <meta name="keywords" content="{{ trans('global.description') }}">
        <title>3Faz | @yield('title')</title>

        <!-- Mobile Specific Metas ================================================== -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicons ================================================== -->
        <link rel="shortcut icon" href="/img/favicon.png" type="image/x-icon">
        <link rel="icon" href="/img/favicon.png" type="image/x-icon">

        <!-- CSS ================================================== -->
        <!-- Mainly scripts -->
        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/font-awesome/css/font-awesome.css">
        <link rel="stylesheet" href="/css/animate.css">
        <link rel="stylesheet" href="/css/style.css">

        <!-- Custom and plugin css -->
        <link rel="stylesheet" href="/js/plugins/sweetalert/sweetalert.css" >

        <!-- Toastr style -->
        <link href="/css/plugins/toastr/toastr.min.css" rel="stylesheet">

        <!--Custom css-->
        <link rel="stylesheet" href="/css/wif_custom_style.css">

        <!-- Page level javascript -->
        @yield('page_level_css')

        <!-- CSRF Script -->
        <script>
            window.Laravel = <?php echo json_encode([
                'csrfToken' => csrf_token(),
            ]); ?>
        </script>


    </head>
<body>
    <div id="wrapper">

        @include('layouts.left_menu')

        <div id="page-wrapper" class="gray-bg">
            @include('layouts.header')

            @yield('content')

            <!--Footer part-->
            <div class="footer" style="margin-top: 20px;">
                <div class="pull-right">
                    <strong><i>{{ trans('global.motto') }}...</i></strong>
                </div>
                <div>
                    <strong>Copyright</strong> Osman Aras Bitirme projesi. &copy; 2018
                </div>
            </div> <!-- .footer -->
        </div>
    </div>

    <!-- Javascript Files ================================================== -->
    <!-- Mainly scripts -->
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script type="text/javascript" src="/js/wif_custom.js"></script>

    <!-- Custom and plugin javascript -->
    <script type="text/javascript" src="/js/inspinia.js"></script>
    <script type="text/javascript" src="/js/plugins/pace/pace.min.js"></script>
    <script type="text/javascript" src="/js/plugins/sweetalert/sweetalert.min.js"></script>

    <!-- Toastr -->
    <script src="/js/plugins/toastr/toastr.min.js"></script>

    <!-- Page level javascript -->
    @yield('page_level_js')

    <!-- Document Ready javascript -->
    <script type="text/javascript">
        $(document).ready(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // add by uk to search case-insensitivity on client-side dataTable
            // This must be manually added to the desired search field
            $.extend($.expr[":"], {
                "containsI": function(elem, i, match, array) {
                    return (elem.textContent || elem.innerText || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
                }
            });

            // added by uk
            n = $('.navbar-default').height();
            p = $('#page-wrapper').height();
            if( n > p ) {
                $('#page-wrapper').css('min-height', Math.round(n) + 'px');
            }

            // added by uk
            // The select2-bootstrap.css file must be installed for this
            $.fn.select2.defaults.set( "theme", "bootstrap" );

            @yield('page_document_ready')
        });
    </script>
</body>
</html>