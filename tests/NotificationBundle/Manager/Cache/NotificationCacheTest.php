<?php


namespace Tests\NotificationBundle\Manager\Cache;


use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tests\NotificationBundle\Server\Helper;


class NotificationCacheTest extends CacheTest
{
    /** @var  Helper $helper */
    private $helper;
    /**
     * Cache constructor.
     */
    public function setUp()
    {
        $this->cache  = new FilesystemAdapter();
        $this->helper = new Helper();
    }

    protected function cacheFile()
    {
        return $this->container->getParameter('cache_file');
    }

    /**
     * @test
     */
    public function setCache()
    {
        $resources = $this->helper->resourcesClients('jean', 'kyc', null);
        $room = $resources->room;
        $jean = $resources->user;

        $formattedKey = $this->keyFormater($room->id);
        $key = $this->createKey($formattedKey);

        $this->cacheSet($key, $jean);

        $retrieve = $key->get();
        self::assertEquals('jean', $retrieve->username);
    }

    /**
     * @test
     */
    public function inputProcess()
    {
        $resources = $this->helper->resourcesClients('jean', 'kyc', null);
        $user = $resources->user;
        $room = $resources->room;
        $key = $this->keyFormater($room->id);
        $val = $user;
        $key = $this->createKey($key);

        $response = $this->input($key, $val);
        //var_dump(gettype($response));
        self::assertEquals("array", gettype($response));
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
        return 'kyc-'. $key;
    }

    private function storeInCache($key, $val, $keyChecker)
    {
        $this->cacheSet($key, $val);
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
     * - Cherche la clé dans le fichier cache
     *
     */
    public function cacheFindKey($key)
    {
        $data = file($this->cacheFile());

        // parse le fichier cache
        foreach ($data as $line)
        {
            $keyCache = str_replace("'", "", explode('|', $line)[0]);

            if($keyCache == str_replace("'", "", explode('|', $key)))
            {
                // matched !!!
                return [
                    'response' => true,
                    'data' => $data
                ];
            }
        }
        // don't mached
        return [
            'response' => false,
            'data' => null
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

    private function getKey($data)
    {
        return str_replace("'", "", explode('|', $data)[1]);
    }

    private function getClientId($data)
    {
        return str_replace("'", "", explode('|', $data)[0]);
    }

    /**
     * - Retourne l'etat du fichier cache
     *
     */
    public function cacheMonitoring($stage)
    {
        $fileContent = file($this->cacheFile());

        return [
            'type' => 'scan_room',
            'msg' => $fileContent,
            'stage' => $stage
        ];
    }

    /**
     * - verifie si une donnée clé - valeur au format de sauvegarde, existe deja en cache
     * @param $dataLine
     */
    public function isExisth($dataLine)
    {
        $file = file($this->cacheFile());

        foreach ($file as $line)
        {
            if($dataLine == $line)
            {
                $this->inCache($line);
            }

            $this->notInCache($dataLine);
        }
    }

    private function inCache($line)
    {
        return [
            'result' => true,
            'data'   => $line
        ];
    }

    private function notInCache($dataLine)
    {
        return [
            'result' => false,
            'data'   => $dataLine
        ];
    }
}