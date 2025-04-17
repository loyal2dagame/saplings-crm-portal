<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaitlistController extends Controller
{
    private $apiUsername;
    private $apiPassword;

    public function __construct()
    {
        $this->apiUsername = env('GREENROPE_USERNAME');
        $this->apiPassword = env('GREENROPE_PASSWORD');
    }

    public function edit($opportunityId)
    {
        try {
            // Authenticate and get token
            $authResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'password' => $this->apiPassword,
                'xml' => '<GetAuthTokenRequest></GetAuthTokenRequest>',
            ]);

            Log::info('Auth Response:', ['body' => $authResponse->body()]);

            if (!$this->isValidXml($authResponse->body())) {
                Log::error('Authentication failed: Non-XML response received.', ['response' => $authResponse->body()]);
                return redirect('/')->with('error', 'Authentication failed.');
            }

            $authXml = simplexml_load_string($authResponse->body());
            if ($authXml->Result != 'Success') {
                return redirect('/')->with('error', 'Authentication failed: ' . $authXml->ErrorText);
            }

            $authToken = (string) $authXml->Token;

            // Step 1: Fetch opportunity data using the specific OpportunityID
            $getOpportunityXml = "<GetOpportunitiesRequest opportunity_id=\"{$opportunityId}\"></GetOpportunitiesRequest>";
            Log::info('Request XML (Step 1):', ['xml' => $getOpportunityXml]);

            $opportunityResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $getOpportunityXml,
            ]);

            Log::info('GetOpportunities Response (Step 1):', ['body' => $opportunityResponse->body()]);

            if (!$this->isValidXml($opportunityResponse->body())) {
                Log::error('GetOpportunities failed: Non-XML response received.', ['response' => $opportunityResponse->body()]);
                return redirect('/')->with('error', 'Failed to fetch opportunity.');
            }

            $opportunityXmlResponse = simplexml_load_string($opportunityResponse->body());
            if ($opportunityXmlResponse->Result != 'Success') {
                return redirect('/')->with('error', 'Failed to fetch opportunity: ' . $opportunityXmlResponse->ErrorText);
            }

            $assignedToContactId = (string) $opportunityXmlResponse->Opportunities->Opportunity->AssignedToContactID;

            // Step 2: Fetch all opportunities for the contact
            $getAllOpportunitiesXml = "<GetOpportunitiesRequest contact_id=\"{$assignedToContactId}\" get_all=\"true\" show_assignables=\"true\"></GetOpportunitiesRequest>";
            Log::info('Request XML (Step 2):', ['xml' => $getAllOpportunitiesXml]);

            $allOpportunitiesResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $getAllOpportunitiesXml,
            ]);

            Log::info('GetOpportunities Response (Step 2):', ['body' => $allOpportunitiesResponse->body()]);

            if (!$this->isValidXml($allOpportunitiesResponse->body())) {
                Log::error('GetAllOpportunities failed: Non-XML response received.', ['response' => $allOpportunitiesResponse->body()]);
                return redirect('/')->with('error', 'Failed to fetch all opportunities.');
            }

            $allOpportunitiesXmlResponse = simplexml_load_string($allOpportunitiesResponse->body());
            if ($allOpportunitiesXmlResponse->Result != 'Success') {
                return redirect('/')->with('error', 'Failed to fetch all opportunities: ' . $allOpportunitiesXmlResponse->ErrorText);
            }

            $children = [];
            foreach ($allOpportunitiesXmlResponse->Opportunities->Opportunity as $opportunity) {
                $notes = (string) $opportunity->Notes;
                preg_match('/Child DOB: ([\d-]+), Gender: (\w+)/', $notes, $matches);

                $children[] = [
                    'firstName' => (string) $opportunity->Title,
                    'dob' => $matches[1] ?? '',
                    'gender' => $matches[2] ?? '',
                ];
            }

            // Pass all necessary data to the view
            return view('waitlist.edit', [
                'opportunityId' => $opportunityId,
                'opportunity' => $opportunityXmlResponse->Opportunities->Opportunity,
                'children' => $children,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching opportunity:', ['exception' => $e->getMessage()]);
            return redirect('/')->with('error', 'An error occurred while fetching the opportunity.');
        }
    }

    public function update(Request $request, $contactId)
    {
        // Update the opportunity in GreenRope
        return redirect()->back()->with('success', 'Information updated successfully.');
    }

    public function optOut($contactId)
    {
        // Move the opportunity to the "close lost" phase in GreenRope
        return redirect('/')->with('success', 'You have opted out of the waitlist.');
    }

    /**
     * Check if a string is valid XML.
     *
     * @param string $content
     * @return bool
     */
    private function isValidXml($content)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            Log::error('Invalid XML:', ['errors' => libxml_get_errors()]);
            libxml_clear_errors();
            return false;
        }
        return true;
    }
}
