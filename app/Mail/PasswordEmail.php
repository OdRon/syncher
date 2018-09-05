<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $credentials;
    public $title;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_id)
    {
        $user = \App\User::where('id', $user_id)->first();
        $this->credentials = (object)[
        						'name' => $user->surname . ' ' .$user->oname,
        						'email' => $user->email,
        						'password' => '12345678'
        					];
        $this->title = 'EID/VL SYTEM UPDATE';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->title)->view('mail.passwordAnnouncement');
    }
}
