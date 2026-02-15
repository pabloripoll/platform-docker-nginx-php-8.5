<?php

namespace App\Service;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class TaskQueue
{
    /**
     * Dispatch task to the appropriate handler.
     */
    public function set(string $task, array $payload = []): object
    {
        $message = [
            'task' => $task,
            'payload' => $payload
        ];

        try {
            $queue = env('RABBITMQ_EXCHANGE_QUEUE');

            // Use the factory that returns an AMQPStreamConnection
            /** @var AMQPStreamConnection $connection */
            $connection = Broker::createConnection();

            $channel = $connection->channel();

            // durable queue
            $channel->queue_declare($queue, false, true, false, false);

            // Delivery mode: prefer the library constant if available, otherwise fallback to 2 (persistent)
            /* @ignored const DELIVERY_MODE_PERSISTENT */
            $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT ?? 2;

            $messageToString = json_encode($message);
            $msg = new AMQPMessage($messageToString, [
                'content_type'  => 'application/json',
                'delivery_mode' => $deliveryMode,
            ]);

            $channel->basic_publish($msg, '', $queue);

            $channel->close();
            $connection->close();

            $response = new \stdClass;
            $response->status = true;

            return $response;

        } catch (\Throwable $e) {
            $response = new \stdClass;
            $response->status = true;
            $response->error = $e->getMessage();

            return $response;
        }
    }
}
