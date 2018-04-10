<?php

namespace Tests\NotificationBundle\Manager\Cache;


use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tests\Framework\WebTestCase;


abstract class CacheTest extends WebTestCase
{
    /** @var  FilesystemAdapter $cache */
    protected $cache;

    /**
     * - Set les data dans le cache
     *
     */
    public function cacheSet($key, $val)
    {
        // assign a value to the item and save it
        $key->set($val);
        $this->cache->save($key);
    }


    public function createKey($key)
    {
        // create a new item getting it from the cache
        return $this->cache->getItem($key);
    }




    /**
     * - Récupère ttes les data du fichier cache
     *
     */
    public function cacheGetAll()
    {
        return file($this->cacheFile());
    }

    /**
     * - Clear cache file
     *
     */
    public function cacheReset()
    {
        $data = '';

        file_put_contents($this->file, $data,  LOCK_EX);

        return$this;
    }


    public function cacheDelete($key)
    {


        $updatedFile = '';
        $file = file($this->cacheFile());
        $val = $this->cacheFindKey($key);

        foreach ($file as $key => $line)
        {
            if($line != $val['data'])
            {
                $updatedFile .= $line;
            }
        }

        file_put_contents($this->cacheFile(), $updatedFile, LOCK_EX);

    }



}