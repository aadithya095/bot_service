<?php
namespace App\Services\Bot;

use App\Models\BotSession;
use App\Models\Invoice;
use App\Services\Bot\CommandService;
use App\Services\Bot\WhatsAppService;

class FlowProcessorService
{
    public function processMessage(
        BotSession $session,
        string $message,
        array $attachments = []
    ): string|array|null {

        $commands = config('bot.commands');
        $message  = trim(strtolower($message));

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ HANDLE INVOICE NUMBER SELECTION
        |--------------------------------------------------------------------------
        */
        $invoice = Invoice::where('invoice_number', strtoupper($message))->first();

        if ($invoice) {

            $whatsApp = new WhatsAppService();

            $whatsApp->sendDocument(
                $session->botUser->channel_user_id,
                $invoice->pdf_url,
                $invoice->invoice_number . '.pdf'
            );

            $session->update([
                'current_command' => null,
                'current_step'    => null,
            ]);

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ FORCE NEW COMMAND OVERRIDE + DETECTION (combined)
        |--------------------------------------------------------------------------
        */
        $commandService = new CommandService();
        $command        = $commandService->resolveCommandFromMessage($message); // ← resolved once

        if ($command) {
            $session->update([
                'current_command' => null,
                'current_step'    => null
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ NEW COMMAND FLOW START
        |--------------------------------------------------------------------------
        */
        if (!$session->current_command) {

            if (!$command || !isset($commands[$command])) {
                return "I did not understand. Try 'ticket', 'invoice', 'meeting' or 'forum'.";
            }

            $commandConfig = $commands[$command];
            $startStep     = $commandConfig['steps']['start'] ?? null;

            if (!$startStep) {
                return "Flow configuration error.";
            }

            $session->update([
                'current_command' => $command,
                'current_step'    => $startStep['next_step'] ?? null
            ]);

            if (isset($startStep['action'])) {

                $session->update([
                    'current_command' => null,
                    'current_step'    => null
                ]);

                return [
                    'type'   => 'action',
                    'action' => $startStep['action']
                ];
            }

            return $startStep['response'] ?? null;
        }

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ ATTACHMENT HANDLING (TICKET FLOW)
        |--------------------------------------------------------------------------
        */
        if ($session->current_step === 'waiting_file') {

            if (!empty($attachments)) {

                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'video/mp4',
                    'video/3gpp',
                ];

                $invalidFiles = [];
                $validFiles   = [];

                foreach ($attachments as $attachment) {
                    if (in_array($attachment['mime_type'] ?? '', $allowedMimeTypes)) {
                        $validFiles[] = $attachment;
                    } else {
                        $invalidFiles[] = $attachment['mime_type'] ?? 'unknown';
                    }
                }

                if (!empty($invalidFiles)) {
                    return "⚠️ Sorry, the file type *" . implode(', ', $invalidFiles) . "* is not supported.\n\nPlease upload one of the following:\n• 📷 Image (JPG, PNG, GIF, WEBP)\n• 📄 PDF\n• 📝 Word Document (DOC, DOCX)\n• 🎥 Video (MP4)\n\nOr type *skip* to create the ticket without an attachment.";
                }

                $session->update([
                    'current_command' => null,
                    'current_step'    => null,
                ]);

                return "✅ Ticket created with attachment successfully.";
            }

            if (in_array($message, ['skip', 'no'])) {

                $session->update([
                    'current_command' => null,
                    'current_step'    => null,
                ]);

                return "✅ Ticket created without attachment.";
            }

            return "📎 Please upload a file or type *skip* to continue without one.";
        }

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ CONTINUE STATE FLOW
        |--------------------------------------------------------------------------
        */
        $command = $session->current_command;

        if (!isset($commands[$command])) {
            return "Command not supported.";
        }

        $commandConfig = $commands[$command];
        $step          = $session->current_step;

        if (!$step || !isset($commandConfig['steps'][$step])) {
            return "Flow error.";
        }

        $stepConfig = $commandConfig['steps'][$step];

        if (isset($stepConfig['action'])) {

            $session->update([
                'current_command' => null,
                'current_step'    => null
            ]);

            return [
                'type'   => 'action',
                'action' => $stepConfig['action']
            ];
        }

        if (($stepConfig['next_step'] ?? null) === null) {

            $session->update([
                'current_command' => null,
                'current_step'    => null
            ]);

        } else {

            $session->update([
                'current_step' => $stepConfig['next_step']
            ]);
        }

        return $stepConfig['response'] ?? null;
    }
}