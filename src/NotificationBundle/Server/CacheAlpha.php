<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 25/03/18
 * Time: 01:27
 */

namespace NotificationBundle\Server;


use Symfony\Component\DependencyInjection\ContainerInterface;

class CacheAlpha extends CacheManager
{

    /**
     * Cache constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }




   /**
     * - Set les data dans le cache
     *
     */
   public function cacheSet($key, $val)
    {
        $val = var_export($val, true);

        $data = $this->dataFormater($key, $val);

        file_put_contents($this->cacheFile(), $data, FILE_APPEND | LOCK_EX);
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