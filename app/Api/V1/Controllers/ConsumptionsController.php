<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Consumption;
/**
 * 
 */
class ConsumptionsController extends Controller
{
	public function create(BlankRequest $request) {
		$consumptions_array = [];
		$consumptions = json_decode($request->input('consumptions'));
		foreach ($consumptions as $key => $consumption) {
			$existing = Consumption::existing($consumption->year, $consumption->month, $consumption->testtype, $consumption->kit_id)->first();
			if ($existing){
				$consumptions_array[] = ['original_id' => $consumption->id, 'national_id' => $existing->id ];
				continue;
			}

			// New consumption to be saved
			$saveconsumption = new Consumption();
			$consumptions_data = get_object_vars($consumption);
            $saveconsumption->fill($consumptions_data);
            $saveconsumption->original_id = $consumption->id;
            $saveconsumption->synched = 1;
            $saveconsumption->datesynched = date('Y-m-d');

            // Unset the ID so that it auto-increments and the national id because it does not exist at national
            unset($saveconsumption->id);
            unset($saveconsumption->national_id);
            $saveconsumption->save();
            $consumptions_array[] = ['original_id' => $saveconsumption->original_id, 'national_id' => $saveconsumption->id ];
		}
		return response()->json([
            'status' => 'ok',
            'consumptions' => $consumptions_array,
        ], 201);
	}	
}
?>