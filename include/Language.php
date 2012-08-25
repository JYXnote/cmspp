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
    		$languageFilePath = PLUGINPATH."{$this->_class}/language/{$this->_language}.xml";
    		if(is_readable($languageFilePath))
    		{
    		    $xmlDocument = DOMDocument::load($languageFilePath);
         		$xmlroots = $xmlDocument->getElementsByTagName("root");
        		$xmlroot = $xmlroots->item(0);
        		$categorys = $xmlroot->childNodes;
        		foreach($categorys as $category)
        		{
        			$values=$category->childNodes;
					if(empty($values))continue;
        			foreach($values as $value)
        			{
        				if(empty($value->tagName))continue;
        				$this->_strTree[$category->tagName][$value->tagName]=
        					iconv('utf-8',$xmlDocument->encoding,$value->nodeValue);
        			}
        		}
        		Cache::writeCache("language/{$this->_class}_{$this->_language}", $this->_strTree);
    		}
    		else
    		{
    		    Event::loseLanguageFile($this->_class,$this->_language);
    		}
	    }
	}
}