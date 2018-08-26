<?php
namespace App\Http\ViewComposers;

use DB;
use Illuminate\View\View;
use App\ViewFacility;

/**
* 
*/
class GeneralComposer
{
	

	/**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = [];
        $usertype = auth()->user()->user_type_id;

        if ($usertype == 1) {
            $data = (object)['name'=>'System Administrator'];
        } else if ($usertype == 2) {
            $data = (object)['name'=>'Program Officer'];
        } else if ($usertype == 6) {
            $data = (object)['name'=>'National'];
        } else if ($usertype == 7) {
            $data = DB::table('partners')->where('id', '=', auth()->user()->level)->first();
        } else if ($usertype == 9) {
            $data = (object)['name'=>'Maryland Support Team'];
        } else {
            $user = ViewFacility::when($usertype, function ($query) use ($usertype){
                                if ($usertype == 3)
                                    return $query->where('partner_id', '=', auth()->user()->level);
                                if ($usertype == 4)
                                    return $query->where('county_id', '=', auth()->user()->level);
                                if ($usertype == 5)
                                    return $query->where('subcounty_id', '=', auth()->user()->level);
                                if ($usertype == 8)
                                    return $query->where('id', '=', auth()->user()->facility_id);
                            })->get()->first();
            if ($usertype == 3) 
                $data = (object)['name'=>$user->partner];
            if ($usertype == 4) 
                $data = (object)['name'=>$user->county.' - County'];
            if ($usertype == 5) 
                $data = (object)['name'=>$user->subcounty.' - Sub-County'];
            if ($usertype == 8) 
                $data = (object)['name'=>$user->name];
        }
        // dd($data);
        $view->with('user', $data);
    }

}