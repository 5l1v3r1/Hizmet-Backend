<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{

    public function __construct()
    {

    }


    public function sendMail(Request $request)
    {

        if(!($request->has("type") && $request->has("message") && strlen($request->input("message"))<=500))
            abort(404);

        $result = DB::table('users as U')
            ->select( DB::raw('(CASE WHEN U.user_type=3 THEN "distributor" WHEN U.user_type=4 THEN "client" ELSE "main_distributor" END) as org_type,(CASE WHEN U.user_type=3 THEN D.name WHEN U.user_type=4 THEN C.name ELSE "'.trans('global.main_distributor').'" END) as org_name, (CASE WHEN U.user_type=3 THEN CONCAT(D.gsm_phone," / ",D.phone) WHEN U.user_type=4 THEN CONCAT(C.gsm_phone," / ",C.phone) ELSE "---" END) as phone'))
            ->leftJoin('clients as C', 'C.id', 'U.org_id')
            ->leftJoin('distributors as D', 'D.id', 'U.org_id')
            ->where("U.id", Auth::user()->id)
            ->first();

        $data = array(
            'name'=>Auth::user()->name,
            'email'=>Auth::user()->email,
            'orgname' => $result->org_name,
            'type' => trans("contact_us.".$request->input("type")),
            'subject'=>trans("contact_us.mail_subject",array("type"=>trans("contact_us.".$request->input("type")))),
            'org_type'=>trans("global.".$result->org_type),
            'body' => $request->input("message")
        );


        Mail::send('mail.contact_us', $data, function($message) use ($data) {
            $message->to("osmanaras50@gmail.com", "Ekopak iletişim")->subject($data["subject"]);
            $message->from(env('MAIL_USERNAME'),trans("global.mail_sender_name"));
        });



        if(count(Mail::failures())>0){
            return "ERROR";
        }
        else{
            return "SUCCESS";
        }


    }
}
