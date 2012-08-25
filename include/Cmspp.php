<?php
/**
 *
 * @author OHM
 *        
 */
class Cmspp
{
    public static $_G;
    
    private static $_coreClassArray = Array(
            'Cache','Cmspp','CommandLine',
            'Event','Language','Setting','String','Template',
            'WebRequest','PluginBase');
    
    public static function getVersion()
    {
        return 'CMSPP alpha 0.0.0.4';
    }
    
    public static function init($routeToPlugin = true)
    {
        spl_autoload_register('Cmspp::autoLoadClass');
        Setting::init();
        WebRequest::init();
        if($routeToPlugin)
            self::pluginRoute();
    }
    
    private static function pluginRoute()
    {
        foreach(Setting::$mountPoint as $route=>$class)
        {
            if(preg_match('/^'.str_replace('/', '\/', $route).'$/',WebRequest::$Path,$match))
            {
                $mountPoint = self::get_plugin_property($class,'mountPoint');
                $function = $mountPoint[$route];
                //$class::$function();
				call_user_func(array($class,$function));
            }
        }
    }
	
    private static function autoLoadClass($className)
    {
        if(in_array($className, self::$_coreClassArray))
        {
            $includePath = INCLUDEPATH.$className.'.php';
            if(is_readable($includePath))
            {
                include($includePath);
            }
        }
        else if(in_array($className, Setting::$plugins))
        {
            $includePath = PLUGINPATH.$className.'/'.$className.'.php';
            if(is_readable($includePath))
            {
            	include($includePath);
                $refrencePlugin = self::get_plugin_property($className,'refrencePlugin');
            	foreach($refrencePlugin as $refrenceClass=>$refrenceClassVersion)
            	{
            	    if(DEBUG)
            	    {
                	    //if(!preg_match('/^'.$refrenceClassVersion.'$/',$refrenceClass::getVersion()))
                	    if(!preg_match('/^'.$refrenceClassVersion.'$/',call_user_func(array($refrenceClass,'getVersion'))))
						{
                	        Event::losePlugin($refrenceClass, $refrenceClassVersion);
                	    }
            	    }
            	    else
            	    {
            	        //$refrenceClass::getVersion();
						call_user_func(array($refrenceClass,'getVersion'));
            	    }
            	}
            	//$className::init($className);
				call_user_func(array($className,'init'),$className);
            }
        }
        else
        {
            Event::losePlugin($className, 'Unknown');
        }
    }
	
    public static function get_plugin_property($class,$property)
    {
        if(!property_exists($class,$property))return NULL;
        $vars = get_class_vars($class);
        return $vars[$property];
    }
}