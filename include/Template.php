<?php

/** 
 * @author OHM
 * 
 */
class Template
{
    private $_language;
    private $_class;
	private $_preSafeCode = "<?php if(!defined('INCMSPP')) exit('Forbidden!'); ?>\n";
    public function __construct($class, $language = DEFAULTLANGUAGE)
    {
        $this->_class = $class;
        $this->_language = new Language($class,$language);
    }
    
    public function show ($location, $params = array(), $mainTemplate = true)
    {
        $location = PLUGINPATH."{$this->_class}/template/".$location;
		if($this->refresh($location))
		{
			extract($params);
		    if($mainTemplate)
            {
                header('Content-type: text/html; charset='.CHARSET);
                ob_start();
            }
			include(self::getCacheFilePath($location));
			if($mainTemplate) ob_end_flush();
		}
		else
		{
			WebRequest::header_status('404 Not Found'); 
		}
    }

    private function refresh ($location)
    {
		if(!is_readable($location))return FALSE;
		if(!CACHE || !is_readable(self::getCacheFilePath($location)))
		{
			$content = file_get_contents($location);
			file_put_contents(self::getCacheFilePath($location),$this->_preSafeCode.$this->_tpl($content));
		}
		if(is_readable(self::getCacheFilePath($location)))
			return TRUE;
		else
			return FALSE;
    }

    private static function getCacheFilePath($location)
    {
        return TEMPLATECACHEPATH.str_replace(array('/','.html'), array('_','.php'), str_replace(PLUGINPATH,'',$location));
    }

    private function _tpl ($content)
    {
    	
        $string = preg_replace_callback('/<!--{template (.*?)}-->/', array(&$this,'_TEMPLATE'), $content);
        $string = preg_replace_callback('/<!--{#(.*?)#}-->/', array(&$this,'_T'),  $string);
        $string = preg_replace_callback('/<!--{@(.*?)@}-->/', array(&$this,'_AT'),  $string);
        $string = preg_replace_callback('/<!--{~\/(.*?)}-->/', array(&$this,'_HOME'),  $string);
        $string = preg_replace_callback('/<!--{\[(.*?)\]}-->/',array(&$this, '_SUBMOUNTPOINT'),$string);
        $string = preg_replace_callback('/<!--{for\((.*?)\)}-->/',array(&$this, '_FOR'), $string);
        $string = preg_replace_callback('/<!--{foreach\((.*?)\)}-->/',array(&$this, 'Template::_FOREACH'), $string);
        $string = preg_replace_callback('/<!--{if\((.*?)\)}-->/',array(&$this, '_IF'), $string);
        $string = preg_replace_callback('/<!--{\/([a-z|A-z]*?)}-->/', array(&$this, 'Template::_END'), $string);
        $string = preg_replace_callback('/<!--{\$(.*?)}-->/',array(&$this, '_VAR'), $string);
        $string = preg_replace_callback('/<!--{eval (.*?)}-->/',array(&$this, '_EVAL'), $string);
        return $string;
    }

    private function _TEMPLATE ($matches)
    {
        $string = trim($matches[1]);
        $location = PLUGINPATH.$this->_class.'/template/'.$string;
        if (!$this->refresh($location))
            WebRequest::header_status('404 Not Found');
        return '<?php include(\'' . self::getCacheFilePath($location) .'\');?>';
    }

    private function _T ($matches)
    {
        $string = trim($matches[1]);
        return $this->_language->_T($string);
    }

    private function _AT ($matches)
    {
        $string = trim($matches[1]);
        return PLUGINURL.$this->_class.'/static/'.$string;
    }
    
    private function _HOME ($matches)
    {
    	$string = trim($matches[1]);
    	return ROOTURL.$string;
    }
    
    private function _SUBMOUNTPOINT($matches)
    {
        $string = trim($matches[1]);
        if(isset(Setting::$subMountPoint[$string]))
        {
            $pluginArray = Setting::$subMountPoint[$string];
            $evalArray= Array();
            foreach($pluginArray as $plugin)
            {
                $subMountPoint = Cmspp::get_plugin_property($plugin,'subMountPoint');
                array_push($evalArray, $plugin.'::'.$subMountPoint[$string].'();');
            }
            $evalString = '<?php '.implode($evalArray).' ?>';
        }
        else
        {
            $evalString = "";
        }
        if(DEBUG)
        	return "[{$string}]".$evalString;
        else
        	return $evalString;
    }

    private function _FOR ($matches)
    {
        $string = trim($matches[1]);
        return "<?php for($string){?>";
    }

    private function _FOREACH ($matches)
    {
        $string = trim($matches[1]);
        return "<?php foreach($string){?>";
    }

    private function _IF ($matches)
    {
        $string = trim($matches[1]);
        return "<?php if($string){?>";
    }

    private function _VAR ($matches)
    {
        return '<?php echo $' . $matches[1] . ';?>';
    }

    private function _EVAL ($matches)
    {
        return '<?php ' . $matches[1] . ';?>';
    }

    private function _END ($matches)
    {
        return "<?php }?>";
    }
}