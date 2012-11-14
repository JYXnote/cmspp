<?php

/** 
 * @author OHM
 * 
 */
class Event
{
    const SUFFIX = '.log';
    public static function repeatMountPoint($classA,$classB)
    {
        self::writeLog('StructError',$classA . ' and ' . $classB .' have the same MountPoint');
        exit();
    }
    
    public static function loginFail($username,$password)
    {
        self::writeLog('LoginError','username:' . $username . ' password: ' . substr($password, 0, 4) .'**** @' . WebRequest::getClientIP(),true);
    }
    
    public static function loseLanguageFile($class,$language)
    {
        self::writeLog('StructError',"Language file {$language}.json in {$class} is lost.");
        exit();
    }
    
    public static function losePlugin($pluginName,$version)
    {
        self::writeLog('StructError',"Plugin {$pluginName}({$version}) is lost.");
        exit();
    }
    
    public static function pluginError($pluginName,$error)
    {
    	self::writeLog('PluginError',"Plugin {$pluginName} : {$error}.");
    	exit();
    }
    
    public static function adminProcess($explain)
    {
        self::writeLog('AdminProcess',$explain,true);
    }
    
    private static function writeLog($type,$explain,$neverShow = false)
    {
        if(DEBUG && !$neverShow)echo $explain;
        $fp = fopen(self::getLogFilePath($type),'a');
        fwrite($fp,date("Y-M-D h:i:s").": " . $type . ' ' . $explain);
        fclose($fp);
    }
    
    private static function getLogFilePath($type)
    {
    	return LOGPATH.$type.self::SUFFIX;
    }
}