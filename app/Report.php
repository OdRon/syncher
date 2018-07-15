<?php

namespace App;

use DB;

use Illuminate\Support\Facades\Mail;

use App\Mail\TestMail;

use App\Mail\EidPartnerPositives;
use App\Mail\EidCountyPositives;
use App\Mail\VlPartnerNonsuppressed;
use App\Mail\VlCountyNonsuppressed;

class Report
{


    public static function test_email()
    {
        Mail::to(['joelkith@gmail.com'])->send(new TestMail());
    }

	public static function eid_partner($partner_contact=null)
	{
		$partner_contacts = DB::table('eid_partner_contacts_for_alerts')
            ->when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 1)->get();
        $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

		foreach ($partner_contacts as $key => $contact) {

	        $cc_array = [];
	        $bcc_array = [];

	        foreach ($contact as $column_name => $value) {
	        	$find = strpos($column_name, 'ccc');
	        	if($find && $value) $cc_array[] = $value;
	        }

	        foreach ($contact as $column_name => $value) {
	        	$find = strpos($column_name, 'bcc');
	        	if($find && $value) $bcc_array[] = $value;
	        }

	        // Mail::to($contact->mainrecipientmail)->cc($cc_array)->bcc($bcc_array)->send(new EidPartnerPositives($contact->id));
	        // DB::table('eid_partner_contacts_for_alerts')->where('id', $contact->id)->update(['lastalertsent' => date('Y-m-d')]);
	     	Mail::to($mail_array)->send(new EidPartnerPositives($contact->id));
		}


	}


}
