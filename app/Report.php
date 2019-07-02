<?php

namespace App;

use DB;
use Exception;

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

	public static function eid_partner($partner_contact=null)
	{
		$partner_contacts = DB::table('eid_partner_contacts_for_alerts')
            ->when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 1)
            // ->where('lastalertsent', '!=', date('Y-m-d'))
            ->get();
        $email_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($partner_contacts as $key => $contact) {

	        $cc_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact as $column_name => $value) {
	        	if(str_contains($column_name, 'ccc') && str_contains($value, ['@']) && !str_contains($value, ['jbatuka'])) $cc_array[] = trim($value);
	        	if(str_contains($column_name, 'bcc') && str_contains($value, ['@']) && !str_contains($value, ['jbatuka'])) $bcc_array[] = trim($value);
	        }


	        if(env('APP_ENV') == 'production'){
		        try {
			        Mail::to(trim($contact->mainrecipientmail))->cc($cc_array)->bcc($bcc_array)->send(new EidPartnerPositives($contact->id));
			        DB::table('eid_partner_contacts_for_alerts')->where('id', $contact->id)->update(['lastalertsent' => date('Y-m-d')]);
		        } catch (Exception $e) {
		        	echo $e->getMessage();
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new EidPartnerPositives($contact->id));
		    }

		}
	}

	public static function eid_county($county_id=null)
	{
		$county_contacts = DB::table('eid_users')
            ->when($county_id, function($query) use ($county_id){
                return $query->where('partner', $county_id);
            })->where(['flag' => 1, 'account' => 7])->where('id', '>', 384)->get();
        $email_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($county_contacts as $key => $contact) {

	        $mail_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact as $column_name => $value) {
	        	if(str_contains($column_name, 'email') && str_contains($value, ['@']) && !str_contains($value, ['jbatuka'])) $mail_array[] = trim($value);
	        }

	        if(env('APP_ENV') == 'production'){
		        try {
			        DB::table('eid_users')->where('id', $contact->id)->update(['datelastsent' => date('Y-m-d')]);
			     	Mail::to($email_array)->bcc($bcc_array)->send(new EidCountyPositives($contact->id));
		        } catch (Exception $e) {
		        	
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
		$partner_contacts = DB::table('vl_partner_contacts_for_alerts')
            ->when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 2)->get();
        $email_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($partner_contacts as $key => $contact) {

	        $cc_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact as $column_name => $value) {
	        	if(str_contains($column_name, 'ccc') && str_contains($value, ['@']) && !str_contains($value, ['jbatuka'])) $cc_array[] = trim($value);
	        	if(str_contains($column_name, 'bcc') && str_contains($value, ['@']) && !str_contains($value, ['jbatuka'])) $bcc_array[] = trim($value);
	        }
	        if(env('APP_ENV') == 'production'){
		        try {
			        Mail::to(trim($contact->mainrecipientmail))->cc($cc_array)->bcc($bcc_array)->send(new VlPartnerNonsuppressed($contact->id));
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
		$county_contacts = DB::table('eid_users')
            ->when($county_id, function($query) use ($county_id){
                return $query->where('partner', $county_id);
            })->where(['flag' => 1, 'account' => 7])->where('id', '>', 384)->get();
        $email_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($county_contacts as $key => $contact) {

	        $mail_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact as $column_name => $value) {
	        	if(str_contains($column_name, 'email') && str_contains($value, ['@']) && !str_contains($value, ['jbatuka'])) $mail_array[] = trim($value);
	        }
	        if(env('APP_ENV') == 'production'){
		        try {
			        DB::table('eid_users')->where('id', $contact->id)->update(['datelastsent' => date('Y-m-d')]);
			     	Mail::to($mail_array)->bcc($bcc_array)->send(new VlCountyNonsuppressed($contact->id));
		        } catch (Exception $e) {
		        	
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
