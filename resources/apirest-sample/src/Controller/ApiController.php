<?php

namespace App\Controller;

use Core\Request;
use App\Service\Mailer;
use App\Service\TaskQueue;

class ApiController
{
    /**
     * POST /api/test/testMail
     * Uses PHPMailer to send via MailHog (local SMTP on port 1025 by default).
     */
    public function testMail(Request $request)
    {
        try {
            $mailer = new Mailer;
            $payload = [
                'to' => 'admin@example.com',
                'subject' => 'Testing email from platform installation',
                'body' => 'This is a testing email from platform install home page, sent at ' . date('Y-m-d H:i:s'),
                'from' => 'dev@example.com',
                'from_name' => 'Dev user',
            ];
            $email = $mailer->send($payload);

            return response()->json(['status' => $email->status, 'message' => $email->message]);

        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'Send failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/test/queue
     * Publishes JSON payload to a RabbitMQ queue (php-amqplib required).
     */
    public function testQueue(Request $request)
    {
        $task = 'installation-test-email';
        $payload = [
            'to' => 'admin@example.com',
            'subject' => 'Testing email from broker installation',
            'body' => 'This is a testing email from broker queued message, sent at ' . date('Y-m-d H:i:s'),
            'from' => 'broker@example.com',
            'from_name' => 'Broker',
        ];

        $queue = (new TaskQueue)->set($task, $payload);

        if ($queue->status === false) {
            return response()->json(['status' => false, 'message' => 'Queue error: ' . $queue->error], 500);
        }

        return response()->json(['status' => true, 'message' => 'Message queued.']);
    }
}
