<?php

namespace App;

use OldSampleView;
use Mother;
use Patient;
use Batch;
use Sample;

class SynchEid extends Model
{

	public static function synch()
	{
		
		$offset_value = 0;
		while(true)
		{
			$samples = OldSampleView::limit(100)->offset($offset_value)->get();
			if($samples->isEmpty()) break;

			
		}

	}
}
