<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Consumption;

class ConsumptionController extends Controller
{
    public function history($consumption = NULL){
    	if (isset($consumption)){
    		$consumption = Consumption::find($consumption);

    		return view('tables.labconsumptionsdetails', compact('consumption'))->with('pageTitle', auth()->user()->lab->labdesc." Consumption Details");
    	} else {
    		$data['consumptions'] = Consumption::get();
	    	$data = (object) $data;
	    	return view('tables.labconsumptions', compact('data'))->with('pageTitle', auth()->user()->lab->labdesc." Consumptions");
    	}
    }
}
