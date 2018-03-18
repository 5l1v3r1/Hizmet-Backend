<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class PdfBillingController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function pdfview(Request $request, $id)
    {
        $the_users = DB::table("clients as C")
            ->select(
                "C.*",
                "B.*",
                "P.*",
                "B.booking_title as booking_title",
                "P.id as invoice_id",
                "C.name as client_name",
                "S.s_name as service"

            )
            ->join('booking as B', 'B.client_id', 'C.id')
            ->join('services as S', 'S.id', 'B.service_id')
            ->join('payment as P', 'P.client_id', 'C.id')
            ->where('P.id', $id)
            ->first();
        view()->share('the_users',$the_users);


        if($request->has('download')) {
            // pass view file
            $pdf = PDF::loadView('pdfview');
            // download pdf
            return $pdf->download('invoice.pdf');
        }
        return view('pdfview');
    }
}