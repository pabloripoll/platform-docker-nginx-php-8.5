<?php

namespace App\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class Broker
{
    private AMQPStreamConnection $connection;

    public function __construct()
    {
        $host = env('RABBITMQ_HOST', '127.0.0.1');
        $port = (int) env('RABBITMQ_PORT', 5672);
        $user = env('RABBITMQ_USER', 'guest');
        $pass = env('RABBITMQ_PASS', 'guest');
        $vhost = env('RABBITMQ_VHOST', '/');

        $this->connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
    }

    /**
     * Return the underlying channel (delegates to AMQPStreamConnection)
     */
    public function channel()
    {
        return $this->connection->channel();
    }

    /**
     * Close the underlying connection
     */
    public function close(): void
    {
        $this->connection->close();
    }

    /**
     * Access the raw connection if needed
     */
    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    /**
     * (Optional) Keep the existing static factory for backward compatibility
     */
    public static function createConnection(): AMQPStreamConnection
    {
        $host = env('RABBITMQ_HOST', '127.0.0.1');
        $port = (int) env('RABBITMQ_PORT', 5672);
        $user = env('RABBITMQ_USER', 'guest');
        $pass = env('RABBITMQ_PASS', 'guest');
        $vhost = env('RABBITMQ_VHOST', '/');

        return new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
    }
}
