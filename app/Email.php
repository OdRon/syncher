<?php

namespace App;


use App\BaseModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Mail\CustomMail;
use App\Mail\CustomEmailFiles;
use Exception;
use DB;

class Email extends BaseModel
{
    use SoftDeletes;

    public function county()
    {
        return $this->belongsTo('App\County');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the user's full name
     *
     * @return string
     */
    public function getContentAttribute()
    {
        return $this->get_raw();
    }

    public function getSendingHourAttribute()
    {
        if($this->time_to_be_sent) return date('H', strtotime($this->time_to_be_sent));
        return null;
    }

    public function getSendingDayAttribute()
    {
        if($this->time_to_be_sent) return date('Y-m-d', strtotime($this->time_to_be_sent));
        return null;
    }

    public function demo_email($recepient)
    {
        $this->save_blade();
        $comm = new CustomMail($this, null);
        Mail::to([$recepient])->send($comm);
        $this->delete_blade();
    }

    public function dispatch()
    {
        $this->save_blade();
        ini_set("memory_limit", "-1");


		$partner_contacts = DB::table('vl_partner_contacts_for_alerts')
            ->when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 2)->get();
        
        $this->sent = true;
        $this->save();

		foreach ($partner_contacts as $key => $contact) {

	        $cc_array = $this->comma_array($this->cc_list);
	        $bcc_array = $this->comma_array($this->bcc_list);
	        $bcc_array = array_merge($bcc_array, ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org']);


	        foreach ($contact as $column_name => $value) {
	        	if(str_contains($column_name, 'ccc') && str_contains($value, ['@']) && !str_contains($value, ['jbatuka'])) $cc_array[] = trim($value);
	        	if(str_contains($column_name, 'bcc') && str_contains($value, ['@']) && !str_contains($value, ['jbatuka'])) $bcc_array[] = trim($value);
	        }
        	$comm = new CustomMail($this, $contact);
	        if(env('APP_ENV') == 'production'){
		        try {
			        Mail::to(trim($contact->mainrecipientmail))->cc($cc_array)->bcc($bcc_array)->send($comm);
		        } catch (Exception $e) {
		        	
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send($comm);
		    }
		}

        $this->send_files();
        $this->delete_blade();
    }

    public function send_files()
    {
        $comm = new CustomEmailFiles($this);
        $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
        Mail::to($mail_array)->send($comm);
    }

    public function save_raw($email_string)
    {
    	if(!is_dir(storage_path('app/emails'))) mkdir(storage_path('app/emails'), 0777, true);

    	$filename = storage_path('app/emails') . '/' . $this->id . '.txt';

    	file_put_contents($filename, $email_string);
    }

    public function get_raw()
    {
    	if(!is_dir(storage_path('app/emails'))) mkdir(storage_path('app/emails'), 0777, true);

    	$filename = storage_path('app/emails') . '/' . $this->id . '.txt';
    	if(!file_exists($filename)) return null;
    	return file_get_contents($filename);
    }

    public function save_blade()
    {
    	$filename = storage_path('app/emails') . '/' . $this->id . '.txt';
    	$blade = base_path('resources/views/emails') . '/' . $this->id . '.blade.php';

    	$str = file_get_contents($filename);
        $recepient = '{{ $contact->mainrecipient ?? ' . "'(Main Recepient Name Here)'"  . ' }}';
        $str = str_replace(':recepient', $recepient, $str);
    	if($this->lab_signature && $this->lab_id) $str .= " @include('emails.lab_signature') ";
    	file_put_contents($blade, $str);
    }

    public function delete_blade()
    {
    	$blade = base_path('resources/views/emails') . '/' . $this->id . '.blade.php';
    	unlink($blade);
    }

    public function delete_raw()
    {
        $filename = storage_path('app/emails') . '/' . $this->id . '.txt';
        if(file_exists($filename)) unlink($filename);
    }

    public function comma_array($str)
    {
        if(!$str || $str == '') return [];
        $emails = explode(',', $str);

        $mail_array = [];

        foreach ($emails as $key => $value) {
            if(str_contains($value, '@')) $mail_array[] = trim($value);
        }
        return $mail_array;
    }
}
