<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DownloadController extends Controller
{
	

	public function user_guide(){
    	$path = public_path('downloads/PartnerLoginUserGuide.pdf');
    	return response()->download($path);
    }

	public function consumption(){
    	$path = public_path('downloads/CONSUMPTION_GUIDE.pdf');
    	return response()->download($path);
    }

	public function hei(){
    	$path = public_path('downloads/HEIValidationToolGuide.pdf');
    	return response()->download($path);
    }

	public function poc(){
    	$path = public_path('downloads/POC_USERGUIDE.pdf');
    	return response()->download($path);
    }


	public function eid_req(){
    	$path = public_path('downloads/EID_REQUISITION_FORM.pdf');
    	return response()->download($path);
    }

	public function vl_req(){
    	$path = public_path('downloads/VL_REQUISITION_FORM.pdf');
    	return response()->download($path);
    }
}
