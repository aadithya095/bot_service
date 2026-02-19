<?php

namespace App\Services\Bot;

class CommandService
{
    public function resolveCommandFromMessage(string $message): ?string
    {
        $message = strtolower(trim($message));

        \Log::info('Loaded Commands', ['commands' => array_keys(config('bot') ?? [])]);
        // FIXED: Correct config key
        $commands = config('bot.commands');

        if (!$commands) {
            return null;
        }

        foreach ($commands as $commandName => $commandData) {

            if (!isset($commandData['keywords'])) {
                continue;
            }

            foreach ($commandData['keywords'] as $keyword) {

                if ($message === strtolower($keyword)) {
                    return $commandName;
                }

            }
        }



        return null;
    }
}