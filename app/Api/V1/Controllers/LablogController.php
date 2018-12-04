<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use DB;

class LablogController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function lablogs(BlankRequest $request)
    {
        $data = $request->input('data');
        $data['dateupdated'] = date('Y-m-d H:i:s');
        $lab_id = $request->input('lab_id');

        DB::table('apidb.lablogs')->where('logdate', date('Y-m-d'))
                            ->where('lab', $lab_id)
                            ->where('testtype', $data['testtype'])
                            ->update($data);

        return response()->json([
          'status' => 'ok',
        ], 200);
    }
}

