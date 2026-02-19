<?php
namespace App\Services\Bot;

use App\Models\BotSession;

class GlobalIntentService
{
    public function handle(string $message, BotSession $session): string|array|null
    {
        $msg = strtolower(trim($message));

        /*
        |--------------------------------------------------------------------------
        | GREETING → SHOW MENU LIST
        |--------------------------------------------------------------------------
        */
        if (in_array($msg, ['hi', 'hello', 'hey', 'hii', 'helo'])) {
            return [
                'type'    => 'list',
                'message' => 'Welcome! Please choose an option:',
                'rows'    => [
                    [
                        'id'          => 'ticket',
                        'title'       => 'Create Ticket',
                        'description' => 'Raise a support ticket'
                    ],
                    [
                        'id'          => 'invoice',
                        'title'       => 'View Invoices',
                        'description' => 'Get past invoices'
                    ],
                    [
                        'id'          => 'meeting',
                        'title'       => 'Today\'s Meetings',
                        'description' => 'View today meetings'
                    ],
                    [
                        'id'          => 'forum',
                        'title'       => 'Forums',
                        'description' => 'Browse forum posts'
                    ]
                ]
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | THANKS / ACKNOWLEDGEMENT → exact match only
        |--------------------------------------------------------------------------
        */
        if (in_array($msg, [
            'thanks',
            'thank you',
            'thankyou',
            'thank u',
            'thx',
            'ty',
            'ok',
            'okay',
            'ok thanks',
            'ok thank you',
            'great',
            'good',
            'nice',
            'perfect',
            'awesome',
            'got it',
            'noted',
            'done',
            'cool'
        ])) {
            return "You're welcome! 😊 Let me know if you need anything else. Type *hi* to see the menu.";
        }

        /*
        |--------------------------------------------------------------------------
        | EXIT → exact match only
        |--------------------------------------------------------------------------
        */
        if (in_array($msg, ['bye', 'exit', 'quit', 'goodbye', 'see you', 'cya'])) {
            $session->update([
                'current_command' => null,
                'current_step'    => null,
            ]);
            return "Goodbye! 👋 Have a great day. Type *hi* anytime to start again.";
        }

        return null;
    }
}