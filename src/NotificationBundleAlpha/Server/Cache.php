<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 25/03/18
 * Time: 01:27
 */

namespace NotificationBundle\Server;


use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Cache extends CacheManager
{
    private $cache;

    /**
     * Cache constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        // available adapters: filesystem, apcu, redis, array, doctrine cache, etc.
        $this->cache = new FilesystemAdapter();
    }




   /**
     * - Set les data dans le cache
     *
     */
   public function cacheSet($key, $val)
    {
        $key = $this->createKey($key);
        // assign a value to the item and save it
        $key->set($val);
        $this->cache->save($val);
        dump($key->get());die();
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


    protected function _storage()
    {
        return $this;
    }
}