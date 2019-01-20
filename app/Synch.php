<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use DB;

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

		$response = $client->request('post', 'auth/login', [
            'http_errors' => false,
			'headers' => [
				'Accept' => 'application/json',
			],
			'json' => [
				'email' => env('LAB_USERNAME', null),
				'password' => env('LAB_PASSWORD', null),
			],
		]);
		$status_code = $response->getStatusCode();
		if($status_code > 399) die();
		$body = json_decode($response->getBody());
		Cache::put($lab->token_name, $body->token, 60);
	}

	public static function get_token($lab)
	{
		if(Cache::has($lab->token_name)){}
		else{
			self::login($lab);
		}
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

		while(true)
		{
			$batches = $batch_class::with(['lab'])->where('synched', 2)->where('site_entry', '!=', 2)->limit(50)->get();
			if($batches->isEmpty()) break;

			foreach ($batches as $batch) {
				$client = new Client(['base_uri' => $batch->lab->base_url]);

				$response = $client->request('post', $url . $batch->original_batch_id, [
				'http_errors' => false,
				'verify' => false,
					'headers' => [
						'Accept' => 'application/json',
						'Authorization' => 'Bearer ' . self::get_token($batch->lab),
					],
					'json' => [
						'batch' => $batch->toJson(),
					],
				]);

				$body = json_decode($response->getBody());
				if($response->getStatusCode() < 400)
				{
					$batch->fill($data);
					$batch->save();
				}
			}
		}

		$labs = Lab::all();
		$batches = $batch_class::where('synched', 2)->where('site_entry', 2)->get();

		foreach ($batches as $batch) {
			foreach ($labs as $lab) {
				$client = new Client(['base_uri' => $lab->base_url]);

				$response = $client->request('post', $url . $batch->original_batch_id, [
				'http_errors' => false,
				'verify' => false,
					'headers' => [
						'Accept' => 'application/json',
						'Authorization' => 'Bearer ' . self::get_token($lab),
					],
					'json' => [
						'batch' => $batch->toJson(),
					],
				]);
				$body = json_decode($response->getBody());
				if($response->getStatusCode() < 400)
				{
					$batch->fill($data);
					$batch->save();
				}
			}
		}
	}


	public static function synch_samples($type)
	{
        ini_set("memory_limit", "-1");
		$classes = self::$synch_arrays[$type];

		$sample_class = $classes['sample_class'];
		$sampleview_class = $classes['sampleview_class'];

		if($type == "eid"){
			$url = 'update/sample/';
		}else{
			$url = 'update/viralsample/';
		}

		$data = ['synched' => 1, 'datesynched' => date('Y-m-d')];

		while(true)
		{
			$samples = $sampleview_class::with(['lab'])->where('synched', 2)->where('site_entry', '!=', 2)->limit(50)->get();
			if($samples->isEmpty()) break;

			foreach ($samples as $s) {
				$sample = $sample_class::find($s->id);

				$client = new Client(['base_uri' => $s->lab->base_url]);

				$response = $client->request('post', $url . $sample->original_sample_id, [
				'http_errors' => false,
				'verify' => false,
					'headers' => [
						'Accept' => 'application/json',
						'Authorization' => 'Bearer ' . self::get_token($s->lab),
					],
					'json' => [
						'sample' => $sample->toJson(),
					],
				]);

				$body = json_decode($response->getBody());
				if($response->getStatusCode() < 400)
				{
					$sample->fill($data);
					$sample->save();
				}
			}
		}

		



	}

}
