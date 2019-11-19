<?php

namespace App;

use DB;
use Exception;
use GuzzleHttp\Client;

use Illuminate\Support\Facades\Mail;

use App\Mail\TestMail;

use App\Mail\EidPartnerPositives;
use App\Mail\EidCountyPositives;
use App\Mail\VlPartnerNonsuppressed;
use App\Mail\VlCountyNonsuppressed;
use App\Mail\PasswordEmail;

class Report
{

	public static $email_array = ['joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com'];


    public static function test_email()
    {
        Mail::to(['baksajoshua09@gmail.com', 'joelkith@gmail.com'])->send(new TestMail());
    }

	public static function clean_emails($base = 'https://api.mailgun.net/v3/nascop.or.ke/complaints', $iter=0)
	{
		// $base = 'https://api.mailgun.net/v3/nascop.or.ke/complaints';
		$client = new Client(['base_uri' => $base]);
		$response = $client->request('get', '', [
			'auth' => ['api', env('MAIL_API_KEY')],
		]);
		$body = json_decode($response->getBody());
		if($response->getStatusCode() > 399) return false;
		// dd($body);

		// $emails = [];

		foreach ($body->items as $key => $value) {
			// $emails[] = $value->address;
			BlockedEmail::firstOrCreate(['email' => $value->address]);
		}
		// if($iter == 1) dd($body);

		if($iter > 30) die();
		self::clean_emails($body->paging->next, $iter++);
	}

	public static function eid_partner($partner_contact=null)
	{
		$partner_contacts = EidPartner::when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 1)
            ->get();
        $email_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($partner_contacts as $key => $contact) {

			echo "Eid Partner contact {$contact->id} \n";

	        $cc_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact as $column_name => $value) {
	        	$value = trim($value);

	        	// Check if email address is blocked
	        	if(str_contains($column_name, ['ccc', 'bcc', 'mainrecipientmail'])){
	        		$b = BlockedEmail::where('email', $value)->first();
	        		if($b){
	        			$contact->$column_name=null;
	        			$contact->save();
	        			echo "\t\t Removed blocked email {$value} \n";
	        			continue;
	        		}
	        	}
	        	$myvar = null;


	        	if(str_contains($column_name, ['ccc', 'mainrecipientmail']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !str_contains($value, ['jbatuka'])){
	        		$cc_array[] = $value;
        			echo "\t\t Added CCC {$value} \n";	        		
	        	}
	        	else if(str_contains($column_name, 'ccc') && !filter_var($value, FILTER_VALIDATE_EMAIL)){
		        	echo "\t\t Email {$column_name} {$value} is invalid \n";	        		
	        	}
	        	else{}
	        	if(str_contains($column_name, 'bcc') && filter_var($value, FILTER_VALIDATE_EMAIL) && !str_contains($value, ['jbatuka'])) $bcc_array[] = $value;
	        	else if(str_contains($column_name, 'bcc') && !filter_var($value, FILTER_VALIDATE_EMAIL)){
		        	echo "\t\t Email {$column_name} {$value} is invalid \n";	        		
	        	}
	        	else{}
	        }
	    	$cc = json_encode($cc_array);
	    	$bcc = json_encode($bcc_array);
	    	echo "\t\t CCC Array {$cc} \n";
	    	echo "\t\t BCC Array {$bcc} \n";
	    	die();



	        if(env('APP_ENV') == 'production'){
		        try {
			        // Mail::to($cc_array)->bcc($bcc_array)->send(new EidPartnerPositives($contact->id));
			        DB::table('eid_partner_contacts_for_alerts')->where('id', $contact->id)->update(['lastalertsent' => date('Y-m-d')]);
		        } catch (Exception $e) {
		        	echo $e->getMessage();
		        }
		    }
		    else{
		    	// Mail::to(self::$email_array)->send(new EidPartnerPositives($contact->id));
		    }

		}
	}

	public static function eid_county($county_id=null)
	{
		$county_contacts = EidUser::when($county_id, function($query) use ($county_id){
                return $query->where('partner', $county_id);
            })->where(['flag' => 1, 'account' => 7])->where('id', '>', 384)->get();
        $email_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($county_contacts as $key => $contact) {

	        $mail_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact as $column_name => $value) {
	        	$value = trim($value);

	        	// Check if email address is blocked
	        	if(str_contains($column_name, ['email'])){
	        		$b = BlockedEmail::where('email', $value)->first();
	        		if($b){
	        			$contact->$column_name=null;
	        			$contact->save();
	        			echo "Removed blocked email {$value} \n";
	        			continue;
	        		}
	        	}

	        	if(str_contains($column_name, 'email') && filter_var($value, FILTER_VALIDATE_EMAIL) && !str_contains($value, ['jbatuka'])) $mail_array[] = trim($value);
	        }

	        if(env('APP_ENV') == 'production'){
		        try {
			        DB::table('eid_users')->where('id', $contact->id)->update(['datelastsent' => date('Y-m-d')]);
			     	Mail::to($mail_array)->bcc($bcc_array)->send(new EidCountyPositives($contact->id));
		        } catch (Exception $e) {
		        	echo $e->getMessage();		        	
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new EidCountyPositives($contact->id));
		    }
		}
	}

	// public static function send_password()
	// {
	// 	$users = \App\User::where('user_type_id', '<>', 8)->where('user_type_id', '<>', 2)->where('user_type_id', '<>', 10)->whereNull('deleted_at')->whereRaw("email like '%@%'")->whereRaw("email not like '%example%'")->whereNull('email_sent')->get();
		
	// 	foreach ($users as $key => $value) {
	// 		$user = \App\User::find($value->id);
	// 		Mail::to($value->email)->send(new PasswordEmail($value->id));
	// 		if( count(Mail::failures()) > 0 ) {
	// 		   echo "==>There was one or more failures. They were: <br />";
	// 		   foreach(Mail::failures() as $email_address) {
	// 		   		$user->email_sent = NULL;
	// 		   		$user->save();
	// 		       	echo " - $email_address <br />";
	// 		    }
	// 		} else {
	// 		    echo "==> No errors, all sent successfully!</br>";
	// 		    $user->email_sent = date('Y-m-d H:i:s');
	// 	   		$user->save();
	// 		}
	// 	}
	// }


	public static function vl_partner($partner_contact=null)
	{
		$partner_contacts = VlPartner::when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 2)->get();
        $email_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($partner_contacts as $key => $contact) {

	        $cc_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];
	        $mainrecipientmail = trim($contact->mainrecipientmail);

	        if(in_array($mainrecipientmail, ['', null]) || !filter_var($mainrecipientmail, FILTER_VALIDATE_EMAIL)) continue;

	        foreach ($contact as $column_name => $value) {
	        	$value = trim($value);

	        	// Check if email address is blocked
	        	if(str_contains($column_name, ['ccc', 'bcc', 'mainrecipientmail'])){
	        		$b = BlockedEmail::where('email', $value)->first();
	        		if($b){
	        			$contact->$column_name=null;
	        			$contact->save();
	        			echo "Removed blocked email {$value} \n";
	        			continue;
	        		}
	        	}

	        	if(str_contains($column_name, 'ccc') && filter_var($value, FILTER_VALIDATE_EMAIL) && !str_contains($value, ['jbatuka'])) $cc_array[] = trim($value);
	        	if(str_contains($column_name, 'bcc') && filter_var($value, FILTER_VALIDATE_EMAIL) && !str_contains($value, ['jbatuka'])) $bcc_array[] = trim($value);
	        }
	        if(env('APP_ENV') == 'production'){
		        try {
			        Mail::to($mainrecipientmail)->cc($cc_array)->bcc($bcc_array)->send(new VlPartnerNonsuppressed($contact->id));
			        DB::table('vl_partner_contacts_for_alerts')->where('id', $contact->id)->update(['lastalertsent' => date('Y-m-d')]);
		        } catch (Exception $e) {
		        	
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new VlPartnerNonsuppressed($contact->id));
		    }
		}
	}

	public static function vl_county($county_id=null)
	{
		$county_contacts = EidUser::when($county_id, function($query) use ($county_id){
                return $query->where('partner', $county_id);
            })->where(['flag' => 1, 'account' => 7])->where('id', '>', 384)->get();
        $email_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($county_contacts as $key => $contact) {

	        $mail_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact as $column_name => $value) {
	        	$value = trim($value);

	        	// Check if email address is blocked
	        	if(str_contains($column_name, ['email'])){
	        		$b = BlockedEmail::where('email', $value)->first();
	        		if($b){
	        			$contact->$column_name=null;
	        			$contact->save();
	        			echo "Removed blocked email {$value} \n";
	        			continue;
	        		}
	        	}

	        	if(str_contains($column_name, 'email') && filter_var($value, FILTER_VALIDATE_EMAIL) && !str_contains($value, ['jbatuka'])) $mail_array[] = trim($value);
	        }
	        if(env('APP_ENV') == 'production'){
		        try {
			        DB::table('eid_users')->where('id', $contact->id)->update(['datelastsent' => date('Y-m-d')]);
			     	Mail::to($mail_array)->bcc($bcc_array)->send(new VlCountyNonsuppressed($contact->id));
		        } catch (Exception $e) {
		        	echo $e->getMessage();			        	
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new VlCountyNonsuppressed($contact->id));
		    }
		}
	}

    public static function send_communication()
    {
        $emails = \App\Email::where('sent', false)->where('time_to_be_sent', '<', date('Y-m-d H:i:s'))->get();

        foreach ($emails as $email) {
        	$email->dispatch();
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


	public static function test()
	{
        $totals = \App\SampleAlertView::selectRaw("facility_id, enrollment_status, facilitycode, facility, county, subcounty, partner, count(distinct patient_id) as total")
            ->whereIn('pcrtype', [1, 2, 3])
            ->where(['result' => 2, 'repeatt' => 0, 'county_id' => 1])
            ->whereYear('datetested', date('Y'))
            ->groupBy('facility_id', 'enrollment_status')
            ->orderBy('facility_id')
            ->get();

        return $totals;
	}


}
