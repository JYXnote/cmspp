<?php
/**
 *
 * @author OHM
 *
 */
class WebRequest
{
    public static $Cookie;
    public static $Session;
    public static $Param;
    public static $Path;
    
    public static function init()
    {
    	if(AUTOSESSIONDOMAIN){
    		ini_set('session.cookie_domain', (strpos($_SERVER['HTTP_HOST'],'.') !== false) ? $_SERVER['HTTP_HOST'] : '');
    	}
    	@session_start();
        self::$Session = $_SESSION;
        self::$Cookie = $_COOKIE;
        @$urlPath = '/' . $_GET['u'];
        if(strripos($urlPath, '.html')==strlen($urlPath)-5)
        {
            $paramString = str_replace('.html', '',substr(strrchr($urlPath, '/'),1));
            self::$Param = $_POST + explode('_', $paramString);
            self::$Path = substr($urlPath, 0,strripos($urlPath, '/'));
            if(empty(self::$Path))
                self::$Path = '/';
        }
        else
        {
            self::$Param = $_POST;
            self::$Path = $urlPath;
        }
		if(strlen(self::$Path)>1 && substr(self::$Path,-1)=='/')
		{
			self::$Path = substr(self::$Path,0,-1);
		}
    }
    
    public static function header($str)
    {
        header($str);
    }
    
    public static function getClientIP()
    {
    	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
    		$ip = getenv("HTTP_CLIENT_IP");
    	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
    		$ip = getenv("HTTP_X_FORWARDED_FOR");
    	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
    		$ip = getenv("REMOTE_ADDR");
    	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
    		$ip = $_SERVER['REMOTE_ADDR'];
    	else
    		$ip = "unknown";
    	return($ip);
    }
    
    public static function header_status($status)
    {
    	if (substr(php_sapi_name(), 0, 3) == 'cgi')
    		header('Status: '.$status, TRUE);
    	else
    		header($_SERVER['SERVER_PROTOCOL'].' '.$status);
    	exit();
    }
    
    public static function response_json($data=NULL,$status=200)
    {
    	exit(json_encode(array('status'=>$status,'data'=>$data)));
    }

}