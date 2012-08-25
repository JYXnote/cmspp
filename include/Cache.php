<?php

/** 
 * @author OHM
 * 
 */
class Cache
{
    const SUFFIX = '.php';
    public static function readCache($cacheName)
    {
        if(!CACHE)return NULL;
        $cacheFilePath = self::getCacheFilePath($cacheName);
        if(is_readable($cacheFilePath))
            return include($cacheFilePath);
        else
            return NULL;
    }
    
    public static function writeCache($cacheName,$data)
    {
    	$cacheFilePath = self::getCacheFilePath($cacheName);
    	return file_put_contents($cacheFilePath, '<?php return '.var_export($data,true).';');
    }
    
    private static function getCacheFilePath($cacheName)
    {
        return CACHEPATH.$cacheName.self::SUFFIX;
    }
}