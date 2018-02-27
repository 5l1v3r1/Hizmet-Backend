<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>2M | {{ trans('login.forgot_password') }} </title>

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="/css/animate.css">
    <link rel="stylesheet" href="/css/style.css">

    <!-- CSRF Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
                'csrfToken' => csrf_token(),
        ]); ?>
    </script>
</head>

<body class="gray-bg">
    <div class="passwordBox animated fadeInDown">
        <div class="row">
            <div class="col-md-12">
                <div class="ibox-content" style="border-radius: 10px;">
                    <h2 class="font-bold text-center"> {{ trans('login.forgot_password') }} </h2>

                    <div class="row">
                        <div class="col-md-12">
                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form class="m-t" role="form" method="POST" action="{{ url('/password/email') }}">
                                {{ csrf_field() }}

                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                    <input id="email" placeholder="{{ trans('login.forgot_password_p') }}" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary block full-width m-b">
                                        <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                                        {{ trans('login.send_password_reset') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top:10px;">
            <div class="col-xs-6">
                <p style="margin-top:5px;"> <small> Copyright 2m Enerji &copy; 2016</small> </p>
            </div>
            <div class="col-xs-6 text-right">
                <button type="button" class="btn btn-success btn-sm" onclick="window.location.href='/login';">
                    <i class="fa fa-sign-in" aria-hidden="true"></i> {{ trans('login.go_login_page') }}
                </button>
            </div>
        </div>

        <br />
        <div class="text-center" style="padding:5px;">
            <a href="http://www.2menerji.com/" title="2M Enerji" target="_blank">
                <img class="img-responsive" alt="2M_Logo" style="max-height: 50px;display:inline;" src="/img/2m_logo.png" />
            </a>
        </div>
    </div>
</body>
</html>