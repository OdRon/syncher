<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Sample;
use App\Viralsample;
use App\SampleView;
use App\SampleCompleteView;
use App\ViralsampleView;
use App\ViralsampleCompleteView;
use App\ViewFacility;
use App\Partner;
use App\Lab;
use Excel;

class ReportController extends Controller
{
    //
    public static $alphabets = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    public static $quarters = ['Q1'=>['name'=>'Jan-Mar', 'start'=>1, 'end'=>3],
                        'Q2'=>['name'=>'Apr-Jun', 'start'=>4, 'end'=>6],
                        'Q3'=>['name'=>'Jul-Sep', 'start'=>7, 'end'=>9],
                        'Q4'=>['name'=>'Oct-Dec', 'start'=>10, 'end'=>12]];
    public function index($testtype = NULL)
    {   
        if (NULL == $testtype) 
            $testtype = 'EID';
        
        $usertype = auth()->user()->user_type_id;
        if ($usertype == 9) 
            $testtype = 'support';
        
        $facilitys = (object)[];
        $countys = (object)[];
        $subcountys = (object)[];
        $partners = (object)[];
        $labs = (object)[];
        
       if ($usertype == 9) {
            $labs = Lab::get();
        } else {
            $facilitys = ViewFacility::when($usertype, function($query) use ($usertype){
                                        if ($usertype == 3)
                                            return $query->where('partner_id', '=', auth()->user()->level);
                                        if ($usertype == 4)
                                            return $query->where('county_id', '=', auth()->user()->level);
                                        if ($usertype == 5)
                                            return $query->where('subcounty_id', '=', auth()->user()->level);
                                        if ($usertype == 7) {
                                            if (auth()->user()->level ==82) {//speed24
                                                return $query->where('partner_id3', '=', auth()->user()->level);
                                            } elseif (auth()->user()->level ==84) {//PHASE
                                                return $query->where('partner_id4', '=', auth()->user()->level);
                                            } elseif (auth()->user()->level ==85) {//jilinde
                                                return $query->where('partner_id5', '=', auth()->user()->level);
                                            } elseif (auth()->user()->level ==80) {//fhi 360
                                                return $query->where('partner_id6', '=', auth()->user()->level);
                                            } else  { //boresha
                                                return $query->where('partner_id2', '=', auth()->user()->level);
                                            }
                                        }
                                    })->orderBy('name', 'asc')->get();
            if ($usertype != 5) {
                if ($usertype != 5) 
                    $countys = ViewFacility::where('partner_id', '=', auth()->user()->level)->groupBy('county_id')->orderBy('county', 'asc')->get();
                if ($usertype == 6)
                    $countys = DB::table('countys')->select('id as county_id', 'name as county')->orderBy('name', 'asc')->get();
                if ($usertype==7 && auth()->user()->level==85)
                    $countys = ViewFacility::where('partner_id5', '=', auth()->user()->level)->groupBy('county_id')->orderBy('county', 'asc')->get();

                if ($usertype == 2)
                    $partners = Partner::where('orderno', '=', 2)->orderBy('name', 'desc')->get();

                $subcountys = ViewFacility::when($usertype, function($query) use ($usertype){
                                        if ($usertype == 3)
                                            return $query->where('partner_id', '=', auth()->user()->level);
                                        if ($usertype == 4)
                                            return $query->where('county_id', '=', auth()->user()->level);
                                    })->groupBy('subcounty_id')->orderBy('subcounty', 'desc')->get();
            }
        }
        
        return view('reports.home', compact('facilitys','countys','subcountys','partners','labs','testtype'))->with('pageTitle', 'Reports '.$testtype);
    }

    public function nodata($testtype='EID', $year=null, $month=null) {
        $testtype = strtoupper($testtype);
        if ($year==null || $year=='null'){
            if (session('reportYear')==null)
                session(['reportYear' => gmdate('Y')]);
        } else {
            session(['reportYear'=>$year]);
        }
        
        if ($testtype == 'EID') {
            $age = $this->nodataObject($testtype, session('reportYear'), $month)
                        ->whereRaw("(age is null or age = 0)")
                        ->whereNull('dob')->get();
            $gender = $this->nodataObject($testtype, session('reportYear'), $month)
                        ->whereRaw("(sex is null or sex = 0)")->get();
            $data = ['age' => $age, 'gender' => $gender];
        } else if ($testtype == 'VL') {
            $age = $this->nodataObject($testtype, session('reportYear'), $month)
                        ->whereRaw("(age is null or age = 0)")
                        ->whereNull('dob')->get();
            $gender = $this->nodataObject($testtype, session('reportYear'), $month)
                        ->whereRaw("(sex is null or sex = 0)")->get();
            $regimen = $this->nodataObject($testtype, session('reportYear'), $month)
                        ->whereRaw("(prophylaxis is null or prophylaxis = 0)")->get();
            $initiation = $this->nodataObject($testtype, session('reportYear'), $month)
                                ->whereNull('initiation_date')->get();
            $data = ['age' => $age, 'gender' => $gender, 'regimen' => $regimen, 'initiation' => $initiation];
        }
        $data['testingSystem'] = $testtype;
        $data = (object)$data;
        $monthName = "";
        $year = session('reportYear');
        
        if (null !== $month) 
            $monthName = "- ".date("F", mktime(null, null, null, $month));
        
        return view('tables.nodata', compact('data'))->with('pageTitle', "No Data $testtype: $year $monthName");
    }

    public function nodataObject($testtype, $year, $month) {
        $model = (object)[];
        if($testtype == 'EID')
            $table = "samples_view";
        if($testtype == 'VL')
            $table = "viralsamples_view";
        $selectStr = "$table.id,$table.patient,labs.labname as lab, view_facilitys.facilitycode, view_facilitys.name as facility, view_facilitys.partner, view_facilitys.county, view_facilitys.subcounty, $table.datetested";

        if ($testtype == 'EID') {
            $model = SampleView::selectRaw($selectStr);
        } else if ($testtype == 'VL') {
            $model = ViralsampleView::selectRaw($selectStr);
        }
        $model = $model->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                    ->leftJoin('labs', 'labs.id', '=', "$table.lab_id")
                    ->where("$table.facility_id", '<>', 7148)
                    ->when($month, function($query) use ($month, $table){
                        return $query->whereRaw("MONTH($table.datetested) = $month");
                    })->whereRaw("YEAR($table.datetested) = $year")
                    ->orderBy('datetested', 'desc');
        
        return $model;
    }

    public function utilization($testtype='EID', $year=null, $month=null) {
        $testtype = strtoupper($testtype);
        $data = [];
        $newdata = [];

        if ($year==null || $year=='null'){
            if (session('reportYear')==null)
                session(['reportYear' => gmdate('Y')]);
        } else {
            session(['reportYear'=>$year]);
        }
        $year = session('reportYear');

        ini_set("memory_limit", "-1");
        
        $machines = DB::table('machines')->select('id','machine')->get();
        $lab = DB::table('labs')->get();
        foreach ($lab as $labkey => $labvalue) {
            $data[$labvalue->id] = ['lab' => $labvalue->labname];
            foreach ($machines as $machinekey => $machinevalue) {
                if($testtype=='EID'){
                    $table = 'samples';
                    $dbData = Sample::selectRaw("worksheets.lab_id, count(*) as totalSamples")
                                            ->leftJoin('worksheets', 'worksheets.id', '=', 'samples.worksheet_id')
                                            ->where('worksheets.machine_type', '=', $machinevalue->id);
                } else if($testtype=='VL'){
                    $table = 'viralsamples';
                    $dbData = Viralsample::selectRaw("viralworksheets.lab_id, count(*) as totalSamples")
                                            ->leftJoin('viralworksheets', 'viralworksheets.id', '=', 'viralsamples.worksheet_id')
                                            ->where('viralworksheets.machine_type', '=', $machinevalue->id);
                } else { return back(); }
                $dbData = $dbData->when($month, function($query) use ($month, $table){
                        return $query->whereRaw("MONTH($table.datetested) = $month");
                    })->whereRaw("YEAR($table.datetested) = $year")->groupBy('lab_id')->get();

                foreach ($dbData as $dbDatakey => $dbDatavalue) {
                    if($dbDatavalue->lab_id == $labvalue->id){
                        $data[$labvalue->id][$machinevalue->machine] = $dbDatavalue->totalSamples;
                    } else {
                        if (!isset($data[$labvalue->id][$machinevalue->machine]) || $data[$labvalue->id][$machinevalue->machine] == 0) 
                            $data[$labvalue->id][$machinevalue->machine] = 0;
                    }
                }
            }
        }
        dd($data);
        $viewdata['machines'] = $machines;
        $viewdata['testingSystem'] = $testtype;
        $viewdata['labs'] = $lab;
        $viewdata['data'] = (object)$data;
        $viewdata = (object) $viewdata;
        $monthName = "";
        $year = session('reportYear');
        
        if (null !== $month) 
            $monthName = "- ".date("F", mktime(null, null, null, $month));
        
        return view('tables.utilization', compact('viewdata'))->with('pageTitle', "Utilization $testtype: $year $monthName");
    }

    public function generate(Request $request)
    {
        if (!isset($request->category)) {
            session(['toast_message'=>'Please Enter a category', 'toast_error'=>1]);
            return back();
        }
        if ($request->testtype == 'support' && ($request->indicatortype == 13 || $request->indicatortype == 14 || $request->indicatortype == 15)) {
            if ($request->category != 'lab') {
                session(['toast_message' => 'This Report type requires a lab to be selected<br/>Please select a lab from the dropdown', 'toast_error'=>1]);
                return back();
            }
            if ($request->period != 'quarterly' && $request->indicatortype == 13) {
                session(['toast_message' => 'This is a quarterly report<br/>Please select a quarter', 'toast_error'=>1]);
                return back();
            }
        }
        // dd($request->all());
        $dateString = '';
        $title = "";
        $briefTitle = "";
        $excelColumns = [];
        
        $data = $this->__getDateData($request,$dateString, $excelColumns, $title, $briefTitle);
        $this->__getExcel($data, $title, $excelColumns, $briefTitle);
        
        return back();
    }

    public function __getDateData($request, &$dateString, &$excelColumns, &$title, &$briefTitle)
    {
        ini_set("memory_limit", "-1");
        
        if (auth()->user()->user_type_id == 3) {
            $partner = ViewFacility::where('partner_id', '=', auth()->user()->level)->first();
            $title = $partner->partner . " ";
        }
        if (auth()->user()->user_type_id == 4) {
            $county = ViewFacility::where('county_id', '=', auth()->user()->level)->first();
            $title .= $county->county . " ";
        }
        if (auth()->user()->user_type_id == 5) {
            $subc = ViewFacility::where('subcounty_id', '=', auth()->user()->level)->first();
            $title .= $subc->subcounty . " ";
        }
        if (auth()->user()->user_type_id == 7) {
            $partner = DB::table('partners')->where('id', '=', auth()->user()->level)->first();
            $title .= $partner->name . " ";
        }
        
    	if ($request->testtype == 'VL') {
            $table = 'viralsample_complete_view';
            $selectStr = "$table.original_sample_id, $table.original_batch_id, $table.patient, IF(site_entry=2, 'POC Site', labs.labdesc) as labdesc, view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility, view_facilitys.facilitycode, $table.gender_description, $table.dob, $table.age, $table.sampletype_name as sampletype, $table.datecollected, $table.justification_name as justification, $table.datereceived, $table.datetested, $table.datedispatched, $table.initiation_date";

            if ($request->indicatortype == 2) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Reasons for Repeat', 'Rejected Reason', 'Regimen', 'Regimen Line', 'PMTCT', 'Result'];
                $selectStr .= ", $table.receivedstatus_name as receivedstatus, $table.reason_for_repeat, viralrejectedreasons.name as rejectedreason, $table.prophylaxis_name as regimen, viralregimenline.name as regimenline, viralpmtcttype.name as pmtct, $table.result";
                
                $title .= "vl TEST OUTCOMES FOR ";
                $briefTitle .= "vl TEST OUTCOMES ";
            } else if ($request->indicatortype == 5) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Rejected Reason', 'Lab Comment'];
                $selectStr .= ", $table.receivedstatus_name as receivedstatus, viralrejectedreasons.name as rejectedreason, $table.labcomment";
                
                $title .= "vl rejected TEST OUTCOMES FOR ";
                $briefTitle .= "vl rejected TEST OUTCOMES ";
            } else if ($request->indicatortype == 4) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Regimen', 'Regimen Line', 'PMTCT', 'Result'];
                $selectStr .= ", $table.receivedstatus_name as receivedstatus, $table.prophylaxis_name as regimen, viralregimenline.name as regimenline, viralpmtcttype.name as pmtct, $table.result";
                
                $title .= "vl Non Suppressed FOR ";
                $briefTitle .= "vl Non Suppressed ";
            } else if ($request->indicatortype == 6) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Regimen', 'Regimen Line', 'PMTCT', 'Result'];
                $selectStr .= ", $table.receivedstatus_name as receivedstatus, $table.prophylaxis_name as regimen, viralregimenline.name as regimenline, viralpmtcttype.name as pmtct, $table.result";
                
                $title .= "VL PREGNANT & LACTATING MOTHERS FOR ";
                $briefTitle .= "vl PREGNANT & LACTATING MOTHERS ";
            } else if ($request->indicatortype == 9) {
                $excelColumns = ['County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code'];
                $selectStr = "distinct $table.facility_id";

                $title .= "VL DORMANT SITES FOR ";
                $briefTitle .= "vl DORMANT ";
            } else if ($request->indicatortype == 10) {
                $excelColumns = ['County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Total Samples'];
                $selectStr =  "view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility , view_facilitys.facilitycode, COUNT($table.id) as totaltests";

                $title .= "VL SITES DIONG REMOTE SAMPLE ENTRY FOR ";
                $briefTitle .= "vl SITES DIONG REMOTE SAMPLE ENTRY ";
            }

            $model = ViralsampleCompleteView::selectRaw($selectStr)
				->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
				->where("$table.flag", '=', 1)->where("$table.facility_id", '<>', 7148);

            if (!($request->indicatortype == 9 || $request->indicatortype == 10)) {
                $model = $model->where('repeatt', '=', 0);
            }
            if ($request->indicatortype == 2 || $request->indicatortype == 4 || $request->indicatortype == 5 || $request->indicatortype == 6) 
                $model = $model->leftJoin('labs', 'labs.id', '=', "$table.lab_id");
            if ($request->indicatortype == 2 || $request->indicatortype == 5)
                $model = $model->leftJoin('viralrejectedreasons', 'viralrejectedreasons.id', '=', "$table.rejectedreason");
            if ($request->indicatortype == 2 || $request->indicatortype == 4 || $request->indicatortype == 6)
                $model = $model->leftJoin('viralpmtcttype', 'viralpmtcttype.id', '=', "$table.pmtct")
                                ->leftJoin('viralregimenline', 'viralregimenline.id', '=', "$table.regimenline");

            if ($request->indicatortype == 5) {
                $model = $model->where("$table.receivedstatus", "=", 2);
            } else if ($request->indicatortype == 4) {
                $model = $model->where("$table.rcategory", "=", 4);
            } else if ($request->indicatortype == 6) {
                $model = $model->whereIn('pmtct', [1, 2]);
            } else if ($request->indicatortype == 9) {
                if (auth()->user()->user_type_id == 3) {
                    $parent = ViewFacility::select('county','subcounty','partner','name','facilitycode')->where('partner_id', '=', auth()->user()->level);
                }
                if (auth()->user()->user_type_id == 4) {
                    $parent = ViewFacility::select('county','subcounty','partner','name','facilitycode')->where('county_id', '=', auth()->user()->level);
                }
                if (auth()->user()->user_type_id == 5) {
                    $parent = ViewFacility::select('county','subcounty','partner','name','facilitycode')->where('subcounty_id', '=', auth()->user()->level);
                }
            } else if ($request->indicatortype == 10) {
                $model = $model->where("$table.site_entry", '=', 1)
                                ->groupBy('facility')
                                ->groupBy('facilitycode')
                                ->groupBy('subcounty')
                                ->groupBy('county')
                                ->orderBy('totaltests', 'desc');
            }
    	} else if ($request->testtype == 'EID') {
            $table = 'sample_complete_view';
            $selectStr = "$table.original_sample_id, $table.patient, $table.original_batch_id, IF(site_entry=2, 'POC Site', labs.labdesc) as labdesc, view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility, view_facilitys.facilitycode, $table.gender_description, $table.dob, $table.age, pcrtype.alias as pcrtype, IF(pcrtype=4, enrollment_ccc_no, null) as enrollment_ccc_no, $table.datecollected, $table.datereceived, $table.datetested, $table.datedispatched";

            if ($request->indicatortype == 1 || $request->indicatortype == 6) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age (Months)', 'PCR Type', 'Enrollment CCC No', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Infant Prophylaxis', 'Received Status', 'Lab Comment', 'Reason for Repeat', 'Spots', 'Feeding', 'Entry Point', 'Result', 'PMTCT Intervention', 'Mother Result', 'Mother Age', 'Mother CCC No', 'Mother Last VL'];
                $selectStr .= ",$table.regimen_name as infantprophylaxis, $table.receivedstatus_name as receivedstatus, $table.labcomment, $table.reason_for_repeat, $table.spots, $table.feeding_name, entry_points.name as entrypoint, ir.name as infantresult, $table.mother_prophylaxis_name as motherprophylaxis, mr.name as motherresult, $table.mother_age, $table.mother_ccc_no, $table.mother_last_result";
                if ($request->indicatortype == 1) {
                    $title .= "EID TEST OUTCOMES FOR ";
                    $briefTitle .= "EID TEST OUTCOMES ";
                }
                if ($request->indicatortype == 6) {
                    $title .= "EID PATIENTS <2M ";
                    $briefTitle .= "EID PATIENTS <2M ";
                }
            } else if ($request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age (Months)', 'PCR Type', 'Enrollment CCC No', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Test Result', 'Validation (CP,A,VL,RT,UF)', 'Enrollment Status', 'Date Initiated on Treatment', 'Enrollment CCC #', 'Other Reasons'];

                $selectStr .= ", ir.name as infantresult, hv.desc as hei_validation, hc.name as enrollment_status, $table.dateinitiatedontreatment, $table.enrollment_ccc_no, $table.otherreason";
                if ($request->indicatortype == 2) {
                    $title .= "EID POSITIVE TEST OUTCOMES FOR ";
                    $briefTitle .= "EID POSITIVE TEST OUTCOMES ";
                }
                if ($request->indicatortype == 3) {
                    $title .= "EID POSITIVE TEST OUTCOMES FOR FOLLOW UP FOR ";
                    $briefTitle .= "EID POSITIVEs FOR FOLLOW UP ";
                }
                if ($request->indicatortype == 4) {
                    $title .= "EID NEGATIVE TEST OUTCOMES FOR FOLLOW UP FOR ";
                    $briefTitle .= "EID NEGATIVEs FOR FOLLOW UP ";
                }
            } else if ($request->indicatortype == 5) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age (Months)', 'PCR Type', 'Enrollment CCC No', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Received Status', 'Rejected Reason'];
                $selectStr .= ", $table.receivedstatus_name as receivedstatus, rejectedreasons.name";
                
                $title .= "EID REJECTED SAMPLES FOR ";
                $briefTitle .= "EID REJECTED SAMPLES ";
            } else if ($request->indicatortype == 7) {
                $excelColumns = ['County', 'Sub-County', 'Facilty', 'Facility Code', 'Total Positives'];
                $selectStr =  "view_facilitys.county, view_facilitys.subcounty, view_facilitys.name as facility , view_facilitys.facilitycode, COUNT($table.id) as totaltests";
                
                $title .= "EID HIGH BURDEN SITES FOR ";
                $briefTitle .= "EID HIGH BURDEN SITES ";
            } else if ($request->indicatortype == 8) {
                $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age (Months)', 'PCR Type', 'Enrollment CCC No', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Test Result'];
                $selectStr .= ", ir.name as infantresult";

                $title .= "RHT TESTING ";
                $briefTitle .= "RHT TESTING ";
            } else if ($request->indicatortype == 9) {
                $excelColumns = ['County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code'];
                $selectStr = "distinct $table.facility_id";
                $title = "EID DORMANT SITES FOR ";
                $briefTitle .= "EID DORMANT SITES ";
            } else if ($request->indicatortype == 10) {
                $excelColumns = ['County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Total Samples'];
                $selectStr =  "view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility , view_facilitys.facilitycode, COUNT($table.id) as totaltests";

                $title .= "EID SITES DIONG REMOTE SAMPLE ENTRY FOR ";
                $briefTitle .= "EID SITES DIONG REMOTE SAMPLE ENTRY ";
            }
            
            if ($request->indicatortype == 7) {
                $model = SampleCompleteView::selectRaw($selectStr)
                            ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                            ->where("$table.facility_id", '<>', 7148);
            } else {
                $model = SampleCompleteView::selectRaw($selectStr)
                        ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                        ->where("$table.facility_id", '<>', 7148);
            }
            //Additional Joins
            if ($request->indicatortype == 1 || $request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4 || $request->indicatortype == 5 || $request->indicatortype == 6 || $request->indicatortype == 8)
                $model = $model->leftJoin('labs', 'labs.id', '=', "$table.lab_id");
            if (!($request->indicatortype == 7 || $request->indicatortype == 9 || $request->indicatortype == 10))
                $model = $model->leftJoin('pcrtype', 'pcrtype.id', '=', "$table.pcrtype");
            if ($request->indicatortype == 5)
                $model = $model->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', "$table.rejectedreason");
            if ($request->indicatortype == 1 || $request->indicatortype == 6)
                $model = $model->leftJoin('entry_points', 'entry_points.id', '=', "$table.entry_point");
            if ($request->indicatortype == 1 || $request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4 || $request->indicatortype == 6 || $request->indicatortype == 8)
                $model = $model->leftJoin('results as ir', 'ir.id', '=', "$table.result");
            if ($request->indicatortype == 1 || $request->indicatortype == 6)
                $model = $model->leftJoin('mothers', 'mothers.id', '=', "$table.mother_id")->leftJoin('results as mr', 'mr.id', '=', 'mothers.hiv_status');
            if ($request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4)
                $model = $model->leftJoin('hei_validation as hv', 'hv.id', '=', "$table.hei_validation")
                                ->leftJoin('hei_categories as hc', 'hc.id', '=', "$table.enrollment_status");
            //Additional Joins

            if (!($request->indicatortype == 5 || $request->indicatortype == 9 || $request->indicatortype == 10)) {
                $model = $model->where(['repeatt' => 0, "$table.flag" => 1]);
            }

            if ($request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4) {
                $model = $model->where("$table.receivedstatus", "<>", '2');
                if ($request->indicatortype == 4) {
                    $model = $model->where("$table.result", '=', 1);
                } else {
                    $model = $model->where("$table.result", '=', 2);
                }
                
                if ($request->indicatortype == 3) 
                    $model->whereIn("$table.pcrtype", [1,2,3])->whereRaw("($table.hei_validation = 0 or $table.hei_validation is null)");
            } else if ($request->indicatortype == 5) {
                $model = $model->where("$table.receivedstatus", "=", '2');
            } else if ($request->indicatortype == 6) {
                $model = $model->whereBetween("$table.age", [0.001,2]);
            } else if ($request->indicatortype == 7) {
                $model = $model->where("$table.result", '=', 2)
                                ->groupBy('facility')
                                ->groupBy('facilitycode')
                                ->groupBy('subcounty')
                                ->groupBy('county')
                                ->orderBy('totaltests', 'desc');
            } else if ($request->indicatortype == 8) {
                
            } else if ($request->indicatortype == 9) {
                if (auth()->user()->user_type_id == 3) {
                    $parent = ViewFacility::select('county','subcounty','partner','name','facilitycode')->where('partner_id', '=', auth()->user()->level);
                }
                if (auth()->user()->user_type_id == 4) {
                    $parent = ViewFacility::select('county','subcounty','partner','name','facilitycode')->where('county_id', '=', auth()->user()->level);
                }
                if (auth()->user()->user_type_id == 5) {
                    $parent = ViewFacility::select('county','subcounty','partner','name','facilitycode')->where('subcounty_id', '=', auth()->user()->level);
                }
            } else if ($request->indicatortype == 10) {
                $model = $model->where("$table.site_entry", '=', 1)
                                ->groupBy('facility')
                                ->groupBy('facilitycode')
                                ->groupBy('subcounty')
                                ->groupBy('county')
                                ->orderBy('totaltests', 'desc');
            }

    	} else if ($request->testtype == 'support') {
            $excelColumns = [
                                ['County', '# of Facilities'],
                                ['Partner', '# of Facilities'],
                                ['Lab', '# of Facilities'],
                                ['MFL Code', 'Facility Name', 'County', 'Partner', '# of Samples'],
                                ['MFL Code', 'Facility Name', 'County', 'Partner', '# of Samples'],
                            ];
            $title = "REMOTE LOGIN FOR ";
            $briefTitle = "REMOTE LOGIN FOR ";
            if ($request->indicatortype == 11) {
                $table = "samples_view";
                $countyData = SampleView::selectRaw("view_facilitys.county as county, count(distinct $table.facility_id) as facilities")
                                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                                ->where('site_entry', '=', 1)->where("$table.facility_id", '<>', 7148)
                                ->groupBy('county')
                                ->orderBy('facilities', 'desc');
                $partnerData = SampleView::selectRaw("view_facilitys.partner as partner, count(distinct $table.facility_id) as facilities")
                                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                                ->where('site_entry', '=', 1)->where("$table.facility_id", '<>', 7148)
                                ->groupBy('partner')
                                ->orderBy('facilities', 'desc');
                $labData = SampleView::selectRaw("labs.name as lab, count(distinct $table.facility_id) as facilities")
                                ->leftJoin('labs', 'labs.id', '=', "$table.lab_id")
                                ->where('site_entry', '=', 1)->where("$table.facility_id", '<>', 7148)
                                ->groupBy('lab')
                                ->orderBy('facilities', 'desc');
                $facilityRemote = SampleView::selectRaw("view_facilitys.facilitycode, view_facilitys.name as facility, view_facilitys.county, view_facilitys.partner as partner, count(distinct $table.id) as samplecount")
                                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                                ->where("$table.site_entry", '=', 1)->where("$table.facility_id", '<>', 7148)
                                ->where('repeatt', '=', 0)->where("$table.flag", '=', 1)
                                ->groupBy(['facilitycode', 'facility', 'county', 'partner'])
                                ->orderBy('samplecount', 'desc');
                $facilityData = SampleView::selectRaw("view_facilitys.facilitycode, view_facilitys.name as facility, view_facilitys.county, view_facilitys.partner as partner, count(distinct $table.id) as samplecount")
                                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                                ->where("$table.site_entry", '=', 0)->where("$table.facility_id", '<>', 7148)
                                ->where('repeatt', '=', 0)->where("$table.flag", '=', 1)
                                ->groupBy(['facilitycode', 'facility', 'county', 'partner'])
                                ->orderBy('samplecount', 'desc');
                $title .= "EID SAMPLES TESTED ";
                $briefTitle = "EID SAMPLES TESTED ";
            } else if ($request->indicatortype == 12) {
                $table = "viralsamples_view";
                $countyData = ViralsampleView::selectRaw("view_facilitys.county as county, count(distinct $table.facility_id) as facilities")
                                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                                ->where('site_entry', '=', 1)->where("$table.facility_id", '<>', 7148)
                                ->groupBy('county')
                                ->orderBy('facilities', 'desc');
                $partnerData = ViralsampleView::selectRaw("view_facilitys.partner as partner, count(distinct $table.facility_id) as facilities")
                                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                                ->where('site_entry', '=', 1)->where("$table.facility_id", '<>', 7148)
                                ->groupBy('partner')
                                ->orderBy('facilities', 'desc');
                $labData = ViralsampleView::selectRaw("labs.name as lab, count(distinct $table.facility_id) as facilities")
                                ->leftJoin('labs', 'labs.id', '=', "$table.lab_id")
                                ->where('site_entry', '=', 1)->where("$table.facility_id", '<>', 7148)
                                ->groupBy('lab')
                                ->orderBy('facilities', 'desc');
                $facilityRemote = ViralsampleView::selectRaw("view_facilitys.facilitycode, view_facilitys.name as facility, view_facilitys.county, view_facilitys.partner as partner, count(distinct $table.id) as samplecount")
                                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                                ->where("$table.site_entry", '=', 1)->where("$table.facility_id", '<>', 7148)
                                ->where('repeatt', '=', 0)->where("$table.flag", '=', 1)
                                ->groupBy(['facilitycode', 'facility', 'county', 'partner'])
                                ->orderBy('samplecount', 'desc');
                $facilityData = ViralsampleView::selectRaw("view_facilitys.facilitycode, view_facilitys.name as facility, view_facilitys.county, view_facilitys.partner as partner, count(distinct $table.id) as samplecount")
                                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                                ->where("$table.site_entry", '=', 0)->where("$table.facility_id", '<>', 7148)
                                ->where('repeatt', '=', 0)->where("$table.flag", '=', 1)
                                ->groupBy(['facilitycode', 'facility', 'county', 'partner'])
                                ->orderBy('samplecount', 'desc');
                $title .= "VL SAMPLES TESTED ";
                $briefTitle = "VL SAMPLES TESTED ";
            } else if ($request->indicatortype == 13) {
                $this->getVLQuarterlyReportData($request);
            } else if ($request->indicatortype == 14) {
                $excelColumns = ['Facility Code', 'Facility', 'County', 'Partner', 'Sub-County', '# of Samples'];
                $table = 'samples_view';
                $title = "EID SAMPLES referral network ";
                $model = SampleView::selectRaw("distinct view_facilitys.facilitycode as facilitycode, view_facilitys.name as facility, view_facilitys.county, view_facilitys.partner, view_facilitys.subcounty, count(*) as totalSamples")
                        ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")->groupBy(['facility', 'facilitycode', 'county', 'partner', 'subcounty'])->where("$table.facility_id", '<>', 7148)->orderBy('totalSamples', 'desc');
            } else if ($request->indicatortype == 15) {
                $excelColumns = ['Facility Code', 'Facility', 'County', 'Partner', 'Sub-County', '# of Samples'];
                $table = 'viralsamples_view';
                $title = "VL SAMPLES referral network ";
                $model = ViralsampleView::selectRaw(" distinct view_facilitys.facilitycode as facilitycode, view_facilitys.name as facility, view_facilitys.county, view_facilitys.partner, view_facilitys.subcounty, count(*) as totalSamples")
                        ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")->groupBy(['facility', 'facilitycode', 'county', 'partner', 'subcounty'])->where("$table.facility_id", '<>', 7148)->orderBy('totalSamples', 'desc');
            }

        } else {
            return back();
        }

        if ($request->indicatortype == 7) {
            if (auth()->user()->user_type_id == 3) {
                $model = $model->where('view_facilitys.partner_id', '=', auth()->user()->level);
                $partner = ViewFacility::where('partner_id', '=', auth()->user()->level)->get()->first();
                $title .= "FOR " . $partner->patner;
            }
            if (auth()->user()->user_type_id == 4) {
                $model = $model->where('view_facilitys.county_id', '=', auth()->user()->level);
                $county = ViewFacility::where('county_id', '=', auth()->user()->level)->get()->first();
                $title .= "FOR " . $county->county;
            }
            if (auth()->user()->user_type_id == 5) {
                $model = $model->where('view_facilitys.subcounty_id', '=', auth()->user()->level);
                $subc = ViewFacility::where('subcounty_id', '=', auth()->user()->level)->get()->first();
                $title .= "FOR " . $subc->subcounty;
            }
        } else {
            if ($request->category == 'county') {
                $model = $model->where('view_facilitys.county_id', '=', $request->county);
                $county = ViewFacility::where('county_id', '=', $request->county)->get()->first();
                $title .= $county->county;
            } else if ($request->category == 'subcounty') {
                $model = $model->where('view_facilitys.subcounty_id', '=', $request->district);
                $subc = ViewFacility::where('subcounty_id', '=', $request->district)->get()->first();
                $title .= $subc->subcounty;
            } else if ($request->category == 'facility') {
                $model = $model->where('view_facilitys.id', '=', $request->facility);
                $facility = ViewFacility::where('id', '=', $request->facility)->get()->first();
                $title .= $facility->name;
            } else if ($request->category == 'lab') {
                $lab = Lab::where('id', '=', $request->lab)->get()->first();
                if($request->indicatortype == 11 || $request->indicatortype == 12) {
                    $countyData = $countyData->where('lab_id', '=', $request->lab);
                    $partnerData = $partnerData->where('lab_id', '=', $request->lab);
                    $labData = $labData->where('lab_id', '=', $request->lab);
                    $facilityData = $facilityData->where('lab_id', '=', $request->lab);
                    $facilityRemote = $facilityRemote->where('lab_id', '=', $request->lab);
                }else {
                    $model = $model->where('lab_id', '=', $request->lab);
                }
                $title .= "($lab->name)";
            } else if ($request->category == 'overall') {
                if (auth()->user()->user_type_id == 3) {
                    $model = $model->where('view_facilitys.partner_id', '=', auth()->user()->level);
                    $partner = ViewFacility::where('partner_id', '=', auth()->user()->level)->get()->first();
                    $title .= $partner->patner;
                }
                if (auth()->user()->user_type_id == 4) {
                    $model = $model->where('view_facilitys.county_id', '=', auth()->user()->level);
                    $county = ViewFacility::where('county_id', '=', auth()->user()->level)->get()->first();
                    $title .= $county->county;
                }
                if (auth()->user()->user_type_id == 5) {
                    $model = $model->where('view_facilitys.subcounty_id', '=', auth()->user()->level);
                    $subc = ViewFacility::where('subcounty_id', '=', auth()->user()->level)->get()->first();
                    $title .= $subc->subcounty;
                }
                if (auth()->user()->user_type_id == 7) {
                    if (auth()->user()->level ==82) {//speed24
                        $model = $model->where('view_facilitys.partner_id3', '=', auth()->user()->level);
                    } elseif (auth()->user()->level ==84) {//PHASE
                        $model = $model->where('view_facilitys.partner_id4', '=', auth()->user()->level);
                    } elseif (auth()->user()->level ==85) {//jilinde
                        $model = $model->where('view_facilitys.partner_id5', '=', auth()->user()->level);
                    } elseif (auth()->user()->level ==80) {//fhi 360
                        $model = $model->where('view_facilitys.partner_id6', '=', auth()->user()->level);
                    } else  { //boresha
                        $model = $model->where('view_facilitys.partner_id2', '=', auth()->user()->level);
                    }
                }
            }
        }

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

    public static function getVLQuarterlyObject($request) {
        $quarter = (object)self::$quarters[$request->quarter];
        return ViralsampleView::selectRaw("COUNT(*) AS totalSamples")
                        ->where(['lab_id'=>$request->lab,'repeatt'=>0,'flag'=>1])
                        ->where('facility_id', '<>', 7148)
                        ->whereRaw("YEAR(datetested) = '".$request->year."' AND MONTH(datetested) BETWEEN '".$quarter->start."' AND '".$quarter->end."'");
    }

    public function getVLQuarterlyReportData($request)
    {
        ini_set("memory_limit", "-1");
        $quarter = (object)self::$quarters[$request->quarter];
        // Build Query objects
        $lab = Lab::where('id', '=', $request->lab)->first();
        // Retreive results
        $validTests = self::getVLQuarterlyObject($request)->whereBetween('rcategory', [1, 4])->get()->first()->totalSamples;
        $supOutcomes = self::getVLQuarterlyObject($request)->whereIn('rcategory', [1,2])->get()->first()->totalSamples;
        $nonsupOutcomes = self::getVLQuarterlyObject($request)->whereIn('rcategory', [3,4])->get()->first()->totalSamples;
        $supmale = self::getVLQuarterlyObject($request)->whereIn('rcategory', [1,2])->where('sex', '=', 1)->get()->first()->totalSamples;
        $nonsupmale = self::getVLQuarterlyObject($request)->whereIn('rcategory', [3,4])->where('sex', '=', 1)->get()->first()->totalSamples;
        $supfemale = self::getVLQuarterlyObject($request)->whereIn('rcategory', [1,2])->where('sex', '=', 2)->get()->first()->totalSamples;
        $nonsupfemale = self::getVLQuarterlyObject($request)->whereIn('rcategory', [3,4])->where('sex', '=', 2)->get()->first()->totalSamples;
        $supnogender = self::getVLQuarterlyObject($request)->whereIn('rcategory', [1,2])->where('sex', '=', 3)->get()->first()->totalSamples;
        $nonsupnogender = self::getVLQuarterlyObject($request)->whereIn('rcategory', [3,4])->where('sex', '=', 3)->get()->first()->totalSamples;
        $supless9 = self::getVLQuarterlyObject($request)->whereIn('age_category',[6,7])->whereIn('rcategory', [1,2])->get()->first()->totalSamples;
        $nonsupless9 = self::getVLQuarterlyObject($request)->whereIn('age_category',[6,7])->whereIn('rcategory', [3,4])->get()->first()->totalSamples;
        $sup10to14 = self::getVLQuarterlyObject($request)->where('age_category','=',8)->whereIn('rcategory', [1,2])->get()->first()->totalSamples;
        $nonsup10to14 = self::getVLQuarterlyObject($request)->where('age_category','=',8)->whereIn('rcategory', [3,4])->get()->first()->totalSamples;
        $sup15to19 = self::getVLQuarterlyObject($request)->where('age_category','=',9)->whereIn('rcategory', [1,2])->get()->first()->totalSamples;
        $nonsup15to19 = self::getVLQuarterlyObject($request)->where('age_category','=',9)->whereIn('rcategory', [3,4])->get()->first()->totalSamples;
        $sup20to24 = self::getVLQuarterlyObject($request)->where('age_category','=',10)->whereIn('rcategory', [1,2])->get()->first()->totalSamples;
        $nonsup20to24 = self::getVLQuarterlyObject($request)->where('age_category','=',10)->whereIn('rcategory', [3,4])->get()->first()->totalSamples;
        $supabove25 = self::getVLQuarterlyObject($request)->where('age_category','=',11)->whereIn('rcategory', [1,2])->get()->first()->totalSamples;
        $nonsupabove25 = self::getVLQuarterlyObject($request)->where('age_category','=',11)->whereIn('rcategory', [3,4])->get()->first()->totalSamples;
        $supnoage = self::getVLQuarterlyObject($request)->whereIn('rcategory', [1,2])->where('age_category', '=', 0)->get()->first()->totalSamples;
        $nonsupnoage = self::getVLQuarterlyObject($request)->whereIn('rcategory', [3,4])->where('age_category', '=', 0)->get()->first()->totalSamples;
        $suppregnant = self::getVLQuarterlyObject($request)->whereIn('rcategory', [1,2])->where('pmtct', '=', 1)->get()->first()->totalSamples;
        $nonsuppregnant = self::getVLQuarterlyObject($request)->whereIn('rcategory', [3,4])->where('pmtct', '=', 1)->get()->first()->totalSamples;
        $supbreastfeeding = self::getVLQuarterlyObject($request)->whereIn('rcategory', [1,2])->where('pmtct', '=', 2)->get()->first()->totalSamples;
        $nonsupbreastfeeding = self::getVLQuarterlyObject($request)->whereIn('rcategory', [3,4])->where('pmtct', '=', 2)->get()->first()->totalSamples;
        // Build Excel Data
        $data = [
            ['Lab',$lab->name],
            ['Quarter',"[$quarter->name], $request->year"],
            ['All Valid Tests',$validTests],
            ['Outcomes','Suppressed','Non Suppressed'],
            ['',$supOutcomes,$nonsupOutcomes],
            ['Male',$supmale,$nonsupmale],
            ['Female',$supfemale,$nonsupfemale],
            ['No Gender',$supnogender,$nonsupnogender],
            ['<9',$supless9,$nonsupless9],
            ['10-14',$sup10to14,$nonsup10to14],
            ['15-19',$sup15to19,$nonsup15to19],
            ['20-24',$sup20to24,$nonsup20to24],
            ['25+',$supabove25,$nonsupabove25],
            ['No Age',$supnoage,$nonsupnoage],
            ['Pregnant',$suppregnant,$nonsuppregnant],
            ['Breast Feeding',$supbreastfeeding,$nonsupbreastfeeding],
        ];
        // dd($data);
        $title = "$lab->name $quarter->name $request->year";
        $string = (strlen($lab->name) > 31) ? substr($lab->name,0,28).'...' : $string;
        $sheetTitle = "$string";
        //Export Data
        Excel::create($title, function($excel) use ($data, $title, $sheetTitle) {
            $excel->setTitle($title);
            $excel->setCreator(auth()->user()->surname.' '.auth()->user()->oname)->setCompany('NASCOP');
            $excel->setDescription($title);
            
            $excel->sheet($sheetTitle, function($sheet) use ($data) {
                $sheet->mergeCells('B1:C1');
                $sheet->mergeCells('B2:C2');
                $sheet->mergeCells('B3:C3');
                $sheet->mergeCells('A4:A5');
                $sheet->fromArray($data, null, 'A1', false, false);
            });
             
        })->download('xlsx');
    }

    public static function __getExcel($data, $title, $dataArray, $briefTitle)
    {
        $newdataArray = [];
        $finaldataArray = [];
        $sheetTitle = [];
        $mergeCellsArray = [];
        ini_set("memory_limit", "-1");
        if (is_array($data)) {
            $count = 0;
            foreach ($data as $key => $value) {
                $newValue = $value->get();
                $newdataArray[] = $dataArray[$count];
                if ($newValue->isNotEmpty()) {
                    foreach ($newValue as $report) {
                        $newdataArray[] = $report->toArray();
                    }
                } else {
                    $newdataArray[] = [];
                }
                $sheetTitle[] = ucfirst($key) . " Summary";
                $finaldataArray[] = $newdataArray;
                $newdataArray = [];
                $count++;
            }
        } else {
            $data = $data->get();
            if($data->isNotEmpty()) {
                $newdataArray[] = $dataArray;
                foreach ($data as $report) {
                    $newdataArray[] = $report->toArray();
                }
            } else {
                $newdataArray[] = [];
            }
            $sheetTitle[] = 'Sheet1';
            $finaldataArray[] = $newdataArray;
        }
        
        Excel::create($title, function($excel) use ($finaldataArray, $title, $sheetTitle) {
            $excel->setTitle($title);
            $excel->setCreator(auth()->user()->surname.' '.auth()->user()->oname)->setCompany('NASCOP');
            $excel->setDescription($title);
            foreach ($finaldataArray as $key => $value) {
                $stitle = $sheetTitle[$key];
                $excel->sheet($stitle, function($sheet) use ($value) {
                    $sheet->fromArray($value, null, 'A1', false, false);
                });
            }
        })->download('xlsx');
    }



    // public static function __dupgetExcel($data, $title, $dataArray)
    // {
    //     ini_set("memory_limit", "-1");
    //     if($data->isNotEmpty()) {
    //         $newdataArray[] = $dataArray;
    //         foreach ($data as $report) {
    //             $newdataArray[] = $report->toArray();
    //         }
            
    //         Excel::create($title, function($excel) use ($newdataArray, $title) {
    //             $excel->setTitle($title);
    //             $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('NASCOP.ORG');
    //             $excel->setDescription($title);

    //             $excel->sheet($title, function($sheet) use ($newdataArray) {
    //                 $sheet->fromArray($newdataArray, null, 'A1', false, false);
    //             });
    //         })->download('xlsx');
    //     } else {
    //         session(['toast_message' => 'No data available for the criteria provided']);
    //     }
    // }
}
