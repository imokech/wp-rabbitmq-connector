<?php

namespace WPRabbitMQConnectorPlugin\RabbitMQ;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class Connector
{
    private static $instance;
    private $connection;
    private $channel;

    private function __construct(array $options = [])
    {
        if (
            !isset($options['rabbitmq_user'])
            && !isset($options['rabbitmq_pass'])
            && !isset($options['rabbitmq_port'])
            && !isset($options['rabbitmq_ip'])
        ) return null;

        $user = $options['rabbitmq_user'];
        $pass = $options['rabbitmq_pass'];
        $port = $options['rabbitmq_port'];
        $ip = $options['rabbitmq_ip'];

        try {
            $this->connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                $ip,
                $port,
                $user,
                $pass
            );

            $this->channel = $this->connection->channel();
        } catch (Exception $exception) {

        }
    }

    public static function get_instance(array $options = [])
    {
        if (is_null(self::$instance))
            self::$instance = new self($options);
        return self::$instance;
    }

    public function publish_message($message, string $queue_name)
    {
        $this->channel->queue_declare($queue_name, false, true, false, false);

        $msg = new \PhpAmqpLib\Message\AMQPMessage($message);

        $this->channel->basic_publish($msg, '', $queue_name);
    }

    public function consume_messages($queue_name, $callback)
    {
        $this->channel->queue_declare($queue_name, false, true, false, false);

        $this->channel->basic_consume(
            $queue_name,
            '',
            false,
            true,
            false,
            false,
            $callback
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function create_queue()
    {

    }

    public function create_exchange(
        string $exchange_name = 'Messenger.API.Events:WordpressRegularSmsEvent',
        string $exchange_type = 'fanout',
        bool   $durable = true,
        string $message_body)
    {
        $routing_key = '';
        $this->channel->exchange_declare($exchange_name, $exchange_type, false, $durable, false);
        $message = new \PhpAmqpLib\Connection\AMQPMessage($message_body, ['content_type' => 'application/json']);
        $this->channel->basic_publish($message, $exchange_name, $routing_key);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}