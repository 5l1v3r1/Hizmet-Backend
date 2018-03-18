
        <!DOCTYPE html>
<html>
<head>
    <title>User list - PDF</title>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
<div class="container">

    <h1>Hizmet guru Fatura</h1>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row" id="div_user_summary">
            <div class="col-md-6">
                <img class="img-responsive" alt="3Faz_Logo" style="max-height: 50px;display:inline;"
                     src="/img/hizmet_logo.jpg">
                <div class="profile-info">
                    <div>
                        <br>
                        <strong> Hizmet.guru<br>
                            Ekopak Mimarlik Muhendislik<br>
                            Yazilim Hizm. Ltd. Sti.</strong>
                        <br>

                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <table class="table small m-b-xs">
                    <tbody>
                    <tr>
                        <td>
                            <strong>Fatura No</strong>
                        </td>
                        <td>
                            {{ $the_users->invoice_id }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Adi Soyadi</strong>
                        </td>
                        <td>
                            {{ $the_users->client_name }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Adress</strong>
                        </td>
                        <td>
                            {{$the_users->location}}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>



<br>
        <table class="table table-bordered">
            <thead>
            <th>Hizmet Adi</th>

            <th>Ucret</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <strong>{{ $the_users->service }}</strong>
                </td>
                <td>
                    {{ $the_users->net_amount }}
                </td>
            </tr>
            </tbody>
        </table>

        <p></p>
    </div>
</body>
</html>