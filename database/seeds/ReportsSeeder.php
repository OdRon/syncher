<?php

use Illuminate\Database\Seeder;

class ReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $report_categories = [
        // 	['name' => 'Samples Reports', 'code_range' => '01-20', 'description' => 'Range for the samples reports'],
        // 	['name' => 'Patients Reports', 'code_range' => '21-40', 'description' => 'Range for the patients reports'],
        // 	['name' => 'Sites Reports', 'code_range' => '41-60', 'description' => 'Range for the sites reports'],
        // 	['name' => 'Labs Reports', 'code_range' => '61-80', 'description' => 'Range for the Labs reports']
        // ];
        // foreach ($report_categories as $key => $value) {
        // 	\App\ReportCategory::create($value);
        // }

        $partner_reports = [
        	// EID samples
        	['name' => 'All Outcomes', 'code' => 1, 'testtype' => 1],
        	['name' => 'Positive Outcomes', 'code' => 2, 'testtype' => 1],
        	['name' => 'Negative Outcomes', 'code' => 3, 'testtype' => 1],
        	['name' => 'Rejected Samples', 'code' => 4, 'testtype' => 1],
        	['name' => 'Site Entry Samples', 'code' => 5, 'testtype' => 1],
        	// Vl samples
        	['name' => 'Detailed Outcomes', 'code' => 11, 'testtype' => 2],
        	['name' => 'Rejected Samples', 'code' => 12, 'testtype' => 2],
        	['name' => 'Non-suppressed (> 1000 cp/ml)', 'code' => 13, 'testtype' => 2],
        	['name' => 'Site Entry Samples', 'code' => 14, 'testtype' => 2],
        	// EID patients
        	['name' => 'Positive Outcomes for follow up', 'code' => 21, 'testtype' => 1],
        	['name' => 'Patients <=2M', 'code' => 22, 'testtype' => 1],
        	// VL patients
        	['name' => 'Pregnant and Lactating mothers', 'code' => 31, 'testtype' => 2],
        	// EID sites
        	['name' => 'High burden sites', 'code' => 41, 'testtype' => 1],
        	['name' => 'Dormant sites', 'code' => 42, 'testtype' => 1],
        	// VL sites
        	['name' => 'Dormant sites', 'code' => 51, 'testtype' => 2],
        	// Labs
        	['name' => 'Lab Tracker', 'code' => 61],
        	['name' => 'Quartely VL Report', 'code' => 62]
        ];
        $report_categories = \App\ReportCategory::get();
        foreach ($partner_reports as $key => $value) {
        	$report = new \App\PartnerReport;
        	$report->fill($value);
        	if ($value['code'] > 0 && $value['code'] < 21)
        		$category_id = $report_categories->where('name', 'Samples Reports')->pluck('id');
        	if ($value['code'] > 20 && $value['code'] < 41)
        		$category_id = $report_categories->where('name', 'Patients Reports')->pluck('id');
        	if ($value['code'] > 40 && $value['code'] < 61)
        		$category_id = $report_categories->where('name', 'Sites Reports')->pluck('id');
        	if ($value['code'] > 60 && $value['code'] < 81)
        		$category_id = $report_categories->where('name', 'Labs Reports')->pluck('id');
        	// dd($category_id[0]);
        	$report->report_category_id = $category_id[0];
        	$report->save();
        }

        $partner_reports = \App\PartnerReport::get();
        // $usertypes = \App\UserType::get();
        foreach ($partner_reports as $key => $value) {
        	\App\ReportPermission::create(['partner_report_id' => $value->id, 'user_type_id' => 10]);
        }
        // $report_permissions = \App\ReportPermission::create();
    }
}

