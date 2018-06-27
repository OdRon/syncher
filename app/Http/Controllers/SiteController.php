<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ViewFacility;

class SiteController extends Controller
{
    //
    public function index()
    {
    	$facilitys = ViewFacility::select('*');
    	if(auth()->user()->user_type_id == 3)
    		$facilitys = $facilitys->where('partner_id', '=', auth()->user()->level)->get();

    	if(auth()->user()->user_type_id == 4)
    		$facilitys = $facilitys->where('county_id', '=', auth()->user()->level)->get();

    	if(auth()->user()->user_type_id == 5)
    		$facilitys = $facilitys->where('subcounty_id', '=', auth()->user()->level)->get();

    	return view('tables.sites', compact('facilitys'))->with('pageTitle', 'Facilities as of::  '.date("F", mktime(null, null, null, date('m'))).' - '.date('Y'));
    }
}
