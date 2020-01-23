<?php
/**
 * Created by PhpStorm.
 * User: manu
 * Date: 12/04/18
 * Time: 15:24
 */

namespace NotificationBundle\Manager\Cache;


use NotificationBundle\Manager\ManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

abstract class Cache implements ManagerInterface
{
    /** @var FilesystemAdapter $cache */
    protected $cache;

    /**
     * - Formatte la clé, créé un cacheItem (clé + valeur) pour une eventuelle insertion en cache
     */
    public abstract function keyFormater($key);


    /**
     * - Create a new item getting it from the cache
     * @param $key
     * @return mixed|\Symfony\Component\Cache\CacheItem
     */
    public function createKey($key)
    {
        return $this->cache->getItem($key);
    }

    /**
     * - Set cache
     */
    public function setCache($key, $val)
    {
        $key->set($val);
        $this->cache->save($key);
    }

    /**
     * - Vérification de la présence de la donnée en cache
     * @param $cacheItem
     * @return array
     */
    public function isExist($cacheItem)
    {
        if ($cacheItem->isHit())
        {
            return $this->inCache($cacheItem);
        }

        return $this->notInCache($cacheItem);
    }

    /**
     * - Delete cache
     * @param $key
     */
    public function delete($key)
    {
        $formattedKey = $this->keyFormater($key);
        $this->cache->deleteItem($formattedKey);
        echo "Deconnexion : client removed from the cache \n";
    }

    /**
     * - Clear cache
     */
    public function clear()
    {
        $this->cache->clear();
    }

    /**
     * - Reset cache
     */
    public function reset()
    {
        return $this->cache->reset();
    }

    public function save()
    {
        // TODO: Implement save() method.
    }
}