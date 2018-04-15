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
            $last_unread_id = "";
            $onclick = "";
            if($total_alerts && COUNT($total_alerts)>0){
                $unread_count = COUNT($total_alerts);
                $last_unread_id = $total_alerts[0]->id;
                $onclick = "mark_as_read();";
            }


            ?>

                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" onclick="{{ $onclick }}" href="#" aria-expanded="true">
                        <i class="fa fa-bell"></i>  <span class="label label-primary">{{ $unread_count }}</span>
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

                    <script>
                        function mark_as_read(){
                            $.ajax({
                                method:"POST",
                                url:"/alerts/update_user_read",
                                data:"id={{ $last_unread_id }}",
                                success: function(return_text){



                                }
                            });
                        }
                    </script>
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