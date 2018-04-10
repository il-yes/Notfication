<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 29/03/18
 * Time: 20:43
 */

namespace tests\NotificationBundleFactory;


use Tests\Framework\WebTestCase;
use tests\NotificationBundle\Server\Helper;

class NotificationCenter extends WebTestCase
{


    /**
     * @test
     */
    public function send_to_new_client()
    {
        $resourcesClients = $this->resourcesClients('jean', 'kyc', null);
        $jean = $resourcesClients->user;
        $room = $resourcesClients->room;

        $notifToServer = $this->notificationServer($clent, $msg);
        $notifToClient = $this->notificationClient($clent, $msg);

        die(var_dump($jean, $room));
    }


    private function notification($client, $message)
    {
        return $client->send($message);
    }

    public function notificationServer($client, $message)
    {
        return $this->notification($client, $message);
    }

    public function notificationClient($client, $message)
    {
        return $this->notification($client, $message);
    }

    public function notify($from, $msg)
    {
        switch ($msg['type'])
        {
            case 'position' :
                return $this->notificationServer($from, $msg['msg']);
                break ;

            case 'scan_room' :
                return $this->notificationServer($from, $msg['msg']) ;
                break ;

            case 'connexion' :
                return $this->notificationClient($from, $msg['msg']) ;
                break ;

            case 'deconnexion' :
                return $this->notificationClient($from, $msg['msg']) ;
                break ;
        }
    }


    public function sendNotificationsFromResources()
    {

    }

    public function resourcesClients($userName, $roomName, $monitor = null)
    {

        $clients = new \stdClass();
        $clients->user = $this->user($userName);
        $clients->room = $this->room($roomName);
        $clients->monitor = $this->room($monitor);

        return $clients;
    }

    private function user($name)
    {
        switch ($name)
        {
            case  'jean' :
                return $this->userJean();
                break;
            case  'michelle' :
                return $this->userMichelle();
                break;
            case  'roger' :
                return $this->userMichelle();
                break;
        }
    }

    private function userJean()
    {
        $jean = new \stdClass();
        $jean->id = 1;
        $jean->username = 'jean';
        $jean->email = 'jean@mail.com';

        return $jean;
    }

    private function userMichelle()
    {
        $michelle = new \stdClass();
        $michelle->id = 10;
        $michelle->username = 'michelle';
        $michelle->email = 'michelle@mail.com';

        return $michelle;
    }

    private function userRoger()
    {
        $roger = new \stdClass();
        $roger->id = 7;
        $roger->username = 'roger';
        $roger->email = 'roger@mail.com';

        return $roger;
    }

    private function room($name)
    {
        $room = new \stdClass();

        switch ($name)
        {
            case 'kyc':
                $room->id = 5;
                $room->name = 'kyc_page';
                break;

            case 'monitor':
                $room->id = 1;
                $room->name = 'accueil';
                break;

            default :
                $room = null;
        }

        return $room;
    }



}