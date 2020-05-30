<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Message;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ExchangeCommand extends Command
{

    protected static $defaultName = 'app:receive-messages';
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Receive messages from producer in rabbitmq server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password');
        $channel = $connection->channel();

        $channel->exchange_declare('logs', 'fanout', false, false, false);
        // 1 - exchange name
        // 2 - exchange type
        // 3 -
        // 4 -
        // 5 -

        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

        $em = $this->em;

        $callback = function ($msg) use ($em) {
            //echo ' [x] Received ', $msg->body, "\n";
            $message = new Message();
            $message->setBody($msg->body);
            $em->persist($message);
            $em->flush();
        };

        $channel->queue_bind($queue_name, 'logs');

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
        // 1 - queue name
        // 2 -
        // 3 -
        // 4 - true does not send ack message to redeliver and false checks interface
        // 5 -
        // 6 -
        // 7 - callback function

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return 0;
    }
}
