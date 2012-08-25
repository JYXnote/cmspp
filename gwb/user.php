<?php
	$vReturn = json_encode(array('status'=>'400','error'=>'server unknow error'));
	$con = mysql_connect('localhost','a0715152831','12164995');
	mysql_select_db('a0715152831',$con);

	switch($_GET['a'])
	{
		case 'regist':
			if(!isset($_POST['aescode']) || !isset($_POST['username']))
			{
				$vReturn = json_encode(array('status'=>'401','error'=>'without post aescode'));
			}
			else
			{
				$sql = sprintf("insert into `gwb_user`(`username`,`aescode`) VALUES('%s','%s')",
					mysql_escape_string($_POST['username']),mysql_escape_string($_POST['aescode']));
				mysql_query($sql,$con);
				if(mysql_affected_rows($con))
				{
					$vReturn = json_encode(array('status'=>'200','success'=>'regist success'));
				}
				else
				{
					$vReturn = json_encode(array('status'=>'402','error'=>'username is existed'));
				}
			}
			break;
		case 'check':
			if(!isset($_GET['username']))
			{
				$vReturn = json_encode(array('status'=>'403','error'=>'without username'));
			}
			else
			{
				$sql = sprintf("select `_id` from `gwb_user` where `username` = '%s'",
					mysql_escape_string($_GET['username']));
				$query = mysql_query($sql,$con);
				if(mysql_fetch_row($query))
				{
					$vReturn = json_encode(array('status'=>'302','warning'=>'username is existed'));
				}
				else
				{
					$vReturn = json_encode(array('status'=>'200','success'=>'username is not existed'));
				}
			}
			break;
		case 'login':
			if(!isset($_GET['username']))
			{
				$vReturn = json_encode(array('status'=>'403','error'=>'without username'));
			}
			else
			{
				$sql = sprintf("select `aescode` from `gwb_user` where `username` = '%s'",
					mysql_escape_string($_GET['username']));
				$query = mysql_query($sql,$con);
				if($rs = mysql_fetch_array($query))
				{
					$vReturn = json_encode(array('status'=>'200','aescode'=>$rs['aescode']));
				}
				else
				{
					$vReturn = json_encode(array('status'=>'404','success'=>'username is not existed'));
				}
			}
			break;
		default:break;
	}
	echo $vReturn;