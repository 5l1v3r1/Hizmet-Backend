<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Null_;

class OrganizationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {

    }

    /* These function isn't used any more

    public function showTable(Request $request){
        return view('pages.organization_schema');
    }

    public function getDistributors(){
        $return_value = array();

        if( Auth::user()->user_type == 1 || Auth::user()->user_type == 2 ){
            $result = DB::table('distributors')
                ->select(
                    'id',
                    'logo',
                    'name',
                    'authorized_name',
                    DB::raw('DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") as created_at'),
                    DB::raw("CONCAT(name, ',', created_at) as text")
                )
                ->where('status','<>',0)
                ->orderBy('created_at', 'desc')
                ->get();

            if( count($result) > 0){
                $return_value = json_encode($result);
            }
            else{
                $return_value = "NEXIST";
            }
        }
        else{
            abort(404);
        }

        return $return_value;
    }

    */

    public function getOrganizationSchema(Request $request){
        if( $request->has('distributor_id') && is_numeric($request->input('distributor_id')) ){
            $distributor_id = $request->input('distributor_id');
            $return_value = array();
            $show_clients = false;
            $show_modems = true;

            $master_node_id = 0;

            if($request->has("show_clients")){
                $show_clients = true;
            }

            if($request->has("no_modems")){
                $show_modems = false;
            }

            if( $distributor_id == 0 ){
                $master_node_id = 0;
                $tmp_array[] = array(
                    "id" => 0,
                    "parent" => "#",
                    "text" => trans('global.main_distributor'),
                    "icon" => "fa fa-star",
                    "state" => array(
                        "opened" => true,
                        "selected" => $show_clients
                    )
                );

                if($show_clients == true){
                    $the_clients = DB::table("clients")
                        ->select('id','name','org_schema_id')
                        ->where('distributor_id',0)
                        ->where('status','<>',0)
                        ->get();

                    if(COUNT($the_clients)>0 && is_numeric($the_clients[0]->id)){
                        foreach($the_clients as $one_client) {
                            $client_parent_id = $master_node_id;
                            if($one_client->org_schema_id != 0)
                                $client_parent_id = $one_client->org_schema_id;

                            $tmp_array[] = array(
                                "id" => $one_client->id."_client",
                                "parent" => $client_parent_id,
                                "text" => $one_client->name,
                                "icon" => "fa fa-handshake-o",
                                "state" => array(
                                )
                            );

                            if($show_clients == true){
                                $the_modems = DB::table("modems")
                                    ->select('id','serial_no')
                                    ->where('client_id', $one_client->id)
                                    ->where('status','<>',0)
                                    ->get();

                                if(COUNT($the_modems)>0 && is_numeric($the_modems[0]->id)){
                                    foreach($the_modems as $one_modem) {
                                        $tmp_array[] = array(
                                            "id" => $one_modem->id."_client_modem",
                                            "parent" => $one_client->id."_client",
                                            "text" => $one_modem->serial_no,
                                            "icon" => "fa fa-podcast",
                                            "state" => array(
                                            )
                                        );
                                    }
                                }
                            }

                        }
                    }
                }

                $return_value = $tmp_array;
            }
            else{
                $result = DB::table('organization_schema as O')
                    ->select('O.*', 'D.name as distributor_name')
                    ->leftJoin('distributors as D', 'D.id', 'O.distributor_id')
                    ->where('O.distributor_id', $distributor_id)
                    ->where('O.status' , '<>', 0)
                    ->orderBy('O.parent_id', 'ASC')
                    ->get();

                if( count($result)>0 && is_numeric($result[0]->distributor_id) ){
                    $tmp_array = array();

                    foreach ( $result as $one_result ){
                        if( $one_result->parent_id == 0 ){ // master node
                            $master_node_id = $one_result->id;
                            $tmp_array[] = array(
                                "id" => $one_result->id,
                                "parent" => "#",
                                "text" => $one_result->name,
                                "icon" => "fa fa-star",
                                "state" => array(
                                    "opened" => true,
                                    "selected" => $show_clients
                                )
                            );
                        }
                        else{
                            $tmp_array[] = array(
                                "id" => $one_result->id,
                                "parent" => $one_result->parent_id,
                                "text" => $one_result->name,
                                "icon" => false,
                                "state" => array(
                                )
                            );
                        }

                    }

                    if($show_clients == true){
                        $the_clients = DB::table("clients")
                            ->select('id','name','org_schema_id')
                            ->where('distributor_id',$distributor_id)
                            ->where('status','<>',0)
                            ->get();

                        if(COUNT($the_clients)>0 && is_numeric($the_clients[0]->id)){
                            foreach($the_clients as $one_client) {
                                $client_parent_id = $master_node_id;
                                if($one_client->org_schema_id != 0)
                                    $client_parent_id = $one_client->org_schema_id;

                                $tmp_array[] = array(
                                    "id" => $one_client->id."_client",
                                    "parent" => $client_parent_id,
                                    "text" => $one_client->name,
                                    "icon" => "fa fa-handshake-o",
                                    "state" => array(

                                    )
                                );

                                if($show_modems == true){
                                    $the_modems = DB::table("modems")
                                        ->select('id','serial_no')
                                        ->where('client_id', $one_client->id)
                                        ->where('status','<>',0)
                                        ->get();

                                    if(COUNT($the_modems)>0 && is_numeric($the_modems[0]->id)){
                                        foreach($the_modems as $one_modem) {
                                            $tmp_array[] = array(
                                                "id" => $one_modem->id."_client_modem",
                                                "parent" => $one_client->id."_client",
                                                "text" => $one_modem->serial_no,
                                                "icon" => "fa fa-podcast",
                                                "state" => array(
                                                )
                                            );
                                        }
                                    }
                                }

                            }
                        }
                    }

                    $return_value = $tmp_array;
                }
                else{
                    // This means that no nodes have been created yet. In other words, return only the distributor as a master node.
                    // insert record to create master node for this distributor
                    $result = DB::table('distributors as D')
                        ->select('D.id as id', 'D.name as name')
                        ->where('D.id', $distributor_id)
                        ->where('D.status' , '<>', 0)
                        ->first();

                    if( count($result)>0 && is_numeric($result->id) ){
                        $last_insert_id = DB::table('organization_schema')->insertGetId(
                            [
                                'name' => $result->name,
                                'parent_id' => 0,
                                'distributor_id' => $distributor_id,
                                'created_by' => Auth::user()->id
                            ]
                        );

                        $return_value = array(
                            "id" => $last_insert_id,
                            "text" => $result->name,
                            "icon" => "fa fa-certificate",
                            "state" => array(
                                "opened" => true,
                                "selected" => true
                            )
                        );
                    }
                    else{
                        abort(404);
                    }
                }
            }

            return json_encode($return_value);
        }
        else{
            abort(404);
        }
    }

    public function createNode(Request $request){
        //edit operation
        if($request->has('node_id') && is_numeric($request->input('node_id')) && $request->has('text')){
            if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                DB::table('organization_schema')
                    ->where('id', $request->input("node_id"))
                    ->update(
                        [
                            'name' => $request->input("text")
                        ]
                    );

            }
            else if(Auth::user()->user_type == 3){
                DB::table('organization_schema')
                    ->where('id', $request->input("node_id"))
                    ->where('distributor_id',Auth::user()->org_id)
                    ->update(
                        [
                            'name' => $request->input("text")
                        ]
                    );
            }
            else{
                abort(404);
            }

            //fire event
            Helper::fire_event("update", Auth::user(), "organization_schema", $request->input('node_id'));

            return "SUCCESS";
        } //else create new node
        else if( $request->has('parent_id') && is_numeric($request->input('parent_id')) && $request->has('distributor_id') && is_numeric($request->input('distributor_id')) ){
            $parent_id = $request->input('parent_id');
            $distributor_id = $request->input('distributor_id');

            if( Auth::user()->user_type == 3 ){
                $distributor_id = Auth::user()->org_id;
            }

            $last_insert_id = DB::table('organization_schema')->insertGetId(
                [
                    'name' => trans('organization_schema.new_element'),
                    'parent_id' => $parent_id,
                    'distributor_id' => $distributor_id,
                    'created_by' => Auth::user()->id
                ]
            );

            //fire event
            Helper::fire_event("create", Auth::user(), "organization_schema", $last_insert_id);

            return $last_insert_id;
        }
        else{
            abort(404);
        }
    }

    public function deleteNode(Request $request){
        // A client can not be deleted from here! Therefore, the ID value must be numeric.
        if( $request->has('id') && is_numeric($request->input('id')) ){
            $id = $request->input('id');

            $result = DB::table('organization_schema')
                ->where('id', $id)
                ->where('status', '<>', 0)
                ->first();

            if( COUNT($result)>0 && is_numeric($result->id) ){
                if( Auth::user()->user_type == 3 && $result->distributor_id != Auth::user()->org_id){
                    abort(404);
                }

                $children = DB::table('organization_schema')
                    ->where('parent_id', $id)
                    ->where('status', '<>', 0)
                    ->first();

                if( COUNT($children)>0 && is_numeric($children->id)){
                    return trans('organization_schema.not_deletable_b');
                }

                // Is this node used in client or report template?
                $c_result = DB::table('clients')
                    ->select(
                        DB::raw('MAX(id) as id'),
                        DB::raw("GROUP_CONCAT(name SEPARATOR ', ') as name")
                    )
                    ->where('org_schema_id', $id)
                    ->where('status', '<>', 0)
                    ->first();

                if( COUNT($c_result)>0 && is_numeric($c_result->id) ){
                    return trans('organization_schema.not_deletable_c', [ "clients" => $c_result->name ]);
                }

                $r_result = DB::table('reports')
                    ->select(
                        DB::raw('MAX(id) as id'),
                        DB::raw("GROUP_CONCAT(template_name SEPARATOR ', ') as name")
                    )
                    ->whereRaw("JSON_EXTRACT(`org_schema_detail`, '$.values') LIKE CONCAT('%','\"".$result->id."\"','%')")
                    ->whereRaw("json_contains(`detail`, '{\"working_type\" : \"periodic\"}')")
                    ->where('is_report', 0)
                    ->where('status', '<>', 0)
                    ->first();

                if( COUNT($r_result)>0 && is_numeric($r_result->id) ){
                    return trans('organization_schema.not_deletable_r', [ "templates" => $r_result->name ]);
                }

                DB::table('organization_schema')
                    ->where('id', $request->input("id"))
                    ->update(
                        [
                            'status' => 0
                        ]
                    );

                //fire event
                Helper::fire_event("delete", Auth::user(), "organization_schema", $request->input('id'));

                return "SUCCESS";

            }
            else{
                abort(404);
            }
        }
        else{
            abort(404);
        }
    }

    public function moveNode(Request $request){
        if( $request->has('node_id') && $request->has('new_parent_id') && is_numeric($request->input('new_parent_id')) && $request->has('old_parent_id') && is_numeric($request->input('old_parent_id')) ){

            $node_id = $request->input('node_id');
            $new_parent_id = $request->input('new_parent_id');
            $old_parent_id = $request->input('old_parent_id');
            $is_client = false;

            if( strpos($request->input('node_id'), 'client') !==false ){
                $is_client = true;
                $node_id = explode('_client', $request->input('node_id'));
                $node_id = $node_id[0];
            }

            if( !is_numeric($node_id) ){
                abort(404);
            }

            //check if the user has right to assgin this new given parent
            $target_result = DB::table("organization_schema")
                ->where("id",$new_parent_id)
                ->first();
            if(COUNT($target_result)>0 && is_numeric($target_result->id)){
                if(Auth::user()->user_type == 3 && Auth::user()->org_id != $target_result->distributor_id)
                    abort(404);
            }
            else{
                abort(404);
            }

            if( $is_client ==true ){
                // change clients db
                $result = DB::table('clients as C')
                    ->where('id', $node_id)
                    ->first();

                if( COUNT($result)>0 && is_numeric($result->id)){
                    if( Auth::user()->user_type == 3 && Auth::user()->org_id != $result->distributor_id ){
                        abort(404);
                    }

                    if($result->distributor_id != $target_result->distributor_id)
                        abort(404);

                    DB::table('clients')
                        ->where('id', $node_id)
                        ->update(
                            [
                                'org_schema_id' => $new_parent_id
                            ]
                        );

                    //fire event
                    Helper::fire_event("update", Auth::user(), "clients", $node_id);

                    return "SUCCESS";
                }
                else{
                    return "ERROR_2";
                }
            }
            else{
                $result = DB::table('organization_schema as O')
                    ->where('id', $node_id)
                    ->first();

                if( COUNT($result)>0 && is_numeric($result->id)){
                    if( Auth::user()->user_type == 3 && Auth::user()->org_id != $result->distributor_id ){
                        abort(404);
                    }

                    if($result->distributor_id != $target_result->distributor_id)
                        abort(404);

                    DB::table('organization_schema')
                        ->where('id', $node_id)
                        ->update(
                            [
                                'parent_id' => $new_parent_id
                            ]
                        );

                    //fire event
                    Helper::fire_event("update", Auth::user(), "organization_schema", $node_id);

                    return "SUCCESS";
                }
                else{
                    return "ERROR_3";
                }
            }
        }
        else{
            abort(404);
        }
    }

    public function updateNodeInfo(Request $request){
        if($request->has("data")){
            $data = json_decode($request->input("data"));
            $node_id = $data->node_id;
            unset($data->node_id);

            if(!is_numeric($node_id))
                abort(404);

            if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2){
                DB::table('organization_schema')
                    ->where('id', $node_id)
                    ->update(
                        [
                            'info' => json_encode($data)
                        ]
                    );

                Helper::fire_event("update", Auth::user(), "organization_schema", $node_id);

                return "SUCCESS";
            }
            else if(Auth::user()->user_type == 3){
                DB::table('organization_schema')
                    ->where('id', $node_id)
                    ->where('distributor_id', Auth::user()->org_id)
                    ->update(
                        [
                            'info' => json_encode($data)
                        ]
                    );

                Helper::fire_event("update", Auth::user(), "organization_schema", $node_id);

                return "SUCCESS";
            }
        }
    }

    public function getNodeDetail(Request $request){
        if($request->has("id") && is_numeric($request->input("id")) && $request->has("distributor_id") && is_numeric($request->input("distributor_id"))){

            if(Auth::user()->user_type == 3 && Auth::user()->org_id != $request->input("distributor_id"))
                abort(404);

            if($request->input("id") == 0){
                $result = DB::table("organization_schema")
                    ->select('info')
                    ->where('distributor_id', $request->input("distributor_id"))
                    ->where('parent_id', 0)
                    ->first();
            }
            else{
                $result = DB::table("organization_schema")
                    ->select('info')
                    ->where('id',$request->input("id"))
                    ->first();
            }

            if( COUNT($result)>0 && $result->info != "" && $result->info != NULL ){
                return $result->info;
            }
            else{
                return "EMPTY";
            }
        }
    }

}
