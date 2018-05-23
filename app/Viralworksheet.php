<?php

namespace App;

use App\BaseModel;

class Viralworksheet extends BaseModel
{

    public function scopeLocate($query, $original)
    {
        return $query->where(['original_worksheet_id' => $original->id, 'lab_id' => $original->lab_id]);
    }
}
