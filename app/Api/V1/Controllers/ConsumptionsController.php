<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;
use App\Api\V1\Requests\CommodityRequest;

use App\Consumption;
use App\ConsumptionDetail;
use App\ConsumptionDetailBreakdown;
use App\Kits;
use DB;
/**
 * 
 */
class ConsumptionsController extends Controller
{

	private $api_machines;
	private $machine_check = ['taqman' => 'taqman', 'abbott' => 'realtime'];
	private $testtypes = [['id' => 1, 'testtype' => 'EID'], ['id' => 2, 'testtype' => 'VL']];
	private $generalAddings = ['opening','consumed', 'qty_received','wasted','issued_out','issued_in','closing','requested'];

	public function __construct(){
		$this->api_machines = \App\Machine::get()->transform(function ($machine, $key) {
							    $machine->machine = strtolower($machine->machine);
							    return $machine;
							});
	}

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

	public function api_create(CommodityRequest $request) {
		$testtype = null;
		$response = [
				'message' => 'Consumption Data save failed',
				'status' => 406,
			];
		$machine = $this->getMachine($request, $testtype);
		if ($machine->isEmpty())
			return response()->json([
				'status' => 403,
				'error' => 'Forbidden action',
				'message' => 'Machine provided does not exist'
			]);
		foreach ($machine as $key => $value) {
			$machine = $value;
		}

		if($testtype->isEmpty())
			return response()->json([
				'status' => 403,
				'error' => 'Forbidden action',
				'message' => 'Test type provided does not exist'
			]);
		foreach ($testtype->toArray() as $key => $value) {
			$testtype = $value;
		}
		
		$consumption = $this->saveAPIConsumption($machine, $testtype, $request);
		if(null !== $consumption) 
			$response = [
				'message' => 'Consumption Data saved successfully to '.session('lab')->name,
				'status' => 201,
			];
		return response()->json($response);
	}

	private function saveAPIConsumption($machine, $testtype, $request) {
		$response = false;
		$date = explode(" ", $request->input('month_end_date'));
		$date = str_replace('/', '-', $date);
		$date = explode("-", $date[0]);
		if (empty($date))
			return null;
		$existing = Consumption::existing($date[2], $date[1], session('lab')->id)->get();
		if ($existing->isEmpty()) {
			$consumption = new Consumption;
			$consumption->year = $date[2];
            $consumption->month = $date[1];
            $consumption->submittedby = $request->input('reported_by');
            $consumption->datesubmitted = date('Y-m-d');
            $consumption->lab_id = session('lab')->id;
			if (strpos(env('APP_URL'), "lab-2.test.nascop.org"))
            	$consumption->test = 1;
            $consumption->apisave();
		} else {
			$consumption = $existing->first();
		}
		
        $details = $this->saveAPIConsumptionDetails($consumption, $testtype, $machine, $request);
		if ($details)
			$response = true;
		return $response;
	}

	private function saveAPIConsumptionDetails($consumption, $testtype, $machine, $request) {
		$response = false;
		$testtype = (object) $testtype;
		$existing = ConsumptionDetail::existing($consumption->id, $testtype->id, $machine->id)->get();
		if ($existing->isEmpty()){
			$consumption_details = new ConsumptionDetail;
			$consumption_details->consumption_id = $consumption->id;
			$consumption_details->testtype = $testtype->id;
			$consumption_details->machine_id = $machine->id;
			$consumption_details->apisave();
		} else
			$consumption_details = $existing->first();

		$details_breakdown = $this->saveAPIConsumptionDetailsBreakdown($consumption_details, $machine, $request, $testtype);
		if ($details_breakdown)
			$response = true;
		return $response;
	}

	private function saveAPIConsumptionDetailsBreakdown($details, $machine, $request, $testtype) {
		$response = false;
		$testtypename = $testtype->testtype;
		$kits = Kits::where('machine_id', '=', $machine->id)->get();
		$qualkit = 0;
		foreach ($kits as $key => $kit) {
			$existing = ConsumptionDetailBreakdown::existing($details->id, $kit->id, \App\Kits::class)->get();
			if ($existing->isEmpty()){
				$factor = json_decode($kit->factor);
				$test_factor = json_decode($kit->testFactor);
				if (is_object($factor))
					$factor = $factor->$testtypename;
				if (is_object($test_factor))
					$test_factor = $test_factor->$testtypename;
				$breakdown = new ConsumptionDetailBreakdown;
				$breakdown->consumption_details_id = $details->id;
				$breakdown->consumption_breakdown_id = $kit->id;
				$breakdown->consumption_breakdown_type = \App\Kits::class;
				foreach ($this->generalAddings as $keyAddings => $value) {
					// Consumed are calculated from the test count
					if ($value == 'consumed'){
						if ($kit->alias == 'qualkit'){
							$qualkit = ($request->input('sample_count')/$test_factor);
						}
						$breakdown->$value = ($factor * $qualkit);
					} else {
						$breakdown->$value = ($factor * (int)$this->getcomputedkitvalue($value, $request));
					}

				}
				$breakdown->apisave();
			} else 
				$breakdown = $existing->first();
		}
		if ($breakdown)
			$response = true;
		return $response;
	}

	private function getcomputedkitvalue($adding, $request) {
		$return_value = 0;
		if ($adding == 'opening')
			$return_value = $request->input('opening_balance') ?? 0;
		if ($adding == 'wasted')
			$return_value = $request->input('loss') ?? 0;
		if ($adding == 'issued_out')
			$return_value = $request->input('neg_adjust') ?? 0;
		if ($adding == 'issued_in')
			$return_value = $request->input('pos_adjust') ?? 0;
		if ($adding == 'closing')
			$return_value = $request->input('closing_balance') ?? 0;
		if ($adding == 'requested')
			$return_value = $request->input('qty_requested') ?? $request->input('gty_requested');
		if ($adding == 'qty_received')
			$return_value = $request->input('qty_received') ?? 0;
		return $return_value;
	}

	private function getMachine($request, &$testtype) {
		$commodity = strtolower($request->input('commodity_name'));
		$machine_type = '';
		$name = explode(" ", $commodity);
		foreach ($this->machine_check as $key => $machine_check) {
			if (in_array($machine_check, $name))
				$machine_type = $key;
			continue;
		}
		$request_testtype = '';

		if (in_array('qualitative', $name))
			$request_testtype = 'eid';
		else if (in_array('quantitative', $name))
			$request_testtype = 'vl';

		$testtype = collect($this->testtypes)->where('testtype', strtoupper($request_testtype));
		return $this->api_machines->where('machine', $machine_type)->flatten(2);
	}
}
?>
	

