<?php

namespace App\Services\Bot;

class MessageNormalizerService
{
    public function normalizeWeb($botMessage): array
    {
        $extras = method_exists($botMessage, 'getExtras')
            ? $botMessage->getExtras()
            : [];

        return [
            'channel' => 'web',
            'user_id' => $botMessage->getSender() ?? 'web_user',
            'message_text' => $botMessage->getText() ?? '',
            'attachments' => $extras['attachments'] ?? []
        ];
    }


    // Placeholder — WhatsApp
    public function normalizeWhatsApp(array $payload): array
    {
        $messageData = $payload['entry'][0]['changes'][0]['value']['messages'][0];

        $type = $messageData['type'] ?? 'text';
        $attachments = [];
        $text = '';

        if ($type === 'text') {
            $text = $messageData['text']['body'] ?? '';
        }

        if (in_array($type, ['image', 'document', 'audio', 'video'])) {

            $attachments[] = [
                'type' => $type,
                'media_id' => $messageData[$type]['id'] ?? null,
                'mime_type' => $messageData[$type]['mime_type'] ?? null
            ];
        }

        return [
            'channel' => 'whatsapp',
            'user_id' => $messageData['from'],
            'message_text' => $text,
            'attachments' => $attachments
        ];
    }

    // Placeholder — Teams
    public function normalizeTeams(array $payload): array
    {
        return [
            'channel' => 'teams',
            'user_id' => $payload['from']['id'] ?? '',
            'message_text' => $payload['text'] ?? '',
            'attachments' => [] // Later file handling
        ];
    }
}
