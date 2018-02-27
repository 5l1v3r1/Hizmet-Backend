<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ trans('login.title') }}</title>

        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/font-awesome/css/font-awesome.css">
        <link rel="stylesheet" href="/css/plugins/iCheck/custom.css">
        <link rel="stylesheet" href="/css/animate.css">
        <link rel="stylesheet" href="/css/style.css">

        <style>
            html,
            body {
                height: 100%;
            }

            .carousel,
            .item,
            .active {
                height: 100%;
            }

            .carousel-inner {
                height: 100%;
            }

            /* Background images are set within the HTML using inline CSS, not here */

            .fill {
                width: 100%;
                height: 100%;
                background-position: center;
                -webkit-background-size: cover;
                -moz-background-size: cover;
                background-size: cover;
                -o-background-size: cover;
            }

            footer {
                margin: 50px 0;
            }

            #slide-0 {
                background-image: url(/img/slider/login_bg_1.jpg);
                opacity: 0.7;
            }

            #slide-1 {
                background-image: url(/img/slider/login_bg_2.jpg);
                opacity: 0.7;
            }

            #slide-2 {
                background-image: url(/img/slider/login_bg_3.jpg);
                opacity: 0.7;
            }

            #slide-3 {
                background-image: url(/img/slider/login_bg_4.jpg);
                opacity: 0.7;
            }

            #slide-4 {
                background-image: url(/img/slider/login_bg_5.jpg);
                opacity: 0.7;
            }

            #slide-5 {
                background-image: url(/img/slider/login_bg_6.jpg);
                opacity: 0.7;
            }
        </style>

         <!-- CSRF Scripts -->
        <script>
            window.Laravel = <?php echo json_encode([
                'csrfToken' => csrf_token(),
            ]); ?>;
        </script>
    </head>

    <body class="gray-bg">
        <img src="/img/slider/login_bg_1.jpg" style="display: none;">
        <img src="/img/slider/login_bg_2.jpg" style="display: none;">
        <img src="/img/slider/login_bg_3.jpg" style="display: none;">
        <img src="/img/slider/login_bg_4.jpg" style="display: none;">
        <img src="/img/slider/login_bg_5.jpg" style="display: none;">
        <img src="/img/slider/login_bg_6.jpg" style="display: none;">

        <header id="myCarousel" class="carousel slide" data-ride="carousel" style="position: fixed; width: 100%; height: 100%;">
            <div class="carousel-inner">
                <div class="item active">
                    <div class="fill" id="slide-0"></div>
                </div>

                <div class="item">
                    <div class="fill" id="slide-1"></div>
                </div>

                <div class="item">
                    <div class="fill" id="slide-2"></div>
                </div>

                <div class="item">
                    <div class="fill" id="slide-3"></div>
                </div>

                <div class="item">
                    <div class="fill" id="slide-4"></div>
                </div>

                <div class="item">
                    <div class="fill" id="slide-5"></div>
                </div>
            </div>
        </header>

        <div class="middle-box text-center loginscreen animated fadeInDown">
            <div>
                <div>
                    <h1 class="logo-name" style="color: #96BC3D;">HG</h1>
                </div>

                <h3 style="color: #00e600;; background-color: black; opacity: 0.7; border-radius: 10px; padding: 3px;">{{ trans('login.definition') }}</h3>

                <form class="m-t" role="form" method="POST" action="{{ url('/login') }}">
                    {{ csrf_field() }}

                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <input id="email" type="email" class="form-control" name="email" placeholder="{{ trans('login.email') }}" value="{{ old('email') }}" required autofocus>

                        @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                        <input id="password" type="password" class="form-control" name="password" placeholder="{{ trans('login.password') }}" required>

                        @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary block full-width m-b">{{ trans('login.login') }}</button>

                    <div class="form-group">
                        <label class="checkbox-inline i-checks" style="margin-right: 30px;color: #ffffff;font-weight: bold;">
                            <input type="checkbox" name="remember" value="remember_me"> {{ trans('login.remember_me') }}
                        </label>
                        <a href="{{ url('/password/reset') }}" style="color: #ffffff;font-weight: bold;">
                            {{ trans('login.forgot_password') }}
                        </a>
                    </div>
                </form>

                <div class="text-center" style="padding:5px;">
                    <a href="http://hizmet.site" title="Hizmet guru" target="_blank">
                        <img class="img-responsive" alt="Logo" style="max-height: 50px;display:inline;" src="/img/hizmet_logo.jpg" />
                    </a>
                </div>

                <p class="m-t" style="color: #ffffff;font-weight: bold;"> <small> Copyright Osman Aras | Hizmet Guru &copy; 2018</small> </p>
            </div>

        </div>

        <!-- Mainly scripts -->
        <script src="/js/jquery.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>

        <!-- iCheck -->
        <script src="/js/plugins/iCheck/icheck.min.js"></script>

        <script>
            $('.carousel').carousel({
                interval: 5000 //changes the speed
            });

            $(document).ready(function () {
                $('.i-checks').iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green',
                });
            });
        </script>
    </body>
</html>
