<?php

namespace NotificationBundle\Manager\Cache;


abstract class Cache
{
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
}