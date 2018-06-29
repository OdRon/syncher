<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SampleView;
use App\ViralsampleView;
use App\Batch;
use App\Viralbatch;
use App\Lookup;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->user_type_id == 1)
            return redirect('users');
            // return view('dashboard.home')->with('pageTitle', 'Dashboard');
        if (auth()->user()->user_type_id == 8) {
            $batch = session('batcheLoggedInWith');
            if (isset($batch['eid'])) {
                $batch = $batch['eid'];
                $data['batch'] = Batch::where('id', '=', $batch)->get()->first();
                $data['samples'] = SampleView::where('batch_id', '=', $batch)->get();
                // $data = Lookup::get_lookups();

                $data = (object) $data;
                // dd($data);
                return view('tables.batch_details', compact('data'))->with('pageTitle', "EID Batch :: $batch");
            } else {
                $batch = $batch['vl'];
                $samples = SampleView::where('batch_id', '=', $batch)->get();

                return view()->with('pageTitle', "VIRAL LOAD Batch :: $batch");
            }
            
            dd($samples);
        }
        
        return redirect('reports/EID');
    }

    public function countysearch(Request $request)
    {
        $search = $request->input('search');
        $county = DB::table('countys')->select('id', 'name', 'letter as facilitycode')
            ->whereRaw("(name like '%" . $search . "%')")
            ->paginate(10);
        return $county;
    }
}
