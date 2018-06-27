<?php
namespace App\Http\ViewComposers;

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
        $usertype = auth()->user()->user_type_id;
        if ($usertype == 6) {
            $data = (object)['name'=>'National'];
        } else {
            $user = ViewFacility::when($usertype, function ($query) use ($usertype){
                                if ($usertype == 3)
                                    return $query->where('partner_id', '=', auth()->user()->level);
                                if ($usertype == 4)
                                    return $query->where('county_id', '=', auth()->user()->level);
                                if ($usertype == 5)
                                    return $query->where('subcounty_id', '=', auth()->user()->level);
                            })->get()->first();
            if ($usertype == 3) 
                $data = (object)['name'=>$user->partner];
            if ($usertype == 4) 
                $data = (object)['name'=>$user->county.' - County'];
            if ($usertype == 5) 
                $data = (object)['name'=>$user->subcounty.' - Sub-County'];
        }
        
        $view->with('user', $data);
    }

}