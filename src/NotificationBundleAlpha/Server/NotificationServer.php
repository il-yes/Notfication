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
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use NotificationBundle\Factory\NotificationCenter;

class NotificationServer extends Server implements MessageComponentInterface
{
    public function __construct(ContainerInterface $container,
                                NotificationCache $cache,
                                Message $message,
                                notificationCenter $center)
    {
        $this->container          = $container;
        $this->clients            = new \SplObjectStorage;
        $this->cache              = $cache;
        $this->messageFactory     = $message;
        $this->notificationCenter = $center;
    }


    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        // Store data user from the client
        //$this->clients->offsetSet($conn, $storage);

        $msg = $this->messageFactory->monitoringConnexion();
        echo "New connection ! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $msg = json_decode(trim($msg));

        // Dispatch
        $this->router($from, $msg);

        $numRecv = count($this->clients) - 1;
        /*
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

*/
    }

    public function onClose(ConnectionInterface $conn)
    {
        $connCloned = clone $conn;
        //var_dump($this->clients[$conn]);
        $this->deconnect($connCloned);
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        //die(var_dump('allo'));


    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function deconnect($conn)
    {
        foreach ($this->clients as $client)
        {
            $msg = [
                'endpoint' => 'kyc_user',
                'type' => 'deconnexion',
                'msg'  => 'au revoir'
            ];
            if($client == $conn)
            {
                $client->send(json_encode($msg));
                echo $client->resourceId .' has just deconnect';
            }else{
                echo "Connection ". $this->clients->getInfo()->name ."  has disconnected\n";

            }
        }
    }

    protected function _connexionsManager()
    {
        return $this;
    }
}