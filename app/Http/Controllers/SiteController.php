<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ViewFacility;

class SiteController extends Controller
{
    //
    public function index()
    {
    	$facilitys = ViewFacility::where('partner_id', '=', auth()->user()->partner)->get();
    	// dd($facilitys);
    	return view('tables.sites', compact('facilitys'))->with('pageTitle', 'Facilities as of::  '.date("F", mktime(null, null, null, date('m'))).' - '.date('Y'));
    }
}
