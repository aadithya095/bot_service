<?php

namespace App\Services\Bot;

class CommandService
{
    public function resolveCommandFromMessage(string $message): ?string
    {
        $message = strtolower($message);

        $commands = config('bot.commands');

        foreach ($commands as $commandName => $commandData) {

            foreach ($commandData['keywords'] as $keyword) {

                if (str_contains($message, strtolower($keyword))) {
                    return $commandName;
                }

            }
        }

        return null;
    }

}
