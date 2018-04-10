<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 31/03/18
 * Time: 19:55
 */

namespace Tests\NotificationBundle\Server;


class Helper
{
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

    public function msgClientConnexion()
    {
        $data = new \stdClass();
        $data->room = 5;
        $data->is_handler = false;
        $data->user = $this->userMichelle();

        $msg = new \stdClass();
        $msg->type = 'connexion';
        $msg->msg = 'has just connected';
        $msg->data = $data;

        return $msg;
    }
}