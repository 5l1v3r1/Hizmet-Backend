<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>2M | {{ trans('login.reset_password_title') }} </title>

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
        <div class="passwordBox animated fadeInDown" style="max-width: 560px;">
            <div class="row">
                <div class="col-md-12">
                    <div class="ibox-content" style="border-radius: 10px;">
                        <h2 class="font-bold text-center"> {{ trans('global.product_name') . " " . trans('login.reset_password_title') }} </h2>

                        <br />
                        <div class="row">
                            <div class="col-md-12">
                                <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
                                    {{ csrf_field() }}

                                    <input type="hidden" name="token" value="{{ $token }}">

                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label for="email" class="col-md-4 control-label"> {{ trans('login.email') }} </label>

                                        <div class="col-md-6">
                                            <input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" required autofocus>
                                            @if ($errors->has('email'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('email') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label for="password" class="col-md-4 control-label"> {{ trans('login.new_password') }} </label>
                                        <div class="col-md-6">
                                            <input id="password" type="password" class="form-control" name="password" required>

                                            @if ($errors->has('password'))
                                                <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                        <label for="password-confirm" class="col-md-4 control-label"> {{ trans('login.confirm_new_password') }} </label>
                                        <div class="col-md-6">
                                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                                            @if ($errors->has('password_confirmation'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-6 col-md-offset-4">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fa fa-refresh"></i> {{ trans('login.reset_password') }}
                                            </button>
                                        </div>
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