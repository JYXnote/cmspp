<?php

/** 
 * @author OHM
 * 
 */
abstract class PluginBase
{
    public static $mountPoint = Array();
    public static $subMontPoint = Array();
    public static $refrencePlugin = Array();
    protected static $_template = NULL;
    
    abstract public static function getVersion();
    abstract public static function init();
    
    public static function onInstall(){return TRUE;}
    public static function onUninstall(){return TRUE;}
    public static function admincp(){return NULL;}
}

?>