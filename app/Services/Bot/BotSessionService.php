<?php

namespace App\Services\Bot;

use App\Models\BotSession;
use App\Models\BotUser;
use Carbon\Carbon;

class BotSessionService
{
    public function getActiveSessionForUser(BotUser $user): BotSession
    {
        $session = BotSession::where('bot_user_id', $user->id)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$session) {
            $session = BotSession::create([
                'bot_user_id' => $user->id,
                'expires_at' => Carbon::now()->addHours(24)
            ]);
        }

        return $session;
    }

    public function updateSessionStep(
        BotSession $session,
        string $command,
        string $step,
        array $data = []
    ) {
        $session->update([
            'current_command' => $command,
            'current_step' => $step,
            'session_data' => $data,
            'expires_at' => Carbon::now()->addHours(24)
        ]);

        return $session;
    }
}
