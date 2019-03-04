<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\BlankRequest;
use GuzzleHttp\Client;


use App\Lab;
use App\Synch;

class TransferController extends BaseController
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transfer(BlankRequest $request)
    {
        $lab = Lab::find($request->input('to_lab'));
        $type = $request->input('type');

        $pre = '';
        if($type == 'vl') $pre = 'viral';
        $url = $pre . 'sample/transfer';
        
        $client = new Client(['base_uri' => $lab->base_url]);
        $response = $client->request('post', $url, [
            'http_errors' => false,
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . Synch::get_token($lab),
            ],
            'json' => [
                'samples' => $request->input('samples'),
            ],
        ]);

        $code = $response->getStatusCode();
        $body = json_decode($response->getBody());

        if($code > 399){
            // dd($body);
            return response()->json(get_object_vars($body), $response->getStatusCode());
        }
        

        $data = [
            'ok' => $body->ok ?? null,
            'samples' => $body->samples ?? null,
            'batches' => $body->batches ?? null,
            'patients' => $body->patients ?? null,
            'others' => $body->others ?? null,
        ];

        if($type == 'eid') $data ['mothers'] = $body->mothers ?? null;

        return response()->json($data, $response->getStatusCode());
    }
}

