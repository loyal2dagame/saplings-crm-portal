<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WaitlistController extends Controller
{
    public function edit($contactId)
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
                throw new \Exception('Authentication failed: ' . $authXml->ErrorText);
            }

            $authToken = (string) $authXml->Token;

            // Fetch contact details
            $getContactXml = "<GetContactsRequest>
                <ContactID>{$contactId}</ContactID>
            </GetContactsRequest>";

            $contactResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $apiUsername,
                'auth_token' => $authToken,
                'xml' => $getContactXml,
            ]);

            $contactXml = simplexml_load_string($contactResponse->body());
            if ($contactXml->Result != 'Success') {
                throw new \Exception('Failed to fetch contact details: ' . $contactXml->ErrorText);
            }

            $contact = $contactXml->Contacts->Contact;

            // Fetch opportunity details
            $getOpportunitiesXml = "<GetOpportunitiesRequest>
                <ContactID>{$contactId}</ContactID>
            </GetOpportunitiesRequest>";

            $opportunitiesResponse = Http::withOptions(['verify' => false])->asForm()->post('https://api.stgi.net/xml.pl', [
                'email' => $apiUsername,
                'auth_token' => $authToken,
                'xml' => $getOpportunitiesXml,
            ]);

            $opportunitiesXml = simplexml_load_string($opportunitiesResponse->body());
            if ($opportunitiesXml->Result != 'Success') {
                throw new \Exception('Failed to fetch opportunities: ' . $opportunitiesXml->ErrorText);
            }

            $opportunities = $opportunitiesXml->Opportunities->Opportunity;

            // Prepare data for the view
            $data = [
                'firstName' => (string) $contact->Firstname,
                'lastName' => (string) $contact->Lastname,
                'email' => (string) $contact->Email,
                'phone' => (string) $contact->Phone,
                'location' => (string) $contact->Groups->Group ?? '',
                'relationship' => (string) $contact->CustomFields->CustomField[0] ?? '',
                'hearAboutUs' => (string) $contact->CustomFields->CustomField[1] ?? '',
                'additionalInfo' => (string) $contact->CustomFields->CustomField[2] ?? '',
                'comment' => (string) $contact->CustomFields->CustomField[3] ?? '',
                'children' => [],
            ];

            foreach ($opportunities as $opportunity) {
                $data['children'][] = [
                    'firstName' => (string) $opportunity->CustomFields->CustomField[0] ?? '',
                    'lastName' => (string) $opportunity->CustomFields->CustomField[1] ?? '',
                    'dob' => (string) $opportunity->CustomFields->CustomField[2] ?? '',
                    'gender' => (string) $opportunity->CustomFields->CustomField[3] ?? '',
                    'startDate' => (string) $opportunity->CustomFields->CustomField[4] ?? '',
                ];
            }

            return view('waitlist.edit', ['contactId' => $contactId, 'data' => $data]);
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Failed to load data: ' . $e->getMessage());
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
}
