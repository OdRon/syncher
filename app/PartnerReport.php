<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerReport extends BaseModel
{
    public function permissions () {
    	return $this->hasMany('App\ReportPermission');
    }

    public function user_types () {
    	$permissions = $this->permissions;
    	$user_types = [];
    	foreach ($permissions as $key => $permission) {
    		$user_types[] = $permission->user_type;
    	}
    	return $user_types;
    }
}
