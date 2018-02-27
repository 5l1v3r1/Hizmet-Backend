<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnnouncementController extends Controller
{

    public function showTable(Request $request){

        return view('pages.announcement');

    }
}
