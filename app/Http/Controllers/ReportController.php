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
// use Excel;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use \App\Exports\ReportGenericExport;
// use App\Exports\ReportExportWithSheets;

class ReportController extends Controller
{
    //
    public static $alphabets = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    public static $quarters = ['Q1'=>['name'=>'Jan-Mar', 'start'=>1, 'end'=>3],
                                'Q2'=>['name'=>'Apr-Jun', 'start'=>4, 'end'=>6],
                                'Q3'=>['name'=>'Jul-Sep', 'start'=>7, 'end'=>9],
                                'Q4'=>['name'=>'Oct-Dec', 'start'=>10, 'end'=>12]];
    private $testtypes = ['EID', 'VL'];
    public function index($testtype = NULL)
    {   
        // echo phpinfo();die();
        if (NULL == $testtype) 
            $testtype = 'EID';
        
        $usertype = auth()->user()->user_type_id;

        if ($usertype == 9) 
            $testtype = 'support';

        if($testtype == 'EID' && $usertype == 16) abort(403);
        
        $facilitys = (object)[];
        $countys = (object)[];
        $subcountys = (object)[];
        $partners = (object)[];
        $labs = (object)[];
        
        if (in_array($usertype, [9,10,16])) 
            $labs = Lab::get();

        if ($usertype != 9) {
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
                                            } elseif (auth()->user()->level ==93) {//MobileWAChX
                                                return $query->where('partner_id7', '=', auth()->user()->level);
                                            } else  { //boresha
                                                return $query->where('partner_id2', '=', auth()->user()->level);
                                            }
                                        }
                                    })->orderBy('name', 'asc')->get();
            if ($usertype != 5) {
                if ($usertype != 5) 
                    $countys = ViewFacility::when($usertype, function($query) use ($usertype) { 
                                                if (!($usertype == 10 || $usertype == 2))
                                                    return $query->where('partner_id', '=', auth()->user()->level);
                                            })->groupBy('county_id')->orderBy('county', 'asc')->get();
                if (in_array($usertype, [6,10,16]))
                    $countys = DB::table('countys')->select('id as county_id', 'name as county')->orderBy('name', 'asc')->get();
                if ($usertype==7 && auth()->user()->level==85)
                    $countys = ViewFacility::where('partner_id5', '=', auth()->user()->level)->groupBy('county_id')->orderBy('county', 'asc')->get();

                if (in_array($usertype, [2,10,16]))
                    $partners = Partner::orderBy('name', 'desc')->get();

                $subcountys = ViewFacility::when($usertype, function($query) use ($usertype){
                                        if ($usertype == 3)
                                            return $query->where('partner_id', '=', auth()->user()->level);
                                        if ($usertype == 4)
                                            return $query->where('county_id', '=', auth()->user()->level);
                                    })->groupBy('subcounty_id')->orderBy('subcounty', 'desc')->get();
            }
        }
        $data['reports'] = auth()->user()->user_type->reports();
        $data['facilitys'] = $facilitys;$data['countys'] = $countys; $data['subcountys'] = $subcountys;
        $data['partners'] = $partners; $data['labs'] = $labs; $data['testtype'] = $testtype;
        
        return view('reports.home', $data)->with('pageTitle', 'Reports '.$testtype);
    }

    public static function __getDateRequested($request, $model, $table, &$dateString, $receivedOnly=true) {
        if ($receivedOnly) { $column = 'datereceived'; } else { $column = 'datetested'; }

        if (!$request->input('period') || $request->input('period') == 'range') {
            $dateString .= date('d-M-Y', strtotime($request->input('fromDate')))." - ".date('d-M-Y', strtotime($request->input('toDate')));
            $model = $model->whereRaw("$table.$column BETWEEN '".$request->input('fromDate')."' AND '".$request->input('toDate')."'");
        } else if ($request->input('period') == 'monthly') {
            $dateString .= date("F", mktime(null, null, null, $request->input('month'))).' - '.$request->input('year');
            $model = $model->whereRaw("YEAR($table.$column) = '".$request->input('year')."' AND MONTH($table.$column) = '".$request->input('month')."'");
        } else if ($request->input('period') == 'quarterly') {
            if ($request->input('quarter') == 'Q1') {
                $startQuarter = 1;
                $endQuarter = 3;
            } else if ($request->input('quarter') == 'Q2') {
                $startQuarter = 4;
                $endQuarter = 6;
            } else if ($request->input('quarter') == 'Q3') {
                $startQuarter = 7;
                $endQuarter = 9;
            } else if ($request->input('quarter') == 'Q4') {
                $startQuarter = 10;
                $endQuarter = 12;
            } else {
                $startQuarter = 0;
                $endQuarter = 0;
            }
            $dateString .= $request->input('quarter').' - '.$request->input('year');
            $model = $model->whereRaw("YEAR($table.$column) = '".$request->input('year')."' AND MONTH($table.$column) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
        } else if ($request->input('period') == 'annually') {
            $dateString .= $request->input('year');
            $model = $model->whereRaw("YEAR($table.$column) = '".$request->input('year')."'");
        }

        return $model;
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
        
        if($testtype=='EID'){
            $table = 'samples_view';
            $join_table = 'worksheets';
            $model = SampleView::class;
        } else if($testtype=='VL'){
            $table = 'viralsamples_view';
            $join_table = 'viralworksheets';
            $model = ViralsampleView::class;
        } else { return back(); }
        $dbData = $model::selectRaw("$join_table.lab_id, 
                        COUNT(IF($join_table.machine_type = 1, 1, NULL)) AS `taqman`, 
                        COUNT(IF($join_table.machine_type = 2, 1, NULL)) AS `abbott`,
                        COUNT(IF($join_table.machine_type = 3, 1, NULL)) AS `c8800`,
                        COUNT(IF($join_table.machine_type = 4, 1, NULL)) AS `panther`")
                    ->join($join_table, function($join) use ($table, $join_table) {
                        $join->on($join_table . '.original_worksheet_id', '=',  $table . '.worksheet_id');
                        $join->on($join_table . '.lab_id','=', $table . '.lab_id');
                    })
                    ->when($month, function($query) use ($month, $table){
                        return $query->whereRaw("MONTH($table.datetested) = $month");
                    })->whereRaw("YEAR($table.datetested) = $year")
                    // ->whereRaw("date($table.datetested) BETWEEN '2018-09-01' AND '2019-08-31'")
                    ->groupBy('lab_id')->get();
        foreach($dbData as $key => $data) {
            $newlab = $lab->where('id', $data->lab_id)->first();
            $dbData[$key]->lab_name = $newlab->labname;
        }
        $viewdata['machines'] = $machines;
        $viewdata['testingSystem'] = $testtype;
        $viewdata['labs'] = $lab;
        $viewdata['data'] = $dbData;
        $viewdata = (object) $viewdata;
        $monthName = "";
        $year = session('reportYear');
        
        if (null !== $month) 
            $monthName = "- ".date("F", mktime(null, null, null, $month));
        
        return view('tables.utilization', compact('viewdata'))->with('pageTitle', "Utilization $testtype: $year $monthName");
    }

    public function generate(Request $request)
    {
        if (!isset($request->category) && !($request->indicatortype == 19 || $request->indicatortype == 20)) {
            session(['toast_message'=>'Please Enter a category', 'toast_error'=>1]);
            return back();
        }
        if ($request->testtype == 'support' && ($request->indicatortype == 13 || $request->indicatortype == 14 || $request->indicatortype == 15 || $request->indicatortype == 16)) {
            if ($request->category != 'lab') {
                session(['toast_message' => 'This Report type requires a lab to be selected<br/>Please select a lab from the dropdown', 'toast_error'=>1]);
                return back();
            }
            if ($request->period != 'quarterly' && $request->indicatortype == 13) {
                session(['toast_message' => 'This is a quarterly report<br/>Please select a quarter', 'toast_error'=>1]);
                return back();
            }
        }
        // End Move this section to middleware
        
        $dateString = '';
        $title = "";
        $briefTitle = "";
        $excelColumns = [];

        // Move this section to middleware
        if ($request->indicatortype == 17) {
            if ($request->category == 'lab' || $request->period == 'annually') {
                $this->__getTestOutComes($request,$dateString, $excelColumns, $title, $briefTitle);
                return back();
            } else {
                session(['toast_message' => 'This report is only for lab and annually', 'toast_error' => 1]);
                return back();
            }
        }
        // End Move this section to middleware

        if($request->indicatortype == 16){
            $this->__getOutcomesByPlartform($request);
        } else if ($request->indicatortype == 18) {
            $data = $this->__getLowLevelViremia($request, $excelColumns, $title);
        } else if ($request->indicatortype == 19 || $request->indicatortype == 20) {
            $this->__getNodataSummary($request);
        } else {
            $data = $this->__getDateData($request,$dateString, $excelColumns, $title, $briefTitle);
            $data = $this->__getExcel($data, $title, $excelColumns, $briefTitle);
        }
        
        return (new ReportExport($data, $excelColumns))->download("$title.csv");
    }

    protected function __getOutcomesByPlartform($request) {
        $columns = "machines.machine, if(viralsampletype.sampletype = 3, 1, viralsampletype.sampletype) as sampletype";
        $plasmaundetectable = "count(if(viralsampletype.id = 1 or viralsampletype.id = 2, if(viralsamples_view.result = '< LDL copies' or viralsamples_view.result = '< LDL copies/ml' or viralsamples_view.result = '< 20' or viralsamples_view.result < 20, 1, null), null)) as `plasmaundetectable`";
        $plasma20_400 = "count(if(viralsampletype.id = 1 or viralsampletype.id = 2, if(viralsamples_view.result between 20 and 400, 1, null),null)) as `plasma20_400`";
        $plasma401_999 = "count(if(viralsampletype.id = 1, if(viralsamples_view.result between 401 and 999, 1, null),null)) as `plasma401_999`";
        $dbsTaqmanundetectable = "count(if(viralsampletype.id = 3 or viralsampletype.id = 4, if(viralsamples_view.result = '< LDL copies' or viralsamples_view.result = '< LDL copies/ml' or viralsamples_view.result < 401, if(machines.id = 1,1,null), null), null)) as `dbsTaqmanundetectable`";
        $dbsAbbotundetectable = "count(if(viralsampletype.id = 3 or viralsampletype.id = 4, if(viralsamples_view.result = '< LDL copies' or viralsamples_view.result = '< LDL copies/ml' or viralsamples_view.result < 840, if(machines.id = 2,1,null), null), null)) as `dbsAbbotundetectable`";
        $dbsAbbot840_999 = "count(if(viralsampletype.id = 3 or viralsampletype.id = 4, if(machines.id = 2, if(viralsamples_view.result between 840 and 999, 1, null), null),null)) as `dbsAbbot840_999`";
        $dbsTaqman401_999 = "count(if(viralsampletype.id = 3 or viralsampletype.id = 4, if(machines.id = 1, if(viralsamples_view.result between 401 and 999, 1, null), null),null)) as `dbsTaqman401_999`";
        $above1000 = "count(if(viralsamples_view.result > 999, 1, null)) as `above1000`";
        $model = DB::table('machines')->selectRaw("$columns, $plasmaundetectable, $plasma20_400, $plasma401_999, $dbsTaqmanundetectable, $dbsAbbotundetectable, $dbsAbbot840_999, $dbsTaqman401_999, $above1000");
        $model->leftJoin('viralworksheets', 'viralworksheets.machine_type', '=', 'machines.id')
                ->leftJoin('viralsamples_view', 'viralsamples_view.worksheet_id', '=', 'viralworksheets.id')
                ->leftJoin('viralsampletype', 'viralsampletype.id', '=', 'viralsamples_view.sampletype')
                ->where('viralsamples_view.lab_id', '=', $request->input('lab'))
                ->groupBy('machine')->groupBy('sampletype');
        $table = "viralsamples_view";
        $lab = Lab::find($request->input('lab'));
        $labname = $lab->labname;
        $dateString = "$labname outcomes by equipment report ";
        $data = self::__getDateRequested($request, $model, $table, $dateString)->get();
        $data = self::__buildOutcomesByPlatformData($data, $lab, $dateString);
        $excel = self::__generateOutcomesByPlatformExcel($data, $dateString);
        return true;
    }

    protected static function __buildOutcomesByPlatformData($data,$lab, $title) {
        $newdata = [];
        $sample_types = ['Plasma' => 1, 'DBS' => 2];
        $machines = DB::table('machines')->get();
        if (isset($data)) {
            $labname = $lab->name;
            $period = str_replace("$labname outcomes by equipment report ", "", $title);
            $newdata[] = ['Lab', $labname];
            $newdata[] = ['Period', $period];
            $newdata[] = ['Sample Type', 'Equipment', 'Categories', 'Totals'];
            foreach ($sample_types as $sampletypekey => $sampletype) {
                foreach ($data as $itemkey => $item) {
                    if ($item->sampletype == $sampletype) {
                        $machine_name = $item->machine;
                        if ($item->machine == 'TaqMan')
                            $machine_name = 'CAPCTM';
                        
                        if ($sampletype == 1) {
                            $newdata[] = [$sampletypekey, $machine_name, 'Undetectable/LDL/', $item->plasmaundetectable];
                            $newdata[] = [$sampletypekey, $machine_name, '20-400 cp/ml', $item->plasma20_400];
                            $newdata[] = [$sampletypekey, $machine_name, '401-999 cp/ml', $item->plasma401_999];
                        }
                        if ($sampletype == 2) {
                            if ($item->machine == 'Abbott') {
                                $newdata[] = [$sampletypekey, $machine_name, 'Undetectable/LDL/', $item->dbsAbbotundetectable];
                                $newdata[] = [$sampletypekey, $machine_name, '839-999 cp/ml', $item->dbsAbbot840_999];
                            } else {
                                $newdata[] = [$sampletypekey, $machine_name, 'Undetectable/LDL/', $item->dbsAbbotundetectable];
                                $newdata[] = [$sampletypekey, $machine_name, '839-999 cp/ml', $item->dbsTaqman401_999];
                            }
                        }
                        $newdata[] = [$sampletypekey, $machine_name, '> 1000 cp/ml', $item->above1000];
                    }
                }
            }
        }
        return $newdata;
    }

    protected function __generateOutcomesByPlatformExcel($data, $title) {
        $title = strtoupper($title);
        Excel::create($title, function($excel) use ($data, $title) {
            $excel->setTitle($title);
            $excel->setCreator(auth()->user()->surname.' '.auth()->user()->oname)->setCompany('NASCOP');
            $excel->setDescription($title);
            
            $excel->sheet($title, function($sheet) use ($data) {
                $sheet->mergeCells('B1:C1:D1');
                $sheet->mergeCells('B2:C2:D2');
                $sheet->fromArray($data, null, 'A1', false, false);
            });
             
        })->download('csv');
    }

    public function __getNodataSummary($request) {
        $model = $this->__getNodataSummaryObject($request);
    }

    public function __getNodataSummaryObject($request) {
        $allcount = "COUNT(*) AS allsamples";
        $dobcount = "COUNT(IF(dob IS NULL, 1, NULL)) AS dob";
        $sexcount = "COUNT(IF(sex IS NULL, 1, NULL)) AS sex";
        $regimencount = "COUNT(IF(prophylaxis IS NULL, 1, NULL)) AS regimen";
        $justificationcount = "COUNT(IF(justification IS NULL, 1, NULL)) AS justification";
        $initiationcount = "COUNT(IF(initiation_date IS NULL, 1, NULL)) AS initiation_date";
        $dateString = '';
        $newdata = [];

        if ($request->input('indicatortype') == 19) { // For EID
            $newdata[] = ['Name', 'Age', 'Gender'];
            $model = SampleView::selectRaw("$allcount, $dobcount, $sexcount");
            $table = "samples_view";
            $dateString = 'EID';
        } else if ($request->input('indicatortype') == 20) { // For VL
            $newdata[] = ['Name', 'Age', 'Gender', 'Regimen', 'Justification', 'Initiation Date'];
            $model = ViralsampleView::selectRaw("$allcount, $dobcount, $sexcount, $regimencount, $justificationcount, $initiationcount");
            $table = "viralsamples_view";
            $dateString = 'VL';
        }

        $dateString .= ' no data ';
        $model = self::__getDateRequested($request, $model, $table, $dateString);
        $model = self::__getBelongingToNoDataSummary($request, $model, $dateString, $table);
        $data = $model->get();
        $sheetTitle[] = 'Sheet1';
        
        foreach ($data as $key => $dataitem) {
            $newdata[$key+1] = ['name' => $dataitem->selection,
                            'age' => number_format(round(($dataitem->dob/$dataitem->allsamples)*100,2)).'%',
                            'gender' => number_format(round(($dataitem->sex/$dataitem->allsamples)*100,2)).'%'];
            if ($request->input('indicatortype') == 20) {
                $newdata[$key+1]['regimen'] = number_format(round(($dataitem->regimen/$dataitem->allsamples)*100,2)).'%';
                $newdata[$key+1]['justification'] = number_format(round(($dataitem->justification/$dataitem->allsamples)*100,2)).'%';
                $newdata[$key+1]['initiationdate'] = number_format(round(($dataitem->initiationdate/$dataitem->allsamples)*100,2)).'%';
            }
        }
        
        ini_set("memory_limit", "-1");
        $title = strtoupper($dateString);
        return Excel::download(new ReportExport($newdata), $title . '.csv');
        // Excel::create($title, function($excel) use ($newdata, $title) {
        //         $excel->setTitle($title);
        //         $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('EID/VL System');
        //         $excel->setDescription($title);

        //         $excel->sheet('Sheet1', function($sheet) use ($newdata) {
        //             $sheet->fromArray($newdata, null, 'A1', false, false);
        //         });

        //     })->download('csv');

    }

    public static function __getBelongingToNoDataSummary($request, $model, &$dateString, $table) {
        $title = ' for ';
        $model = $model->join('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id");
        if ($request->input('level') == 'counties') {
            $model = $model->selectRaw("view_facilitys.county as selection");
            $title .= 'counties ';
        } else if ($request->input('level') == 'subcounties') {
            $model = $model->selectRaw("view_facilitys.subcounty as selection");
            $title .= 'subcounties ';
        } else if ($request->input('level') == 'facility') {
            $model = $model->selectRaw("view_facilitys.name as selection");
            $title .= 'facilities ';
        } else if ($request->input('level') == 'partners') {
            $model = $model->selectRaw("view_facilitys.partner as selection");
            $title .= 'partners ';
        }
        $model = $model->groupBy('selection');
        $dateString .= $title;
        return $model;
    }

    public function __getTestOutComes($request, &$dateString, &$excelColumns, &$title, &$briefTitle) {
        $months = [];
        $lab = Lab::find($request->lab);
        $below40 = $this->__getTestOutComesData($request,1);
        $below999 = $this->__getTestOutComesData($request,2);
        $above1000 = $this->__getTestOutComesData($request,3);
        $dbs = $this->__getTestOutComesData($request);
        // dd($dbs);
        foreach ($below40 as $key => $value) {
            $months[] = $value->month;
        }
        $newdataArray[] = ['Month', 'Outcomes', '', '', 'Total', 'DBS Samples'];
        $newdataArray[] = ['', '0 to 40', '41 to 999', 'Above 1000', ];
        $below40total = 0;
        $below999total = 0;
        $above1000total = 0;
        $dbstotal = 0;
        $last = 0;
        foreach ($months as $key => $value) {
            $current = 0;
            $last = $key;
            $data[$key]['month'] = date("F", mktime(null, null, null, $value));
            foreach ($below40 as $below40key => $below40value) {
                if ($value == $below40value->month) {
                    $data[$key]['below40'] = $below40value->samples;
                    $current += $below40value->samples;
                    $below40total += $below40value->samples;
                }
            }
            foreach ($below999 as $below999key => $below999value) {
                if ($value == $below999value->month) {
                    $data[$key]['below999'] = $below999value->samples;
                    $current += $below999value->samples;
                    $below999total += $below999value->samples;
                }
            }
            foreach ($above1000 as $above1000key => $above1000value) {
                if ($value == $above1000value->month) {
                    $data[$key]['above1000'] = $above1000value->samples;
                    $current += $above1000value->samples;
                    $above1000total += $above1000value->samples;
                }
            }
            $data[$key]['rowtotal'] = $current;
        }

        foreach ($months as $key => $value) {
            foreach ($dbs as $dbskey => $dbsvalue) {
                if ($value == $dbsvalue->month) {
                    $data[$key]['dbs'] = $dbsvalue->samples;
                    $dbstotal += $dbsvalue->samples;
                }
            }
        }
        $data[$last+1] = ['month' => 'Total', 'below40total' => $below40total, 'below999total' => $below999total, 'above1000total' => $above1000total, 'alltotal' => ($below40total + $below999total + $above1000total), 'dbstotal' => $dbstotal];
        
        foreach ($data as $report) {
            $newdataArray[] = $report;
        }
        // dd($newdataArray);
        $title = "$lab->labname Test Outcomes $request->year";
        $string = (strlen($lab->labname) > 31) ? substr($lab->labname,0,28).'...' : $lab->labname;
        $sheetTitle = "$string";

        ini_set("memory_limit", "-1");
        //Export Data
        Excel::create($title, function($excel) use ($newdataArray, $title, $sheetTitle) {
            $excel->setTitle($title);
            $excel->setCreator(auth()->user()->surname.' '.auth()->user()->oname)->setCompany('NASCOP');
            $excel->setDescription($title);
            
            $excel->sheet($sheetTitle, function($sheet) use ($newdataArray) {
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:D1');
                $sheet->mergeCells('E1:E2');
                $sheet->mergeCells('F1:F2');
                $sheet->fromArray($newdataArray, null, 'A1', false, false);
            });
             
        })->download('csv');
    }

    public function __getTestOutComesData($request, $type = null) {
        ini_set("memory_limit", "-1");
        $model = ViralsampleView::selectRaw("COUNT(*) as samples, MONTHNAME(datetested) as `monthname`, MONTH(datetested) as `month`")
                            ->WhereYear('datetested', $request->year)->where('lab_id', $request->lab)->where('repeatt', '=', 0)
                            ->groupBy('month')->groupBy('monthname')->orderBy('month','asc');
        if ($type == 1) {
            $model = $model->whereRaw("(rcategory = 1 OR result BETWEEN 0 AND 40)");
        } else if ($type == 2) {
            $model = $model->whereBetween('result', [41, 999]);
        } else if ($type == 3) {
            $model = $model->whereIn('rcategory', [3,4]);
        } else if ($type == null) {
            $model = $model->whereIn('sampletype', [3,4])->whereNotNull('result');
        }

        return $model->get();
    }

    public function __getLowLevelViremia($request, &$columns, &$title) {
        ini_set("memory_limit", "-1");
        $data = [
                    [
                        'range' => '0-200',
                        'dbs0to200' => $this->__getLowLevelViremiaData($request, 1, 2),
                        'plasma0to200' => $this->__getLowLevelViremiaData($request, 1, 1)
                    ],[ 
                        'range' => '201-400',
                        'dbs201to400' => $this->__getLowLevelViremiaData($request, 2, 2),
                        'plasma201to400' => $this->__getLowLevelViremiaData($request, 2, 1)
                    ],[ 
                        'range' => '401-500',
                        'dbs401to500' => $this->__getLowLevelViremiaData($request, 3, 2),
                        'plasma401to500' => $this->__getLowLevelViremiaData($request, 3, 1)
                    ],[ 
                        'range' => '501-600',
                        'dbs501to600' => $this->__getLowLevelViremiaData($request, 4, 2),
                        'plasma501to600' => $this->__getLowLevelViremiaData($request, 4, 1)
                    ],[ 
                        'range' => '601-800',
                        'dbs601to800' => $this->__getLowLevelViremiaData($request, 5, 2),
                        'plasma601to800' => $this->__getLowLevelViremiaData($request, 5, 1)
                    ],[ 
                        'range' => '801-999',
                        'dbs801to999' => $this->__getLowLevelViremiaData($request, 6, 2),
                        'plasma801to999' => $this->__getLowLevelViremiaData($request, 6, 1)
                    ]
                ];
        $columns = ['Result Ranges', 'DBS', 'Plasma'];
        foreach ($data as $report) {
            $newdataArray[] = $report;
        }

        $title = "National";
        if ($request->category == 'lab') {
            $lab = Lab::find($request->lab);
            $title = "$lab->labname";
        } elseif ($request->category == 'partner') {
            $partner = Partner::find($request->partner);
            $title = "$partner->name";
        } else if ($request->category == 'county') {
            $county = DB::table('countys')->find($request->county);
            $title = "$county->name";
        } else if ($request->category == 'subcounty') {
            $subcounty = DB::table('districts')->find($request->district);
            $title = "$subcounty->name";
        } else if ($request->category == 'facility') {
            $facility = ViewFacility::find($request->facility);
            $title = "$facility->name";
        }
        $title .= "  Low level Viremia data";

        if($request->period == "range") {
            $title .= " BETWEEN ".$request->fromDate." and ".$request->toDate;
        } else if ($request->period == "monthly") {
            $title .= " for ".$request->year." - ".$request->month;
        } else if ($request->period == "quarterly") {
            $title .= " for ".$request->year." - ".$request->quarter;
        } elseif ($request->period == "annually") {
            $title .= " for ".$request->year;
        }
        $title = strtoupper($title);
        $string = (strlen($title) > 31) ? substr($title,0,28).'...' : $title;
        $sheetTitle = "$string";
        
        return $newdataArray;
        // dd($newdataArray);
        
        //Export Data
        // Excel::create($title, function($excel) use ($newdataArray, $title, $sheetTitle) {
        //     $excel->setTitle($title);
        //     $excel->setCreator(auth()->user()->surname.' '.auth()->user()->oname)->setCompany('NASCOP');
        //     $excel->setDescription($title);
            
        //     $excel->sheet($sheetTitle, function($sheet) use ($newdataArray) {
        //         $sheet->fromArray($newdataArray, null, 'A1', false, false);
        //     });
             
        // })->download('csv');
        // Excel::download($title, function($excel) use ($newdataArray, $title, $sheetTitle) {
        //     $excel->setTitle($title);
        //     $excel->setCreator(auth()->user()->surname.' '.auth()->user()->oname)->setCompany('NASCOP');
        //     $excel->setDescription($title);
            
        //     $excel->sheet($sheetTitle, function($sheet) use ($newdataArray) {
        //         $sheet->fromArray($newdataArray, null, 'A1', false, false);
        //     });
             
        // }, 'csv');
        return Excel::download(new ReportExport($newdataArray), 'Low Level Viremia.csv');
    }

    public function __getLowLevelViremiaData($request, $result = null, $sampletype = null) {
        ini_set("memory_limit", "-1");

        $model = ViralsampleView::selectRaw("count(*) as samples")
                            ->where('repeatt', '=', 0)->whereNotNull('result')
                            ->when($request, function($query) use ($request){
                                if($request->period == "range") {
                                    $query = $query->whereBetween('datetested', [gmdate('Y-m-d', strtotime($request->fromDate)), gmdate('Y-m-d', strtotime($request->toDate))]);
                                } else if ($request->period == "monthly") {
                                    $query = $query->whereYear('datetested', $request->year)->whereMonth('datetested', $request->month);
                                } else if ($request->period == "quarterly") {
                                    $query = $query->whereYear('datetested', $request->year)
                                                    ->whereRaw("MONTH(datetested) IN (".self::$quarters[$request->quarter]['start'].", ".self::$quarters[$request->quarter]['end'].")");
                                } elseif ($request->period == "annually") {
                                    $query = $query->whereYear('datetested', $request->year);
                                }

                                if ($request->category == 'lab') {
                                    $query = $query->where('lab_id', '=', $request->lab);
                                } elseif ($request->category == 'partner') {
                                    $query = $query->join('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                                                    ->where('view_facilitys.partner_id', '=', $request->partner);
                                } else if ($request->category == 'county') {
                                    $query = $query->join('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                                                    ->where('view_facilitys.county_id', '=', $request->county);
                                } else if ($request->category == 'subcounty') {
                                    $query = $query->join('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                                                    ->where('view_facilitys.subcounty_id', '=', $request->district);
                                } else if ($request->category == 'facility') {
                                    $query = $query->where('facility_id', '=', $request->facility);
                                }
                            });

        if ($sampletype == 1) { //Plasma samples
            $model = $model->whereIn('sampletype', [1, 2, 5]);
        } else if ($sampletype == 2) { //DBS Samples
            $model = $model->whereIn('sampletype', [3, 4]);
        }

        if ($result == 1) { // Result 0-200
            $model = $model->whereBetween('result', [0, 200]);
        } else if ($result == 2) { // Result 201-400
            $model = $model->whereBetween('result', [201, 400]);
        } else if ($result == 3) { // Result 401-500
            $model = $model->whereBetween('result', [401, 500]);
        } else if ($result == 4) { // Result 501-600
            $model = $model->whereBetween('result', [501, 600]);
        } else if ($result == 5) { // Result 601-800
            $model = $model->whereBetween('result', [601, 800]);
        } else if ($result == 6) { // Result 801-999
            $model = $model->whereBetween('result', [801, 999]);
        }

        return $model->first()->samples;
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
            $selectStr = "$table.id, $table.original_batch_id, $table.patient, IF(lab.name IS NULL, poclab.name, lab.name) as labdesc, view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility, view_facilitys.facilitycode, $table.gender_description, $table.dob, $table.age, $table.sampletype_name as sampletype, $table.datecollected, $table.justification_name as justification, $table.datereceived, $table.datetested, $table.datedispatched, $table.initiation_date";

            if ($request->indicatortype == 2) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Reasons for Repeat', 'Rejected Reason', 'Regimen', 'Regimen Line', 'PMTCT', 'Result'];
                $selectStr .= ", $table.receivedstatus_name as receivedstatus, $table.reason_for_repeat, viralrejectedreasons.name as rejectedreason, $table.prophylaxis_name as regimen, viralregimenline.name as regimenline, viralpmtcttype.name as pmtct, $table.result";
                
                $title .= "vl TEST OUTCOMES FOR ";
                $briefTitle .= "vl TEST OUTCOMES ";
            }else if ($request->indicatortype == 100) {
                $excelColumns = ['System ID', 'Batch','Patient CCC No', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age', 'Sample Type', 'Date Collected', 'Justification', 'Date Received', 'Date Tested', 'Date Dispatched', 'ART Initiation Date', 'Received Status', 'Reasons for Repeat', 'Regimen', 'Result', 'Recency Number'];
                $selectStr .= ", $table.receivedstatus_name as receivedstatus, $table.reason_for_repeat, $table.prophylaxis_name as regimen, $table.result, recency_number";
                
                $title .= "vl RECENCY TEST OUTCOMES FOR ";
                $briefTitle .= "vl RECENCY TEST OUTCOMES ";
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
                $excelColumns = ['County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Remote Logged Samples', 'Total Samples'];
                $selectStr =  "view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility , view_facilitys.facilitycode, COUNT(IF($table.site_entry = 1, 1, null)) as remotelogged, COUNT($table.id) as totaltests";

                $title .= "VL SITES DIONG REMOTE SAMPLE ENTRY FOR ";
                $briefTitle .= "vl SITES DIONG REMOTE SAMPLE ENTRY for";
            }

            $model = ViralsampleCompleteView::selectRaw($selectStr)
				->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
				->where("$table.flag", '=', 1)->where("$table.facility_id", '<>', 7148);

            if (!($request->indicatortype == 9)) {
                $model = $model->where('repeatt', '=', 0);
            }
            if (in_array($request->indicatortype, [2,4,5,6,100]) && $request->input('category') != 'poc') 
                $model = $model->leftJoin('labs as lab', 'lab.id', '=', "$table.lab_id");
            else if (in_array($request->indicatortype, [2,4,5,6,100]) && $request->input('category') == 'poc')
                $model = $model->leftJoin('view_facilitys as lab', 'lab.id', '=', "$table.lab_id");

            if($request->indicatortype == 100) $model = $model->where("$table.justification", "=", 12);
            if (in_array($request->indicatortype, [2,5]))
                $model = $model->leftJoin('viralrejectedreasons', 'viralrejectedreasons.id', '=', "$table.rejectedreason");
            if ($request->indicatortype == 2 || $request->indicatortype == 4 || $request->indicatortype == 6)
                $model = $model->leftJoin('viralpmtcttype', 'viralpmtcttype.id', '=', "$table.pmtct")
                                ->leftJoin('viralregimenline', 'viralregimenline.id', '=', "$table.regimenline");

            if ($request->indicatortype == 5) {
                $model = $model->where("$table.receivedstatus", "=", 2);
            } else if ($request->indicatortype == 4) {
                $model = $model->whereIn("$table.rcategory", [3,4]);
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
                $model = $model->groupBy('facility')
                                ->groupBy('facilitycode')
                                ->groupBy('subcounty')
                                ->groupBy('county')
                                ->orderBy('totaltests', 'desc');
            }

            if(null !== $request->input('age')){
                $model = $model->when(true, function($query) use ($request){
                            if ($request->input('age') == 2)
                                return $query->where('age_category', '=', 6);
                            if ($request->input('age') == 3)
                                return $query->where('age_category', '=', 7);
                            if ($request->input('age') == 4)
                                return $query->where('age_category', '=', 8);
                            if ($request->input('age') == 5)
                                return $query->where('age_category', '=', 9);
                            if ($request->input('age') == 6)
                                return $query->where('age_category', '=', 10);
                            if ($request->input('age') == 7)
                                return $query->where('age_category', '=', 11);
                        });
                if ($request->input('age') == 2)
                    $title .= " less 2 ";
                if ($request->input('age') == 3)
                    $title .= " 2 - 9 ";
                if ($request->input('age') == 4)
                    $title .= " 10 - 14 ";
                if ($request->input('age') == 5)
                    $title .= " 15 - 19 ";
                if ($request->input('age') == 6)
                    $title .= " 20 - 24 ";
                if ($request->input('age') == 7)
                    $title .= " above 25 ";

            }
    	} else if ($request->testtype == 'EID') {
            $table = 'sample_complete_view';
            $selectStr = "$table.id, $table.patient, $table.original_batch_id, IF(lab.name IS NULL, poclab.name, lab.name) as labdesc, view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility, view_facilitys.facilitycode, $table.gender_description, $table.dob, $table.age, pcrtype.alias as pcrtype, IF($table.pcrtype=4, $table.enrollment_ccc_no, null) as enrolment_ccc_no, $table.datecollected, $table.datereceived, $table.datetested, $table.datedispatched";

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

                $selectStr .= ", ir.name as infantresult, hv.desc as hei_validation, hc.name as enrollment_status, $table.dateinitiatedontreatment, $table.ccc_no, $table.otherreason";
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
                $excelColumns = ['County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Remote Logged Samples', 'Total Samples'];
                $selectStr =  "view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility , view_facilitys.facilitycode, COUNT(IF($table.site_entry = 1, 1, null)) as remotelogged, COUNT($table.id) as totaltests";

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
            if (($request->indicatortype == 1 || $request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4 || $request->indicatortype == 5 || $request->indicatortype == 6 || $request->indicatortype == 8) && $request->input('category') != 'poc')
                $model = $model->leftJoin('labs as lab', 'lab.id', '=', "$table.lab_id");
            else if(($request->indicatortype == 1 || $request->indicatortype == 2 || $request->indicatortype == 3 || $request->indicatortype == 4 || $request->indicatortype == 5 || $request->indicatortype == 6 || $request->indicatortype == 8) && $request->input('category') == 'poc')
                $model = $model->leftJoin('view_facilitys as lab', 'lab.id', '=', "$table.lab_id");

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

            if (!($request->indicatortype == 5 || $request->indicatortype == 9)) {
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
                $model = $model->groupBy('facility')
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
            } else if ($request->indicatortype == 16) {
                // $model = ;
            } else {
                
            }
        } else {
            return back();
        }
        
        $model = $model->leftJoin('view_facilitys as poclab', 'poclab.id', '=', "$table.lab_id");

        
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
            } else if ($request->category == 'partner'){
                $model = $model->where('view_facilitys.partner_id', '=', $request->partner);
                $partner = Partner::where('id', '=', $request->partner)->first();
                $title .= $partner->name;
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
                    } elseif (auth()->user()->level ==93) {//MobileWAChX
                        $model = $model->where('view_facilitys.partner_id7', '=', auth()->user()->level);
                    } else  { //boresha
                        $model = $model->where('view_facilitys.partner_id2', '=', auth()->user()->level);
                    }
                }
            } else if ($request->category == 'poc') {
                $model = $model->where('site_entry', '=', 2);
                $title .= 'POC';
            }
        }

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
        // dd($model->toSql());
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
             
        })->download('csv');
    }

    public function remote_login($testtype = 'EID', $year = null, $month = null){
        if (!in_array(strtoupper($testtype), $this->testtypes)) {
            session(['toast_message' => 'Invaid parameters received', 'toast_error' => 1]);
            return back();
        }
        $year = strtolower($year);
        if (!($year == 'null' || $year == null)) {
            if (isset($year) && ($year < 2010) || ($year > date('Y'))){
                session(['toast_message' => 'Incorrect date values provided', 'toast_error' => 1]);
                return back();
            } 
        }        

        if ($year==null || $year=='null'){
            if (session('remoteloginyear')==null)
                session(['remoteloginyear' => Date('Y')]);
        } else {
            session(['remoteloginyear'=>$year]);
        }

        if ($month==null || $month=='null'){
            session()->forget('remoteloginmonth');
        } else {
            session(['remoteloginmonth'=>$month]);
        }
        $year = session('remoteloginyear');
        $month = session('remoteloginmonth');
        $monthName = "";
        
        if (null !== $month) 
            $monthName = "- ".date("F", mktime(null, null, null, $month));

        $testtypes = [
                'EID' => ['class' => SampleView::class, 'table' => 'samples_view'],
                'VL' => ['class' => ViralsampleView::class, 'table' => 'viralsamples_view']
            ];
        $class = $testtypes[$testtype]['class'];
        $table = $testtypes[$testtype]['table'];
        $samples = $class::selectRaw("labs.id, labs.labdesc, year(datereceived) as `year`, monthname(datereceived) as `actualmonth`, month(datereceived) as `month`, count(*) as `samples`")
                        ->join('labs', 'labs.id', '=', $table.'.lab_id')
                        ->whereYear('datereceived', $year)->where('site_entry', '<>', 2)
                        ->when($month, function($query) use ($month){
                            return $query->whereMonth('datereceived', $month);
                        })->groupBy('id')->groupBy('year')->groupBy('month')->groupBy('actualmonth')
                        ->orderBy("month", "asc")->orderBy("year", "asc")->get();
        $remotesamples = $class::selectRaw("labs.id, year(datereceived) as `year`, month(datereceived) as `month`, count(*) as `samples`")
                        ->join('labs', 'labs.id', '=', $table.'.lab_id')
                        ->whereYear('datereceived', $year)->where('site_entry', '=', 1)
                        ->when($month, function($query) use ($month){
                            return $query->whereMonth('datereceived', $month);
                        })->groupBy('id')->groupBy('year')->groupBy('month')
                        ->orderBy("month", "asc")->orderBy("year", "asc")->get();
        // dd($remotesamples);
        $data = [];
        $labs = Lab::get();
        foreach ($labs as $key => $lab) {
            $totallogged = $samples->where('id', $lab->id);
            $remotelogged = $remotesamples->where('id', $lab->id);
            foreach ($totallogged as $key => $total) {
                $remote = $remotelogged->where('year', $total->year)->where('month', $total->month);
                if ($remote->isEmpty()){
                    $data[] = (object)[
                        'labname' => $lab->labdesc, 'year' => $total->year, 'month' => $total->actualmonth, 'monthNo' => $total->month,
                        'remotelogged' => 0,
                        'totallogged' => $total->samples ?? 0
                    ];
                } else {
                    $remote = $remote->first();
                    $data[] = (object)[
                        'labname' => $lab->labdesc, 'year' => $total->year, 'month' => $total->actualmonth, 'monthNo' => $total->month,
                        'remotelogged' => $remote->samples ?? 0,
                        'totallogged' => $total->samples ?? 0
                    ];
                }
            }
        }

        $data['sampleslogs'] = collect($data)->shuffle()->sortBy('year')->sortBy('monthNo');
        $data['testtype'] = $testtype;
        $data['year'] = $year;
        $data['month'] = $monthName;

        return view('tables.remoteloginreport', $data)->with('pageTitle', 'Remote Login Reports '. $year . $monthName);
    }

    public static function __getExcel($data, $title, $dataArray, $briefTitle)
    {
        $newdataArray = [];
        $finaldataArray = [];
        $sheetTitle = [];
        $mergeCellsArray = [];
        ini_set("memory_limit", "-1");
        $withSheets = false;
        if (is_array($data)) {
            $count = 0;
            $withSheets = true;
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
            $titleArray = $dataArray;
            $data = $data->get();
            // dd($data);
            if($data->isNotEmpty()) {
                // $newdataArray[] = $dataArray;
                // foreach ($data as $report) {
                //     $newdataArray[] = $report->toArray();
                // }
            } else {
                $newdataArray[] = [];
            }
            $sheetTitle[] = 'Sheet1';
            $finaldataArray = $data->toArray();
        }
        
        return $finaldataArray;
        // if($withSheets)
        //     return (new ReportExportWithSheets($titleArray, $data))->download($title);
        // else
        //     return (new ReportExport)->download($title . '.csv', \Maatwebsite\Excel\Excel::CSV);
        // dd($finaldataArray);
        /****** Has multiple sheets options *****/
        // return Excel::download(new ReportExport($finaldataArray), "$title.csv");
        // return Excel::download(new ReportExport, "$title.csv");
        // return (new ReportExport($finaldataArray))->download("$title.csv");
        // return Excel::download(new ReportGenericExport, "$title.csv");

        // Excel::create($title, function($excel) use ($finaldataArray, $title, $sheetTitle) {
        //     $excel->setTitle($title);
        //     $excel->setCreator(auth()->user()->surname.' '.auth()->user()->oname)->setCompany('NASCOP');
        //     $excel->setDescription($title);
        //     foreach ($finaldataArray as $key => $value) {
        //         $stitle = $sheetTitle[$key];
        //         $excel->sheet($stitle, function($sheet) use ($value) {
        //             $sheet->fromArray($value, null, 'A1', false, false);
        //         });
        //     }
        // })->download('csv');
    }

    public function setup(Request $request) {
        if ($request->method() == 'POST') {
            foreach($request->input('partner_report_id') as $report){
                $permission = ReportPermission::where('partner_report_id', $report)->where('user_type_id', $request->input('user_type_id'))->get();
                if($permission->isEmpty()) {
                    $permission = new ReportPermission;
                    $permission->fill(['partner_report_id' => $report, 'user_type_id' => $request->input('user_type_id')])->save();
                }
            }
            return back();
        } else {
            $data['categroies'] = ReportCategory::with(['reports'])->get();
            $data['reports'] = PartnerReport::get();
            $data['usertypes'] = UserType::get();
            
            return view('tables.setup', $data)->with('pageTitle', 'Reports Setup');
        }
    }


    public static function __dupgetExcel($data, $title, $dataArray)
    {
        ini_set("memory_limit", "-1");
        if($data->isNotEmpty()) {
            $newdataArray[] = $dataArray;
            foreach ($data as $report) {
                $newdataArray[] = $report->toArray();
            }
            return Excel::download(new ReportExport($newdataArray), $title . '.csv');
            
            // Excel::create($title, function($excel) use ($newdataArray, $title) {
            //     $excel->setTitle($title);
            //     $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('NASCOP.ORG');
            //     $excel->setDescription($title);

            //     $excel->sheet($title, function($sheet) use ($newdataArray) {
            //         $sheet->fromArray($newdataArray, null, 'A1', false, false);
            //     });
            // })->download('csv');
        } else {
            session(['toast_message' => 'No data available for the criteria provided']);
        }
    }
}
