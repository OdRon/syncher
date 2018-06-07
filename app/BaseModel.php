<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
	protected $guarded = [];

    public function pre_update()
    {
        if($this->synched == 1 && $this->isDirty()) $this->synched = 2;
        $this->save();
    }

    public function pre_delete()
    {
        if($this->synched == 1){
            $this->synched = 3;
        }else{
            $this->delete();
        }        
    }
}
