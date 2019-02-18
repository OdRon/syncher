<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Deliveries;
/**
 * 
 */
class DeliveriesController extends Controller
{
	public function create(BlankRequest $request){
		$deliveries_array = [];
		$deliveries = json_decode($request->input('deliveries'));
		foreach ($deliveries as $key => $delivery) {
			$existing = Deliveries::existing($delivery->year, $delivery->quarter, $delivery->testtype, $delivery->kit_id)->first();
			if ($existing){
				$deliveries_array[] = ['original_id' => $delivery->id, 'national_id' => $existing->id ];
				continue;
			}

			// New allocation to be saved
			$savedelivery = new Deliveries();
			$deliveries_data = get_object_vars($delivery);
            $savedelivery->fill($deliveries_data);
            $savedelivery->original_id = $delivery->id;
            $savedelivery->synched = 1;
            $savedelivery->datesynched = date('Y-m-d');

            // Unset the ID so that it auto-increments and the national id because it does not exist at national
            unset($savedelivery->id);
            unset($savedelivery->national_id);
            $savedelivery->save();
            $deliveries_array[] = ['original_id' => $savedelivery->original_id, 'national_id' => $savedelivery->id ];
		}
		return response()->json([
            'status' => 'ok',
            'deliveries' => $deliveries_array,
        ], 201);
	}
}
?>