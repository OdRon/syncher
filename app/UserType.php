<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    public function report_permissions(){
    	return $this->hasMany('App\ReportPermission');
    }

    public function reports () {
    	$permissions = $this->report_permissions;
    	$reports = [];
    	foreach ($permissions as $key => $permission) {
    		dd($permission);
    		$reports[] = $permission->reports;
    	}
    	return $reports;
    }
}
