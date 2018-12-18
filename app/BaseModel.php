<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
	protected $guarded = [];

    

    public function my_date_format($value=null, $format='d-M-Y')
    {
        if($this->$value) return date($format, strtotime($this->$value));

        return '';
    }

    public function my_string_format($value, $default='0')
    {
        if($this->$value) return (string) $this->$value;
        return $default;
    }


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
