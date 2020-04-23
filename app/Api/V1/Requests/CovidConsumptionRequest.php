<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;
// use App\Rules\BeforeOrEqual;

class CovidConsumptionRequest extends FormRequest
{
    
    public function rules()
    {
        return [];
    }

    public function authorize()
    {
        dd($this->headers->get('apikey'));
    	$apikey = $this->headers->get('apikey');
        $actual_key = env('COVID_KEY');
        if($apikey != $actual_key || !$actual_key) return false;
        else{
            return true;
        }
    }

    public function messages()
    {
        return [];
    }
}
