<?php

namespace App\Imports;

use DB;
use \App\PartnerFacility;
use \App\Facility;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FacilityImport implements OnEachRow, WithHeadingRow
{
	protected $partner_id;
	protected $start_date;
	protected $end_date;

	public function __construct($partner_id, $start_date=null)
	{
		$this->partner_id = $partner_id;
		if(!$start_date){
			$d = date('d') - 1;
			$start_date = date('Y-m-d', strtotime("-{$d} days"));
		}
		$this->start_date = $start_date;
		$this->end_date = date('Y-m-d', strtotime($start_date . ' -1 day'));
	}

    public function onRow(Row $row)
    {
    	// $row = json_decode(json_encode($row));
    	dd($row);
		if(!is_numeric($row->mfl_code) || (is_numeric($row->mfl_code) && $row->mfl_code < 10000)) return;	

		$fac = Facility::where('facilitycode', $row->mfl_code)->first();
		if($fac){
			$pf = PartnerFacility::where(['facility_id' => $fac->id])->whereNull('end_date')->where('partner_id', '!=', $this->partner_id)->first();
			if($pf){
				$pf->end_date = $this->end_date;
				$pf->save();
			}

			$pf = PartnerFacility::where(['facility_id' => $fac->id])->whereNull('end_date')->where('partner_id', '!=', $this->partner_id)->first();

			if(!$pf){
				$pf = PartnerFacility::create([
					'facility_id' => $fac->id,
					'partner_id' => $this->partner_id,
					'start_date' => $this->start_date,
				]);
			}

			$fac->partner = $this->partner_id;
			$fac->save();
			DB::table('apidb.facilitys')->where('id', $fac->id)->update(['partner' => $this->partner_id]);
		}
		DB::table('hcm.facilitys')->where('facilitycode', $row->mfl_code)->update(['partner' => $this->partner_id]);
    }
}
