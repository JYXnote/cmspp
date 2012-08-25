<?php
define('INCMSPP', true);

define('ROOTURL','http://'.$_SERVER['HTTP_HOST'].'/');
define('PLUGINURL',ROOTURL.'plugins/');
define('ROOTPATH', dirname(dirname(__FILE__)).'/');
define('SETTINGPATH', ROOTPATH.'data/setting/');
define('CACHEPATH', ROOTPATH.'data/cache/');
define('LOGPATH', ROOTPATH.'data/log/');
define('PLUGINPATH', ROOTPATH.'plugins/');
define('INCLUDEPATH', ROOTPATH.'include/');
define('LANGUAGEPATH', ROOTPATH.'language/');
define('TEMPLATECACHEPATH', CACHEPATH.'template/');


define('DEBUG',true);
define('CACHE',false);
define('DEFAULTLANGUAGE','zh_cn');
define('CHARSET','utf-8');