<?php

namespace App\Http\Controllers;

use App\Models\Crm\CrmClient;
use App\Models\Crm\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeadWebhookController extends Controller
{
    // Messenger Webhook
    public function messengerWebhook(Request $request){
        // dd('hasan');
        $verify_token = env('FB_VERIFY_TOKEN');
        // dd($verify_token);
        if ($request->hub_mode === 'subscribe' && $request->hub_verify_token === $verify_token) {
            return $request->hub_challenge;
        }
        return response('Forbidden', 403);
    }
    public function messengerWebhookPost(Request $request)
    {
        $input = $request->all();

        $senderId = $input['entry'][0]['messaging'][0]['sender']['id'] ?? null;
        // $name = $input['entry'][0]['messaging'][0]['sender']['name'] ?? null;
        $accessToken = env('FB_PAGE_ACCESS_TOKEN');

        $response = Http::get("https://graph.facebook.com/v21.0/{$senderId}", [
            'fields' => 'first_name,last_name,profile_pic',
            'access_token' => $accessToken,
        ]);


        if ($response->successful()) {
            $user = $response->json();
            $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        } else {
            \Log::error('Messenger profile fetch failed', [
                'senderId' => $senderId,
                'error' => $response->json(),
            ]);
            $name = 'Messenger Client';
        }

        if ($senderId) {
            // Check if Lead already exists
            $existingLead = CrmClient::where('contact_no', $senderId)
                // ->where('lead_source', 'messenger')
                ->first();

            if (!$existingLead) {
                // Create CRM Client first
                $client = CrmClient::create([
                    'sister_concern_id' => 1,
                    'name' => $name ?? 'Messenger Client',
                    'contact_no' => $senderId,
                ]);

                // Create Lead linked to CRM Client
                $lead = Lead::create([
                    'sister_concern_id' => 1,
                    'order_no' => generateOrder(1),
                    'crm_client_id' => $client->id,

                    'assigned_to' => auth()->id() ?? null,
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
    public function whatsappWebhook(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === env('WA_VERIFY_TOKEN')) {
            return response($challenge, 200); // Verification successful
        }

        return response('Forbidden', 403);
    }
    // WhatsApp Webhook
    public function whatsappWebhookPost(Request $request)
    {
        $input = $request->all();

        // Get contact info from WhatsApp webhook
        $contact = $input['entry'][0]['changes'][0]['value']['contacts'][0] ?? null;

        if ($contact) {
            $phone = $contact['wa_id']; // Sender phone number
            $name = $contact['profile']['name'] ?? 'WhatsApp Client';

            // Check if client already exists
            $existingLead = CrmClient::where('contact_no', $phone)->first();

            if (!$existingLead) {
                // Create CRM Client
                $client = CrmClient::create([
                    'sister_concern_id' => 1,
                    'name' => $name,
                    'contact_no' => $phone,
                ]);

                // Create Lead linked to CRM Client
                $lead = Lead::create([
                    'sister_concern_id' => 1,
                    'order_no' => generateOrder(1),
                    'crm_client_id' => $client->id,

                    'assigned_to' => auth()->id() ?? null,
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
    // public function whatsappWebhookPost(Request $request)
    // {
    //     $input = $request->all();

    //     $phone = $input['messages'][0]['from'] ?? null;
    //     $name = $input['contacts'][0]['profile']['name'] ?? null;

    //     if ($phone) {
    //         Lead::updateOrCreate(
    //             ['phone' => $phone, 'source' => 'whatsapp'],
    //             ['name' => $name, 'phone' => $phone, 'source' => 'whatsapp']
    //         );
    //     }

    //     return response()->json(['status' => 'ok']);
    // }

    // Instagram Webhook
    public function instagramWebhook(Request $request){
        $verify_token = env('IG_VERIFY_TOKEN');
        if ($request->hub_mode === 'subscribe' && $request->hub_verify_token === $verify_token) {
            return $request->hub_challenge;
        }
        return response('Forbidden', 403);
    }
    public function instagramWebhookPost(Request $request)
    {
        $input = $request->all();

        $userId = $input['entry'][0]['messaging'][0]['sender']['id'] ?? null;
        $username = $input['entry'][0]['messaging'][0]['sender']['username'] ?? null;

        if ($userId) {
            Lead::updateOrCreate(
                ['phone' => $userId, 'source' => 'instagram'],
                ['name' => $username, 'phone' => $userId, 'source' => 'instagram']
            );
        }

        return response()->json(['status' => 'ok']);
    }
}
