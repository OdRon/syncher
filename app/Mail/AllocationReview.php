<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AllocationReview extends Mailable
{
    use Queueable, SerializesModels;

    public $allocation;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($allocation)
    {
        $this->allocation = $allocation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.name');
    }
}
