<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $credentials;

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
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->credentials)->view('mail.passwordAnnouncement');
    }
}
