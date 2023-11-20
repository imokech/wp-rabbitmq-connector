<?php

use WPRabbitMQPlugin\RabbitMQ\Connector;
use PHPUnit\Framework\TestCase;

class Test_WP_RabbitMQ_Connector extends TestCase
{
    public function test_singleton_instance() {
        $instance1 = Connector::get_instance();
        $instance2 = Connector::get_instance();

        $this->assertInstanceOf(Connector::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }
}