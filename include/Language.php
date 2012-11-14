<?php

/**
 * @author OHM
 *
 */
class Language
{
	private $_strTree = NULL;
	private $_language;
	private $_class;
	public function __construct($class,$language = DEFAULTLANGUAGE)
	{
	    $this->_language = $language;
	    $this->_class = $class;
	    $this->init();
	}
	
	public function __destruct()
	{
	    unset($_strTree);
	}
	
	public function _T($string)
	{
		$path = explode('/', $string);
		if(count($path)==1)array_unshift($path, 'common');

		if(isset($this->_strTree[$path[0]][$path[1]]))
			return $this->_strTree[$path[0]][$path[1]];
		else 
			return NULL;
	}
	
	private function init()
	{
	    $this->_strTree = Cache::readCache("language/{$this->_class}_{$this->_language}");
	    if($this->_strTree==NULL)
	    {
    		$this->_strTree = array();
    		$languageFilePath = PLUGINPATH."{$this->_class}/language/{$this->_language}.json";
    		if(is_readable($languageFilePath))
    		{
    			$this->_strTree = json_decode(file_get_contents($languageFilePath),true);
        		Cache::writeCache("language/{$this->_class}_{$this->_language}", $this->_strTree);
    		}
    		else
    		{
    		    Event::loseLanguageFile($this->_class,$this->_language);
    		}
	    }
	}
}