<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\WaitlistReminderMail;

class TestEmailController extends Controller
{
    public function sendTestEmail()
    {
        $testContactId = 12345; // Replace with a mock or test contact ID
        $testEmail = 'chris@zoominlive.com'; // Replace with your email address for testing

        // Send the test email
        Mail::to($testEmail)->send(new WaitlistReminderMail($testContactId));

        return 'Test email sent to ' . $testEmail;
    }
}
