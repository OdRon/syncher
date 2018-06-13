<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HEIController extends Controller
{
    //
    public function index()
    {
    	return view('hei.followup')->with('pageTitle','HEI Followup');
    }
}
