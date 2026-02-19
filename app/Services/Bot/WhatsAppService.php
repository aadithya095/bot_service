<?php

namespace App\Services\Bot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $phoneNumberId;

    public function __construct()
    {
        $this->token = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    public function sendText(string $to, string $message): void
    {
        $response = Http::withToken($this->token)
            ->post("https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages", [
                "messaging_product" => "whatsapp",
                "to" => $to,
                "type" => "text",
                "text" => [
                    "body" => $message
                ]
            ]);

        Log::info('WA Text Response', [
            'status' => $response->status(),
            'body' => $response->json()
        ]);
    }

    public function sendButtons(string $to, string $message, array $buttons): void
    {
        $formatted = [];

        foreach ($buttons as $btn) {
            $formatted[] = [
                "type" => "reply",
                "reply" => [
                    "id" => $btn['id'],
                    "title" => substr($btn['title'], 0, 20)
                ]
            ];
        }

        Http::withToken($this->token)->post(
            "https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages",
            [
                "messaging_product" => "whatsapp",
                "to" => $to,
                "type" => "interactive",
                "interactive" => [
                    "type" => "button",
                    "body" => ["text" => $message],
                    "action" => ["buttons" => $formatted]
                ]
            ]
        );
    }

    public function sendList(string $to, string $message, array $rows): void
    {
        Http::withToken($this->token)->post(
            "https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages",
            [
                "messaging_product" => "whatsapp",
                "to" => $to,
                "type" => "interactive",
                "interactive" => [
                    "type" => "list",
                    "body" => ["text" => $message],
                    "action" => [
                        "button" => "View Options",
                        "sections" => [
                            [
                                "title" => "Available Options",
                                "rows" => $rows
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function sendDocument(string $to, string $fileUrl, string $fileName): void
    {
        $response = Http::withToken($this->token)
            ->post("https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages", [
                "messaging_product" => "whatsapp",
                "to" => $to,
                "type" => "document",
                "document" => [
                    "link" => $fileUrl,
                    "filename" => $fileName
                ]
            ]);

        Log::info('WA Document Response', [
            'status' => $response->status(),
            'body' => $response->json()
        ]);
    }
}