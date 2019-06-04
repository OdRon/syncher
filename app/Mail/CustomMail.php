<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $contact;
    public $lab;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $contact=null)
    {
        $this->email = $email;
        $this->contact = $contact;
        $this->lab = \App\Lab::find($email->lab_id);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view_name = 'emails.' . $this->email->id;
        $from = env('MAIL_FROM_NAME');
        if($this->email->from_name != '') $from = $this->email->from_name;
        return $this->subject($this->email->subject)->from(env('MAIL_FROM_ADDRESS'), $from)->view($view_name);
    }
}
