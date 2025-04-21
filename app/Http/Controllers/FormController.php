<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FormController extends Controller
{
    private $apiUsername;
    private $apiPassword;

    public function __construct()
    {
        $this->apiUsername = env('GREENROPE_USERNAME');
        $this->apiPassword = env('GREENROPE_PASSWORD');
    }

    private function sendApiRequest($url, $payload)
    {
        try {
            $response = Http::withOptions(['verify' => false])->asForm()->post($url, $payload);
            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function authenticate()
    {
        try {
            $url = 'https://api.stgi.net/xml.pl';
            $payload = [
                'email' => $this->apiUsername,
                'password' => $this->apiPassword,
                'xml' => '<GetAuthTokenRequest></GetAuthTokenRequest>',
            ];

            $response = $this->sendApiRequest($url, $payload);

            $xml = simplexml_load_string($response->body());
            if ($xml->Result == 'Success') {
                return (string) $xml->Token;
            }

            throw new \Exception("Authentication failed: " . ($xml->ErrorText ?? 'Unknown error'));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function handleInquiry(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'inquiry' => 'required|string|max:1500',
        ]);

        return response()->json(['message' => 'Inquiry submitted successfully']);
    }

    public function processInquiry(Request $request)
    {
        if ($request->isMethod('post')) {
            $inquiryData = $request->all();

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
                    return response()->json(['error' => 'Authentication failed: Invalid response from GreenRope API.'], 500);
                }

                $authXml = simplexml_load_string($authResponse->body());
                if ($authXml->Result != 'Success') {
                    return response()->json(['error' => 'Authentication failed: ' . $authXml->ErrorText], 500);
                }

                $authToken = (string) $authXml->Token;

                // Escape and validate special characters in inquiry data
                $firstName = htmlspecialchars(trim($inquiryData['firstName']), ENT_XML1, 'UTF-8');
                $lastName = htmlspecialchars(trim($inquiryData['lastName']), ENT_XML1, 'UTF-8');
                $email = htmlspecialchars(trim($inquiryData['email']), ENT_XML1, 'UTF-8');
                $inquiry = htmlspecialchars(trim($inquiryData['inquiry']), ENT_XML1, 'UTF-8');

                if (empty($firstName) || empty($lastName) || empty($email) || empty($inquiry)) {
                    return response()->json(['error' => 'All fields are required and must be valid.'], 400);
                }

                // Determine the group name based on the location
                $groupName = null;
                if ($inquiryData['location'] === 'Mill Street') {
                    $groupName = 'Mill Street';
                } elseif ($inquiryData['location'] === 'Third Street') {
                    $groupName = 'Third Street';
                }

                // Prepare XML for AddContactsRequest
                $addContactsXml = "<AddContactsRequest>
                    <Contacts>
                        <Contact>
                            <Firstname>{$firstName}</Firstname>
                            <Lastname>{$lastName}</Lastname>
                            <Email>{$email}</Email>
                            <Notes>{$inquiry}</Notes>
                            <Groups>
                                <Group>{$groupName}</Group>
                            </Groups>
                            <UserDefinedFields>
                                <UserDefinedField fieldname=\"Inquiry\">{$inquiry}</UserDefinedField>
                            </UserDefinedFields>
                        </Contact>
                    </Contacts>
                </AddContactsRequest>";

                Log::info('Generated AddContactsRequest XML:', ['xml' => $addContactsXml]);

                // Send AddContactsRequest
                $addContactsResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                    'email' => $this->apiUsername,
                    'auth_token' => $authToken,
                    'xml' => $addContactsXml,
                ]);

                Log::info('AddContacts Response:', ['body' => $addContactsResponse->body()]);

                if (!$this->isValidXml($addContactsResponse->body())) {
                    Log::error('AddContacts failed: Non-XML response received.', ['response' => $addContactsResponse->body()]);
                    return response()->json(['error' => 'AddContacts failed: Invalid response from GreenRope API.'], 500);
                }

                $addContactsXmlResponse = simplexml_load_string($addContactsResponse->body());
                if (strtolower((string) $addContactsXmlResponse->Contacts->Contact->Result) !== 'success') {
                    $errorText = (string) $addContactsXmlResponse->Contacts->Contact->ErrorText;
                    if (strpos($errorText, 'already exists') !== false) {
                        // Extract the existing contact ID from the error message
                        preg_match('/ID (\d+)/', $errorText, $matches);
                        if (isset($matches[1])) {
                            $contactId = $matches[1];

                            // Prepare XML for AddOpportunitiesRequest
                            $addOpportunitiesXml = "<AddOpportunitiesRequest>
                                <Opportunities>
                                    <Opportunity>
                                        <Title>(P) {$firstName} {$lastName}</Title>
                                        <ContactID>{$contactId}</ContactID>
                                        <Notes>{$inquiry}</Notes>
                                        <Phase>New</Phase>
                                        <OpportunityValue>0</OpportunityValue>
                                        <PercentWin>50</PercentWin>
                                        <CloseDate>20260101</CloseDate>
                                        <Quality>A</Quality>
                                    </Opportunity>
                                </Opportunities>
                            </AddOpportunitiesRequest>";

                            Log::info('Generated AddOpportunitiesRequest XML for existing contact:', ['xml' => $addOpportunitiesXml]);

                            // Send AddOpportunitiesRequest
                            $addOpportunitiesResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                                'email' => $this->apiUsername,
                                'auth_token' => $authToken,
                                'xml' => $addOpportunitiesXml,
                            ]);

                            Log::info('AddOpportunities Response for existing contact:', ['body' => $addOpportunitiesResponse->body()]);

                            if (!$this->isValidXml($addOpportunitiesResponse->body())) {
                                Log::error('AddOpportunities failed for existing contact: Non-XML response received.', ['response' => $addOpportunitiesResponse->body()]);
                                return response()->json(['error' => 'AddOpportunities failed: Invalid response from GreenRope API.'], 500);
                            }

                            $addOpportunitiesXmlResponse = simplexml_load_string($addOpportunitiesResponse->body());
                            if (!isset($addOpportunitiesXmlResponse->Opportunities->Opportunity->Result) || 
                                strtolower((string) $addOpportunitiesXmlResponse->Opportunities->Opportunity->Result) !== 'success') {
                                Log::error('Failed to create opportunity for existing contact:', ['response' => $addOpportunitiesResponse->body()]);
                                return response()->json(['error' => 'Failed to create opportunity: ' . ($addOpportunitiesXmlResponse->Opportunities->Opportunity->ErrorText ?? 'Unknown error')], 500);
                            }

                            // Return success response
                            return response()->json(['message' => 'Inquiry and opportunity submitted successfully for existing contact.']);
                        }
                    }

                    Log::error('Failed to add contact:', ['response' => $addContactsResponse->body()]);
                    return response()->json(['error' => 'Failed to add contact: ' . $errorText], 500);
                }

                $contactId = (string) $addContactsXmlResponse->Contacts->Contact->Contact_id;

                // Prepare XML for AddOpportunitiesRequest
                $addOpportunitiesXml = "<AddOpportunitiesRequest>
                    <Opportunities>
                        <Opportunity>
                            <Title>(P) {$firstName} {$lastName}</Title>
                            <ContactID>{$contactId}</ContactID>
                            <Notes>{$inquiry}</Notes>
                            <Phase>New</Phase>
                            <OpportunityValue>0</OpportunityValue>
                            <PercentWin>50</PercentWin>
                            <CloseDate>20260101</CloseDate>
                            <Quality>A</Quality>
                        </Opportunity>
                    </Opportunities>
                </AddOpportunitiesRequest>";

                Log::info('Generated AddOpportunitiesRequest XML:', ['xml' => $addOpportunitiesXml]);

                // Send AddOpportunitiesRequest
                $addOpportunitiesResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                    'email' => $this->apiUsername,
                    'auth_token' => $authToken,
                    'xml' => $addOpportunitiesXml,
                ]);

                Log::info('AddOpportunities Response:', ['body' => $addOpportunitiesResponse->body()]);

                if (!$this->isValidXml($addOpportunitiesResponse->body())) {
                    Log::error('AddOpportunities failed: Non-XML response received.', ['response' => $addOpportunitiesResponse->body()]);
                    return response()->json(['error' => 'AddOpportunities failed: Invalid response from GreenRope API.'], 500);
                }

                $addOpportunitiesXmlResponse = simplexml_load_string($addOpportunitiesResponse->body());

                // Log the full response structure for debugging
                Log::info('Parsed AddOpportunitiesResponse:', ['response' => json_encode($addOpportunitiesXmlResponse)]);

                // Adjust success validation logic
                if (!isset($addOpportunitiesXmlResponse->Opportunities->Opportunity->Result) || 
                    strtolower((string) $addOpportunitiesXmlResponse->Opportunities->Opportunity->Result) !== 'success') {
                    Log::error('Failed to create opportunity:', ['response' => $addOpportunitiesResponse->body()]);
                    return response()->json(['error' => 'Failed to create opportunity: ' . ($addOpportunitiesXmlResponse->Opportunities->Opportunity->ErrorText ?? 'Unknown error')], 500);
                }

                // Prepare XML for GetGroupsRequest
                $getGroupsXml = "<GetGroupsRequest></GetGroupsRequest>";

                // Send GetGroupsRequest
                $getGroupsResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                    'email' => $this->apiUsername,
                    'auth_token' => $authToken,
                    'xml' => $getGroupsXml,
                ]);

                Log::info('GetGroups Response:', ['body' => $getGroupsResponse->body()]);

                if (!$this->isValidXml($getGroupsResponse->body())) {
                    Log::error('GetGroups failed: Non-XML response received.', ['response' => $getGroupsResponse->body()]);
                    return response()->json(['error' => 'GetGroups failed: Invalid response from GreenRope API.'], 500);
                }

                $getGroupsXmlResponse = simplexml_load_string($getGroupsResponse->body());
                if (strtolower((string) $getGroupsXmlResponse->Result) !== 'success') {
                    Log::error('Failed to fetch groups:', ['response' => $getGroupsResponse->body()]);
                    return response()->json(['error' => 'Failed to fetch groups: ' . ($getGroupsXmlResponse->ErrorText ?? 'Unknown error')], 500);
                }

                // Parse groups from the response
                $groups = [];
                foreach ($getGroupsXmlResponse->Groups->Group as $group) {
                    $groups[] = [
                        'group_id' => (string) $group->Group_id,
                        'name' => (string) $group->Name,
                        'group_type' => (string) $group->Group_type,
                        'emails_sent_from_name' => (string) $group->EmailsSentFromName,
                        'emails_sent_from_email' => (string) $group->EmailsSentFromEmail,
                        'email_physical_address' => (string) $group->EmailPhysicalAddress,
                        'events_time_zone' => (string) $group->EventsTimeZone,
                    ];
                }

                // Return success response with groups
                return response()->json([
                    'message' => 'Inquiry and opportunity submitted successfully to GreenRope',
                    'groups' => $groups,
                ]);
            } catch (\Exception $e) {
                Log::error('Error in processInquiry:', ['exception' => $e->getMessage()]);
                return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
            }
        }

        return response('Inquiry processed successfully');
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
            libxml_clear_errors();
            return false;
        }
        return true;
    }

    public function handleWaitlist(Request $request)
    {
        $validated = $request->validate([
            'parent.first_name' => 'required|string|max:255',
            'parent.last_name' => 'required|string|max:255',
            'parent.relationship' => 'required|string',
            'parent.email' => 'required|email|max:255',
            'parent.phone' => 'required|string|max:20',
            'children' => 'required|array',
            'children.*.first_name' => 'required|string|max:255',
            'children.*.last_name' => 'required|string|max:255',
            'children.*.dob' => 'required|date',
            'children.*.gender' => 'required|string',
            'children.*.start_date' => 'required|date',
            'location' => 'required|string',
            'source' => 'required|string',
            'comment' => 'nullable|string|max:500',
        ]);

        $token = $this->authenticate();

        $contactXml = "<AddContactsRequest>
            <Contacts>
                <Contact>
                    <Firstname>{$validated['parent']['first_name']}</Firstname>
                    <Lastname>{$validated['parent']['last_name']}</Lastname>
                    <Email>{$validated['parent']['email']}</Email>
                    <Phone>{$validated['parent']['phone']}</Phone>
                </Contact>
            </Contacts>
        </AddContactsRequest>";

        $contactResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.greenrope.com/api2', [
            'email' => $this->apiUsername,
            'auth_token' => $token,
            'xml' => $contactXml,
        ]);

        $contactXmlResponse = simplexml_load_string($contactResponse->body());
        if ($contactXmlResponse->Result != 'Success') {
            throw new \Exception("Failed to add contact: " . $contactXmlResponse->ErrorText);
        }

        $opportunityId = (string) $contactXmlResponse->Contacts->Contact->OpportunityID;

        return view('waitlist.edit', [
            'opportunityId' => $opportunityId,
        ]);
    }

    public function processWaitlist(Request $request)
    {
        if ($request->isMethod('post')) {
            $waitlistData = $request->all();

            try {
                $authToken = $this->authenticate();

                $firstName = htmlspecialchars(trim($waitlistData['firstName']), ENT_XML1, 'UTF-8');
                $lastName = htmlspecialchars(trim($waitlistData['lastName']), ENT_XML1, 'UTF-8');
                $email = htmlspecialchars(trim($waitlistData['email']), ENT_XML1, 'UTF-8');
                $phone = htmlspecialchars(trim($waitlistData['phone']), ENT_XML1, 'UTF-8');
                $location = htmlspecialchars(trim($waitlistData['location']), ENT_XML1, 'UTF-8');
                $comment = htmlspecialchars(trim($waitlistData['comment']), ENT_XML1, 'UTF-8');
                $relationship = htmlspecialchars(trim($waitlistData['relationship']), ENT_XML1, 'UTF-8');

                $allowedRelationships = ['Mother', 'Father', 'Grandmother', 'Grandfather', 'Guardian', 'Joint Custody', 'Other'];
                if (!in_array($relationship, $allowedRelationships)) {
                    return response()->json(['error' => 'Invalid relationship selected.'], 400);
                }

                if ($location !== 'Mill Street' && $location !== 'Third Street') {
                    return response()->json(['error' => 'Invalid location selected.'], 400);
                }

                if (empty($firstName) || empty($lastName) || empty($email) || empty($location)) {
                    return response()->json(['error' => 'All fields are required and must be valid.'], 400);
                }

                $addContactsXml = "<AddContactsRequest>
                    <Contacts>
                        <Contact>
                            <Firstname>{$firstName}</Firstname>
                            <Lastname>{$lastName}</Lastname>
                            <Email>{$email}</Email>
                            <Phone>{$phone}</Phone>
                            <Notes>{$comment}</Notes>
                            <Groups>
                                <Group>{$location}</Group>
                            </Groups>
                            <UserDefinedFields>
                                <UserDefinedField fieldname=\"relationship\">{$relationship}</UserDefinedField>
                            </UserDefinedFields>
                        </Contact>
                    </Contacts>
                </AddContactsRequest>";

                $addContactsResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                    'email' => $this->apiUsername,
                    'auth_token' => $authToken,
                    'xml' => $addContactsXml,
                ]);

                if (!$this->isValidXml($addContactsResponse->body())) {
                    return response()->json(['error' => 'AddContacts failed: Invalid response from GreenRope API.'], 500);
                }

                $addContactsXmlResponse = simplexml_load_string($addContactsResponse->body());
                if (strtolower((string) $addContactsXmlResponse->Contacts->Contact->Result) !== 'success') {
                    $errorText = (string) $addContactsXmlResponse->Contacts->Contact->ErrorText;

                    if (strpos($errorText, 'already exists') !== false) {
                        preg_match('/ID (\d+)/', $errorText, $matches);
                        if (isset($matches[1])) {
                            $contactId = $matches[1];
                        } else {
                            return response()->json(['error' => 'Failed to process waitlist: Unable to retrieve existing contact ID.'], 500);
                        }
                    } else {
                        return response()->json(['error' => 'Failed to add contact: ' . $errorText], 500);
                    }
                } else {
                    $contactId = (string) $addContactsXmlResponse->Contacts->Contact->Contact_id;
                }

                foreach ($waitlistData['children'] as $child) {
                    $childFirstName = htmlspecialchars(trim($child['firstName']), ENT_XML1, 'UTF-8');
                    $childLastName = htmlspecialchars(trim($child['lastName']), ENT_XML1, 'UTF-8');
                    $childDob = htmlspecialchars(trim($child['dob']), ENT_XML1, 'UTF-8');
                    $childStartDate = htmlspecialchars(trim($child['startDate']), ENT_XML1, 'UTF-8');
                    $comment = htmlspecialchars(trim($waitlistData['comment']), ENT_XML1, 'UTF-8');
                    $hearAboutUs = htmlspecialchars(trim($waitlistData['hearAboutUs']), ENT_XML1, 'UTF-8');

                    if (empty($childFirstName) || empty($childLastName) || empty($childDob) || empty($childStartDate)) {
                        return response()->json(['error' => 'All child fields are required and must be valid.'], 400);
                    }

                    $addOpportunitiesXml = "<AddOpportunitiesRequest>
                        <Opportunities>
                            <Opportunity>
                                <Title>{$childFirstName} {$childLastName}</Title>
                                <ContactID>{$contactId}</ContactID>
                                <Notes>{$comment}</Notes>
                                <OpportunityValue>1</OpportunityValue>
                                <PercentWin>50</PercentWin>
                                <CloseDate>20260101</CloseDate>
                                <Quality>A</Quality>
                                <PhaseID>17</PhaseID>
                                <CustomFields>
                                    <CustomField fieldnum=\"1\">{$childFirstName}</CustomField>
                                    <CustomField fieldnum=\"2\">{$childLastName}</CustomField>
                                    <CustomField fieldnum=\"3\">{$childDob}</CustomField>
                                    <CustomField fieldnum=\"4\">{$childStartDate}</CustomField>
                                    <CustomField fieldnum=\"7\">{$hearAboutUs}</CustomField>
                                </CustomFields>
                            </Opportunity>
                        </Opportunities>
                    </AddOpportunitiesRequest>";

                    $addOpportunitiesResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                        'email' => $this->apiUsername,
                        'auth_token' => $authToken,
                        'xml' => $addOpportunitiesXml,
                    ]);

                    if (!$this->isValidXml($addOpportunitiesResponse->body())) {
                        return response()->json(['error' => 'Failed to create opportunity: Internal server error from GreenRope API.'], 500);
                    }

                    $addOpportunitiesXmlResponse = simplexml_load_string($addOpportunitiesResponse->body());
                    if (!isset($addOpportunitiesXmlResponse->Opportunities->Opportunity->Result) ||
                        strtolower((string) $addOpportunitiesXmlResponse->Opportunities->Opportunity->Result) !== 'success') {
                        return response()->json(['error' => 'Failed to create opportunity: ' . ($addOpportunitiesXmlResponse->Opportunities->Opportunity->ErrorText ?? 'Unknown error')], 500);
                    }
                }

                return response()->json(['message' => 'Waitlist and opportunities submitted successfully.']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
            }
        }

        return response('Hello World');
    }

    public function getCustomFields(Request $request)
    {
        try {
            $authToken = $this->authenticate();

            $firstName = htmlspecialchars(trim($request->input('first_name')), ENT_XML1, 'UTF-8');
            $lastName = htmlspecialchars(trim($request->input('last_name')), ENT_XML1, 'UTF-8');
            $email = htmlspecialchars(trim($request->input('email')), ENT_XML1, 'UTF-8');

            if (empty($firstName) && empty($lastName) && empty($email)) {
                return response()->json(['error' => 'At least one filter (first_name, last_name, or email) is required.'], 400);
            }

            $getContactsXml = "<GetContactsRequest";
            if (!empty($firstName)) {
                $getContactsXml .= " firstname=\"{$firstName}\"";
            }
            if (!empty($lastName)) {
                $getContactsXml .= " lastname=\"{$lastName}\"";
            }
            if (!empty($email)) {
                $getContactsXml .= " email=\"{$email}\"";
            }
            $getContactsXml .= "></GetContactsRequest>";

            $response = $this->sendApiRequest('https://api.stgi.net/xml.pl', [
                'email' => $this->apiUsername,
                'auth_token' => $authToken,
                'xml' => $getContactsXml,
            ]);

            if (!mb_check_encoding($response->body(), 'UTF-8')) {
                return response()->json(['error' => 'Failed to fetch contact: Invalid response encoding.'], 500);
            }

            if (!$this->isValidXml($response->body())) {
                return response()->json(['error' => 'Failed to fetch contact: Invalid response from GreenRope API.'], 500);
            }

            $xmlResponse = simplexml_load_string($response->body());
            if (strtolower((string) $xmlResponse->Result) !== 'success') {
                return response()->json(['error' => 'Failed to fetch contact: ' . ($xmlResponse->ErrorText ?? 'Unknown error')], 500);
            }

            $contacts = $xmlResponse->Contacts->Contact ?? [];
            $contactDetails = [];

            foreach ($contacts as $contact) {
                $userDefinedFields = $contact->UserDefinedFields->UserDefinedField ?? [];
                $fields = [];

                foreach ($userDefinedFields as $field) {
                    $fields[] = [
                        'FieldName' => (string) $field->FieldName,
                        'FieldValue' => (string) ($field->FieldValue ?? ''),
                    ];
                }

                $contactDetails[] = [
                    'contact' => $contact,
                    'user_defined_fields' => $fields,
                ];
            }

            return response()->json($contactDetails);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
