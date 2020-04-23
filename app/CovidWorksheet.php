<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidWorksheet extends BaseModel
{

	protected $connection = 'covid';

	protected $dates = ['datecut', 'datereviewed', 'datereviewed2', 'dateuploaded', 'datecancelled', 'daterun'];
}
