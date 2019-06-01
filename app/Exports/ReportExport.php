<?php

namespace App\Exports;

// use App\SampleView;
// use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportExport implements FromArray ,WithHeadings, ShouldAutoSize
{
 //    use Exportable;

    protected $data;
    protected $title;

    public function __construct($data, $title){
    	$this->data = $data;
        $this->title = $title;
    }

    /**
    * @return heading array()
    */
    public function headings(): array
    {
        return $this->title;
    }

    public function array(): array
    {
        // dd($this->data);
        return $this->data;
    }

    private static function query($request) {
        $table = '';
        $title = '';
        $briefTitle = '';
        $model = self::getModel($request, $table, $title, $briefTitle);
        $model = self::filter($model, $request, $table, $title, $briefTitle);
        return $model;  
    }

    private static function getModel($request, &$table, &$title, &$briefTitle) {
        $model = PartnerReport::where('code', $request->input('indicatortype'))->first();
        if ($request->testtype == 'EID') {
            $table = 'sample_complete_view';
            $selectStr = "$table.id, $table.patient, $table.original_batch_id, lab.name as labdesc, view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility, view_facilitys.facilitycode, $table.gender_description, $table.dob, $table.age, $table.pcrtypename, IF($table.pcrtype=4, $table.enrollment_ccc_no, null) as enrolment_ccc_no, $table.datecollected, $table.datereceived, $table.datetested, $table.datedispatched";
            if ($request->indicatortype == 1 || $request->indicatortype == 6) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age (Months)', 'PCR Type', 'Enrollment CCC No', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Infant Prophylaxis', 'Received Status', 'Lab Comment', 'Reason for Repeat', 'Spots', 'Feeding', 'Entry Point', 'Result', 'PMTCT Intervention', 'Mother Result', 'Mother Age', 'Mother CCC No', 'Mother Last VL'];
                $selectStr .= ",$table.regimen_name as infantprophylaxis, $table.receivedstatus_name as receivedstatus, $table.labcomment, $table.reason_for_repeat, $table.spots, $table.feeding_name, $table.entry_point_name, $table.result_name, $table.mother_prophylaxis_name as motherprophylaxis, $table.mother_age, $table.mother_ccc_no, $table.mother_last_result";
                if ($request->indicatortype == 1) {
                    $title .= "EID TEST OUTCOMES FOR ";
                    $briefTitle .= "EID TEST OUTCOMES ";
                }
                if ($request->indicatortype == 6) {
                    $title .= "EID PATIENTS <2M ";
                    $briefTitle .= "EID PATIENTS <2M ";
                }
                
            }
        }
        return $model->class::selectRaw($selectStr);
    }

    private static function filter($model, $request, $table, &$title, &$briefTitle) {
        if (auth()->user()->user_type_id == 3) 
            $model = $model->where('view_facilitys.partner_id', '=', auth()->user()->level);
        if (auth()->user()->user_type_id == 4) 
            $model = $model->where('view_facilitys.county_id', '=', auth()->user()->level);
        if (auth()->user()->user_type_id == 5) 
            $model = $model->where('view_facilitys.subcounty_id', '=', auth()->user()->level);

        if (isset($request->specificDate)) {
            $dateString = date('d-M-Y', strtotime($request->specificDate));
            if ($request->testtype == 'support' && ($request->indicatortype == 11 || $request->indicatortype == 12)) {
                $countyData = $countyData->where("$table.datereceived", '=', $request->specificDate);
                $partnerData = $partnerData->where("$table.datereceived", '=', $request->specificDate);
                $labData = $labData->where("$table.datereceived", '=', $request->specificDate);
                $facilityRemote = $facilityRemote->where("$table.datetested", '=', $request->specificDate);
                $facilityData = $facilityData->where("$table.datetested", '=', $request->specificDate);
            } else {
              $model = $model->where("$table.datereceived", '=', $request->specificDate);
            }
        }else {
            if (!isset($request->period) || $request->period == 'range') {
                $dateString = date('d-M-Y', strtotime($request->fromDate))." & ".date('d-M-Y', strtotime($request->toDate));
                if ($request->indicatortype == 5 || $request->indicatortype == 9) {
                    $column = 'datereceived';
                } else {
                    if ($request->period) { $column = 'datetested'; } 
                    else { $column = 'datereceived'; }
                }
                if ($request->testtype == 'support' && ($request->indicatortype == 11 || $request->indicatortype == 12)) {
                    $countyData = $countyData->whereRaw("$table.$column BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
                    $partnerData = $partnerData->whereRaw("$table.$column BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
                    $labData = $labData->whereRaw("$table.$column BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
                    $facilityRemote = $facilityRemote->whereRaw("$table.$column BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
                    $facilityData = $facilityData->whereRaw("$table.$column BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
                } else {
                  $model = $model->whereRaw("$table.$column BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
                }
            } else if ($request->period == 'monthly') {
                $dateString = date("F", mktime(null, null, null, $request->month)).' - '.$request->year;
                $column = 'datetested';
                if ($request->indicatortype == 5 || $request->indicatortype == 9) 
                    $column = 'datereceived';
                if ($request->testtype == 'support' && ($request->indicatortype == 11 || $request->indicatortype == 12)) {
                    $countyData = $countyData->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) = '".$request->month."'");
                    $partnerData = $partnerData->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) = '".$request->month."'");
                    $labData = $labData->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) = '".$request->month."'");
                    $facilityRemote = $facilityRemote->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) = '".$request->month."'");
                    $facilityData = $facilityData->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) = '".$request->month."'");
                } else {
                  $model = $model->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) = '".$request->month."'");
                }
            } else if ($request->period == 'quarterly') {
                if ($request->quarter == 'Q1') {
                    $startQuarter = 1;
                    $endQuarter = 3;
                } else if ($request->quarter == 'Q2') {
                    $startQuarter = 4;
                    $endQuarter = 6;
                } else if ($request->quarter == 'Q3') {
                    $startQuarter = 7;
                    $endQuarter = 9;
                } else if ($request->quarter == 'Q4') {
                    $startQuarter = 10;
                    $endQuarter = 12;
                } else {
                    $startQuarter = 0;
                    $endQuarter = 0;
                }
                $dateString = $request->quarter.' - '.$request->year;
                $column = 'datetested';
                if ($request->indicatortype == 5 || $request->indicatortype == 9) 
                    $column = 'datereceived';
                if ($request->testtype == 'support' && ($request->indicatortype == 11 || $request->indicatortype == 12)) {
                    $countyData = $countyData->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
                    $partnerData = $partnerData->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
                    $labData = $labData->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
                    $facilityRemote = $facilityRemote->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
                    $facilityData = $facilityData->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
                } else {
                  $model = $model->whereRaw("YEAR($table.$column) = '".$request->year."' AND MONTH($table.$column) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
                }
            } else if ($request->period == 'annually') {
                $dateString = $request->year;
                $column = 'datetested';
                if ($request->indicatortype == 5 || $request->indicatortype == 9) 
                    $column = 'datereceived';
                if ($request->testtype == 'support' && ($request->indicatortype == 11 || $request->indicatortype == 12)) {
                    $countyData = $countyData->whereRaw("YEAR($table.$column) = '".$request->year."'");
                    $partnerData = $partnerData->whereRaw("YEAR($table.$column) = '".$request->year."'");
                    $labData = $labData->whereRaw("YEAR($table.$column) = '".$request->year."'");
                    $facilityRemote = $facilityRemote->whereRaw("YEAR($table.$column) = '".$request->year."'");
                    $facilityData = $facilityData->whereRaw("YEAR($table.$column) = '".$request->year."'");
                } else {
                  $model = $model->whereRaw("YEAR($table.$column) = '".$request->year."'");
                }
            }
        }
        if ($request->indicatortype == 9) {
            $model = $parent->whereNotIn('id',$model);
        }
        
        if ($request->testtype == 'support' && ($request->indicatortype == 11 || $request->indicatortype == 12)) {
            $model = [
                    'county' => $countyData,
                    'partner' => $partnerData,
                    'lab' => $labData,
                    'facility Doing Remote' => $facilityRemote,
                    'facility' => $facilityData
                ];
        }
        $title .= " IN ".$dateString;
        $briefTitle .= " - ".$dateString;
        $title = strtoupper($title);
        $briefTitle = strtoupper($briefTitle);
        return $model;
    }
}