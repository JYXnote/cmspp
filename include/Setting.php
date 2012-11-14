<?php

/** 
 * @author OHM
 * 
 */
class Setting
{
    const PLUGINS_ENABLE = 'plugins_enable.php';
    
    public static $mountPoint = Array();
    public static $subMountPoint = Array();
    public static $plugins = Array();
    
    public static function init()
    {
        self::$plugins = self::readSetting('plugins_enable');
        self::loadMountPoint();
        self::loadSubMountPoint();
    }

    public static function installPlugin($plugin)
    {
        $pluginFilePath = PLUGINPATH."{$plugin}/{$plugin}.php";
        if(is_readable($pluginFilePath))
        {
            if(!in_array($plugin, self::$plugins))
            {
                $needToInstall = array();
                include ($pluginFilePath);
                $refrencePlugin = Cmspp::get_plugin_property($plugin,'refrencePlugin');
                foreach($refrencePlugin as $needPlugin=>$needVersion)
                {
                    if(!in_array($needPlugin, self::$plugins))
                    {
                        $needToInstall[] = "{$needPlugin}({$needVersion})";
                    }
                }
                if(!empty($needToInstall))
                    return "Need to install those plugins before:\n".implode("\n", $needToInstall);
                //if($plugin::onInstall()==false)
				if(call_user_func(array($plugin,'onInstall'))==false)
                    return "An error occurred in {$plugin} installation.";
                self::$plugins[] = $plugin;
                return self::writeSetting('plugins_enable', self::$plugins)?NULL:"Access forbidden!";
            }
            else
            {
                return $plugin." had been installed.";
            }
        }
        else
        {
            return "Plugin $plugin is not exsist!";
        }
    }
      
    public static function uninstallPlugin($plugin)
    {
    	$pluginFilePath = PLUGINPATH."{$plugin}/{$plugin}.php";
    	if(is_readable($pluginFilePath))
    	{
    	    $pos = array_search($plugin, self::$plugins);
    		if($pos!==false)
    		{
    		    $needTounInstall = array();
    		    foreach(self::$plugins as $onUsePlugin)
    		    {
                    $refrencePlugin = Cmspp::get_plugin_property($onUsePlugin,'refrencePlugin');
    		    	if(isset($refrencePlugin[$plugin]))
    		    	{
    		    		$needToUninstall[] = "{$onUsePlugin}";
    		    	}
    		    }
    		    if(!empty($needToUninstall))
    		    	return "Need to uninstall those plugins before:\n".implode("\n", $needToUninstall);
                //if($plugin::onUninstall()==false)
				if(call_user_func(array($plugin,'onUninstall'))==false)
                    return "An error occurred in {$plugin} uninstallation.";

    			unset(self::$plugins[$pos]);
    			return self::writeSetting('plugins_enable', self::$plugins)?NULL:"Access forbidden!";
    		}
    		else
    		{
    			return $plugin." was not been installed.";
    		}
    	}
    	else
    	{
    	    $pos = array_search($plugin, Setting::$plugins);
    	    if($pos!==false)
    	    {
    	        unset(Setting::$plugins[$pos]);
    	        self::writeSetting('plugins_enable', self::$plugins);
    	    }
    		return "Plugin $plugin is not exsist!";
    	}
	}

    private static function loadMountPoint()
    {
        self::$mountPoint = Cache::readCache('MountPoint');
        if(self::$mountPoint == NULL)
        {
            foreach(self::$plugins as $plugin)
            {
                $mountPoint = Cmspp::get_plugin_property($plugin,'mountPoint');
                foreach($mountPoint as $pluginMountPoint=>$enterFunction)
                {
                    if(isset(self::$mountPoint[$pluginMountPoint]))
                    {
                        Event::repeatMountPoint(self::$mountPoint[$pluginMountPoint], $plugin);
                    }
                    else
                    {
                        self::$mountPoint[$pluginMountPoint] = $plugin;
                    }
                }
            }
            Cache::writeCache('MountPoint',self::$mountPoint);
        }
    }

    private static function loadSubMountPoint ()
    {
        self::$subMountPoint = Cache::readCache('SubMountPoint');
        if (self::$subMountPoint == NULL)
        {
            foreach (self::$plugins as $plugin)
            {
                $subMountPoint = Cmspp::get_plugin_property($plugin,'subMountPoint');
                foreach ($subMountPoint as $pluginSubMountPoint => $enterFunction)
                {
                    if (isset(self::$subMountPoint[$pluginSubMountPoint]))
                    {
                        self::$subMountPoint[$pluginSubMountPoint][] = $plugin;
                    }
                    else
                    {
                        self::$subMountPoint[$pluginSubMountPoint] = Array($plugin);
                    }
                }
            }
            Cache::writeCache('SubMountPoint', self::$subMountPoint);
        }
    }
    
    public static function writeSetting($settingName,$data)
    {
        $settingFilePath = self::getSettingFilePath($settingName);
    	return file_put_contents($settingFilePath, '<?php return '.var_export($data,true).';');
    }
    
    public static function readSetting($settingName)
    {
        $settingFilePath = self::getSettingFilePath($settingName);
        if(is_readable($settingFilePath))
            return include($settingFilePath);
        else
            return NULL;
    }
	
    private static function getSettingFilePath($settingName)
    {
        return SETTINGPATH.$settingName.'.php';
    }
}