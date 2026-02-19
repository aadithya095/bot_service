<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Services\Bot\MessageNormalizerService;
use App\Services\Bot\BotUserService;
use App\Services\Bot\BotSessionService;
use App\Services\Bot\GlobalIntentService;
use App\Services\Bot\FlowProcessorService;
use App\Services\Bot\WhatsAppService;
use App\Services\Bot\InvoiceService;
use App\Services\Bot\MeetingService;
use App\Services\Bot\ForumService;

use App\Models\BotAttachment;
use App\Models\BotMessage;

class WhatsAppController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Webhook Verification
    |--------------------------------------------------------------------------
    */
    public function verify(Request $request)
    {
        if (
            $request->query('hub_mode') === 'subscribe' &&
            $request->query('hub_verify_token') === config('services.whatsapp.verify_token')
        ) {
            return response($request->query('hub_challenge'), 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Incoming Webhook
    |--------------------------------------------------------------------------
    */
    public function handle(Request $request)
    {
        try {

            $payload = $request->all();
            $value   = $payload['entry'][0]['changes'][0]['value'] ?? [];

            if (isset($value['statuses'])) {
                return response()->json(['status' => 'status_update'], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Convert Interactive to Text
            |--------------------------------------------------------------------------
            */
            if (
                isset($value['messages'][0]['type']) &&
                $value['messages'][0]['type'] === 'interactive'
            ) {

                $interactive = $value['messages'][0]['interactive'] ?? null;
                $selectedId = null;

                if (isset($interactive['button_reply'])) {
                    $selectedId = $interactive['button_reply']['id'];
                }

                if (isset($interactive['list_reply'])) {
                    $selectedId = $interactive['list_reply']['id'];
                }

                if ($selectedId) {

                    $payload['entry'][0]['changes'][0]['value']['messages'][0]['type'] = 'text';
                    $payload['entry'][0]['changes'][0]['value']['messages'][0]['text'] = [
                        'body' => $selectedId
                    ];

                    unset($payload['entry'][0]['changes'][0]['value']['messages'][0]['interactive']);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Normalize
            |--------------------------------------------------------------------------
            */
            $normalizer = new MessageNormalizerService();
            $data = $normalizer->normalizeWhatsApp($payload);

            $messageText   = $data['message_text'] ?? '';
            $attachments   = $data['attachments'] ?? [];
            $channel       = $data['channel'];
            $channelUserId = $data['user_id'];

            /*
            |--------------------------------------------------------------------------
            | Resolve User + Session
            |--------------------------------------------------------------------------
            */
            $userService = new BotUserService();
            $user = $userService->findOrCreateUser($channel, $channelUserId);

            $sessionService = new BotSessionService();
            $session = $sessionService->getActiveSessionForUser($user);

            $whatsApp = new WhatsAppService();

            /*
            |--------------------------------------------------------------------------
            | Global Intent
            |--------------------------------------------------------------------------
            */
            $globalIntent = new GlobalIntentService();
            $globalReply  = $globalIntent->handle($messageText, $session);

            if ($globalReply !== null) {

                if (is_array($globalReply) && $globalReply['type'] === 'list') {

                    $whatsApp->sendList(
                        $channelUserId,
                        $globalReply['message'],
                        $globalReply['rows']
                    );

                } else {

                    $whatsApp->sendText($channelUserId, $globalReply);
                }

                return response()->json(['status' => 'ok'], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Flow Processor
            |--------------------------------------------------------------------------
            */
            $flow = new FlowProcessorService();
            $reply = $flow->processMessage(
                $session,
                $messageText,
                $attachments   // IMPORTANT FIX
            );

            if ($reply === null) {
                return response()->json(['status' => 'processed'], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Handle Action
            |--------------------------------------------------------------------------
            */
            if (is_array($reply) && $reply['type'] === 'action') {

                switch ($reply['action']) {

                    case 'show_invoices':
                        $invoiceService = new InvoiceService();
                        $invoices = $invoiceService->getLatest();

                        if ($invoices->isEmpty()) {
                            $whatsApp->sendText($channelUserId, "No invoices found.");
                            break;
                        }

                        if ($invoices->count() <= 3) {

                            $buttons = [];

                            foreach ($invoices as $invoice) {
                                $buttons[] = [
                                    'id' => $invoice->invoice_number,
                                    'title' => $invoice->invoice_number
                                ];
                            }

                            $whatsApp->sendButtons(
                                $channelUserId,
                                "Select an invoice:",
                                $buttons
                            );

                        } else {

                            $rows = [];

                            foreach ($invoices as $invoice) {
                                $rows[] = [
                                    'id' => $invoice->invoice_number,
                                    'title' => $invoice->invoice_number,
                                    'description' => "Amount: ₹" . $invoice->amount
                                ];
                            }

                            $whatsApp->sendList(
                                $channelUserId,
                                "Select an invoice:",
                                $rows
                            );
                        }
                        break;

                    case 'show_meetings':
                        $meetingService = new MeetingService();
                        $text = $meetingService->getTodayMeetingsText();
                        $whatsApp->sendText($channelUserId, $text);
                        break;

                    case 'show_forums':
                        $forumService = new ForumService();
                        $text = $forumService->getForumsText();
                        $whatsApp->sendText($channelUserId, $text);
                        break;
                }

                return response()->json(['status' => 'processed'], 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Normal Text Reply
            |--------------------------------------------------------------------------
            */
            $whatsApp->sendText($channelUserId, $reply);

            return response()->json(['status' => 'processed'], 200);

        } catch (\Throwable $e) {

            Log::error('WhatsApp Bot Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error'], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 24 Hour Check
    |--------------------------------------------------------------------------
    */
    private function isWithin24Hours($user): bool
    {
        if (!$user->last_received_message_timestamp) {
            return false;
        }

        return Carbon::parse($user->last_received_message_timestamp)
            ->gt(Carbon::now()->subHours(24));
    }

    /*
    |--------------------------------------------------------------------------
    | Download Media
    |--------------------------------------------------------------------------
    */
    private function downloadMedia(string $mediaId): ?string
    {
        $token = config('services.whatsapp.access_token');

        $meta = \Illuminate\Support\Facades\Http::withToken($token)
            ->get("https://graph.facebook.com/v22.0/{$mediaId}");

        if (!$meta->ok()) return null;

        $url = $meta->json()['url'] ?? null;
        if (!$url) return null;

        $file = \Illuminate\Support\Facades\Http::withToken($token)->get($url);
        if (!$file->ok()) return null;

        $fileName = 'whatsapp_' . $mediaId . '.bin';
        $path = "whatsapp/{$fileName}";

        Storage::disk('public')->put($path, $file->body());

        return $path;
    }
}