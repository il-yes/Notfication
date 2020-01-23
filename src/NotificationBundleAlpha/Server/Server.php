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
use NotificationBundle\Manager\Cache\NotificationCache;

abstract class Server
{
    protected $clients;

    protected $container;

    /** @var  NotificationCache $cache */
    protected $cache;

    /** @var  Message $messageFactory */
    protected $messageFactory;

    /** @var  NotificationCenter $notificationCenter */
    protected $notificationCenter;

    public function server()
    {
        return $this->_connexionsManager();
    }

    abstract protected function _connexionsManager();

    public function router($from, $msg)
    {
       if($msg->type == 'connexion')
       {
            $this->connect($from, $msg->data);
       }
        if($msg->type == 'initialisation')
        {
            var_dump($msg);
            foreach ($this->clients as $client)
            {
                if ($from !== $client)
                {
                    // The server send to each client connected
                    $client->send(json_encode($msg));
                }
            }
        }



    }


    /**
     * - Gère la connexion d'une ressource
     *
     */
    public function connect($from, $resourceData)
    {
        // gestion cache
        $cacheResponse = $this->cacheHandler($resourceData);

        // gestion server
        $this->serverHandler($from, $resourceData, $cacheResponse);

        // gestion message après operation
        $message = $this->messageFactory->messageFromCache( 'connexion', $cacheResponse);

        /** TODO : developper cette fonction */
        $this->notificationCenter->notificationClient($from, $message);
        $this->notificationCenter->notificationServer($from, $message);        

        // monitoring du fichier cache après operation
        //$monitoring = $this->cacheMonitoring('connexion');

        //dump($monitoring);

        // Gestion des notifications
        //$this->messagesConnexionSender($connexion->resourceId, $message, $monitoring);

    }

    protected function serverhandler($from, $resourceData, $cacheResponse)
    {
        $this->addClient($from, $resourceData);

        if($cacheResponse['is_handler'] == true)
        {
            $this->clients->offsetGet($from)->isHandler = true;
        }
    }

    // match the client and the connection
    private function addClient($from, $resourceData)
    {
        $this->clients[$from] = $resourceData;
        echo $from->resourceId . ' added to the server ok \n';
    }

    /**
     * - Gestion connexion user côté cache
     *
     */
    protected function cacheHandler($resourceData)
    {
        $resourcesBackupData = $this->dataFormater($resourceData);

        return $this->cache->inputProcess($resourcesBackupData->key, $resourcesBackupData->value);
    }


    /**
     * Retourne les data au format de sauvegarde
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
}