<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;
// use App\Rules\BeforeOrEqual;
// use Illuminate\Http\Request;

class CommodityRequest extends FormRequest
{
    
    public function rules()
    {
        return [];
    }

    public function authorize()
    {
    	$apikey = $this->headers->get('apikey');
        $apilab = \App\Lab::select('id', 'name')->where('apikey', '=', $apikey)->get();
        if($apilab->isEmpty()) return false;
        else{
            if(!session()->has('lab'));
                session(['lab' => $apilab->first()]);
            return true;
        }
    }

    public function messages()
    {
        return [];
    }
}
