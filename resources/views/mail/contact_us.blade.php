<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="text/html">

</head>
<body>

{{ trans("contact_us.mail_hello") }},
<br/><br/>

{{ trans("contact_us.mail_intro",array("type"=>$type)) }}

<br/><br/>
<u>{{ trans("contact_us.mail_sender") }}:</u><br/>
{{ trans("contact_us.mail_name",array("name"=>$name)) }}<br/>
{{ trans("contact_us.mail_email",array("email"=>$email)) }}<br/>
{{ trans("contact_us.mail_phone",array("phone"=>$phone)) }}<br/>
{{ trans("contact_us.mail_orgname",array("orgname"=>$orgname,"org_type"=>$org_type)) }}<br/>



<br/><br/>

<u>Mesaj:</u><br/>
<div style=" background-color: #d9edf7;
    border-color: #bce8f1;
    border-radius: 4px;
    color: #31708f;
    margin: 10px;
    padding: 15px;">

    {!! $body !!}

</div>

</body>
</html>