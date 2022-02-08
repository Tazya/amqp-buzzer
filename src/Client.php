<?php
namespace AmqpBuzzer;

use PhpAmqpLib\Message\AMQPMessage;

class Client
{
    protected const DEFAULT_OPTIONS = [
        'exchange'        => 'router',
        'exchangeType'    => 'direct',
        'exchangeDurable' => false,
        'passive'         => false,
        'durable'         => true,
        'exclusive'       => false,
        'autoDelete'      => false,
        'noWait'          => false,
        'arguments'       => [],
        'internal'        => false,
        'ticket'          => null,
        'usePcntl'        => false,
        'prefetchCount'   => 1,
        'autoAck'         => true,
        'declare'         => false,
        'bindingKeys'     => [],
        'prefix'          => '',
    ];

    /**
     * @var \PhpAmqpLib\Connection\AbstractConnection
     */
    protected $connection;

    /**
     * @var \PhpAmqpLib\Channel\AbstractChannel
     */
    protected $channel;

    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options = [])
    {
        $this->connection = ConnectionFactory::create();
        $this->options    = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    public function publish(string $messageBody, string $queue)
    {
        $channel = $this->getChannel();

        $this->declareExchange();
        $this->declareQueue($queue);

        $message = new AMQPMessage($messageBody, [
            'content_type'  => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $channel->basic_publish($message, $this->options['exchange'], $queue);

        $this->closeChannel();
    }

    public function listen(string $queue)
    {
        $channel = $this->getChannel();

        $this->declareExchange();
        $this->declareQueue($queue);

        $channel->basic_consume(
            $queue,
            '',
            false,
            false,
            false,
            false,
            [$this, 'processMessage']
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $this->closeChannel();
    }

    public function closeConnection()
    {
        $this->connection->close();
    }

    protected function getChannel()
    {
        if (!$this->channel) {
            return $this->connection->channel();
        }

        return $this->channel;
    }

    protected function closeChannel()
    {
        if ($this->channel) {
            $this->channel->close();
            $this->channel = null;
        }
    }

    protected function declareQueue(string $queue)
    {
        $channel = $this->getChannel();
        $channel->queue_declare(
            $queue,
            $this->options['passive'],
            $this->options['durable'],
            $this->options['exclusive'],
            $this->options['autoDelete']
        );
        $channel->queue_bind($queue, $this->options['exchange'], $queue);
    }

    protected function declareExchange()
    {
        $this->getChannel()->exchange_declare(
            $this->options['exchange'],
            $this->options['exchangeType'],
            $this->options['passive'],
            $this->options['exchangeDurable'],
            $this->options['autoDelete'],
            $this->options['internal'],
            $this->options['noWait'],
            $this->options['arguments'],
            $this->options['ticket']
        );
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     */
    public function processMessage(AMQPMessage $message)
    {
        echo "\n--------\n";
        echo $message->body;
        echo "\n--------\n";

        $message->ack();

        if ($message->body === 'quit') {
            $message->getChannel()->basic_cancel($message->getConsumerTag());
        }
    }
}
