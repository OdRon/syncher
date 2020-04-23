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
        $apikey = $this->headers->get('apikey');
        $actual_key = env('COVID_KEY');
        print_r($actual_key);die();
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
