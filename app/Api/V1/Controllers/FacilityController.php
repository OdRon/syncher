<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Jobs\NewFacility;

use App\Facility;

class FacilityController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlankRequest $request)
    {
        $facility_data = json_decode($request->input('facility'));
        $lab_id = $request->input('lab_id');
        $facility = new Facility;
        $facility->fill($facility_data);
        unset($facility->id);
        $facility->save();

        NewFacility::dispatch($facility);

        return response()->json([
          'status' => 'ok',
          // 'facility' => $facility,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function show(Facility $facility)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function update(BlankRequest $request, Facility $facility)
    {
        $data = json_decode($request->input('facility'));
        $facility->fill($data);
        $facility->save();

        NewFacility::dispatch($facility, true);

        return response()->json([
          'status' => 'ok',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function destroy(Facility $facility)
    {
        //
    }
}

