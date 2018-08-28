<?php

namespace App\OldModels;

use App\OldModels\ViewModel;

class User extends ViewModel
{
	protected $table = 'users';
	protected $connection = 'eid';
}