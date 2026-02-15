<?php

require __DIR__ . '/vendor/autoload.php';

use App\Service\Broker;
use App\Service\TaskDispatcher;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

if (php_sapi_name() !== 'cli') {
    echo "This consumer is intended to run from CLI.", PHP_EOL;
    exit(1);
}

// --- Create AMQP connection connection and channel ---
try {
    $connection = Broker::createConnection();
    $channel = $connection->channel();
} catch (\Exception $e) {
    echo "Failed to create AMQP connection: ", $e->getMessage(), PHP_EOL;
    exit(1);
}

// --- Exchange / queue setup ---
// Simple default: one exchange that all producers publish to and one queue that binds to all routing keys.
// This lets this worker receive "all" tasks without having to subscribe to many separate queue names.
$queueName = env('RABBITMQ_EXCHANGE_QUEUE');

// --- declare (durable) queue for this worker group ---
$channel->queue_declare($queueName, false, true, false, false);

// --- Fair dispatch ---
$channel->basic_qos(null, 1, null);

echo " [*] Waiting for messages on {$queueName}. To exit press CTRL+C", PHP_EOL;

// Build dispatcher and auto-register handlers via HandlerRegistry
// --- Dispatcher & handler auto-registration (simple) ---
$dispatcher = new TaskDispatcher();

// --- Message callback ---
$callback = function (AMQPMessage $amqpm) use ($dispatcher) {

    echo " [x] Received: ", $amqpm->getBody(), PHP_EOL;

    $message = json_decode($amqpm->getBody(), true);

    if (json_last_error() !== JSON_ERROR_NONE || ! is_array($message)) {
        error_log("[consumer] invalid JSON, rejecting message");
        $amqpm->nack(false, false); // reject, no requeue
        echo " [!] Invalid JSON — message rejected (no requeue)", PHP_EOL;
        return;
    }

    if (empty($message['task'])) {
        error_log("[consumer] missing task, rejecting message");
        $amqpm->nack(false, false); // reject, no requeue
        echo " [!] Missing task — message rejected (no requeue)", PHP_EOL;
        return;
    }

    $attempts = isset($message['attempts']) ? (int) $message['attempts'] : 0;
    $message['attempts'] = $attempts + 1;

    try {
        $status = $dispatcher->dispatch($message['task'], $message['payload'] ?? [], $message);

        if ($status === true) {
            $amqpm->ack();
            echo " [v] Job succeeded — message acknowledged", PHP_EOL;
            return;
        }

        // handler returned false -> treat as transient failure
        if ($message['attempts'] >= 3) {
            error_log("[consumer] max attempts reached for message_id=" . ($message['message_id'] ?? ''));
            $amqpm->nack(false, false); // reject, no requeue (consider DLX)
            echo " [x] Max attempts reached — message rejected (no requeue)", PHP_EOL;
        } else {
            // simple immediate retry by requeueing (minimal approach)
            $amqpm->nack(false, true);
            echo " [!] Handler returned false — message requeued (attempt {$message['attempts']})", PHP_EOL;
        }
    } catch (\Throwable $e) {
        error_log("[consumer] Exception while processing message: " . $e->getMessage());
        $amqpm->nack(false, false); // reject, no requeue
        echo " [x] Exception occurred — message rejected (no requeue)", PHP_EOL;
    }
};

// --- start consuming from the single worker queue which is bound to the exchange for all tasks ---
$consumerTag = $channel->basic_consume($queueName, '', false, false, false, false, $callback);

// --- graceful shutdown support ---
$stop = false;
$cancelConsumer = function() use (&$stop, $channel, $consumerTag) {
    echo PHP_EOL, "SIGINT/SIGTERM received, cancelling consumer...", PHP_EOL;
    $stop = true;
    try {
        $channel->basic_cancel($consumerTag);
    } catch (\Exception $e) {
        // ignore
    }
};

if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
    pcntl_signal(SIGINT, $cancelConsumer);
    pcntl_signal(SIGTERM, $cancelConsumer);
} else {
    echo "pcntl_async_signals not available; signals may not be handled until wait() returns.", PHP_EOL;
}

// --- main loop ---
try {
    while ($channel->is_consuming() && ! $stop) {
        try {
            $channel->wait(null, false, 5);
        } catch (AMQPTimeoutException $e) {
            if (! function_exists('pcntl_async_signals')) {
                @pcntl_signal_dispatch();
            }
            gc_collect_cycles();
            continue;
        } catch (\Exception $e) {
            echo "Unexpected exception in wait(): " . $e->getMessage() . PHP_EOL;
            break;
        }
    }
} catch (\Exception $e) {
    echo "Consumer loop exited with exception: " . $e->getMessage() . PHP_EOL;
}

// --- cleanup ---
try {
    if ($channel->is_open()) $channel->close();
    if ($connection->isConnected()) $connection->close();
} catch (\Exception $e) {
    // ignore close errors
}

echo "Consumer stopped.", PHP_EOL;
