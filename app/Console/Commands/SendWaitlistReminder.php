<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class SendWaitlistReminder extends Command
{
    protected $signature = 'waitlist:send-reminders';
    protected $description = 'Send monthly reminders to waitlist contacts';

    public function handle()
    {
        ini_set('max_execution_time', getenv('SCRIPT_TIMEOUT') ?: 90); // Default to 90 seconds if not set

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

                // Hash the opportunity ID
                $hashedOpportunityId = Crypt::encryptString((string) $opportunity->OpportunityID);

                try {
                    // Generate the update link
                    $updateLink = '<a href="https://inquiry.saplingsearlylearning.com/waitlist/update/' . $hashedOpportunityId . '" target="_blank" style="color: white; text-decoration: none; background-color: #007BFF; padding: 10px 20px; border-radius: 5px; display: inline-block;">Update Information or Opt Out</a>';

                    // Add the PNG logo using a table structure
                    $logo = '
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td align="center" style="padding: 20px 0;">
                          <img src="https://inquiry.saplingsearlylearning.com/images/Saplings_Logo_Linear_For_White.png"
                               alt="Saplings Early Learning Centres"
                               width="150"
                               height="40"
                               style="display: block; width: 150px; height: auto; max-width: 100%;" />
                        </td>
                      </tr>
                    </table>';

                    // Combine the logo and update link into the email content
                    $emailContent = $logo . '<br><br>' . $updateLink;

                    // Send email with the logo and update link
                    Mail::to($assignedToEmail)
                        ->send((new \App\Mail\WaitlistReminderMail($emailContent))
                            ->from('waitlist@saplingsearlylearning.com', 'Saplings Waitlist')); // Set "from" in the Mailable

                    Log::info('Email sent to:', ['email' => $assignedToEmail]); // Log email sent
                    echo "Email sent to: $assignedToEmail\n"; // Echo result to screen

                    // Log to a file
                    file_put_contents(storage_path('logs/sent_emails.log'), "Email sent to: $assignedToEmail\n", FILE_APPEND);
                } catch (\Exception $mailException) {
                    Log::error('Failed to send email:', [
                        'email' => $assignedToEmail,
                        'error' => $mailException->getMessage()
                    ]);
                    echo "Failed to send email to: $assignedToEmail. Error: " . $mailException->getMessage() . "\n"; // Echo error to screen
                }
            }

            $this->info('Waitlist reminders sent successfully.');
        } catch (\Exception $e) {
            Log::error('Error in SendWaitlistReminder command: ' . $e->getMessage());
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
