<?php

namespace App\Services\Bot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TeamsAuthService
{
    public function getAccessToken()
    {
        // Cache token to avoid hitting Microsoft every request
        return Cache::remember('teams_bot_token', 3000, function () {

            $response = Http::asForm()->post(
                "https://login.microsoftonline.com/botframework.com/oauth2/v2.0/token",
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('services.teams.app_id'),
                    'client_secret' => config('services.teams.app_secret'),
                    'scope' => 'https://api.botframework.com/.default'
                ]
            );
            return $response['access_token'];
        });
    }
}