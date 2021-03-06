<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 25/03/18
 * Time: 00:57
 */

namespace NotificationBundle\Server;

use NotificationBundle\Factory\Message;
use NotificationBundle\Manager\Cache\NotificationCache;
use NotificationBundle\Manager\ManagerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use NotificationBundle\Factory\NotificationCenter;

class NotificationServer extends Server implements MessageComponentInterface
{
    public function __construct(ContainerInterface $container,
                                ManagerInterface $cache,
                                Message $message,
                                notificationCenter $center)
    {
        $this->container          = $container;
        $this->clients            = new \SplObjectStorage;
        $this->manager            = $cache;
        $this->messageFactory     = $message;
        $this->notificationCenter = $center;

    }


    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $msg = json_decode(trim($msg));

        // Dispatcher
        $this->router($from, $msg);

        $numRecv = count($this->clients) - 1;
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->deconnect($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

}