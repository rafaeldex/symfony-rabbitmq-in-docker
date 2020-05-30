<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SenderController extends AbstractController
{
    /**
     * @Route("/send/{message}", name="send")
     */
    public function send($message)
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password');
        $channel = $connection->channel();

        $channel->queue_declare('first', false, false, false, false);

        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, '', 'first');

        $channel->close();
        $connection->close();

        return new Response(
            '<html><body>Message sent: '. $message .'</body></html>'
        );
    }

    /**
     * @Route("/work/{message}", name="work")
     */
    public function work($message)
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password');
        $channel = $connection->channel();

        $channel->queue_declare('first', false, true, false, false);
        // 1 - queue name
        // 2 -
        // 3 - if true the queue is marked as durable and need be applied in producer and consumer
        // 4 -
        // 5 -

        if (empty($message)) {
            $message = "Hello World!";
        }
        //$msg = new AMQPMessage($data);
        $msg = new AMQPMessage($message, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($msg, '', 'first');
        // 1 - messages
        // 2 - exchange name
        // 3 - routing_key (used to indetify the queue name) - does not need if use exchange name

        $channel->close();
        $connection->close();

        return new Response(
            '<html><body>Message sent: '. $message .'</body></html>'
        );
    }

    /**
     * @Route("/exchange/{message}", name="exchange")
     */
    public function exchange($message)
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password');
        $channel = $connection->channel();

        $channel->exchange_declare('logs', 'fanout', false, false, false);
        // 1 - exchange name
        // 2 - exchange type
        // 3 -
        // 4 -
        // 5 -

        if (empty($message)) {
            $message = "Hello World!";
        }
        //$msg = new AMQPMessage($data);
        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, 'logs');
        // 1 - messages
        // 2 - exchange name
        // 3 - routing_key (used to indetify the queue name) - does not need if use exchange name

        $channel->close();
        $connection->close();

        return new Response(
            '<html><body>Message sent: '. $message .'</body></html>'
        );
    }
}
