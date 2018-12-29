<?php
class mysql_db_model {
	public $querynum = 0;
	public $link;
	public $charset;
	public $lastsql = '';
	public $totalsql = '';
    
    public $transTimes = 0;
	
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $charset = '', $pconnect = 0, $halt = TRUE) {
		$this->charset = $charset;
		
		if($pconnect) {
			if(!$this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link = @mysql_connect($dbhost, $dbuser, $dbpw, 1)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		}

		if($this->version() > '4.1') {
			if($this->charset) {
				@mysql_query("SET character_set_connection=$this->charset, character_set_results=$this->charset, character_set_client=binary", $this->link);
			}
			if($this->version() > '5.0.1') {
				@mysql_query("SET sql_mode=''", $this->link);
			}
		}
		if($dbname) {
			@mysql_select_db($dbname, $this->link);
		}
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function query($sql, $type = '') {
		$this->lastsql = $sql;
		$logsql = strpos(strtolower(trim($sql)),'select')!==0;
        mysql_query("SET NAMES 'UTF8'"); 
        mysql_query("SET CHARACTER SET UTF8"); 
		if(!$query = mysql_query($sql, $this->link)) {
			$logsql && $this->totalsql .= $this->getSqlError();
//			$this->halt();
			/*
			$savepath = '../jxc/';//保存路径
			$filepath = $savepath.'sql_error.txt';//文件路径
			
			$writeStr = str_replace('<br>',"\n",$this->getSqlError());
			$fp = @fopen($filepath,"a+");
			fwrite($fp,date('Y-m-d H:i:s').$writeStr."\n\n");
			fclose($fp);
			*/
		}else{
//			$logsql && $this->totalsql .= "<li>$sql</li>";
		}
//		$logsql && getModel('sys_log')->addLog("<li>$sql</li>");
		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		return mysql_get_server_info($this->link);
	}

	function close() {
		return mysql_close($this->link);
	}

	function halt() {
		echo $this->getSqlError();
		exit(); //delete 正式上线时应注释掉.
	}
	
	function getSqlError() {
		$dberror = $this->error();
		$dberrno = $this->errno();
		return "<div style=\"font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">
				<b>MySQL Error</b><br>
				<b>SQL</b>: ".$this->lastsql."<br>
				<b>Error</b>: $dberror<br>
				<b>Errno.</b>: $dberrno<br>
				</div>";
	}
	
	function getTotalSql(){
		return $this->totalsql;
	}
	
	public function getSql(){
		return $this->lastsql;
	}
    
    public function startTrans() {      
        if ( !$this->link ) return false;     
        if ($this->transTimes == 0) {
            mysql_query('START TRANSACTION', $this->link);
        }
        $this->transTimes++;
        return ;
    }
    
     public function commit() {
        if ($this->transTimes > 0) {
            $result = mysql_query('COMMIT', $this->link);
            mysql_query("END", $this->link);
            $this->transTimes = 0;
            if(!$result){
               throw new Exception($this->error());             
            }
        }
        
        return true;
    }
    
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = mysql_query('ROLLBACK', $this->link);
            mysql_query("END", $this->link);
            $this->transTimes = 0;
            if(!$result){
                throw new Exception($this->error());              
            }
        }
        return true;
    }
    
    
}

?>