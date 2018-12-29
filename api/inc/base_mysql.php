<?php
class base_mysql{
		var $querynum = 0 ; //当前页面进程查询数据库的次数
		var $dblink ;       //数据库连接资
		
		//链接数据
		function __construct($mysql,$dbcharset='utf8',$pconnect=1 , $halt=true)
		{
			$mysqldb = array(
				'driver'                => 'mysql',
				'host' 					=> '192.168.1.15',
				'port' 					=> '3306',
				'username'              => 'root',
				'password'              => '49BA59ABBE56E057',
				'charset'               => 'utf8',


			);
			$mysqldb_new = array(
				'driver'                => 'mysql',
				'host' 					=> '192.168.1.15',
				'port' 					=> '3306',
				'username'              => 'root',
				'password'              => '49BA59ABBE56E057',
				'charset'               => 'utf8',
			);
			
		 $func = empty($pconnect) ? 'mysql_connect' : 'mysql_pconnect';
		 
		 if($mysql){
		 	$mysqldb = $mysqldb_new;
		 }
		 $this->dblink = @$func($mysqldb['host'],$mysqldb['username'],$mysqldb['password']) ;

		 if ($halt && !$this->dblink)
		 {
			$this->halt("无法链接数据库！");
		 }
		 mysql_query("SET character_set_connection='".$dbcharset."',character_set_results='".$dbcharset."',character_set_client=binary",$this->dblink) ;
		 $dbname && @mysql_select_db($dbname,$this->dblink);
		}

		//选择数据
		function select_db($dbname)
		{
		 return mysql_select_db($dbname,$this->dblink);
		}

		//执行SQL查询
		function query($sql)
		{
		//echo $sql.'<br />';
		 $this->querynum++ ;
		 return mysql_query($sql,$this->dblink) ;
		}

		//返回最近一次与连接句柄关联的INSERT，UPDATE 或DELETE 查询所影响的记录行
		function affected_rows()
		{
		 return mysql_affected_rows($this->dblink) ;
		}

		//取得结果集中行的数目,只对select查询的结果集有效
		function num_rows($result)
		{
		 return mysql_num_rows($result) ;
		}

		//获得单格的查询结
		function result($result,$row=0)
		{
		 return mysql_result($result,$row) ;
		}

		//取得上一INSERT 操作产生ID,只对表有AUTO_INCREMENT ID的操作有
		function insert_id()
		{
		 return ($id = mysql_insert_id($this->dblink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
		}

		//从结果集提取当前行，以数字为key表示的关联数组形式返
		function fetch_row($result)
		{
		 return mysql_fetch_row($result) ;
		}


		//从结果集提取当前行，以字段名为key表示的关联数组形式返
		function fetch_assoc($result)
		{
		 return mysql_fetch_assoc($result);
		}

		//从结果集提取当前行，以字段名和数字为key表示的关联数组形式返
		function fetch_array($result)
		{
		 return mysql_fetch_array($result);
		}

		//关闭链接
		function close()
		{
		 return mysql_close($this->dblink) ;
		}

		 //输出简单的错误html提示信息并终止程
		function halt($msg)
		{
		 $message = "<html>\n<head>\n" ;
		 $message .= "<meta content='text/html;charset=gb2312'>\n" ;
		 $message .= "</head>\n" ;
		 $message .= "<body>\n" ;
		 $message .= "数据库出错：".htmlspecialchars($msg)."\n" ;
		 $message .= "</body>\n" ;
		 $message .= "</html>" ;

		 echo $message ;
		 exit ;
		}
	
}


?>
