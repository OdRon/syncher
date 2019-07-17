<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RandomController extends Controller
{

	public function test_connection()
	{
		echo \App\Synch::test_connection();
	}
}
