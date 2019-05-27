<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use DB;
use Exception;

class Poc
{

	public static function alereq()
	{
		$client = new Client(['base_uri' => 'https://datapoint.alere.com/api/v1/measurements/alereq']);
		$response = $client->request('get', '', [
			'debug' => true,
			'auth' => [
				env('ALERE_USERNAME'), env('ALERE_PASSWORD')
			],
			'query' => [
				'StartDate' => date('Y-m-d', strtotime('-1 year')),
				'pagesize' => 1
			],
		]);
		$body = json_decode($response->getBody());
		dd($body);
	}
}
