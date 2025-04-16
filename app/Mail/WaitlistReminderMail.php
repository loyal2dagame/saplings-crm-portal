<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class WaitlistReminderMail extends Mailable
{
    public $contactId;

    public function __construct($contactId)
    {
        $this->contactId = $contactId;
    }

    public function build()
    {
        $url = url("/waitlist/update/{$this->contactId}");
        return $this->subject('Update Your Waitlist Information')
            ->view('emails.waitlist_reminder', ['url' => $url]);
    }
}
