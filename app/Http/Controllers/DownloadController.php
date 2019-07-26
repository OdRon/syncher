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

    public function collection_guidelines(){
        $path = public_path('downloads/collection_manual.pdf');
        return response()->download($path, 'KEMRI Nairobi HIV Lab sample collection manual 2019.pdf');
    }

    public function api(){
        $path = public_path('downloads/Lab.postman_collection.json');
        return response()->download($path);
    }

    public function hit_api(){
        $path = public_path('downloads/HIT.postman_collection.json');
        return response()->download($path);
    }

    public function remotelogin() {
        $path = public_path('downloads/NASCOP_Remote_Login_SOP.pdf');
        return response()->download($path, 'NASCOP Lab Remote Login SOP.pdf');
    }

    public function resource($resource) {
        dd($resource);
    }

}
