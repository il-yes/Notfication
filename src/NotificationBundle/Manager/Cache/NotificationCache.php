<?php


namespace NotificationBundle\Manager\Cache;


use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;


class NotificationCache
{
    /* ================= V-2 ================= */
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

    protected function cacheFile()
    {
        return $this->container->getParameter('cache_file');
    }

    /**
     *
     */
    public function setCache($key, $val)
    {
        var_dump($key->get());
        $key->set($val);
        $this->cache->save($key);
    }

    /**
     *
     */
    public function inputProcess($room, $val)
    {
        $key = $this->keyFormater($room->id);
        $key = $this->createKey($key);

        return $this->input($key, $val);
    }

    public function createKey($key)
    {
        // create a new item getting it from the cache
        return $this->cache->getItem('stats.'. $key);
    }


    /**
     * - Gère l'entrée (le user qui vient de se connecter) dans le fichier cache
     *
     */
    public function input($key, $val)
    {
        // Vérification de la présence de la clé dans le cache
        $keyChecker = $this->isExist($key);

        if($keyChecker['response'])
        {
            // Clé dans le cache - Controle des valeurs du cache et de l'entreé
            return $this->managementOfTheExisting($key, $val, $keyChecker);
        }

        // Enregistrement dans le cache
        return $this->storeInCache($key, $val, $keyChecker);
    }

    private function isExist($key)
    {
        if (!$key->isHit())
        {
            return [
                'response' => false,
                'data'   => $key
            ];
        }

        return [
            'response' => true,
            'data'   => $key
        ];
    }

    /**
     * - Traite les data au bon format pour l'enregistrement en cache
     *
     */
    public function keyFormater($key)
    {
        $key = (string) $key;
        $exp = 'kyc_'. (string) $key;
        return $exp;
    }

    private function storeInCache($key, $val, $keyChecker)
    {
        $this->setCache($key, $val);
        return $this->handlerResponse($keyChecker);
    }

    private function managementOfTheExisting($key, $val, $keyChecker)
    {
        $valChecker = $this->valueMatcher($keyChecker['data'], $val);

        if($valChecker['response'])
        {
            // le connecté est le gestionnaire et est déjà dans le cache | notif : confirmation
            return $this->toHandlerResponse($valChecker);
        }
        // le connecté n'est pas le gestionnaire | notif : information
        return $this->toNewClientResponse($valChecker);
    }

    private function toHandlerResponse($valChecker)
    {
        return [
            'response' => false,
            'is_handler' => true,
            'data' => $valChecker['data']
        ];
    }

    private function toNewClientResponse($valChecker)
    {
        return [
            'response' => false,
            'is_handler' => false,
            'data' => $valChecker['data']
        ];
    }

    public function handlerResponse($keyChecker)
    {
        return [
            'response' => true,
            'is_handler' => true,
            'data' => $keyChecker['data']
        ];
    }



    /**
     * - Compare l'id dans la donnée du cache et l'id du connecté
     * - True : ça match, la val correspond a celle dans le cache
     * - False : l'id du connecté ne correspond pas a celui du gestionnaire
     * @param $key
     * @param $resource
     * @return array
     */
    public function valueMatcher($key, $resource)
    {
        // comparaison entre la valeur en cache et la resource
        if($key->get() == $resource)
        {
            // matched !!! : le connecté est bien le gestionnaire
            return [
                'response' => true,
                'data' => $key
            ];
        }

        // no matched : clé enregistrée mais pas appareillée avec ce connecté
        return [
            'response' => false,
            'data' => $key
        ];
    }

    /* ================= / END V-2 ================= */




}