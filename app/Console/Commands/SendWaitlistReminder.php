<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWaitlistReminder extends Command
{
    protected $signature = 'waitlist:send-reminders';
    protected $description = 'Send monthly reminders to waitlist contacts';

    public function handle()
    {
        try {
            $apiUsername = env('GREENROPE_USERNAME');
            $apiPassword = env('GREENROPE_PASSWORD');

            // Authenticate and get token
            $authResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $apiUsername,
                'password' => $apiPassword,
                'xml' => '<GetAuthTokenRequest></GetAuthTokenRequest>',
            ]);

            $authXml = simplexml_load_string($authResponse->body());
            if ($authXml->Result != 'Success') {
                $this->error('Authentication failed: ' . $authXml->ErrorText);
                return;
            }

            $authToken = (string) $authXml->Token;

            // Fetch opportunities (no PhaseID filter here, filter later in the loop)
            $getOpportunitiesXml = "<GetOpportunitiesRequest></GetOpportunitiesRequest>";

            $opportunitiesResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $apiUsername,
                'auth_token' => $authToken,
                'xml' => $getOpportunitiesXml,
            ]);

            $opportunitiesXml = simplexml_load_string($opportunitiesResponse->body());
            if ($opportunitiesXml->Result != 'Success') {
                $this->error('Failed to fetch opportunities: ' . $opportunitiesXml->ErrorText);
                return;
            }

            $processedContacts = []; // Track processed contacts

            foreach ($opportunitiesXml->Opportunities->Opportunity as $opportunity) {
                // Filter by PhaseID
                if ((string) $opportunity->PhaseID !== '1') {
                    continue; // Skip if PhaseID is not 1
                }

                $assignedToEmail = (string) $opportunity->AssignedToEmail;

                // Validate AssignedToEmail before proceeding
                if (empty($assignedToEmail)) {
                    Log::warning('Skipping opportunity with missing AssignedToEmail', ['opportunityId' => (string) $opportunity->OpportunityID]);
                    continue;
                }

                // Skip if contact has already been processed
                if (in_array($assignedToEmail, $processedContacts)) {
                    Log::info('Skipping duplicate contact:', ['email' => $assignedToEmail]);
                    continue;
                }

                $processedContacts[] = $assignedToEmail; // Mark contact as processed

                // Send email
                Mail::to($assignedToEmail)->send(new \App\Mail\WaitlistReminderMail((string) $opportunity->OpportunityID));
                Log::info('Email sent to:', ['email' => $assignedToEmail]); // Log email sent
            }

            $this->info('Waitlist reminders sent successfully.');
        } catch (\Exception $e) {
            Log::error('Error in SendWaitlistReminder command: ' . $e->getMessage());
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
