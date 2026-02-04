<?php

namespace App\Services\Bot;

use App\Models\BotSession;

class FlowProcessorService
{
    public function processMessage(
        BotSession $session,
        string $message
    ): string {

        $commands = config('bot.commands');

        // If no command yet → detect new command
        if (!$session->current_command) {

            $commandService = new CommandService();
            $command = $commandService->resolveCommandFromMessage($message);

            if (!$command || !isset($commands[$command])) {
                return "I did not understand. Try 'ticket' or 'invoice'.";
            }

            $commandConfig = $commands[$command];
            $startStep = $commandConfig['steps']['start'];

            // Update session
            $session->update([
                'current_command' => $command,
                'current_step' => $startStep['next_step']
            ]);

            return $startStep['response'];
        }

        // Continue existing flow
        $command = $session->current_command;

        if (!isset($commands[$command])) {
            return "Command not supported.";
        }

        $commandConfig = $commands[$command];
        $step = $session->current_step;

        if (!$step || !isset($commandConfig['steps'][$step])) {
            return "Flow error.";
        }

        $stepConfig = $commandConfig['steps'][$step];

        // Move to next step
        $session->update([
            'current_step' => $stepConfig['next_step']
        ]);

        return $stepConfig['response'];
    }

}
