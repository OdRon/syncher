<?php

namespace App;

use DB;

use Illuminate\Support\Facades\Mail;

use App\Mail\EidPartnerPositives;
use App\Mail\EidCountyPositives;
use App\Mail\VlPartnerNonsuppressed;
use App\Mail\VlCountyNonsuppressed;

class Report
{

	public static function eid_partner($partner_contact=null)
	{
		$partner_contacts = DB::table('eid_partner_contacts_for_alerts')
            ->when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 1)->get();

		foreach ($partner_contacts as $key => $contact) {
	        $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');

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

	        Mail::to($contact->mainrecipientmail)->cc($cc_array)->bcc($bcc_array)->send(new EidPartnerPositives($contact->id));
	        DB::table('eid_partner_contacts_for_alerts')->where('id', $contact->id)->update(['lastalertsent' => date('Y-m-d')]);
		}


	}


}
