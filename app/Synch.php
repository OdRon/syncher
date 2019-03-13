<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use DB;
use Exception;

use App\Sample;
use App\Batch;
use App\Patient;
use App\Mother;
use App\Worksheet;

use App\Viralsample;
use App\Viralbatch;
use App\Viralpatient;
use App\Viralworksheet;

use App\Facility;
use App\Lab;

/*
	This is for the synching of updates down to the lab
*/

class Synch
{

	public static $synch_arrays = [
		'eid' => [
			'misc_class' => \App\Misc::class,
			'sample_class' => Sample::class,
			'sampleview_class' => \App\SampleView::class,
			'batch_class' => Batch::class,
			'worksheet_class' => Worksheet::class,
			'patient_class' => Patient::class,
			'view_table' => 'samples_view',
			'worksheets_table' => 'worksheets',
		],

		'vl' => [
			'misc_class' => \App\MiscViral::class,
			'sample_class' => Viralsample::class,
			'sampleview_class' => \App\ViralsampleView::class,
			'batch_class' => Viralbatch::class,
			'worksheet_class' => Viralworksheet::class,
			'patient_class' => Viralpatient::class,
			'view_table' => 'viralsamples_view',
			'worksheets_table' => 'viralworksheets',
		],
	];



	public static function login($lab)
	{
		Cache::forget($lab->token_name);
		$client = new Client(['base_uri' => $lab->base_url]);
		// dd($lab->base_url);
		try {
			$response = $client->request('post', 'auth/login', [
	            'http_errors' => false,
	            'connect_timeout' => 1.5,
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'email' => env('LAB_USERNAME', null),
					'password' => env('LAB_PASSWORD', null),
				],
			]);
			$status_code = $response->getStatusCode();
			// if($status_code > 399) die();
			$body = json_decode($response->getBody());
			Cache::put($lab->token_name, $body->token, 60);	
			// echo $lab->token_name . " is {$body->token} \n";		
		} catch (Exception $e) {
			Cache::put($lab->token_name, 'null', 60);	
			// echo $lab->token_name . " is {$e->getMessage()}. \n";			
		}
	}

	public static function get_token($lab)
	{
		if(Cache::has($lab->token_name)){}
		else{
			self::login($lab);
		}
		// dd($lab);
		return Cache::get($lab->token_name);
	}


	public static function synch_batches($type)
	{
        ini_set("memory_limit", "-1");
		$classes = self::$synch_arrays[$type];

		$batch_class = $classes['batch_class'];

		if($type == "eid"){
			$url = 'update/batch/';
		}else{
			$url = 'update/viralbatch/';
		}

		$data = ['synched' => 1, 'datesynched' => date('Y-m-d')];

		$batches = $batch_class::with(['lab'])->where('synched', 2)->where('site_entry', '!=', 2)->limit(50)->get();

		foreach ($batches as $batch) {
			$lab = $batch->lab;
			unset($batch->lab);
			self::send_update($batch, $lab);
		}

		$labs = Lab::all();
		$batches = $batch_class::where('synched', 2)->where('site_entry', 2)->get();

		foreach ($batches as $batch) {
			foreach ($labs as $lab) {
				if(self::send_update($batch, $lab)) break;
			}
		}
	}


	public static function synch_samples($type)
	{
        ini_set("memory_limit", "-1");
		$classes = self::$synch_arrays[$type];

		$sample_class = $classes['sample_class'];
		$sampleview_class = $classes['sampleview_class'];

		\App\Common::save_tat($sampleview_class, $sample_class);

		$data = ['synched' => 1, 'datesynched' => date('Y-m-d')];

		$samples = $sampleview_class::with(['lab'])->where('synched', 2)->where('site_entry', '!=', 2)->get();

		foreach ($samples as $s) {
			$sample = $sample_class::find($s->id);
			self::send_update($sample, $s->lab);
		}

		$labs = Lab::all();
		$samples = $sampleview_class::where('synched', 2)->where('site_entry', 2)->get();

		foreach ($samples as $samples) {
			foreach ($labs as $lab) {
				if(self::send_update($sample, $lab)) break;
			}
		}
	}


	public static function synch_patients($type)
	{
        ini_set("memory_limit", "-1");
		$classes = self::$synch_arrays[$type];

		$patient_class = $classes['patient_class'];
		$sampleview_class = $classes['sampleview_class'];
		$labs = Lab::all();

		$data = ['synched' => 1, 'datesynched' => date('Y-m-d')];

		$patients = $patient_class::where('synched', 2)->get();

		foreach ($patients as $patient) {
			$sample = $sampleview_class::where(['synched' => 2, 'patient_id' => $patient->id])->where('site_entry', '!=', 2)->first();

			if(!$sample){
				foreach ($labs as $lab) {
					if(self::send_update($patient, $lab)) break;
				}
			}
			else{
				$lab = $labs->where('id', $sample->lab_id)->first();
				self::send_update($patient, $lab);
			}
		}
	}

	public static function synch_allocations() {
		$allocations = Allocation::with(['details' => function($query){
									$query->where('synched', 2);
								},'details.breakdowns' => function($query){
									$query->where('synched', 2);
								}])->where('synched', '=', 2)->get();
		$labs = Lab::all();
		foreach ($allocations as $key => $model) {
			$lab = $labs->where('id', $model->lab_id)->first();
			// $synch_data = self::send_update($model, $lab);
			if (strpos(url()->current(), "lab-2.test"))
				$lab->base_url = "http://lab.test.nascop.org/api";
			$client = new Client(['base_uri' => $lab->base_url]);
			dd($lab);
			dd(self::get_token($lab));
			dd($lab->base_url);
			$response = $client->request('put', 'allocation', [
				'http_errors' => false,
				'verify' => false,
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token($lab),
				],
				'json' => [
					'allocation' => $model->toJson(),
				],
			]);
			$body = json_decode($response->getBody());
			$data = ['synched' => 1, 'datesynched' => date('Y-m-d')];
			if($response->getStatusCode() < 400) {
				$model->fill($data);
				$model->save();
				foreach($model->details as $details) {
					$details->fill($data);
					$details->save();
					foreach ($details->breakdowns as $breakdown) {
						$breakdown->fill($data);
						$breakdown->save();
					}
				}
				return true;
			} else{
				print_r($body);
				// return false;
			}
		}
	}

	private static function send_update($model, $lab, $site_entry=false)
	{
		$data = ['synched' => 1, 'datesynched' => date('Y-m-d')];

		$class = get_class($model);
		$col = 'original_';
		if(str_contains($class, 'sample')) $param = 'sample';
		if(str_contains($class, 'patient')) $param = 'patient';
		if(str_contains($class, 'batch')) $param = 'batch';
		if(str_contains($class, 'Allocation')) $param = 'allocation';
		$col .= $param . '_id';
		
		$url = str_replace('App\\', '', $class);
		$url = strtolower($url) . '/' . $model->$col;
		
		if (strpos(url()->current(), "lab-2.test"))
			$lab->base_url = "http://lab.test.nascop.org/api";
		$client = new Client(['base_uri' => $lab->base_url]);
		// dd(self::get_token($lab));
		$response = $client->request('put', $url, [
			'http_errors' => false,
			'verify' => false,
			'headers' => [
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . self::get_token($lab),
			],
			'json' => [
				$param => $model->toJson(),
				'site_entry' => $site_entry,
			],
		]);
		
		$body = json_decode($response->getBody());
		
		if($response->getStatusCode() < 400)
		{
			$model->fill($data);
			$model->save();
			return true;
		}
		else{
			print_r($body);
			return false;
		}
	}

	public static function correct_no_patient($type)
	{
        ini_set("memory_limit", "-1");
		$classes = self::$synch_arrays[$type];

		$sample_class = $classes['sample_class'];

		$base = str_replace('App\\', '', $sample_class);
		$base = strtolower($base) . '/';

		$data = ['synched' => 1, 'datesynched' => date('Y-m-d')];

		$samples = $sample_class::where('patient_id', 0)->with(['batch.lab'])->get();

		foreach ($samples as $key => $sample) {
			if(!$sample->batch ||  $sample->batch->site_entry == 2) continue;
			$client = new Client(['base_uri' => $sample->batch->lab->base_url]);
			$url = $base . $sample->original_sample_id;

			try {
				$response = $client->request('get', $url, [
					'headers' => [
						'Accept' => 'application/json',
						'Authorization' => 'Bearer ' . self::get_token($sample->batch->lab),
					],
		            'connect_timeout' => 1.5,
					'http_errors' => false,
					'verify' => false,
				]);

				$body = json_decode($response->getBody());

				if($response->getStatusCode() < 400)
				{				
					$sample->patient_id = $body->patient->national_patient_id;
					$sample->save();
				}
				
			} catch (Exception $e) {
				
			}
		}

	}


	public static function correct_no_batch($type)
	{
        ini_set("memory_limit", "-1");
		$classes = self::$synch_arrays[$type];

		$sample_class = $classes['sample_class'];

		$labs = Lab::all();
		$base = str_replace('App\\', '', $sample_class);
		$base = strtolower($base) . '/';

		$data = ['synched' => 1, 'datesynched' => date('Y-m-d')];

		$samples = $sample_class::where('batch_id', 0)->where('datecollected', '>', '2017-01-01')->get();

		foreach ($samples as $key => $sample) {
			
			$url = $base . $sample->original_sample_id;

			foreach ($labs as $lab) {

				$client = new Client(['base_uri' => $lab->base_url]);

				try {
					$response = $client->request('get', $url, [
						'headers' => [
							'Accept' => 'application/json',
							'Authorization' => 'Bearer ' . self::get_token($lab),
						],
			            'connect_timeout' => 1.5,
						'http_errors' => false,
						'verify' => false,
					]);

					$body = json_decode($response->getBody());

					if($response->getStatusCode() < 400)
					{				
						if($sample->datecollected == $body->datecollected)
						{
							$sample->batch_id = $body->batch->national_batch_id;
							$sample->save();
						}
					}
					
				} catch (Exception $e) {
					
				}
			}
		}
	}

	public static function correct_no_gender($type)
	{
        ini_set("memory_limit", "-1");
		$classes = self::$synch_arrays[$type];

		$sampleview_class = $classes['sampleview_class'];
		$sample_class = $classes['sample_class'];
		$patient_class = $classes['patient_class'];

		$labs = Lab::all();
		$base = str_replace('App\\', '', $sample_class);
		$base = strtolower($base) . '/';

		$samples = $sampleview_class::where(['sex' => 3])->where('datecollected', '>', '2017-01-01')->where('site_entry', '!=', 2)->get();

		foreach ($samples as $key => $sample) {
			
			$url = $base . $sample->original_sample_id;
			$lab = $labs->where('id', $sample->lab_id)->first();
			$client = new Client(['base_uri' => $lab->base_url]);

			try {
				$response = $client->request('get', $url, [
					'headers' => [
						'Accept' => 'application/json',
						'Authorization' => 'Bearer ' . self::get_token($lab),
					],
		            'connect_timeout' => 1.5,
					'http_errors' => false,
					'verify' => false,
				]);

				$body = json_decode($response->getBody());

				// dd($body);

				if($response->getStatusCode() < 400)
				{		
					$patient = $patient_class::find($sample->patient_id);		
					if($patient->id == $body->patient->national_patient_id)
					{
						$patient->sex = $body->patient->sex;
						$patient->save();
					}
				}				
			} catch (Exception $e) {
				
			}
		}
	}


	public static function test_connection() {
		$labs = Lab::all();

		foreach ($labs as $lab) {
			try {
				$client = new Client(['base_uri' => $lab->base_url]);
				$response = $client->request('get', 'hello', [
					'headers' => [
						'Accept' => 'application/json',
					],
					// 'debug' => true,
					'http_errors' => false,
					// 'verify' => false,
				]);
				$body = json_decode($response->getBody());
				echo $lab->name . ' '. $body->message . "\n";
				
			} catch (Exception $e) {
				echo $lab->name . ' has error ' . $e->getMessage() . "\n";
			}
		}
	}

	public static function logins()
	{
		$labs = Lab::all();

		foreach ($labs as $lab) {
			self::login($lab);
		}
	}
}
