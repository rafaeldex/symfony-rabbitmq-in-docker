<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Message;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ReceiveCommand extends Command
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

        $channel->queue_declare('first', false, true, false, false);
        // 1 - queue name
        // 2 -
        // 3 - if true the queue is marked as durable and need be applied in producer and consumer
        // 4 -
        // 5 -

        $em = $this->em;

        $callback = function ($msg) use ($em) {
            //echo ' [x] Received ', $msg->body, "\n";
            $message = new Message();
            $message->setBody($msg->body);
            $em->persist($message);
            $em->flush();
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        // 1 -
        // 2 - if true determines that messages wont be delivered to busy workers
        // 3 -
        $channel->basic_consume('first', '', false, false, false, false, $callback);
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
