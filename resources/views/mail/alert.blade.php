<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="text/html">
    </head>
    <?php

        $style = [
            /* Layout ------------------------------ */

            'body' => 'margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;',
            'email-wrapper' => 'width: 100%; margin: 0; padding: 0;',

            /* Masthead ----------------------- */

            'email-masthead' => 'padding: 25px 0; text-align: center;',
            'email-masthead_name' => 'font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;',

            'email-body' => 'width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;',
            'email-body_inner' => 'width: auto; max-width: 100%; margin: 0 auto; padding: 0;',
            'email-body_cell' => 'padding: 20px;',

            /* Body ------------------------------ */

            'body_action' => 'width: 100%; margin: 20px auto; padding: 0; text-align: center;',
            'body_sub' => 'margin-top: 20px; padding-top: 10px; border-top: 1px solid #EDEFF2;',

            /* Type ------------------------------ */

            'anchor' => 'color: #3869D4;',
            'header-1' => 'margin-top: 0; margin-bottom: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;',
            'paragraph' => 'margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em;',
            'paragraph-sub' => 'margin-top: 0; color: #74787E; font-size: 12px; line-height: 1.5em;',
            'paragraph-center' => 'text-align: center;',

            /* Buttons ------------------------------ */

            'button' => 'display: block; display: inline-block; width: 200px; min-height: 20px; padding: 10px;
                         background-color: #3869D4; border-radius: 3px; color: #ffffff; font-size: 15px; line-height: 25px;
                         text-align: center; text-decoration: none; -webkit-text-size-adjust: none;',

            'button--green' => 'background-color: #22BC66;',
            'button--red' => 'background-color: #dc4d2f;',
            'button--blue' => 'background-color: #3869D4;',

            'reactive_background' => 'background-color: #cc0000;',
            'voltage_background' => 'background-color: #ff9900;',
            'current_background' => 'background-color: #666633;',
            'connection_background' => 'background-color: #000099;',
            'report_background' => 'background-color: #000099;',
        ];

        $fontFamily = 'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;';
    ?>

    <body>
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="{{ $style['email-wrapper'] }} {{ $style[$type.'_background'] }}" align="center">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <!-- Logo -->
                        <tr>
                            <td style="{{ $style['email-masthead'] }} font-size:24px; color:#ffffff; line-height:1.25">
                                {{ $title }}
                            </td>
                        </tr>

                        <!-- Email Body -->
                        <tr>
                            <td>
                                <table style="width:100%; border:1px solid #f0f0f0; border-bottom:2px solid #c0c0c0; border-top:0;" cellspacing="0" cellpadding="0" border="0" bgcolor="#FAFAFA" width="100%">
                                    <tbody>
                                        <tr height="16px">
                                            <td rowspan="3" width="32px"></td>
                                            <td></td>
                                            <td rowspan="3" width="32px"></td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table style="text-align:left; width:100%" cellspacing="0" cellpadding="0" border="0">
                                                    <tbody>
                                                        <!-- Greeting -->
                                                        <tr>
                                                            <td style="{{ $fontFamily }}">
                                                                <h1 style="{{ $style['header-1'] }}">
                                                                    {{ trans('global.hello') }},
                                                                </h1>
                                                            </td>
                                                        </tr>

                                                        <tr height='10px'></tr>

                                                        <tr>
                                                            <td>
                                                                <!-- Intro -->
                                                                <p style="{{ $fontFamily }} {{ $style['paragraph'] }} margin-bottom: 0px;">
                                                                    {{ $detail_exp }}
                                                                </p>

                                                                {!! $detail_info !!}

                                                            </td>
                                                        </tr>

                                                        <tr height='5px'></tr>

                                                        <!-- Salutation -->
                                                        <tr>
                                                            <td style="{{ $fontFamily }}">
                                                                <p style="{{ $style['paragraph'] }}">
                                                                    {{ trans("alerts.mail_final_solute") }}
                                                                </p>
                                                            </td>
                                                        </tr>

                                                        <tr height='5px'></tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="padding: 10px 0; text-align: center; background-color: #FAFAFA;">
                                <p style="{{ $style['paragraph-sub'] }} margin-bottom: 0px;">
                                    &copy; {{ date('Y') }}
                                    <a style="{{ $style['anchor'] }}" href="{{ url('/') }}" target="_blank">{{ config('app.name') }}</a> |
                                    {{ trans('global.all_right_reserved') }}
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>