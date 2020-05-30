<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Message;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ReceiverController extends AbstractController
{
    /**
     * @Route("/receive", name="receive")
     */
    public function receive()
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password');
        $channel = $connection->channel();

        $channel->queue_declare('first', false, false, false, false);
        $entityManager = $this->getDoctrine()->getManager();

        $callback = function ($msg) use ($entityManager) {
            //echo ' [x] Received ', $msg->body, "\n";
            $message = new Message();
            $message->setBody($msg->body);
            $entityManager->persist($message);
            $entityManager->flush();
        };

        $channel->basic_consume('first', '', false, true, false, false, $callback);

        /*while ($channel->is_consuming()) {
            $channel->wait();
        }*/

        $channel->close();
        $connection->close();

        return new Response(
            '<html><body>Check database to received messages</body></html>'
        );
    }

    /**
     * @Route("/show", name="show")
     */
    public function show()
    {

        $entityManager = $this->getDoctrine()->getManager();

        $messages = $entityManager->getRepository(Message::class)->findAll();
        $response = '';
        foreach ($messages as $message) {
            $response = $response . '<br>' . $message->getBody();
        }

        return new Response(
            '<html><body>Received messages: '. $response .'</body></html>'
        );
    }
}
