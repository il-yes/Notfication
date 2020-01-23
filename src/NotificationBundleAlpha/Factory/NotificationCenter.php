<?php


namespace NotificationBundle\Factory;



class NotificationCenter
{
    private function notification($client, $message)
    {
        return $client->send(json_encode($message));
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


}