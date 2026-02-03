<?php

namespace App\Services\Bot;

use App\Models\BotUser;
use Carbon\Carbon;

class BotUserService
{
    public function findOrCreateUser(string $channel, string $channelUserId): BotUser
    {
        $user = BotUser::where('channel', $channel)
            ->where('channel_user_id', $channelUserId)
            ->first();

        if (!$user) {
            return BotUser::create([
                'channel' => $channel,
                'channel_user_id' => $channelUserId,
                'last_received_message_timestamp' => Carbon::now(),
                'is_active' => true
            ]);
        }

        // Update timestamp every time message comes
        $user->update([
            'last_received_message_timestamp' => Carbon::now()
        ]);

        return $user;
    }
}
