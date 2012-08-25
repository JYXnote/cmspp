<?php
/**
 *
 * @author OHM
 *        
 */
class Mainframe extends PluginBase
{
    public static $mountPoint = Array(
    		'/' => 'index',
    		'/products' => 'products',
    		'/manual' => 'manual'
    );
    public static $subMontPoint = Array();
    protected static $_template = NULL;
    
    public static function init()
    {
        self::$_template = new Template(__CLASS__,DEFAULTLANGUAGE);
    }
    
    public static function getVersion()
    {
        return 'Mainframe V0.0.1';
    }

    public static function index()
    {
        self::$_template->show('index.html');
    }

    public static function products()
    {
    	self::$_template->show('products.html');
    }
	
	public static function manual()
	{
		self::$_template->show('manual.html');
    }
    
    public static function onInstall(){return TRUE;}
    public static function onUninstall(){return TRUE;}
}