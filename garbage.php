<?php

/* ================= V-1 ================= */
protected $container;

protected function cacheFile()
{
    return $this->container->getParameter('cache_file');
}

public function cache()
{
    return $this->_storage();
}




/**
 * - Gère l'entrée (le user qui vient de se connecter) dans le fichier cache
 *
 */
public function input($key, $val)
{
    // Controle des clés du cache et de l'entrée
    $keyChecker = $this->cacheFindKey($key);

    if($keyChecker['response']){
        // Clé dans le cache
        // Controle des valeurs du cache et de l'entreé
        $valChecker = $this->valueMatcher($keyChecker['data'], $val);

        if($valChecker['response'])
        {
            // le connecté est le gestionnaire et est déjà dans le cache | notif : confirmation
            return [
                'response' => false,
                'is_handler' => true,
                'data' => $valChecker['data']
            ];
        }
        // le connecté n'est pas le gestionnaire | notif : information
        return [
            'response' => false,
            'is_handler' => false,
            'data' => $valChecker['data']
        ];

    }else{
        // Enregistrement dans le cache
        $this->cacheSet($key, $val);
        return [
            'response' => true,
            'is_handler' => true,
            'data' => $keyChecker['data']
        ];
    }
}

/**
 * - Traite les data au bon format pour l'enregistrement en cache
 *
 */
public function dataFormater($key, $resourcesInfo)
{
    return $key .'|'.$resourcesInfo .'\n';
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
 * @param $dataCahe
 * @param $resourceCache
 * @return array
 */
public function valueMatcher($dataCahe, $resourceCache)
{
    // Entrées après traitement
    $idFromValCache = $this->getKey($dataCahe);
    $idFromVal      = $this->getClientId($resourceCache);


    // comparaison entre l'id en cache et l'id du connecté
    if($idFromValCache == $idFromVal)
    {
        // matched !!! : le connecté est bien le gestionnaire
        return [
            'response' => true,
            'data' => $dataCahe
        ];
    }

    // no matched : clé enregistrée mais pas appareillée avec ce connecté
    return [
        'response' => false,
        'data' => $dataCahe
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
public function isExist($dataLine)
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

/* ================= / END V-1 ================= */