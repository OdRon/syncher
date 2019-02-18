<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sample;
use App\Viralsample;
use App\Synch;
use App\Lookup;

class SamplesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($testtype = 'EID', $id)
    {
        $testtype = strtolower($testtype);
        if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);
        $prefix = 'eid';
        if($testtype == 'vl') $prefix = 'viral';
        $sample_class = Synch::$synch_arrays[$testtype]['sample_class'];
        $sample = $sample_class::findOrFail($id);

        $lookups = 'get_'.$prefix.'_lookups';
        $data = Lookup::$lookups();
        $data['sample'] = $sample;
        $data['testtype'] = strtoupper($testtype);
        $data = (object)$data;
        // dd($data);
        return view('forms.sample', compact('data'))->with("Update {$testtype} Sample {$sample->id} ");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $testtype = 'EID', $id)
    {
        $testtype = strtolower($testtype);
        if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);
        $sample_class = Synch::$synch_arrays[$testtype]['sample_class'];
        $sample = $sample_class::findOrFail($id);
        if ($testtype == 'eid') {
            $sample->pcrtype = $request->input('pcrtype');
            $sample->pre_update();
            $patient = $sample->patient;
            $patient->dob = $request->input('dob');
            $patient->pre_update();
        } else if ($testtype == 'vl') {

        }
        session(['toast_message' => 'Sample successfully updated']);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
