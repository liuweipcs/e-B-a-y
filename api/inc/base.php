<?php
class base_model {
	/* 表名,不填则自动加载( */
	public $tablename = '';
	
	public $_last_query_count = -1;
	
	public $db = '';
	
	/* 构造函数  */
	function __construct(){
		//init
		$this->_init();
	}
	
	function getValue($key){
		return $this->$key;
	}
	
	function setValue($key,$value){
		return $this->$key = $value;
	}
	
	/* 初始化函数 子类需要初始化,可重写 */
	function _init(){       
		$this->db = $this->getDb();
	}
	
	
	/* 插入 */
	function insert($insertsqlarr, $returnid=0, $replace = false){
		$insertkeysql = $insertvaluesql = $comma = '';
		foreach ($insertsqlarr as $insert_key => $insert_value) {
			$insertkeysql .= $comma.'`'.$insert_key.'`';
			$insertvaluesql .= $comma.'\''.$insert_value.'\'';
			$comma = ', ';
		}
		$method = $replace?'REPLACE':'INSERT';        
		$query = $this->db->query($method.' INTO '.$this->tablename.' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')');
		if($returnid && !$replace) {
			return $this->db->insert_id();
		}else{
			return $query;			
		}
	}
	
	/* 更新 */
	function update($setsqlarr, $wheresqlarr) {
		if(is_array($setsqlarr)){
			$setsql = $comma = '';
			foreach ($setsqlarr as $set_key => $set_value) {//fix
				$setsql .= $comma.'`'.$set_key.'`'.'=\''.$set_value.'\'';
				$comma = ', ';
			}
		}else{
			$setsql = $setsqlarr;
		}
		
		$where = $this->_getWheresql($wheresqlarr);      
		return $this->db->query('UPDATE '.$this->tablename.' SET '.$setsql.' WHERE '.$where);
	}
	
	/* 删除 */
	function delete($wheresqlarr) {
		$where = $this->_getWheresql($wheresqlarr);
		return $this->db->query('DELETE FROM '.$this->tablename.' WHERE '.$where);
	}
	
	/* 获取一个字段(简单) */
	function getFieldBySimple($where='',$select='',$order=''){
		$result = $this->getRowBySimple($where,$select,$order);
		return $result[$select];
	}
	
	/* 获取一个字段 */
	function getField($params = array()){
		$result = $this->getRow($params);
		return $result[$params['select']];
	}
	
	/* 获取一条记录(简单) */
	function getRowBySimple($where='',$select='',$order=''){
		return $this->getRow(
			array(
	            'where'      => $where,
	            'order'      => $order,
	            'select'     => $select,
			)
		);
	}
	
	/* 获取一条记录 */
	function getRow($params = array()){
		$params = $this->_initFindParams($params);
		!$params['limit'] && $params['limit'] = '0,1';
		$result = $this->getCollection($params);
		return $result[0];
	}
	
	/* 简单查找 */
	function getCollectionBySimple($where='',$select='',$order='',$index_key=''){
		return $this->getCollection(
			array(
	            'where'      => $where,
	            'select'     => $select,
	            'order'      => $order,
	            'index_key'  => $index_key,
			)
		);
	}
	
	/* 查找 */
	function getCollection($params = array())
    {
        extract($this->_initFindParams($params));

        /* 字段(SELECT FROM) */
        $select = $this->_getSelectsql($select);

        /* 表名(包括join,和联合查询) */
        $tables = $this->_getTables($relation,$alias);

        /* 条件(WHERE) */
        $where = $this->_getWheresql($where);

        /* 分组(GROUP BY) */
        $group && $group = ' GROUP BY ' . $group;
        
        $having && $having = " HAVING " . $having;
        
        /* 排序(ORDER BY) */
        $order && $order = ' ORDER BY ' . $order;

        /* 分页(LIMIT) */
        $limit && $limit = ' LIMIT ' . $limit;

        /* 统计 */
        if($count){
        	if($group){
				$query = $this->db->query("SELECT count(*) FROM (SELECT count(*) as c FROM {$tables} WHERE {$where}{$group}{$having} ) as pk");
        	}else{
//        		$count_select = $group ? "count(distinct $alias.{$index_key})" : 'count(*)';
        		$query = $this->db->query("SELECT count(*) as c FROM {$tables} WHERE {$where}{$group}{$having}");
        	}
			$this->_last_query_count = $this->db->result($query,0);
		}
		
		/* 获取数据集 */
		$data = array();
		$sql = "SELECT {$select} FROM {$tables} WHERE {$where}{$group}{$having}{$order}{$limit}";
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_array($query)){
			if($index_key && isset($row[$index_key])){
				if($group_key || $index_key2){//按key分组
					if($index_key2){
						$data[$row[$index_key]][$row[$index_key2]] = $row;
					}else{
						$data[$row[$index_key]][] = $row;
					}
				}else{
					$data[$row[$index_key]] = $row;
				}
			}else{
				$data[] = $row;
			}
		}
		return $data;			
    }
    
    public function getTableCount($params = array())
    {
        extract($this->_initFindParams($params));

        /* 字段(SELECT FROM) */
        $select = $this->_getSelectsql($select);

        /* 表名(包括join,和联合查询) */
        $tables = $this->_getTables($relation,$alias);

        /* 条件(WHERE) */
        $where = $this->_getWheresql($where);

        /* 分组(GROUP BY) */
        $group && $group = ' GROUP BY ' . $group;
        
        $having && $having = " HAVING " . $having;
        
        /* 排序(ORDER BY) */
        $order && $order = ' ORDER BY ' . $order;

        /* 分页(LIMIT) */
        $limit && $limit = ' LIMIT ' . $limit;

        /* 统计 */     
        if($group){
            $query = $this->db->query("SELECT count(*) FROM (SELECT count(*) as c FROM {$tables} WHERE {$where}{$group}{$having} ) as pk");
        }else{
//        		$count_select = $group ? "count(distinct $alias.{$index_key})" : 'count(*)';
            $query = $this->db->query("SELECT count(*) as c FROM {$tables} WHERE {$where}{$group}{$having}");
        }
        
        return $this->db->result($query,0);		
    }
    
	function getCount(){
        return $this->_last_query_count;
    }
    
    function _getTables($relation,$alias){
    	$tables = $this->tablename;
    	
    	if($alias) {
    		$tables .= ' as '.$alias.' ';
    	}

    	if(empty($relation)) {
			
		} elseif(is_array($relation)) {
			foreach ($relation as $value) {
				$tables .= ' '.$value;
			}
		} else {
			$tables .= $relation;
		}
		return $tables;
    }
    
	function _getSelectsql($selectsqlarr){
		$selectsql = $comma = '';
		if(empty($selectsqlarr)) {
			$selectsql = '*';
		} elseif(is_array($selectsqlarr)) {
			foreach ($selectsqlarr as $key => $value) {//fix
				$selectsql .= $comma.'`'.$key.'`'.'=\''.$value.'\'';
				$comma = ', ';
			}
		} else {
			$selectsql = $selectsqlarr;
		}
		return $selectsql;
	}
	
	function _getWheresql($wheresqlarr){
		$where = $comma = '';
		if(empty($wheresqlarr)) {
			$where = '1';
		} elseif(is_array($wheresqlarr)) {
			foreach ($wheresqlarr as $key => $value) {
				$where .= $comma.'`'.$key.'`'.'=\''.$value.'\'';
				$comma = ' AND ';
			}
		} else {
			$where = $wheresqlarr;
		}
		return $where;
	}
	
	function _initFindParams($params){
        $arr = array(
        	'alias'      => '',
            'relation'   => '',
            'where'      => '',
            'order'      => '',
        	'group'      => '',
            'having'     => '',
            'select'     => '',
            'limit'      => '',
            'count'      => false,
            'index_key'  => '',
        	'index_key2'  => '',
        	'group_key'  => false,          
        );
        if (is_array($params))
        {
            return array_merge($arr, $params);
        }
        else
        {
            $arr['where'] = $params;
            return $arr;
        }
    }
    
	private function getDb(){       
		global $connection,$MYSQL_SERVER,$MYSQL_USER,$MYSQL_PASS,$MYSQL_DB,$MYSQL_CHARSET;
		static $db;      
		if(empty($db)){
			if($connection){               
				$db = getModel('mysql_db');
				$db->charset = $MYSQL_CHARSET;
				$db->link = $connection;
			}else{
				$db = getModel('mysql_db');
				$db->connect($MYSQL_SERVER,$MYSQL_USER,$MYSQL_PASS,$MYSQL_DB,$MYSQL_CHARSET,1);
				$connection = $db->link;
			}
		}
		return $db;
	}
	
	function getSqlError(){
		return $this->db->getSqlError();
	}
	
	function getTotalSql(){
		return $this->db->getTotalSql();
	}
	
	function getSql(){
		return $this->db->getSql();
	}
    
    public function startTrans() {
        return $this->db->startTrans();
    }
    
    public function commit() {
        return $this->db->commit();
    }
    
    public function rollback() {
        return $this->db->rollback();
    }
    
}


?>