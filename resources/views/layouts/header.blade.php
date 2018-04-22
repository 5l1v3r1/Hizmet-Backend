<div class="row border-bottom" id="top_header_menu">
    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
        </div>
        <ul class="nav navbar-top-links navbar-right">


            <?php

            $total_alerts = DB::table('alerts as A')
                ->where('status','1')
                ->orderBy('A.id','DESC')
                ->get();
            $show_alerts = DB::table('alerts as A')
                ->orderBy('A.id','desc')
                ->where('status','1')
                ->limit('3')
                ->get();


            $unread_count = 0;
            if($total_alerts && COUNT($total_alerts)>0){
                $unread_count = COUNT($total_alerts);
            }

            $total_support = DB::table('support as A')
                ->where('status','1')
                ->orderBy('id','DESC')
                ->get();
            $show_support = DB::table('support')
                ->orderBy('id','desc')
                ->where('status','1')
                ->limit('3')
                ->get();
            $unread_count_support = 0;
            if($total_support && COUNT($total_support)>0){
                $unread_count_support = COUNT($total_support);

            }

            ?>

                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#" aria-expanded="true">
                        <i class="fa fa-envelope"></i> @if($unread_count_support==0)
                        @else<span class="label label-warning">{{ $unread_count_support }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-messages">
                        @foreach($show_support as $support)

                            <?php
                            $tarih= date('d.m.Y H:i:s', strtotime(str_replace('/', '-', $support->created_at)));
                            $user_data = DB::table('clients')
                                ->where('id',$support->created_by)
                                ->first();

                            ?>
                        <li>
                            <div class="dropdown-messages-box">
                                <a href="/support/detail/{{$support->id}}" class="pull-left" style="color: #000000;">
                                <div class="media-body">
                                    <small class="pull-right"> {!!  Helper::XZamanOnce($tarih) !!}</small>
                                    <strong>{{$user_data->name}}</strong> tarafından oluşturulan talebin konusu <strong>{{$support->subject}}</strong></strong><br>
                                    <small class="text-muted"> {{$tarih}}</small>
                                </div>
                                </a>

                            </div>
                        </li>

                                <li class="divider"></li>
                        @endforeach

                            <div class="text-center link-block">
                                <a href="/support">
                                    <strong>Tüm Talepler</strong>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </div>

                    </ul>
                </li>

                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown"  href="#" aria-expanded="true">
                        <i class="fa fa-bell"></i>  @if($unread_count==0)
                            @else<span class="label label-primary">{{ $unread_count }}</span>
                             @endif
                    </a>
                    <ul class="dropdown-menu dropdown-alerts">
                        @foreach($show_alerts as $alert)

                            <?php
                            $tarih= date('d.m.Y H:i:s', strtotime(str_replace('/', '-', $alert->created_at)));

                            ?>
                        {{$tarih}}
                        <li>
                            <a href="/alerts">
                                <div>
                                    <i class="fa fa-envelope fa-fw"></i> <strong>{{$alert->type}}</strong> {{$alert->sub_type}}
                                    <span class="pull-right text-muted small">  {!!  Helper::XZamanOnce($tarih) !!} </span>
                                </div>
                            </a>
                        </li>
                            <li class="divider"></li>
                        @endforeach
                            <div class="text-center link-block">
                                <a href="/alerts">
                                    <strong>Tüm Bildirimler</strong>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </div>
                    </ul>


                </li>


            <li>

                <a href="{{ url('/logout') }}"
                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                    <i class="fa fa-sign-out"></i> {{ trans('global.logout') }}
                </a>

                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </li>
        </ul>

    </nav>
</div> <!-- ./row border-bottom -->