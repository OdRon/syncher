<?php

namespace App;

use App\Common;
use Carbon\Carbon;
use DB;
use Excel;

use App\Facility;

use GuzzleHttp\Client;

class Misc extends Common
{
	// public static $mlab_url = 'http://197.248.10.20:3001/api/results/results';
	public static $mlab_url = 'https://api.mhealthkenya.co.ke/api/vl_results';

    public static function getTotalHolidaysinMonth($month)
	{
		switch ($month) {
			case 0:
				$totalholidays=10;
				break;
			case 1:
				$totalholidays=1;
				break;
			case 4:
				$totalholidays=2;
				break;
			case 5:
				$totalholidays=1;
				break;
			case 6:
				$totalholidays=1;
				break;
			case 8:
				$totalholidays=1;
				break;
			case 10:
				$totalholidays=1;
				break;
			case 12:
				$totalholidays=3;
				break;
			default:
				$totalholidays=0;
				break;
		}
		return $totalholidays;

	}

	public static function getWorkingDays($startDate,$endDate){


	    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
	    //We add one to inlude both dates in the interval.
	    $days = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;

	    $no_full_weeks = floor($days / 7);

	    $no_remaining_days = fmod($days, 7);

	    //It will return 1 if it's Monday,.. ,7 for Sunday
	    $the_first_day_of_week = date("N",strtotime($startDate));

	    $the_last_day_of_week = date("N",strtotime($endDate));
	    // echo              $the_last_day_of_week;
	    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
	    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
	    if ($the_first_day_of_week <= $the_last_day_of_week){
	        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
	        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
	    }

	    else{
	        if ($the_first_day_of_week <= 6) {
	        //In the case when the interval falls in two weeks, there will be a Sunday for sure
	            $no_remaining_days--;
	        }
	    }

	    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
		//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
	   $workingDays = $no_full_weeks * 5;
	    if ($no_remaining_days > 0 )
	    {
	      $workingDays += $no_remaining_days;
	    }

	    //We subtract the holidays
		/*    foreach($holidays as $holiday){
	        $time_stamp=strtotime($holiday);
	        //If the holiday doesn't fall in weekend
	        if (strtotime($startDate) <= $time_stamp && $time_stamp <= strtotime($endDate) && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
	            $workingDays--;
	    }*/

	    return $workingDays;
	}

	public static function get_new_id($samples_array, $parent_id)
	{
		foreach ($samples_array as $key => $value) {
			if($parent_id == $value['original_id']) return $value['national_sample_id'];
		}
		return 1;
	}



    public static function sites_suppression(){
    	ini_set("memory_limit", "-1");

    	$sql = 'SELECT facility_id as facility, rcategory, count(*) as totals ';
		$sql .= 'FROM ';
		$sql .= '(SELECT v.id, v.facility_id, v.rcategory ';
		$sql .= 'FROM viralsamples_view v ';
		$sql .= 'RIGHT JOIN ';
		$sql .= '(SELECT id, patient_id, max(datetested) as maxdate ';
		$sql .= 'FROM viralsamples_view ';
		$sql .= 'WHERE year(datetested) = 2018 ';
		$sql .= "AND patient != '' AND patient != 'null' AND patient is not null ";
		$sql .= 'AND flag=1 AND repeatt=0 AND rcategory in (1, 2, 3, 4) ';
		$sql .= 'AND justification != 10 AND facility_id != 7148 ';
		$sql .= 'GROUP BY patient_id) gv ';
		$sql .= 'ON v.id=gv.id) tb ';
		$sql .= 'WHERE rcategory=1 ';
		$sql .= 'GROUP BY facility_id ';
		$sql .= 'ORDER BY facility_id ';

		$data = DB::select($sql);
		$data = collect($data);

		$rows = [];

		$file = public_path('downloads/sites.csv');

		$handle = fopen($file, "r");
        while (($value = fgetcsv($handle, 1000, ",")) !== FALSE)
        {
        	$fac = Facility::where(['facilitycode' => $value[1]])->first();
	        if(!$fac) continue;
        	$val = $data->where('facility', $fac->id)->first()->totals ?? 0;

        	$rows[] = [
        		'Facility Name' => $value[0],
        		'MFL Code' => $value[1],
        		'Total No Of Patients With Vl as LDL' => $val,
        	];
        }

        $filename = 'LDL';

        if(file_exists($filename)) unlink($filename);

        Excel::create($filename, function($excel) use($rows) {
            $excel->sheet('Sheetname', function($sheet) use($rows) {
                $sheet->fromArray($rows);
            });
        })->store('csv');

		return null;
    }

    public static function send_to_mlab_eid()
    {
    	ini_set('memory_limit', "-1");
        $min_date = date('Y-m-d', strtotime('-2 month'));
    	$batches = \App\Batch::join('facilitys', 'batches.facility_id', '=', 'facilitys.id')
    			->select("batches.*")
    			->with(['facility'])
    			->where('lab_id', 4)
    			->where('sent_to_mlab', 0)
    			->where('smsprinter', 1)
    			->where('batch_complete', 1)
				->where('datedispatched', '>', $min_date)
    			->get();

    	foreach ($batches as $batch) {
    		$samples = $batch->sample;

    		foreach ($samples as $sample) {
    			if($sample->repeatt == 1) continue;

    			$client = new Client(['base_uri' => self::$mlab_url]);

                if(!$sample->patient->patient || $sample->patient->patient == '' ) $sample->patient->patient = "null";

    			$post_data = [
						'source' => '1',
						'result_id' => "{$sample->id}",
						'result_type' => '2',
						'request_id' => '',
						'client_id' => $sample->patient->patient,
						'age' => $sample->my_string_format('age'),
						'gender' => $sample->patient->gender,
						'result_content' => $sample->my_string_format('result'),
						'units' => '0',
						'mfl_code' => "{$batch->facility->facilitycode}",
						'lab_id' => "{$batch->lab_id}",
						'date_collected' => $sample->datecollected ?? '0000-00-00',
						'cst' => '0',
						'cj' => '0',
						'csr' => "{$sample->rejectedreason}",
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
					// print_r(json_decode($sample->toJson()));
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

    public static function send_to_mlab_vl()
    {
        ini_set('memory_limit', "-1");
        $min_date = date('Y-m-d', strtotime('-1 month'));
        $batches = \App\Viralbatch::join('facilitys', 'viralbatches.facility_id', '=', 'facilitys.id')
                ->select("viralbatches.*")
                ->with(['facility'])
                ->where('sent_to_mlab', 0)
    			->where('lab_id', 4)
                ->where('smsprinter', 1)
                ->where('batch_complete', 1)
                ->where('datedispatched', '>', $min_date)
                ->get();

        foreach ($batches as $batch) {
            $samples = $batch->sample;

            foreach ($samples as $sample) {
                if($sample->repeatt == 1) continue;

                $client = new Client(['base_uri' => self::$mlab_url]);

                if(!$sample->patient->patient || $sample->patient->patient == '' ) $sample->patient->patient = "null";

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


    public static function delete_folder($path)
    {
        if(!ends_with($path, '/')) $path .= '/';
        $files = scandir($path);
        if(!$files) rmdir($path);
        else{
            foreach ($files as $file) {
            	if($file == '.' || $file == '..') continue;
            	$a=true;
                if(is_dir($path . $file)) self::delete_folder($path . $file);
                else{
                	unlink($path . $file);
                }              
            }
            rmdir($path);
        }
    }


}
