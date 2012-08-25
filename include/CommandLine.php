<?php
/** 
 * @author OHM
 * 
 */
class CommandLine
{    
	public static function command($command)
	{
	    $command = self::commandFilter($command);
	    if(strlen($command)==0)return '';
	    $commandArray = explode(' ', $command);
	    $command = strtolower(array_shift($commandArray));
	    $params = $commandArray;
	    if(!self::isLogin())
	    {
	        if($command=='help' || $command=='hello')
	            return self::$command();
	        else if($command=='login')
	            return self::login($params);
	        else
	            return 'Please login first';
	    }       

	    switch ($command)
	    {
	        case 'install':case 'uninstall':case 'login':case 'admin':case 'password': case 'ip':
	            return self::$command($params);
	        case 'logout':case 'exit':
	            return self::logout();
	        case 'help':case 'hello':case 'plugins':
	            return self::$command();
	        default: return $command . ' is not internal or external command';
	    }
	}
	
	private static function commandFilter($command)
	{
	    $command = trim($command);
	    $command = preg_replace('/\s(?=\s)/', '', $command);
	    $command = preg_replace('/[\n\r\t]/', ' ', $command);
	    return $command;
	}
	
	private static function install($plugins)
	{
	    if(count($plugins)==0)
	        return 'Command syntax is incorrect';
	    $plugins=array_unique($plugins);
	    $successArray = Array();
	    $errorArray = Array();
	    foreach($plugins as $plugin)
	    {
	    	$returnString = Setting::installPlugin($plugin);
	    	if($returnString == NULL)
	    	{
	    		$successArray[] = "$plugin is installed successfully";
	    	}
	    	else
	    	{
	    		$errorArray[] = $returnString;
	    	}
	    }
	    return implode("\n",$successArray+$errorArray);
	}
	
	private static function uninstall($plugins)
	{
	    if(count($plugins)==0)
	    	return 'Command syntax is incorrect';
	    $plugins=array_unique($plugins);
	    $successArray = Array();
	    $errorArray = Array();
	    foreach($plugins as $plugin)
	    {
	    	$returnString = Setting::uninstallPlugin($plugin);
	    	if($returnString == NULL)
	    	{
	    		$successArray[] = "$plugin is uninstalled successfully";
	    	}
	    	else
	    	{
	    		$errorArray[] = $returnString;
	    	}
	    }
	    return implode("\n",$successArray+$errorArray);
	}
	
	private static function login($params)
	{
	    $clientIp = WebRequest::getClientIP();
	    $loginError = Setting::readSetting('admin_LoginError');
	    if($loginError!=NULL)
	    {
	        foreach($loginError as $ip => $item)
	        {
	            if(time() - $item['timestamp']>900)
	                unset($loginError[$ip]);
	        }
	        if(isset($loginError[$clientIp]) && $loginError[$clientIp]['errorcount']>3)
	        {
	            return 'Access forbidden';
	        }
	    }
	    if(count($params)!=2)
	    	return 'Command syntax is incorrect';
	    $username = $params[0];
	    $password = $params[1];
	    
	    $userSetting = Setting::readSetting('admin_userInfo');
	    if(isset($userSetting[$username]) &&
	             md5($password.'&CMSPP&'.$username) == $userSetting[$username]['password'] &&
	            ($userSetting[$username]['allowIP']=='%' || $userSetting[$username]['allowIP'] == WebRequest::getClientIP()))
	    {
	        session_regenerate_id();
	        $sercetCode = Array(
	        		$clientIp,
	        		$_SERVER[HTTP_USER_AGENT],
	        		session_id()
	        );
	        $_SESSION['adminLoginFlag'] =  md5(implode('&', $sercetCode));
	        $_SESSION['adminUserName'] = $username;
	        return "Welcome $username!";
	    }
	    else
	    {
	        if($loginError == NULL)
	        {
	            $loginError = array(
	                        $clientIp =>
	                        array('timestamp'=>time(),'errorcount'=>1)
	                    );
	        }
	        else if(!isset($loginError[$clientIp]))
	        {
	            $loginError[$clientIp] = array('timestamp'=>time(),'errorcount'=>1);
	        }
	        else
	        {
	            $loginError[$clientIp]['errorcount']++;
	        }
	        Event::loginFail($username, $password);
	        Setting::writeSetting('admin_LoginError',$loginError);
	        return 'Username/password is incorrect or remote login is not allowed';
	    }
	}
	
	private static function isLogin()
	{
	    if(isset($_SESSION['adminLoginFlag']))
	    {
	        $sercetCode = Array(
	               WebRequest::getClientIP(),
	               $_SERVER[HTTP_USER_AGENT],
	               session_id()
	        );
	        $sercetCode = md5(implode('&', $sercetCode));
	        return $_SESSION['adminLoginFlag'] == $sercetCode?true:false;
	    }
	    else
	    {
	        return false;
	    }
	}
	
	private static function admin($params)
	{
	    if(count($params)<1)
	    	return 'Command syntax is incorrect';
	    switch (strtolower(trim(array_shift($params))))
	    {
	        case 'create':
	            return self::admin_create($params);
	            break;
	        case 'delete':
	            return self::admin_delete($params);
	            break;
	        case 'update':
	            return self::admin_update($params);
	            break;
	        case 'list':
	            return self::admin_list($params);
	            break;
	        default:
	            return 'Command syntax is incorrect';
	    }
	}
	
	private static function admin_create($params)
	{
	    if(count($params)!=2)
	    	return 'Command syntax is incorrect';
	    
	    $userSetting = Setting::readSetting('admin_userInfo');
	    if(!$userSetting[$_SESSION['adminUserName']]['isSupperAdmin'])
	    	return 'Access forbidden';
	    
	    $userInfo = explode('@', $params[0]);
	    if(count($userInfo)!=2)
	    	return 'Command syntax is incorrect';
	    $username = trim($userInfo[0]);
	    $allowIP = trim($userInfo[1]);
	    $password = $params[1];
	    if(empty($username) || empty($allowIP) || empty($password))
	    	return 'Command syntax is incorrect';
	    if(isset($userSetting[$username]))
	    {
	    	return "Admin {$username} is exsit";
	    }
    	$userSetting[$username] = Array(
        	'allowIP'=>$allowIP,
        	'password'=>md5($password.'&CMSPP&'.$username),
        	'isSupperAdmin'=>false);
    	Setting::writeSetting('admin_userInfo', $userSetting);
    	return "Admin {$params[0]} is created successfully";
	}
	
	private static function admin_delete($params)
	{
	    if(count($params)!=1)
	    	return 'Command syntax is incorrect';
	    
	    $userSetting = Setting::readSetting('admin_userInfo');
	    if(!$userSetting[$_SESSION['adminUserName']]['isSupperAdmin'])
	    	return 'Access forbidden';
	    
	    $username = trim($params[0]);
	    if(empty($username))
	    	return 'Command syntax is incorrect';
	    if(isset($userSetting[$username]))
	    {
	    	if($userSetting[$username]['isSupperAdmin'])
	    		return 'SuperAdmin cannot be deleted';
	    	unset($userSetting[$username]);
	    	Setting::writeSetting('admin_userInfo', $userSetting);
	    	return "Admin {$params[0]} is deleted successfully";
	    }
	    else
	    {
	        return "Admin {$username} is not exsit";
	    }
	}
	
	private static function admin_update($params)
	{
	    if(count($params)!=3)
	    	return 'Command syntax is incorrect';
	    $userSetting = Setting::readSetting('admin_userInfo');
	    if(!$userSetting[$_SESSION['adminUserName']]['isSupperAdmin'])
	    	return 'Access forbidden';
	    $oldUsername = trim($params[0]);
	    if(empty($oldUsername))
	    	return 'Command syntax is incorrect';
	    if(!isset($userSetting[$oldUsername]))
	    {
	    	return "Old admin {$oldUsername} is not exsit";
	    }
	    $userInfo = explode('@', $params[1]);
    	if(count($userInfo)!=2)
    		return 'Command syntax is incorrect';
		$username = trim($userInfo[0]);
		$allowIP = trim($userInfo[1]);
    	$password = $params[2];
    	if(empty($username) || empty($allowIP) || empty($password))
    		return 'Command syntax is incorrect';
    	if(isset($userSetting[$username]) && $username!=$oldUsername)
    	{
	    	return "New admin {$username} is exsit";
	    }
	    if($username!=$oldUsername)
    	{
        	$userSetting[$username] = $userSetting[$oldUsername];
        	unset($userSetting[$oldUsername]);
    	}
    	$userSetting[$username]['allowIP'] = $allowIP;
    	$userSetting[$username]['password'] = md5($password.'&CMSPP&'.$username);
		Setting::writeSetting('admin_userInfo', $userSetting);
		if($oldUsername==$_SESSION['adminUserName'])
		{
		    self::logout();
		}
		return "Admin is updated successfully";
	}

	private static function admin_list($params)
	{
		if(!empty($params))
			return 'Command syntax is incorrect';
		 
		$userSetting = Setting::readSetting('admin_userInfo');
		if(!$userSetting[$_SESSION['adminUserName']]['isSupperAdmin'])
			return 'Access forbidden';
		$rValArray = array(sprintf("%10s     %s",'Username','AllowIP'));
		foreach($userSetting as $username=>$userInfo)
		{
		    $rValArray[] = sprintf("%10s     %s",$username,$userInfo['allowIP']);
		}
		return implode("\n", $rValArray);
    }
	
	private static function ip($params)
	{
	    if(count($params)!=1)
	    	return 'Command syntax is incorrect';
	    $userSetting = Setting::readSetting('admin_userInfo');
	    $ip = trim($params[0]);
	    if(empty($ip))
	    	return 'Command syntax is incorrect';
	    $userSetting[$_SESSION['adminUserName']]['allowIP'] = $ip;
	    Setting::writeSetting('admin_userInfo', $userSetting);
	    self::logout();
	    return "Your login IP is modified successfully";
	}
	
	private static function password($params)
	{
	    if(count($params)!=1)
	    	return 'Command syntax is incorrect';
	    $userSetting = Setting::readSetting('admin_userInfo');
	    $password = trim($params[0]);
	    if(empty($password))
	    	return 'Command syntax is incorrect';
	    $userSetting[$_SESSION['adminUserName']]['password'] =
	    md5($password.'&CMSPP&'.$_SESSION['adminUserName']);
	    Setting::writeSetting('admin_userInfo', $userSetting);
	    self::logout();
	    return "Your password is modified successfully";
	}
	
	private static function help()
	{
	     $rVal = Array();
	     $rVal[] =  "admin create USERNAME@IP PASSWORD                 Create admin, ip can be '%'";
	     $rVal[] =  "      delete USERNAME                             Delete admin";
	     $rVal[] =  "      update USERNAME NEWUSERNAME@IP NEWPASSWORD  Update admin";
	     $rVal[] =  "      list                                        Show admin list";
	     $rVal[] =  "install/uninstall PLUGIN1, PLUGIN2 ...            Install or uninstall plugin(s)";
	     $rVal[] =  "ip NEWIP                                          Modify login ip";
	     $rVal[] =  "login USERNAME PASSWORD                           Login to admin CommandLine";
	     $rVal[] =  "logout/exit                                       Logout";
	     $rVal[] =  "plugins                                           Show plugins";
	     $rVal[] =  "password NEWPASSWORD                              Modify password";
	     return implode("\n", $rVal);
	}
	
	private static function plugins()
	{ 
	    if(is_dir(PLUGINPATH))
	    {
	        $rVal = Array(sprintf("%15s  %8s",'PluginName','IsEnable'));
	        $plugins = array();
	        $rootpath = opendir(PLUGINPATH);
	        while(($dirhand = readdir($rootpath))!=NULL)
	        {
	            if(is_dir(PLUGINPATH.$dirhand) && $dirhand!='.' && $dirhand!='..')
	            {
	                $plugins[]=$dirhand;
	            }
	        }
	        foreach($plugins as $plugin)
	        {
	            $rVal[] = sprintf("%15s  %6s",$plugin,in_array($plugin, Setting::$plugins)?'   On ':'  Off ');
	        }
	        return implode("\n", $rVal);
	    }
	    else
	    {
	        return 'Plugin path is not exsist';
	    }
	}
	
	private static function hello()
	{
	    return "Welcome! \n".Cmspp::getVersion()."\nPlease Login";
	}
	
	private static function logout()
	{
	    unset($_SESSION['adminLoginFlag']);
	    return 'Logout successfully';
	}
}