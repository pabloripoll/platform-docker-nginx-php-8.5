<?php
namespace App\Task;

use App\Service\Mailer;

/**
 * Example task class showing the expected signature.
 * Put real Mailer/DB dependencies via constructor if you have a DI container.
 */
class EmailTask
{
    /**
     * Example instance method used by the dispatcher.
     */
    public static function installationTestEmail(array $payload = [], array $envelope = []): bool
    {
        try {
            $mailer = new Mailer;
            $payload = [
                'to' => $payload['to'] ?? 'admin@example.com',
                'subject' => $payload['subject'] ?? 'Testing email from broker installation',
                'body' => $payload['body'] ?? 'This is a testing email from broker queued message, sent at ' . date('Y-m-d H:i:s'),
                'from' => $payload['from'] ?? 'dev@example.com',
                'from_name' => $payload['from_name'] ?? 'Dev user',
            ];

            $sent = $mailer->send($payload);

            return (bool) $sent->status;

        } catch (\Throwable $e) {
            error_log("[EmailTask] send failed: " . $e->getMessage());

            return false;
        }
    }
}
