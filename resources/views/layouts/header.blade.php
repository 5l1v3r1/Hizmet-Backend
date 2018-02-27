<div class="row border-bottom" id="top_header_menu">
    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
        </div>
        <ul class="nav navbar-top-links navbar-right">



            <?php
                $param_array = array();
                $param_array[] = Auth::user()->last_read_alert;
                $where_clause = "A.id>? AND A.status=1";


                if(Auth::user()->user_type == 3){
                    $param_array[] = Auth::user()->org_id;
                    $where_clause .= " AND D.id=?";
                }
                else if(Auth::user()->user_type == 4){
                    $param_array[] = Auth::user()->org_id;
                    $where_clause .= " AND C.id=?";
                }

            $total_alerts = DB::table('alerts as A')
                ->select("A.id")
                ->leftJoin('devices as D', 'D.id', 'A.device_id')
                ->leftJoin('modems as M', 'D.modem_id', 'M.id')
                ->leftJoin('clients as C', 'M.client_id', 'C.id')
                ->leftJoin('distributors as DD', 'C.distributor_id', 'DD.id')
                ->whereRaw($where_clause, $param_array)
                ->orderBy('A.id','DESC')
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
                <a title="{{ trans('global.show_all_alerts') }}" onclick="{{ $onclick }}" class="dropdown-toggle count-info" data-toggle="dropdown" href="javascript:void(1);">
                    <i class="fa fa-bell"></i> <span class="label label-primary"> {{ $unread_count }}</span>
                </a>

                <script>
                    function mark_as_read(){
                        $.ajax({
                            method:"POST",
                            url:"/alerts/update_user_read",
                            data:"id={{ $last_unread_id }}",
                            success: function(return_text){

                                if(return_text=="SUCCESS")
                                    location.href = "/alerts";

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