<?php
	include('AutoSqlTables.php');
	class Mysql extends PluginBase
	{
		private static $_connect = NULL;
		
		public static function getVersion()
		{
			return 'Mysql V0.0.1';
		}
		
		public static function init()
		{
			if(self::$_connect==NULL){
				self::$_connect = mysql_connect(DBSERVER,DBUSER,DBPSW);
				mysql_select_db(DBNAME,self::$_connect);
				mysql_query('set names '.str_replace('-', '', CHARSET),self::$_connect);
			}
		}
		
		public static function fetchAll($sql)
		{
			$rVal = array();
			$result = self::query($sql);
			if(!$result)return array();
			while(($rs = mysql_fetch_assoc($result))!=NULL){
				$rVal[]=$rs;
			}
			return $rVal;
		}
		
		public static function fetchOneRow($sql)
		{
			return mysql_fetch_assoc(self::query($sql));
		}
		
		public static function fetchOneFiled($sql)
		{
			$row = mysql_fetch_row(self::query($sql));
			return $row[0];
		}
		
		public static function query($sql)
		{
			if(self::$_connect==NULL)self::init();
			return mysql_query($sql,self::$_connect);
		}
		
		public static function insert($sql)
		{
			self::query($sql);
			return mysql_insert_id(self::$_connect);
		}
		
		public static function autoRefresh($table,$data)
		{
			if(!isset(Cmspp::$_G['autoSqlTables'][$table])){
				Event::pluginError(__CLASS__, "Table {$table} is not supported in autoRefresh");
				return NULL;
			}
			Mysql::query('DELETE FROM `'.DBPREFIX.$table.'`');
			$count = array();
			foreach (Cmspp::$_G['autoSqlTables'][$table]['keys'] as $key=>$value){
				$count[] = count($data[$key]);
			}
			$count = min($count);
			for($i=0;$i<$count;$i++)
			{
				$thoseValues = array();
				foreach (Cmspp::$_G['autoSqlTables'][$table]['keys'] as $key=>$value){
					$thoseValues[] = mysql_escape_string($data[$key][$i]);
				}
				$sql = 'INSERT INTO `' . DBPREFIX . $table . '`(`' . implode('`,`', array_keys(Cmspp::$_G['autoSqlTables'][$table]['keys'])) . '`) VALUES("'. implode('","', $thoseValues) .'")';
				Mysql::query($sql);
			}
		}
		
		public static function autoSql($option,$table,$data)
		{
			if(!isset(Cmspp::$_G['autoSqlTables'][$table])){
				Event::pluginError(__CLASS__, "Table {$table} is not supported in autoSql");
				return NULL;
			}
			switch (strtoupper($option))
			{
				case 'INSERT':
					$insertArray = array();
					foreach($data as $key=>$value){
						if(isset(Cmspp::$_G['autoSqlTables'][$table]['keys'][$key])){
							switch(Cmspp::$_G['autoSqlTables'][$table]['keys'][$key]){
								case 'string':case 'datetime':
									$insertArray['`'.$key.'`'] = '"'.mysql_escape_string(stripslashes($value)).'"';
									break;
								case 'int':
									$insertArray['`'.$key.'`']=intval($value);
									break;
								case 'float':
									$insertArray['`'.$key.'`']=floatval($value);
									break;
								case 'array':
									$insertArray['`'.$key.'`'] = '"'.mysql_escape_string(json_encode($value)).'"';
									break;
							}
						}
					}
					$sql = 'INSERT INTO `'.DBPREFIX.$table.'`(' . implode(',', array_keys($insertArray))  . ') VALUES('.implode(',', $insertArray) .')';
					return Mysql::insert($sql);
					break;
				case 'UPDATE':
					if(!isset($data[Cmspp::$_G['autoSqlTables'][$table]['primary']]))return NULL;
					$primary = intval($data[Cmspp::$_G['autoSqlTables'][$table]['primary']]);
					$updateArray = array();
					foreach($data as $key=>$value){
						if(isset(Cmspp::$_G['autoSqlTables'][$table]['keys'][$key])){
							switch(Cmspp::$_G['autoSqlTables'][$table]['keys'][$key]){
								case 'string':case 'datetime':
									array_push($updateArray, '`'.$key.'` = "'.mysql_escape_string(stripslashes($value)).'"');
									break;
								case 'int':
									array_push($updateArray, sprintf('`%s` = %d',$key,intval($value)));
									break;
								case 'float':
									array_push($updateArray, sprintf('`%s` = %f',$key,floatval($value)));
									break;
								case 'array':
									array_push($updateArray, '`'.$key.'` = "'.mysql_escape_string(json_encode($value)).'"');
									break;
								default:
									array_push($updateArray, '`'.$key.'` = "'.mysql_escape_string(stripslashes($value)).'"');
									break;
							}
						}
					}
					$sql = 'UPDATE `'.DBPREFIX.$table.'` SET '.implode(' , ', $updateArray).' WHERE '.sprintf('`%s` = %d',Cmspp::$_G['autoSqlTables'][$table]['primary'],$primary);
					return Mysql::query($sql);
					break;
				case 'DELETE':
					if(!isset($data[Cmspp::$_G['autoSqlTables'][$table]['primary']]))return NULL;
					$primary = abs(intval($data[Cmspp::$_G['autoSqlTables'][$table]['primary']]));
					$sql = 'DELETE FROM `'.DBPREFIX.$table.'` WHERE '.sprintf('`%s` = %d',Cmspp::$_G['autoSqlTables'][$table]['primary'],$primary);
					return self::query($sql);
					break;
				default:
					return NULL;
					break;
			}
			
		}
		
		public static function fetchRowByPrimaryKey($table,$key)
		{
			if(!isset(Cmspp::$_G['autoSqlTables'][$table])){
				Event::pluginError(__CLASS__, "Table {$table} is not supported in fetchRowByPrimaryKey");
				return NULL;
			}
			return self::fetchOneRow('SELECT * FROM `'.DBPREFIX.$table.'` WHERE `'.Cmspp::$_G['autoSqlTables'][$table]['primary'].'` = "'.mysql_escape_string($key).'"');
		}
	};