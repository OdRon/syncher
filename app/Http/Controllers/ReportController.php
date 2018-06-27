<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SampleView;
use App\ViralsampleView;
use App\ViewFacility;
use Excel;

class ReportController extends Controller
{
    //

    public function index($testtype = NULL)
    {   
        if (NULL == $testtype) 
            $testtype = 'EID';
        // dd(auth()->user());
        $usertype = auth()->user()->user_type_id;

        $facilitys = ViewFacility::when($usertype, function($query) use ($usertype){
                                    if ($usertype == 3)
                                        return $query->where('partner_id', '=', auth()->user()->level);
                                    if ($usertype == 4)
                                        return $query->where('county_id', '=', auth()->user()->level);
                                })->get();
        $countys = ViewFacility::where('partner_id', '=', auth()->user()->level)->groupBy('county_id')->get();
        $subcountys = ViewFacility::when($usertype, function($query) use ($usertype){
                                    if ($usertype == 3)
                                        return $query->where('partner_id', '=', auth()->user()->level);
                                    if ($usertype == 4)
                                        return $query->where('county_id', '=', auth()->user()->level);
                                })->groupBy('subcounty_id')->get();

        return view('reports.home', compact('facilitys','countys','subcountys','testtype'))->with('pageTitle', 'Reports '.$testtype);
    }

    public function dateselect(Request $request)
    {
    	$dateString = '';

	    $data = self::__getDateData($request, $dateString)->get();
    	$this->__getExcel($data, $dateString);
    	
    	return back();
    }

    public function generate(Request $request)
    {
        if (!isset($request->category)) {
            session(['toast_message'=>'Please Enter a category', 'toast_error'=>1]);
            return back();
        }
        // dd($request->all());
        $dateString = '';
        $excelColumns = [];
        
        $data = self::__getDateData($request,$dateString, $excelColumns)->get();
        $this->__getExcel($data, $dateString, $excelColumns);
        
        return back();
    }

    public static function __getDateData($request, &$dateString, &$excelColumns)
    {
        // dd($request);
    	if ($request->testtype == 'VL') {
    		$table = 'viralsamples_view';
            $selectStr = "$table.id, $table.batch_id, $table.patient, labs.labdesc, view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility, view_facilitys.facilitycode, gender.gender, $table.dob, $table.age, viralsampletype.name as sampletype, $table.datecollected, viraljustifications.name as justification, $table.datereceived, $table.datetested, $table.datedispatched, $table.initiation_date";

            if ($request->indicatortype == 2) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Reasons for Repeat', 'Rejected Reason', 'Regimen', 'Regimen Line', 'PMTCT', 'Result'];
                $selectStr .= ", receivedstatus.name as receivedstatus, $table.reason_for_repeat, viralrejectedreasons.name as rejectedreason, viralprophylaxis.name as regimen, viralregimenline.name as regimenline, viralpmtcttype.name as pmtct, $table.result";
            } else if ($request->indicatortype == 3) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Rejected Reason', 'Lab Comment'];
                $selectStr .= ", receivedstatus.name as receivedstatus, viralrejectedreasons.name as rejectedreason, $table.labcomment";
            } else if ($request->indicatortype == 4) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Regimen', 'Regimen Line', 'PMTCT', 'Result'];
                $selectStr .= ", receivedstatus.name as receivedstatus, viralprophylaxis.name as regimen, viralregimenline.name as regimenline, viralpmtcttype.name as pmtct, $table.result";
            } else if ($request->indicatortype == 6) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Regimen', 'Regimen Line', 'PMTCT', 'Result'];
                $selectStr .= ", receivedstatus.name as receivedstatus, viralprophylaxis.name as regimen, viralregimenline.name as regimenline, viralpmtcttype.name as pmtct, $table.result";
            }

    		$model = ViralsampleView::selectRaw($selectStr)
    				->leftJoin('labs', 'labs.id', '=', 'viralsamples_view.lab_id')
    				->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
    				->leftJoin('gender', 'gender.id', '=', 'viralsamples_view.sex')
    				->leftJoin('viralsampletype', 'viralsampletype.id', '=', 'viralsamples_view.sampletype')
    				->leftJoin('receivedstatus', 'receivedstatus.id', '=', 'viralsamples_view.receivedstatus')
    				->leftJoin('viralrejectedreasons', 'viralrejectedreasons.id', '=', 'viralsamples_view.rejectedreason')
    				->leftJoin('viralprophylaxis', 'viralprophylaxis.id', '=', 'viralsamples_view.prophylaxis')
    				->leftJoin('viraljustifications', 'viraljustifications.id', '=', 'viralsamples_view.justification')
                    ->leftJoin('viralpmtcttype', 'viralpmtcttype.id', '=', 'viralsamples_view.pmtct')
                    ->leftJoin('viralregimenline', 'viralregimenline.id', '=', 'viralsamples_view.regimenline');

            if ($request->indicatortype == 3) {
                $model = $model->where("$table.receivedstatus", "=", 2);
            } else if ($request->indicatortype == 4) {
                $model = $model->where("$table.rcategory", "=", 4);
            } else if ($request->indicatortype == 6) {
                $model = $model->where('pmtct', '=', 1)->whereOr('pmtct', '=', 2);
            }
    	} else if ($request->testtype == 'EID') {
    		$table = 'samples_view';
            $selectStr = "$table.id, $table.patient, $table.batch_id, labs.labdesc, view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility, view_facilitys.facilitycode, gender.gender, $table.dob, $table.age, pcrtype.alias as pcrtype, $table.datecollected, $table.datereceived, $table.datetested, $table.datedispatched";

            if ($request->indicatortype == 1 || $request->indicatortype == 6) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'PCR Type', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Infant Prophylaxis', 'Received Status', 'Spots', 'Feeding', 'Entry Point', 'Result', 'PMTCT Intervention', 'Mother Result'];
                $selectStr .= ",ip.name as infantprophylaxis, receivedstatus.name as receivedstatus, samples_view.spots, feedings.feeding, entry_points.name as entrypoint, ir.name as infantresult, mp.name as motherprophylaxis, mr.name as motherresult";
            } else if ($request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'PCR Type', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Test Result', 'Validation (CP,A,VL,RT,UF)', 'Enrollment Status', 'Date Initiated on Treatment', 'Enrollment CCC #', 'Other Reasons'];

                $selectStr .= ", ir.name as infantresult, hv.desc as hei_validation, hc.name as enrollment_status, $table.dateinitiatedontreatment, $table.enrollment_ccc_no, $table.otherreason";
            } else if ($request->indicatortype == 5) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'PCR Type', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Received Status', 'Rejected Reason'];
                $selectStr .= ", receivedstatus.name as receivedstatus, rejectedreasons.name";
            } else if ($request->indicatortype == 7) {
                $excelColumns = ['County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Total Positives'];
                $selectStr =  "view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name , $table.facilitycode, COUNT($table.id) as totaltests";
            } else if ($request->indicatortype == 8) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'PCR Type', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Test Result'];
                $selectStr .= ", ir.name as infantresult";
            } else if ($request->indicatortype == 9) {

            } else if ($request->indicatortype == 10) {

            }
            
    		$model = SampleView::selectRaw($selectStr)
    				->leftJoin('labs', 'labs.id', '=', 'samples_view.lab_id')
    				->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
    				->leftJoin('gender', 'gender.id', '=', 'samples_view.sex')
    				->leftJoin('prophylaxis as ip', 'ip.id', '=', 'samples_view.regimen')
    				->leftJoin('prophylaxis as mp', 'mp.id', '=', 'samples_view.mother_prophylaxis')
    				->leftJoin('pcrtype', 'pcrtype.id', '=', 'samples_view.pcrtype')
    				->leftJoin('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
    				->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'samples_view.rejectedreason')
    				->leftJoin('feedings', 'feedings.id', '=', 'samples_view.feeding')
    				->leftJoin('entry_points', 'entry_points.id', '=', 'samples_view.entry_point')
    				->leftJoin('results as ir', 'ir.id', '=', 'samples_view.result')
    				->leftJoin('mothers', 'mothers.id', '=', 'samples_view.mother_id')
    				->leftJoin('results as mr', 'mr.id', '=', 'mothers.hiv_status')
                    ->leftJoin('hei_validation as hv', 'hv.id', '=', 'samples_view.hei_validation')
                    ->leftJoin('hei_categories as hc', 'hc.id', '=', 'samples_view.enrollment_status');

            if ($request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4) {
                $model = $model->where("$table.receivedstatus", "=", '2')->where("$table.pcrtype", '=', 1)
                            ->where("$table.repeatt", '=', 0);
                if ($request->indicatortype == 4) {
                    $model = $model->where("$table.result", '=', 2);
                } else {
                    $model = $model->where("$table.result", '=', 2);
                }
                
                if ($request->indicatortype == 3) 
                    $model->where("$table.hei_validation", "<>", 0);
            } else if ($request->indicatortype == 5) {
                $model = $model->where("$table.receivedstatus", "=", 2);
            } else if ($request->indicatortype == 6) {
                $model = $model->where("$table.age", "<=", 2);
            } else if ($request->indicatortype == 7) {
                $model = $model->where("$table.repeatt", '=', 0)->where("$table.result", '=', 2)
                                ->groupBy('county')
                                ->groupBy('subcounty')
                                ->groupBy('partner')
                                ->groupBy('facilitycode')
                                ->groupBy('name')
                                ->orderBy('totaltests', 'desc');
            } else if ($request->indicatortype == 8) {
                $model = $model->where("$table.repeatt", '=', 0);
            }
    	}

        if ($request->category == 'county') {
            $model = $model->where('view_facilitys.county_id', '=', $request->county);
        } else if ($request->category == 'subcounty') {
            $model = $model->where('view_facilitys.subcounty_id', '=', $request->district);
        } else if ($request->category == 'facility') {
            $model = $model->where('view_facilitys.id', '=', $request->facility);
        } else if ($request->category == 'overall') {
            if (auth()->user()->user_type_id == 3) 
                $model = $model->where('view_facilitys.partner_id', '=', auth()->user()->level);
            if (auth()->user()->user_type_id == 4) 
                $model = $model->where('view_facilitys.county_id', '=', auth()->user()->level);
        }

    	if (isset($request->specificDate)) {
    		$dateString = date('d-M-Y', strtotime($request->specificDate));
    		$model = $model->where("$table.datereceived", '=', $request->specificDate);
    	}else {
            if (!isset($request->period) || $request->period == 'range') {
                $dateString = date('d-M-Y', strtotime($request->fromDate))." & ".date('d-M-Y', strtotime($request->toDate));
                if ($request->period) { $column = 'datetested'; } 
                else { $column = 'datereceived'; }
                $model = $model->whereRaw("$table.$column BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
            } else if ($request->period == 'monthly') {
                $dateString = date("F", mktime(null, null, null, $request->month)).' - '.$request->year;
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->year."' AND MONTH($table.datetested) = '".$request->month."'");
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
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->year."' AND MONTH($table.datetested) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
            } else if ($request->period == 'annually') {
                $dateString = $request->year;
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->year."'");
            }
    	}

        // dd($model->toSql());
    	return $model;
    }

    public static function __getExcel($data, $dateString, $dataArray)
    {
        if($data->isNotEmpty()) {
            $newdataArray[] = $dataArray;
            foreach ($data as $report) {
                $newdataArray[] = $report->toArray();
            }
            // dd($newdataArray);
            $report = 'Detailed Report';
            
            Excel::create($report, function($excel) use ($newdataArray, $report) {
                $excel->setTitle($report);
                $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('WJ Gilmore, LLC');
                $excel->setDescription('TEST OUTCOME REPORT FOR '.$report);

                $excel->sheet($report, function($sheet) use ($newdataArray) {
                    $sheet->fromArray($newdataArray, null, 'A1', false, false);
                });

            })->download('xlsx');
        } else {
            session(['toast_message' => 'No data available for the criteria provided']);
        }
    }
}
