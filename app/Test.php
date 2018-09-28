<?php

namespace App;

use JWTAuth;


class Test 
{

    public static function send_to_mlab()
    {
        ini_set('memory_limit', "-1");
        $min_date = date('Y-m-d', strtotime('-1 years'));
        $batches = \App\Viralbatch::join('facilitys', 'viralbatches.facility_id', '=', 'facilitys.id')
                ->select("viralbatches.*")
                ->with(['facility'])
                // ->where('sent_to_mlab', 0)
                ->where('smsprinter', 1)
                ->where('batch_complete', 1)
                ->where('datedispatched', '>', $min_date)
                ->get();

        foreach ($batches as $batch) {
            $samples = $batch->sample;

            foreach ($samples as $sample) {
                if($sample->repeatt == 1) continue;

                $client = new Client(['base_uri' => self::$mlab_url]);

                $post_data = [
                        'source' => '1',
                        'result_id' => "{$sample->id}",
                        'result_type' => '1',
                        'request_id' => '',
                        'client_id' => $sample->patient->patient,
                        'age' => $sample->my_string_format('age'),
                        'gender' => $sample->patient->gender,
                        'result_content' => $sample->my_string_format('result', 'No Result'),
                        'units' => $sample->units ?? '',
                        'mfl_code' => "{$batch->facility->facilitycode}",
                        'lab_id' => "{$batch->lab_id}",
                        'date_collected' => $sample->datecollected ?? '0000-00-00',
                        'cst' => $sample->my_string_format('sampletype'),
                        'cj' => $sample->my_string_format('justification'),
                        'csr' =>  "{$sample->rejectedreason}",
                        'lab_order_date' => $sample->datetested ?? '0000-00-00',
                    ];

                $response = $client->request('post', '', [
                    // 'debug' => true,
                    'http_errors' => false,
                    'json' => $post_data,
                ]);
                $body = json_decode($response->getBody());
                // print_r($body);
                if($response->getStatusCode() > 399){
                    print_r($post_data);
                    print_r($body);
                    return null;
                }
            }
            $batch->sent_to_mlab = 1;
            $batch->save();
            // break;
        }
    }
    
	public static function api_login($username, $password)
	{
		// $JWTAuth = new JWTAuth;
		$token = JWTAuth::attempt([
			'email' => $username,
			'password' => $password,
		]);
		return $token;
	}

}
