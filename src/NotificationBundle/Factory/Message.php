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
    const TYPE_CONNEXION  = 'connexion';
    const TYPE_POSITION   = 'position';
    const ICON_HANDLER    = 'handler';
    const ICON_NO_HANDLER = 'no-handler';

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
         * True : le cache vient de sauvegarder le client
         * False : fichier cache non modifié
         */
        if($cacheResponse['response'] === false)
        {
            if($cacheResponse['is_handler'] === true)
            {
                /**
                 * - Pas d'insertion en cache, le connecté en etait déjà le gestionnaire
                 * Return $cacheResponse [Array: 'type'=>'connexion', 'icon'=>'handler', 'msg'=>
                 *  handlerAlreadyInCache, 'data'=>$cacheResponse['data']]
                 */
                return $this->toHandlerMessage($cacheResponse);
            }

            /**
             * - Pas d'insertion en cache, le connecté n'est pas le gestionnaire
             * Return
             *  $cacheResponse [Array: 'type'=>'connexion', 'icon'=>'no-handler', 'msg'=>
             *  newRessource, 'data'=>$cacheResponse['data']]
             *  $handlerName string
             */
            return $this->toNewClientMessage($cacheResponse);
        }

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
        return 'vous êtes le gestionnaire de cette pièce';
    }

    /**
     * - Pas d'insertion en cache, le connecté n'est pas le gestionnaire
     * @param $resourceName
     * @return string
     */
    public static function newResource($resourceName)
    {
        return trim($resourceName) . ' est le gestionnaire de cette pièce';
    }

    /**
     * - Insertion en cache, le connecté est maintenant le gestionnaire
     * @return string
     */
    public static function newRessourceHandler()
    {
        return 'vous êtes le gestionnaire de cette pièce';
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
        return [
            'type' => self::TYPE_CONNEXION,
            'icon' => self::ICON_HANDLER,
            'msg'  => self::handlerAlreadyInCache(),
            'data' => $cacheResponse['data']->get()
        ];
    }

    private function toNewClientMessage($cacheResponse)
    {
        return [
            'type' => self::TYPE_CONNEXION,
            'icon' => self::ICON_NO_HANDLER,
            'msg'  => self::newResource($cacheResponse['data']->get()->username),
            'data' => $cacheResponse['data']->get()
        ];
    }

    public function handlerMessage($cacheResponse)
    {
        return [
            'type' => self::TYPE_POSITION,
            'icon' => self::ICON_HANDLER,
            'msg'  => self::newRessourceHandler(),
            'data' => $cacheResponse['data']->get()
        ];
    }

    public function monitoringConnexion()
    {
        return [
            'type' => 'initialisation',
            'msg'  => 'Client connected to server ok',
            'data' => [
                'stage' => 'connexion',
                'is_handler' => true,
                'user'       => null
            ]
        ];
    }


}