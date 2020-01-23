<?php


namespace NotificationBundle\Manager\Cache;


use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;


class NotificationCache extends Cache
{
    protected $container;

    /**
     * Cache constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cache  = new FilesystemAdapter();
    }


    /**
     * - Formatte la clé, créé un cacheItem (clé + valeur) pour une eventuelle insertion en cache
     */
    public function processing($room, $val)
    {
        //var_dump($room);
        $key = $this->keyFormater($room->id);
        $cacheItem = $this->createKey($key);

        return $this->input($cacheItem, $val);
    }



    /**
     * - Gère l'entrée (le user qui vient de se connecter) dans le fichier cache
     */
    public function input($cacheItem, $val)
    {
        $keyChecker = $this->isExist($cacheItem);

        if($keyChecker['response'])
        {

            /** Clé dans le cache - Controle des valeurs du cache et de l'entreé */
            return $this->existingKey($val, $keyChecker);
        }

        /** Enregistrement dans le cache */
        return $this->storeInCache($cacheItem, $val, $keyChecker);
    }

    /**
     * - Traite la clé au bon format pour l'enregistrement en cache
     *
     */
    public function keyFormater($key)
    {
        $key = (string) $key;
        $exp = 'stats.kyc_'. (string) $key;
        return $exp;
    }

    /**
     * - Sauvegarde en cache
     * @param $key
     * @param $val
     * @param $keyChecker
     * @return array
     */
    protected function storeInCache($key, $val, $keyChecker)
    {
        $this->setCache($key, $val);
        return $this->handlerResponse($keyChecker);
    }

    /**
     * - Gère les cas ou la cle existe (déjà en cache)
     * @param $val
     * @param $keyChecker
     * @return array
     */
    protected function existingKey($val, $keyChecker)
    {
        $valChecker = $this->valueMatcher($keyChecker['data'], $val);

        if($valChecker['response'])
        {
            /** le connecté est le gestionnaire et est déjà dans le cache */
            return $this->toHandlerResponse($valChecker);
        }
        /** le connecté n'est pas le gestionnaire */
        return $this->toNewClientResponse($valChecker);
    }

    /**
     * - Comparaison entre la valeur en cache et la resource
     * - True : ça match, la val correspond a celle dans le cache
     * - False : l'id du connecté ne correspond pas a celui du gestionnaire
     * @param $key
     * @param $resource
     * @return array
     */
    public function valueMatcher($key, $resource)
    {
        if($key->get() == $resource)
        {
            /** matched !!! : le connecté est bien le gestionnaire */
            return $this->valueMatched($key);
        }

        /** no matched : clé enregistrée mais pas appareillée avec ce connecté */
        return $this->valueNotMatched($key);
    }


    /**
     * - Message à destination du new client déjà enregistré dans le cache (à la suite d'un rafraîchissement du navigateur...)
     * @param $valChecker
     * @return array
     */
    protected function toHandlerResponse($valChecker)
    {
        return [
            'response' => false,
            'is_handler' => true,
            'data' => $valChecker['data']
        ];
    }

    /**
     * - Message à destination du new client
     * @param $valChecker
     * @return array
     */
    protected function toNewClientResponse($valChecker)
    {
        return [
            'response' => false,
            'is_handler' => false,
            'data' => $valChecker['data']
        ];
    }

    /**
     * - Message à destination du new client dont la clé vient de lui etre affecté dans le cache
     * @param $keyChecker
     * @return array
     */
    public function handlerResponse($keyChecker)
    {
        return [
            'response' => true,
            'is_handler' => true,
            'data' => $keyChecker['data']
        ];
    }

    /**
     * @param $cacheItem
     * @return array
     */
    protected function inCache($cacheItem)
    {
        return [
                'response' => true,
                'data'     => $cacheItem
            ];
    }

    /**
     * @param $cacheItem
     * @return array
     */
    protected function notInCache($cacheItem)
    {
        return [
            'response' => false,
            'data'     => $cacheItem
        ];
    }

    /**
     * @param $key
     * @return array
     */
    protected function valueMatched($key)
    {
        return [
            'response' => true,
            'data' => $key
        ];
    }

    /**
     * @param $key
     * @return array
     */
    protected function valueNotMatched($key)
    {
        return [
            'response' => false,
            'data' => $key
        ];
    }


}