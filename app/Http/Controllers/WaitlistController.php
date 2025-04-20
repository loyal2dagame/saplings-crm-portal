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

    public function edit($contactId)
    {
        try {
            // Authenticate and get token
            $authToken = $this->authenticate();

            // Step 1: Fetch opportunity details
            $getOpportunityXml = "<GetOpportunitiesRequest opportunity_id=\"{$contactId}\"></GetOpportunitiesRequest>";
            $opportunityResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $getOpportunityXml,
            ]);

            Log::info('GetOpportunities Response:', ['body' => $opportunityResponse->body()]);

            if (!$this->isValidXml($opportunityResponse->body())) {
                Log::error('Invalid XML response for GetOpportunitiesRequest.');
                return response()->json(['error' => 'Failed to fetch opportunity details.'], 500);
            }

            $opportunityXml = simplexml_load_string($opportunityResponse->body());
            if (strtolower((string) $opportunityXml->Result) !== 'success') {
                Log::error('Failed to fetch opportunity details:', ['response' => $opportunityResponse->body()]);
                return response()->json(['error' => 'Failed to fetch opportunity details.'], 500);
            }

            $opportunity = $opportunityXml->Opportunities->Opportunity;
            $assignedToContactId = (string) $opportunity->AssignedToContactID;

            // Extract opportunity fields
            $formData = [
                'first_name' => (string) $opportunity->AssignedToFirstname,
                'last_name' => (string) $opportunity->AssignedToLastname,
                'email' => (string) $opportunity->AssignedToEmail,
                'phone' => (string) $opportunity->AssignedToPhone,
                'comment' => (string) $opportunity->Notes,
                'hear_about_us' => (string) $opportunity->CustomFields->CustomField[4]->FieldValue, // Adjusted to fetch from opportunity
            ];

            // Step 2: Fetch contact details
            $getContactXml = "<GetContactsRequest firstname=\"{$opportunity->AssignedToFirstname}\" lastname=\"{$opportunity->AssignedToLastname}\" email=\"{$opportunity->AssignedToEmail}\"></GetContactsRequest>";
            $contactResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $getContactXml,
            ]);

            Log::info('GetContacts Response:', ['body' => $contactResponse->body()]);

            if (!$this->isValidXml($contactResponse->body())) {
                Log::error('Invalid XML response for GetContactsRequest.');
                return response()->json(['error' => 'Failed to fetch contact details.'], 500);
            }

            $contactXml = simplexml_load_string($contactResponse->body());
            if (strtolower((string) $contactXml->Result) !== 'success') {
                Log::error('Failed to fetch contact details:', ['response' => $contactResponse->body()]);
                return response()->json(['error' => 'Failed to fetch contact details.'], 500);
            }

            $contact = $contactXml->Contacts->Contact;

            // Extract contact fields
            foreach ($contact->UserDefinedFields->UserDefinedField as $field) {
                $fieldName = strtolower(trim((string) $field->FieldName)); // Correctly access FieldName as an element
                if ($fieldName === 'relationship') {
                    $formData['relationship'] = (string) $field->FieldValue; // Access FieldValue for the value
                }
            }

            // Extract groups
            $formData['location'] = [];
            foreach ($contact->Groups->Group as $group) {
                $formData['location'][] = (string) $group->Name;
            }

            // Step 3: Fetch opportunities for the contact
            $getOpportunitiesXml = "<GetOpportunitiesRequest contact_id=\"{$assignedToContactId}\"></GetOpportunitiesRequest>";
            $opportunitiesResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $getOpportunitiesXml,
            ]);

            Log::info('GetOpportunities Response for Contact:', ['body' => $opportunitiesResponse->body()]);

            if (!$this->isValidXml($opportunitiesResponse->body())) {
                Log::error('Invalid XML response for GetOpportunitiesRequest by contact ID.');
                return response()->json(['error' => 'Failed to fetch opportunities for contact.'], 500);
            }

            $opportunitiesXml = simplexml_load_string($opportunitiesResponse->body());
            if (strtolower((string) $opportunitiesXml->Result) !== 'success') {
                Log::error('Failed to fetch opportunities for contact:', ['response' => $opportunitiesResponse->body()]);
                return response()->json(['error' => 'Failed to fetch opportunities for contact.'], 500);
            }

            // Extract child-related opportunities
            $children = [];
            foreach ($opportunitiesXml->Opportunities->Opportunity as $childOpportunity) {
                $children[] = [
                    'opportunity_id' => (string) $childOpportunity->OpportunityID, // Add opportunity_id
                    'first_name' => (string) $childOpportunity->CustomFields->CustomField[0]->FieldValue,
                    'last_name' => (string) $childOpportunity->CustomFields->CustomField[1]->FieldValue,
                    'dob' => (string) $childOpportunity->CustomFields->CustomField[2]->FieldValue,
                    'start_date' => (string) $childOpportunity->CustomFields->CustomField[3]->FieldValue,
                    'gender' => '', // Gender is not provided in the response; leave blank or handle separately
                ];
            }

            // Pass data to the view
            return view('waitlist.edit', [
                'opportunityId' => $contactId,
                'formData' => $formData,
                'children' => $children,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit method:', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $opportunityId)
    {
        try {
            // Authenticate and get token
            $authToken = $this->authenticate();

            // Step 1: Fetch opportunity details to get the associated contact_id
            $getOpportunityXml = "<GetOpportunitiesRequest opportunity_id=\"{$opportunityId}\"></GetOpportunitiesRequest>";
            $opportunityResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $getOpportunityXml,
            ]);

            Log::info('GetOpportunities Response:', ['body' => $opportunityResponse->body()]);

            if (!$this->isValidXml($opportunityResponse->body())) {
                Log::error('Invalid XML response for GetOpportunitiesRequest.');
                return redirect()->back()->withErrors(['error' => 'Failed to fetch opportunity details.']);
            }

            $opportunityXml = simplexml_load_string($opportunityResponse->body());
            if (strtolower((string) $opportunityXml->Result) !== 'success' || empty($opportunityXml->Opportunities->Opportunity)) {
                Log::error('Opportunity not found:', ['response' => $opportunityResponse->body()]);
                return redirect()->back()->withErrors(['error' => 'Opportunity not found.']);
            }

            $contactId = (string) $opportunityXml->Opportunities->Opportunity->AssignedToContactID;

            // Step 2: Fetch existing contact details
            $getContactXml = "<GetContactsRequest contact_id=\"{$contactId}\"></GetContactsRequest>";
            $contactResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $getContactXml,
            ]);

            Log::info('GetContacts Response:', ['body' => $contactResponse->body()]);

            if (!$this->isValidXml($contactResponse->body())) {
                Log::error('Invalid XML response for GetContactsRequest.');
                return redirect()->back()->withErrors(['error' => 'Failed to fetch contact details.']);
            }

            $contactXml = simplexml_load_string($contactResponse->body());
            if (strtolower((string) $contactXml->Result) !== 'success' || empty($contactXml->Contacts->Contact)) {
                Log::error('Contact not found:', ['response' => $contactResponse->body()]);
                return redirect()->back()->withErrors(['error' => 'Contact not found.']);
            }

            $existingContact = $contactXml->Contacts->Contact;

            // Compare existing data with new data
            if (
                (string) $existingContact->Firstname === $request->input('first_name') &&
                (string) $existingContact->Lastname === $request->input('last_name') &&
                (string) $existingContact->Email === $request->input('email') &&
                (string) $existingContact->Phone === $request->input('phone') &&
                (string) $existingContact->UserDefinedFields->UserDefinedField[0]->FieldValue === $request->input('relationship')
            ) {
                Log::info('No changes detected. Skipping update.');
                return redirect()->back()->with('info', 'No changes were made.');
            }

            // Step 3: Update contact details using EditContactsRequest
            $requestId = uniqid();
            $editContactXml = "<EditContactsRequest>
                <Contacts>
                    <Contact contact_id=\"{$contactId}\" request_id=\"{$requestId}\">
                        <Firstname>{$request->input('first_name')}</Firstname>
                        <Lastname>{$request->input('last_name')}</Lastname>
                        <Email>{$request->input('email')}</Email>
                        <Phone>{$request->input('phone')}</Phone>
                        <UserDefinedFields>
                            <UserDefinedField fieldname=\"Relationship\">{$request->input('relationship')}</UserDefinedField>
                        </UserDefinedFields>
                    </Contact>
                </Contacts>
            </EditContactsRequest>";

            Log::info('XML being sent for EditContactsRequest:', ['xml' => $editContactXml]);

            $editResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $editContactXml,
            ]);

            Log::info('EditContactsResponse:', ['body' => $editResponse->body()]);

            if (!$this->isValidXml($editResponse->body())) {
                Log::error('Invalid XML response for EditContactsRequest.');
                return redirect()->back()->withErrors(['error' => 'Failed to update contact details.']);
            }

            $editXml = simplexml_load_string($editResponse->body());
            if (strtolower((string) $editXml->Result) !== 'success') {
                Log::error('Failed to update contact details:', ['response' => $editResponse->body()]);
                return redirect()->back()->withErrors(['error' => 'Failed to update contact details.']);
            }

            return redirect()->back()->with('success', 'Contact updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in update method:', ['exception' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function optOut($contactId)
    {
        // Move the opportunity to the "close lost" phase in GreenRope
        return redirect('/')->with('success', 'You have opted out of the waitlist.');
    }

    private function authenticate(): string
    {
        $authResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
            'email' => $this->apiUsername,
            'password' => $this->apiPassword,
            'xml' => '<GetAuthTokenRequest></GetAuthTokenRequest>',
        ]);

        Log::info('Auth Response:', ['body' => $authResponse->body()]);

        if (!$this->isValidXml($authResponse->body())) {
            Log::error('Authentication failed: Non-XML response received.', ['response' => $authResponse->body()]);
            throw new \Exception('Authentication failed.');
        }

        $authXml = simplexml_load_string($authResponse->body());
        if ($authXml->Result != 'Success') {
            throw new \Exception('Authentication failed: ' . $authXml->ErrorText);
        }

        return (string) $authXml->Token;
    }

    private function isValidXml($content): bool
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
