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
        // Construct the full URL using the hashedOpportunityId
        $baseUrl = rtrim(env('APP_URL', 'http://localhost'), '/'); // Ensure no trailing slash
        $updateLink = $baseUrl . '/waitlist/update/' . $this->contactId;

        return $this->subject('Update Your Waitlist Information')
            ->view('emails.waitlist_update', ['updateLink' => $updateLink]);
    }
}
