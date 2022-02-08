<?php
namespace AmqpBuzzer;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConnectionFactory
{
    public static function create(): AbstractConnection
    {
        return new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    }
}
