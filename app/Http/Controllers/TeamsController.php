<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Services\Bot\MessageNormalizerService;
use App\Services\Bot\BotUserService;
use App\Services\Bot\BotSessionService;
use App\Services\Bot\GlobalIntentService;
use App\Services\Bot\FlowProcessorService;
use App\Services\Bot\TeamsAuthService;

class TeamsController extends Controller
{
    public function handle(Request $request)
    {
        try {

            $payload = $request->all();

            Log::info("Teams Incoming Payload", $payload);

            /*
            |--------------------------------------------------------------------------
            | STEP 1 — Normalize Teams Payload → Standard Bot Format
            |--------------------------------------------------------------------------
            */

            $normalizer = new MessageNormalizerService();

            $data = $normalizer->normalizeTeams($payload);

            /*
            |--------------------------------------------------------------------------
            | STEP 2 — Resolve Bot User
            |--------------------------------------------------------------------------
            */

            $userService = new BotUserService();

            $user = $userService->findOrCreateUser(
                $data['channel'],
                $data['user_id']
            );

            /*
            |--------------------------------------------------------------------------
            | STEP 3 — Resolve Session
            |--------------------------------------------------------------------------
            */

            $sessionService = new BotSessionService();

            $session = $sessionService->getActiveSessionForUser($user);

            /*
            |--------------------------------------------------------------------------
            | STEP 4 — Global Intent (hi / bye / exit / help etc)
            |--------------------------------------------------------------------------
            */

            $globalIntent = new GlobalIntentService();

            $globalReply = $globalIntent->handle(
                $data['message_text'],
                $session
            );

            /*
            |--------------------------------------------------------------------------
            | STEP 5 — Flow Engine OR Global Reply
            |--------------------------------------------------------------------------
            */

            if ($globalReply !== null) {

                $replyText = $globalReply;

            } else {

                $flow = new FlowProcessorService();

                $replyText = $flow->processMessage(
                    $session,
                    $data['message_text'],
                    $data['attachments'] ?? []
                );
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 6 — Send Reply To Teams (Bot Framework Activity)
            |--------------------------------------------------------------------------
            */

            $this->sendTeamsReply($payload, $replyText);

            return response()->json([], 200);

        } catch (\Throwable $e) {

            Log::error("Teams Bot Error: " . $e->getMessage());

            return response()->json([], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send Reply Using Bot Framework Activity Protocol
    |--------------------------------------------------------------------------
    */


    public function sendMessage($serviceUrl, $conversationId, $token, $messageText) {
        $url = rtrim($serviceUrl, '/') . "/v3/conversations/{$conversationId}/activities";
        
        $payload = json_encode([
            "type" => "message",
            "text" => $messageText
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }


    private function sendTeamsReply(array $payload, string $replyText): void
    {
        $authService = new TeamsAuthService();
        $token = $authService->getAccessToken();

        $serviceUrl = rtrim($payload['serviceUrl'], '/');

        // $response = Http::withToken($token)->post(
        //     $serviceUrl . "/v3/conversations/" . $payload['conversation']['id'] . "/activities",
        //     [
        //         "type" => "message",
        //         "channelId" => $payload["channelId"],
        //         "serviceUrl" => $payload["serviceUrl"],

        //         "from" => [
        //             "id" => $payload["recipient"]["id"],
        //             "name" => $payload["recipient"]["name"]
        //         ],

        //         "recipient" => [
        //             "id" => $payload["from"]["id"],
        //             "name" => $payload["from"]["name"]
        //         ],

        //         "conversation" => [
        //             "id" => $payload["conversation"]["id"]
        //         ],

        //         "text" => $replyText
        //     ]
        // );

        $response = $this->sendMessage(
            $payload['serviceUrl'],
            $payload['conversation']['id'],
            $token,
            $replyText
        );
        Log::info("Send Response", [
         'response' => $response
        ]);

    }
}
