<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Artisan;

class WaitlistController extends Controller
{
    private $apiUsername;
    private $apiPassword;

    public function __construct()
    {
        $this->apiUsername = env('GREENROPE_USERNAME');
        $this->apiPassword = env('GREENROPE_PASSWORD');
    }

    public function edit($hashedOpportunityId)
    {
        try {
            $opportunityId = Crypt::decryptString($hashedOpportunityId); // Decrypt the hashed ID

            // Authenticate and get token
            $authToken = $this->authenticate();

            // Step 1: Fetch opportunity details
            $getOpportunityXml = "<GetOpportunitiesRequest opportunity_id=\"{$opportunityId}\"></GetOpportunitiesRequest>";
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
                'hear_about_us' => isset($opportunity->CustomFields->CustomField[4]) && !empty($opportunity->CustomFields->CustomField[4]->FieldValue)
                    ? (string) $opportunity->CustomFields->CustomField[4]->FieldValue
                    : null, // Add null check for CustomField[4] and FieldValue
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
                    $formData['relationship'] = isset($field->FieldValue) ? (string) $field->FieldValue : null; // Add null check
                }
            }

            // Set comment from contact notes
            $formData['comment'] = (string) $contact->Notes;

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

            // Log the structure of the XML for debugging
            Log::info('Parsed Opportunities XML:', ['xml' => $opportunitiesXml]);

            // Extract child-related opportunities
            $children = [];
            foreach ($opportunitiesXml->Opportunities->Opportunity as $childOpportunity) {
                $children[] = [
                    'opportunity_id' => (string) $childOpportunity->OpportunityID,
                    'first_name' => isset($childOpportunity->CustomFields->CustomField[0]) && !empty($childOpportunity->CustomFields->CustomField[0]->FieldValue)
                        ? (string) $childOpportunity->CustomFields->CustomField[0]->FieldValue
                        : null, // Check if FieldValue is not empty
                    'last_name' => isset($childOpportunity->CustomFields->CustomField[1]) && !empty($childOpportunity->CustomFields->CustomField[1]->FieldValue)
                        ? (string) $childOpportunity->CustomFields->CustomField[1]->FieldValue
                        : null, // Check if FieldValue is not empty
                    'dob' => isset($childOpportunity->CustomFields->CustomField[2]) && !empty($childOpportunity->CustomFields->CustomField[2]->FieldValue)
                        ? (string) $childOpportunity->CustomFields->CustomField[2]->FieldValue
                        : null, // Check if FieldValue is not empty
                    'start_date' => isset($childOpportunity->CustomFields->CustomField[3]) && !empty($childOpportunity->CustomFields->CustomField[3]->FieldValue)
                        ? (string) $childOpportunity->CustomFields->CustomField[3]->FieldValue
                        : null, // Check if FieldValue is not empty
                    'gender' => '', // Gender is not provided in the response; leave blank or handle separately
                ];
            }
            // Log the extracted children for debugging
            Log::info('Extracted Children:', ['children' => $children]);

            // Pass data to the view
            return view('waitlist.edit', [
                'opportunityId' => $opportunityId,
                'formData' => $formData,
                'children' => $children,
            ]);
        } catch (\Exception $e) {
            Log::error('Invalid or tampered opportunity ID.', ['hashedOpportunityId' => $hashedOpportunityId]);
            return redirect('/')->withErrors(['error' => 'Invalid link.']);
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
                (string) $existingContact->Comment === $request->input('comment') &&
                (string) $contactXml->Contacts->Contact->Groups->Group->Name === $request->input('location') &&
                (string) $existingContact->UserDefinedFields->UserDefinedField[0]->FieldValue === $request->input('relationship')
            ) {
                Log::info('No changes detected. Skipping update.');
                return redirect()->back()->with('info', 'No changes were made.');
            }

            $existingLocation = $contactXml->Contacts->Contact->Groups->Group->Name ?? '';
            $newLocation = $request->input('location', []);

            $groupsXml = '';
            foreach ((array) $newLocation as $location) {
                $groupsXml .= "<Group>{$location}</Group>\n"; // Add newline after each group
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
                        <Notes>{$request->input('comment')}</Notes>
                        <UserDefinedFields>
                            <UserDefinedField fieldname=\"Relationship\">{$request->input('relationship')}</UserDefinedField>
                        </UserDefinedFields>
                        <Groups replace=\"true\">
                            {$groupsXml} <!-- Ensure groups are properly formatted -->
                        </Groups>
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

            $editResponseXml = simplexml_load_string($editResponse->body());
            if (strtolower((string) $editResponseXml->Contacts->Contact->Result) !== 'success') {
                Log::error('Failed to update contact details:', ['response' => $editResponse->body()]);
                return redirect()->back()->withErrors(['error' => 'Failed to update contact details.']);
            }

            // Step 4: Update opportunities for each child
            $children = $request->input('children', []);
            foreach ($children as $child) {
                $editOpportunityXml = "<EditOpportunitiesRequest>
                    <Opportunities>
                        <Opportunity opportunity_id=\"{$child['opportunity_id']}\">
                            <Quality>A</Quality>
                            <CloseDate>20260101</CloseDate>
                            <CustomFields>
                                <CustomField fieldnum=\"1\">{$child['first_name']}</CustomField>
                                <CustomField fieldnum=\"2\">{$child['last_name']}</CustomField>
                                <CustomField fieldnum=\"3\">{$child['dob']}</CustomField>
                                <CustomField fieldnum=\"4\">{$child['start_date']}</CustomField>
                            </CustomFields>
                        </Opportunity>
                    </Opportunities>
                </EditOpportunitiesRequest>";

                Log::info('XML being sent for EditOpportunitiesRequest:', ['xml' => $editOpportunityXml]);

                $editOpportunityResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                    'email' => $this->apiUsername,
                    'auth_token' => $authToken,
                    'xml' => $editOpportunityXml,
                ]);

                Log::info('EditOpportunitiesResponse:', ['body' => $editOpportunityResponse->body()]);

                if (!$this->isValidXml($editOpportunityResponse->body())) {
                    Log::error('Invalid XML response for EditOpportunitiesRequest.');
                    return redirect()->back()->withErrors(['error' => 'Failed to update opportunity details.']);
                }

                $editOpportunityXmlResponse = simplexml_load_string($editOpportunityResponse->body());
                if (strtolower((string) $editOpportunityXmlResponse->Opportunities->Opportunity->Result) !== 'success') {
                    Log::error('Failed to update opportunity details:', ['response' => $editOpportunityResponse->body()]);
                    return redirect()->back()->withErrors(['error' => 'Failed to update opportunity details.']);
                }
            }

            return redirect()->back()->with('success', 'Contact and opportunities updated successfully.');
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

    private function waitlist_cron(Request $request){
        $params = $request->all();
        $response = ['status' => 'Schedule executed'];
        if(isset($params['keysec']) && ($params['keysec'] == env('KEYSEC_VAL'))){
            Artisan::call('waitlist:send-reminders');
        }
        return response()->json($response, 200);
    }
}
