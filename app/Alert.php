<?php

namespace App;

use Mpdf\Mpdf;

use App\Lookup;

use App\SampleAlertView;
use App\ViralsampleAlertView;


class Alert
{

	public static $time_period = '1';


	public static function eid_partner_alerts()
	{
		$partners = EidPartnerContacts::where('active', 1)->get();

		foreach ($partners as $partner) {

			$samples = SampleAlertView::where('eqa', 0)
						->where('enrollment_status', 0)
						->whereIn('pcrtype', [1, 2, 3])
						->where('repeatt', 0)
						->whereYear('datetested', date('Y'))
						->where('result', 2)
						->where('partner_id', $partner->id)
						->orderBy('facility_id', 'asc')
						->orderBy('facility_id', 'asc')
						->get();


		}
	}
}
