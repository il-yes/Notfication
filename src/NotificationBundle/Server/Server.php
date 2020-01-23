<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 25/03/18
 * Time: 14:17
 */

namespace NotificationBundle\Server;


use NotificationBundle\Factory\Message;
use NotificationBundle\Factory\NotificationCenter;
use NotificationBundle\Manager\ManagerInterface;
use Ratchet\ConnectionInterface;

abstract class Server
{
    /** @var  \SplObjectStorage $clients */
    protected $clients;

    protected $container;

    /** @var  ManagerInterface $manager */
    protected $manager;

    /** @var  Message $messageFactory */
    protected $messageFactory;

    /** @var  NotificationCenter $notificationCenter */
    protected $notificationCenter;

    /**
     * - Dispatch messages
     * @param $from
     * @param $msg
     */
    public function router($from, $msg)
    {
        if($msg->namespace !== 'kyc_user') {
            return false;
        }
        if($msg->type == 'connexion')
        {
            $this->connect($from, $msg->data);
        }  
    }


    /**
     * - Gère la connexion d'une ressource
     *
     */
    public function connect($from, $resourceData)
    {
        /** gestion cache */
        $cacheResponse = $this->cacheHandler($resourceData);

        /** gestion server */
        $this->serverHandler($from, $resourceData, $cacheResponse);

        /** gestion message après operation */
        $message = $this->messageFactory->messageFromCache( 'connexion', $cacheResponse);

        /** Gestion des notifications */
        $this->notify($from, $message);
    }

    /**
     * - Gestion connexion user côté server
     * @param $from
     * @param $resourceData
     * @param $cacheResponse
     */
    protected function serverhandler($from, $resourceData, $cacheResponse)
    {
        if($cacheResponse['is_handler'] == true)
        {
            $resourceData->is_handler = true;
        }

        $this->addClient($from, $resourceData);         // match the client and the connection
    }

    /**
     * - Gestion connexion user côté cache
     */
    protected function cacheHandler($resourceData)
    {
        $resourcesBackupData = $this->dataFormater($resourceData);

        return $this->manager->processing($resourcesBackupData->key, $resourcesBackupData->value);
    }

    /**
     * - Gestion de l'envoie des notifications sur le reseau : connexion client & liste clients connectés
     * @param $from
     * @param $message
     */
    private function notify($from, $message)
    {
        $this->notificationCenter->notificationClient($from, $message);     // notif client - connexion

        $msg = $this->messageFactory->informPeople($this->clients[$from]->resource, 'connexion');
        foreach ($this->clients as $client)
        {
            if($client != $from)
            {
                $this->notificationCenter->notificationServer($client, $msg);                                       // notif clients - connexion
            }else{
                $this->notifyToConnectingPeople('connexion', $this->clients[$from]->resource->room->id);        // notif clients - liste clients connectés
            }
        }
    }


    /**
     * - Match the client and the connection
     * @param $from
     * @param $resourceData
     */
    private function addClient($from, $resourceData)
    {
        $this->clients[$from] = $this->storage($from, $resourceData);
        echo $from->resourceId . ' added to the server ok \n';
    }

    /**
     * Retourne les data au format de sauvegarde for the cache
     *
     * @param $resourceData
     * @return \stdClass
     */
    private function dataFormater($resourceData)
    {
        $dataFormated = new \stdClass();

        $dataFormated->value = $resourceData->user;
        $dataFormated->key   = $resourceData->room;

        return $dataFormated;
    }


    /**
     * - Stores the connection and the resource as an object for the server
     *
     * @param $from
     * @param $resourceData
     * @return \stdClass
     */
    private function storage($from, $resourceData)
    {
        $storage = new \stdClass();
        $storage->connexion = $from;
        $storage->resource  = $resourceData;

        return $storage;
    }

    /**
     * - Get all clients on line
     * @param $roomId
     * @return array
     */
    public function connectedClients($roomId = null)
    {
        $tab = [];

        if (!empty($this->clients) && $roomId != null)
        {
            foreach ($this->clients as $client)
            {
                if (!empty($this->clients->getInfo()->resource))
                    $resource = $this->clients->getInfo()->resource;
                else
                    continue;

                $tab = $this->addConnectedClient($tab, $resource);  // all connections from the same room

            }
        }

        return $tab;
    }

    protected function addConnectedClient($tab, $resource)
    {
        $data = [
            'id' => $resource->user->id,
            'username' => $resource->user->username,
            'email' => $resource->user->email,
            'room_id' => $resource->room->id,
            'is_handler' => $resource->is_handler
        ];

        array_push($tab, $data);
        return $tab;
    }



    /**
     * - Deconnect from the server
     * @param ConnectionInterface $conn
     */
    protected function deconnect(ConnectionInterface $conn)
    {
        /** ressource du client qui se deconnecte */
        $resource = $this->getResource($conn);

        /** processus de deconnexion */
        $this->processDeconnexion($conn, $resource);

        /** Mise a jour de la liste des users connectés dans la room */
        $this->notifyToConnectingPeople('deconnexion', $resource->room->id );
    }


    /**
     * @param $conn
     * @return mixed
     */
    protected function getResource($conn)
    {
        foreach ($this->clients as $client)
        {
            if($client->resourceId == $conn->resourceId)
            {
                return $this->clients->getInfo()->resource;
            }
        }
    }


    /**
     * - Processus de deconnexion
     * @param $conn
     * @param $resource
     */
    protected function processDeconnexion($conn, $resource)
    {
        $msg = $this->messageFactory->informPeople($resource, 'deconnexion');

        foreach ($this->clients as $client)
        {
            if($client->resourceId == $conn->resourceId)
            {
                $this->removeClient($conn, $resource);
            }

            /** notifier la deconnexion aux autres clients */
            $this->notificationCenter->notificationServer($client, $msg);
        }

    }


    /**
     * - Remove the client from the session and from the cache (if necessary)
     * @param $conn
     * @param $resource
     */
    public function removeClient($conn, $resource)
    {
        /** cache handler */
        if($resource->is_handler)
        {
            $this->manager->delete($resource->room->id);
        }

        /** server handler : The connection is closed, remove it, as we can no longer send it messages */
        $this->clients->detach($conn);
        echo "Deconnexion : {$resource->user->username} has been removed from the session \n";
    }



    /**
     * - Notification generale a destination de tous les clients
     * @param $roomId
     * @param $stage
     */
    public function notifyToConnectingPeople($stage, $roomId = null)
    {
        $connectedClients = $this->connectedClients($roomId);       // Clients connectés

        if(count($connectedClients) > 0)
        {
            $message = $this->messageFactory->connectedPeople($connectedClients, $stage);

            foreach ($this->clients as $client)
            {
                $client->send(json_encode($message));
            }
        }
    }



}