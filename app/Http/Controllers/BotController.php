<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Web\WebDriver;

use App\Services\Bot\BotUserService;
use App\Services\Bot\MessageNormalizerService;
use App\Services\Bot\BotSessionService;
use App\Services\Bot\GlobalIntentService;
use App\Services\Bot\FlowProcessorService;

class BotController extends Controller
{
    public function handle(Request $request)
    {
        DriverManager::loadDriver(WebDriver::class);

        $botman = BotManFactory::create([], null, $request);

        $botman->hears('.*', function ($bot) {

            try {

                // ⭐ STEP 1 — Normalize Message
                $normalizer = new MessageNormalizerService();

                $normalized = $normalizer->normalizeWeb(
                    $bot->getMessage()
                );

                $message = $normalized['message_text'];
                $channel = $normalized['channel'];
                $channelUserId = $normalized['user_id'];
                $attachments = $normalized['attachments'];

                // ⭐ STEP 2 — Resolve User
                $userService = new BotUserService();

                $user = $userService->findOrCreateUser(
                    $channel,
                    $channelUserId
                );

                // ⭐ STEP 3 — Get Session
                $sessionService = new BotSessionService();

                $session = $sessionService->getActiveSessionForUser($user);

                // ⭐ STEP 4 — Global Intent Layer
                $globalIntentService = new GlobalIntentService();

                $globalReply = $globalIntentService->handle(
                    $message,
                    $session
                );

                if ($globalReply !== null) {
                    $bot->reply($globalReply);
                    return;
                }

                // ⭐ STEP 5 — Workflow Engine
                $flowService = new FlowProcessorService();

                $replyText = $flowService->processMessage(
                    $session,
                    $message,
                    $attachments
                );

                // ⭐ STEP 6 — Reply
                $bot->reply($replyText);

            } catch (\Throwable $e) {

                \Log::error('Bot Error: ' . $e->getMessage());

                $bot->reply(
                    "Sorry, something went wrong. Please try again."
                );
            }

        });

        $botman->listen();
    }
}
