<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\Helper;
use App\Helpers\DataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    private $columns;

    public function __construct()
    {

    }

    public function show(Request $request)
    {

        return view('pages.stats');
    }




}
