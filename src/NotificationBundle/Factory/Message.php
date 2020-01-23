<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 26/03/18
 * Time: 23:23
 */

namespace NotificationBundle\Factory;


class Message
{
    const ROUTE        = 'kyc_user';
    const TYPE_CONNEXION   = 'connexion';
    const TYPE_DECONNEXION = 'deconnexion';
    const TYPE_POSITION    = 'position';
    const ICON_HANDLER     = 'handler';
    const ICON_NO_HANDLER  = 'no-handler';
    const ICON_LOGOUT      = '<i class="fas fa-sign-out-alt"></i>';

    private function core($type, $icon, $msg, $data)
    {
        return [ 
            'namespace' => self::ROUTE,
            'type' => $type,
            'icon' => $icon,
            'msg'  => $msg,
            'data' => $data
        ];
    }

    /**
     * - Return the message according to the result of the cache
     *
     * @param $cacheResponse
     * @return array
     */
    public function connexion($cacheResponse)
    {
        /**
         * - Le cache n'a pas procédé à un enregistrement
         *      True : le cache vient de sauvegarder le client
         *      False : fichier cache non modifié
         */
        if($cacheResponse['response'] === false)
        {
            if($cacheResponse['is_handler'] === true)
            {
                var_dump('toHandlerMessage');
                /**
                 * - Pas d'insertion en cache, le connecté en etait déjà le gestionnaire
                 * Return $cacheResponse [Array: 'type'=>'connexion', 'icon'=>'handler', 'msg'=>
                 *  handlerAlreadyInCache, 'data'=>$cacheResponse['data']]
                 */
                return $this->toHandlerMessage($cacheResponse);
            }

            var_dump($this->toNewClientMessage($cacheResponse));
            /**
             * - Pas d'insertion en cache, le connecté n'est pas le gestionnaire
             * Return
             *  $cacheResponse [Array: 'type'=>'connexion', 'icon'=>'no-handler', 'msg'=>
             *  newRessource, 'data'=>$cacheResponse['data']]
             */
            return $this->toNewClientMessage($cacheResponse);
        }

        var_dump('handlerMessage');
        /**
         * - Insertion en cache, le connecté est maintenant le gestionnaire
         * Return
         *  $cacheResponse [Array: 'type'=>'connexion', 'icon'=>'handler', 'msg'=>
         *  newRessourceHandler, 'data'=>$cacheResponse['data']]
         *  $handlerName string
         */
        return $this->handlerMessage($cacheResponse);

    }

    /**
     * - Pas d'insertion en cache, le connecté est le gestionnaire
     * @return string
     */
    public static function handlerAlreadyInCache()
    {
        return 'vous êtes le gestionnaire de ce dossier client';
    }

    /**
     * - Pas d'insertion en cache, le connecté n'est pas le gestionnaire
     * @param $resourceName
     * @return string
     */
    public static function newResource($resourceName)
    {
        return trim($resourceName) . ' est le gestionnaire de ce dossier client';
    }

    /**
     * - Insertion en cache, le connecté est maintenant le gestionnaire
     * @return string
     */
    public static function newRessourceHandler()
    {
        return 'vous êtes le gestionnaire de ce dossier client';
    }


    /**
     * - Return the message according the step
     *
     * @param $stage
     * @param $cacheResponse
     * @return array
     */
    public function messageFromCache($stage, $cacheResponse)
    {
        if($stage === self::TYPE_CONNEXION)
        {
            return $this->connexion($cacheResponse);
        }
    }

    private function toHandlerMessage($cacheResponse)
    {
        return $this->core(
            self::TYPE_CONNEXION,
            self::ICON_HANDLER,
            self::handlerAlreadyInCache(),
            $cacheResponse['data']->get()
        );
    }

    private function toNewClientMessage($cacheResponse)
    {
        return $this->core(
            self::TYPE_CONNEXION,
            self::ICON_NO_HANDLER,
            self::newResource($cacheResponse['data']->get()->username),
            $cacheResponse['data']->get()
        );
    }

    public function handlerMessage($cacheResponse)
    {
        return $this->core(
            self::TYPE_POSITION,
            self::ICON_HANDLER,
            self::newRessourceHandler(),
            $cacheResponse['data']->get()
        );
    }

    public function monitoringConnexion()
    {
        return $this->core(
            'initialisation',
            '',
            'Client connected to server ok',
            [
                'stage' => 'connexion',
                'is_handler' => true,
                'user'       => null
            ]
        );
    }


    /**
     * - Message de connexion
     *
     * @param $resource
     * @return array
     */
    public function informPeople($resource, $stage)
    {
        $action = $stage === 'connexion' ? ' has just connected' : ' has just deconnected';

        return $this->core(
            'scan_room',
            '',
            $resource->user->username .''. $action,
            [
                'stage'     => $stage,
                'resource'  => $resource
            ]
        );
    }


    /**
     * - Message contenant la liste des clients connectés
     *
     * @param $resource
     * @param $stage
     * @return array
     */
    public function connectedPeople($resource, $stage)
    {
        return $this->core(
            'connecting_people',
            '',
            '',
            [
                'stage' => $stage,
                'clients'  => $resource
            ]
        );
    }

    /**
     * - Message de deconnexion
     *
     * @param $name
     * @return array
     */
    public function deconnexion($name)
    {
        return $this->core(
            self::TYPE_DECONNEXION,
            self::ICON_LOGOUT,
            $name .' has just deconnect',
            [
                'stage' => 'deconnexion',
                'clients'  => ''
            ]
        );
    }


}