<?php
namespace AmqpBuzzer;

class Cli
{
    protected const DOC = "
    Cli utility for publish and listen queues
    Usage:
      amqp-buzzer publish <queue> (--message=<message>) [--config=<config>]
      amqp-buzzer listen  <queue> [--config=<config>]
      amqp-buzzer -h | --help
    Options:
      -h --help              Show this screen
      -v --version           Show version
      -c --config=<config>   Exchange config for publisher and Queue config for listener
      -m --message=<message> Message for publish
      -f --file=<file-path>  File for publish
    ";

    public function run()
    {
        $args = \Docopt::handle(self::DOC, [
            'help'    => true,
            'version' => 'AMQP Buzzer v0.0.1'
        ]);

        $client = new Client();

        if ($args['publish']) {
            $client->publish($args['--message'], $args['<queue>']);
        }

        if ($args['listen']) {
            $client->listen($args['<queue>']);
        }

        $client->closeConnection();
    }
}
