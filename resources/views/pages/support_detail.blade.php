@extends('layouts.master')

@section('title')
    Talep Detayları
@endsection

@section('page_level_css')
    <link rel="stylesheet" type="text/css" href="/css/fileinput.min.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/select2/dist/css/new.min.css" />
    <link rel="stylesheet" type="text/css" href="/js/plugins/bootstrap-switch/bootstrap-switch.min.css" />


@endsection

@section('content')
    <?php
    $the_support = json_decode($the_support);
    $the_clients = json_decode($the_clients);
    $the_users = json_decode($the_users);
    $the_content = json_decode($the_content);

    $client_name = DB::table('clients')
        ->where("status",'<>', 0)
        ->where("type",'=', 1)
        ->orderBy('id')
        ->get();
    $user_date = DB::table('users')
        ->where("status",'<>', 0)
        ->orderBy('name')
        ->get();

    $client_date = DB::table('clients')
        ->where("status",'<>', 0)
        ->orderBy('name')
        ->get();
    $support_category = DB::table('support_category')
        ->orderBy('id')
        ->get();

    ?>

    <div class="row" id="div_user_summary">
        <div class="col-md-6">

            <div class="profile-info">
                <div>
                    <h2 class="no-margins">
                       <strong> {{ $the_support->subject}}</strong>
                    </h2>
                    <p style="margin: 10px 0 0;">
                        Destek Kategorisi:  <strong>  {{ $the_support->name}} </strong>

                    </p>

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <table class="table small m-b-xs">
                <tbody>
                <tr>
                    <td>
                        <strong>Talabi Oluşturan</strong>
                    </td>
                    <td>
                        {{$the_clients->name}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Atanan</strong>
                    </td>
                    <td>
                        {{$the_users->name}}
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong> Oluşturulma Tarihi</strong>
                    </td>
                    <td>
                        {{$the_support->created_at}}
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong> Son Güncelleme</strong>
                    </td>
                    <td>
                        {{$the_support->updated_at}}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading" id="accordion">
                        <span class="glyphicon glyphicon-comment"></span> Mesajlar
                        <div class="btn-group pull-right">
                            <a type="button" class="btn btn-default btn-xs" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                <span class="glyphicon glyphicon-chevron-down"></span>
                            </a>
                        </div>
                    </div>
                    <div class="panel-collapse collapse in" id="collapseOne">
                        <div class="panel-body">
                            <ul class="chat">


                                @foreach($the_content as $content)

                                     @if($content->message_type==1)
                                <li class="left clearfix"><span class="chat-img pull-left">
                            <img src="http://placehold.it/50/55C1E7/fff&text=U" alt="User Avatar" class="img-circle" />
                        </span>
                                    <div class="chat-body clearfix">
                                        <div class="header">
                                            <strong class="primary-font">{{$the_clients->name}}</strong> <small class="pull-right text-muted">
                                                <span class="glyphicon glyphicon-time"></span>{{$content->created_at}}</small>
                                        </div>
                                        <p>
                                            {{$content->content}}
                                        </p>
                                    </div>
                                </li>
                                @elseif($content->message_type==2)
                                <li class="right clearfix"><span class="chat-img pull-right">
                            <img src="http://placehold.it/50/FA6F57/fff&text=ME" alt="User Avatar" class="img-circle" />
                        </span>
                                    <div class="chat-body clearfix">
                                        <div class="header">
                                            <small class=" text-muted"><span class="glyphicon glyphicon-time"></span>1{{$content->created_at}}</small>
                                            <strong class="pull-right primary-font">{{$the_users->name}}</strong>
                                        </div>
                                        <p>
                                            {{$content->content}}
                                        </p>
                                    </div>
                                </li>
                                    @endif
                                @endforeach

                            </ul>
                        </div>
                        <div class="panel-footer">
                            <div class="input-group">
                                <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/support/message_send') }}" >
                                    {{ csrf_field() }}

                                <textarea rows="4" cols="50" id="btn-input" class="form-control input-sm" placeholder="Mesajınızı buraya yazınız" name="send_content"></textarea>
                                    <input type="hidden" value="send_message" id="message_op_type" name="message_op_type">
                                    <input type="hidden" value="2" id="are_you_admin" name="are_you_admin">
                                    <input type="hidden" value="{{$the_support->interested}}" id="admin_id" name="admin_id">
                                    <input type="hidden" value="{{$the_users->id}}" id="support_send_id" name="support_send_id">
                                <span class="input-group-btn">
                            <button class="btn btn-warning btn-sm" id="btn-chat">
                                Gönder</button>
                        </span>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-md-5">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5 id="modal_title"> Talebi Düzenle</h5>
                        <div class="ibox-tools">
                            <a class="" onclick="cancel_add_new_form('#div_support_dataTable','#div_add_new_support');">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">

                        <div class="row" id="add_new_user_warning" style="display: none;">
                            <div class="col-lg-10 col-lg-offset-1">
                                <div class="alert alert-danger">
                                    <i class="fa fa-exclamation-circle fa-lg" aria-hidden="true"></i>
                                    @if(Auth::user()->user_type == 3)
                                        {{ trans('user_management.add_new_user_d_warning') }}
                                    @elseif(Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                                        {{ trans('user_management.add_new_user_a_warning') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form class="m-t form-horizontal" role="form" method="POST" action="{{ url('/support/add') }}" id="add_new_support_form">
                            {{ csrf_field() }}



                            <div class="form-group">
                                <label class="col-sm-3 control-label">Başlık<span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <input type="text" placeholder="" class="form-control" id="new_support_title" name="new_support_title" required minlength="3" maxlength="255" value="{{$the_support->subject}}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Kategori <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="support_selected_category" name="support_selected_category" class="form-control" style="width: 100%;">
                                        <option value="0"></option>
                                        @foreach($support_category as $one_list)
                                            <option value="{{ $one_list->id }}">{{ $one_list->name }}</option>
                                        @endforeach
                                    </select></div>

                                <br>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Müşteri <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="selected_client_id" name="selected_client_id" class="form-control" style="width: 100%;">
                                        <option value="0"></option>
                                        @foreach($client_date as $one_list)
                                            <option value="{{ $one_list->id }}">{{ $one_list->name }}</option>
                                        @endforeach
                                    </select></div>

                                <br>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Atanan <span style="color:red;">*</span></label>
                                <div class="col-sm-6">
                                    <select id="selected_interested_id" name="selected_interested_id" class="form-control" style="width: 100%;">
                                        <option value="0"></option>
                                        @foreach($user_date as $one_list)
                                            <option value="{{ $one_list->id }}">{{ $one_list->name }}</option>
                                        @endforeach
                                    </select></div>

                                <br>
                            </div>
                            {!!  Helper::get_status("support_status", false) !!}


                            <input type="hidden" value="edit" id="support_op_type" name="support_op_type">
                            <input type="hidden" value="{{$the_users->id}}" id="support_edit_id" name="support_edit_id">

                            <div class="form-group">
                                <div class="col-lg-4 col-lg-offset-3">

                                    <button type="submit" class="btn btn-primary" id="save_support_button" name="save_support_button" ><i class="fa fa-thumbs-o-up"></i> {{ trans('user_management.save') }}</button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>


@endsection

@section('page_level_js')
<script>
    $(document).ready(function(){
        $("#support_selected_category").val("<?php echo $the_support->category_id ?>").trigger("change");
        $("#selected_client_id").val("<?php echo $the_support->created_by ?>").trigger("change");
        $("#selected_interested_id").val("<?php echo $the_support->interested ?>").trigger("change");
        $("#support_status").val("<?php echo $the_support->status ?>");
    });
</script>

@endsection

@section('page_document_ready')

    @if (session()->has('new_support_message_insert_success') && session('new_support_message_insert_success'))
        {{ session()->forget('new_support_message_insert_success') }}

        custom_toastr('Mesaj Gönderme Başarılı');
    @endif
@endsection