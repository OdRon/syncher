<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;
// use App\Rules\BeforeOrEqual;

class PullRequest extends FormRequest
{
    
    public function rules()
    {
        return [
            // 'test' => 'required|integer|max:2',
            // 'start_date' => ['date_format:Y-m-d', 'required_with:end_date', new BeforeOrEqual($this->input('end_date'), 'end_date')],
            'start_date' => ['date_format:Y-m-d', 'required_with:end_date', 'before_or_equal:' . $this->input('end_date')],
            'end_date' => 'date_format:Y-m-d',
            'date_dispatched_start' => ['date_format:Y-m-d', 'required_with:date_dispatched_end', 'before_or_equal:' . $this->input('date_dispatched_end')],
            'date_dispatched_end' => 'date_format:Y-m-d',            
        ];
    }

    public function authorize()
    {
    	$apikey = $this->headers->get('apikey');
        // $actual_key = env('HIT_KEY');
        // if($apikey != $actual_key || !$actual_key) return false;
        // else{
        //     return true;
        // }
    }

    public function messages()
    {
        return [
            'before_or_equal' => 'The :attribute field must be before or equal to the start of the range.'
        ];
    }
}
