<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\BotUser;

class BroadcastController extends Controller
{
    public function send(Request $request)
    {
        // Bearer Token Protection
        $authHeader = $request->header('Authorization');

        if (!$authHeader || $authHeader !== 'Bearer ' . config('services.broadcast.token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $message = $request->input('message');

        if (!$message) {
            return response()->json(['error' => 'Message required'], 400);
        }

        $users = BotUser::where('channel', 'whatsapp')
            ->where('is_active', true)
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($users as $user) {

            if (!$this->isWithin24Hours($user)) {
                $skipped++;
                continue;
            }

            $this->sendTextMessage($user->channel_user_id, $message);
            $sent++;
        }

        return response()->json([
            'status' => 'completed',
            'sent' => $sent,
            'skipped_outside_24h' => $skipped
        ]);
    }

    private function sendTextMessage(string $to, string $message)
    {
        $accessToken = config('services.whatsapp.access_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');

        Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v22.0/{$phoneNumberId}/messages", [
                "messaging_product" => "whatsapp",
                "to" => $to,
                "type" => "text",
                "text" => [
                    "body" => $message
                ]
            ]);
    }

    private function isWithin24Hours($user): bool
    {
        if (!$user->last_received_message_timestamp) return false;

        return Carbon::parse($user->last_received_message_timestamp)
            ->gt(Carbon::now()->subHours(24));
    }
}