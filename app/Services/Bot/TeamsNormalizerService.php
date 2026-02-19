<?php

namespace App\Services\Bot;

class TeamsNormalizerService
{
    public function normalize(array $payload): array
    {
        return [
            'message_text' => $payload['text'] ?? '',
            'channel' => 'teams',
            'user_id' => $payload['from']['id'] ?? null,
            'conversation_id' => $payload['conversation']['id'] ?? null,
            'service_url' => $payload['serviceUrl'] ?? null,
            'attachments' => $payload['attachments'] ?? [],
            'raw' => $payload
        ];
    }
}
