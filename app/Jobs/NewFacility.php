<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use GuzzleHttp\Client;

use App\Facility;
use App\Lab;


class NewFacility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $facility;
    protected $is_update;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Facility $facility, $is_update=false)
    {
        $this->facility = $facility;
        $this->is_update = $is_update;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $labs = Lab::all();
        foreach ($labs as $lab) {
            if(!$lab->base_url) continue;
            $client = new Client(['base_uri' => $lab->base_url]);

            if($is_update){
                $type = 'put';
                $url = 'facility/' . $this->facility->id;
            }
            else{
                $type = 'post';
                $url = 'facility';
            }

            $response = $client->request($type, $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'facility' => $this->facility->toJson(),
                ],
            ]);
        }
    }
}
