<?php

class config
{
  /* 表名,不填则自动加 */
	public $tablename = '';
	
	public $_last_query_count = -1;
	
	public $db = '';
	var $UDB = 'ueb_system';
	var $UPRO = 'ueb_product';
	var $UPUR = 'ueb_purchase';
	var $UWAR = 'ueb_warehouse';
	var $ULOG = 'ueb_logistics';
	var $UORD = 'ueb_order';
	var $UWEB = 'ueb_website';
	function __construct($mysql){
		$this->db = new base_mysql($mysql);
	}
	function getValue($key){
		return $this->$key;
	}
	
	function setValue($key,$value){
		return $this->$key = $value;
	}
	
	/* 插入 */
	function insert($insertsqlarr,$tablename='', $returnid=0, $replace = false){
		$insertkeysql = $insertvaluesql = $comma = '';
		foreach ($insertsqlarr as $insert_key => $insert_value) {
			$insertkeysql .= $comma.'`'.$insert_key.'`';
			$insertvaluesql .= $comma.'\''.addslashes($insert_value).'\'';
			$comma = ', ';
		}
		$method = $replace?'REPLACE':'INSERT';
		//echo $method.' INTO '.$tablename.' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')<br/><br/>';exit;
		$query = $this->db->query($method.' INTO '.$tablename.' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')');
		if($returnid && !$replace) {
			return $this->db->insert_id();
		}else{
			return $query;			
		}
	}
	
	/* 更新 */
	function update($setsqlarr, $wheresqlarr,$tablename="") {
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
		return $this->db->query('UPDATE '.$tablename.' SET '.$setsql.' WHERE '.$where);
	}
	
	/* 删除 */
	function delete($wheresqlarr,$tablename='') {
		$where = $this->_getWheresql($wheresqlarr);
		return $this->db->query('DELETE FROM '.$tablename.' WHERE '.$where);
	}
	
	/* 获取一个字简 */
	function getFieldBySimple($where='',$select='',$order='',$tablename=''){
		$result = $this->getRowBySimple($where,$select,$order,$tablename);
		return $result[$select];
	}
	
	/* 获取一个字�?*/
	function getField($params = array(),$tablename=''){
		$result = $this->getRow($params,$tablename);
		return $result[$params['select']];
	}
	
	/* 获取一条记�?简�? */
	function getRowBySimple($where='',$select='',$order='',$tablename=''){
		return $this->getRow(array(
	            'where'      => $where,
	            'order'      => $order,
	            'select'     => $select,
			),$tablename
		);
	}
	
	/* 获取一条记�?*/
	function getRow($params = array(),$tablename=''){
		$params = $this->_initFindParams($params);
		!$params['limit'] && $params['limit'] = '0,1';
		$result = $this->getCollection($params,$tablename);
		return $result[0];
	}
	
	/* 简单查*/
	public function getCollectionBySimple($where='',$select='',$order='',$limit='',$tablename='',$group=''){
		return $this->getCollection(array(
	            'where'      => $where,
	            'select'     => $select,
	            'order'      => $order,
	            'limit'  => $limit,
	            'group'  => $group,
			),$tablename
		);
	}
	
	/* 查找 */
	function getCollection($params = array(),$tablename='')
    {
        extract($this->_initFindParams($params));

        /* 字段(SELECT FROM) */
        $select = $this->_getSelectsql($select);

        /* 表名(包括join,和联合查 */
        $tables = $this->_getTables($relation,$alias,$tablename);

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
		
		/* 获取数据�?*/
		$data = array();
		$sql = "SELECT {$select} FROM {$tables} WHERE {$where}{$group}{$having}{$order}{$limit}";

 		//echo $sql.'<br/><br/>';
		
		
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

        /* 表名(包括join,和联合查�? */
        $tables = $this->_getTables($relation,$alias,$tablename);

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
    
    function _getTables($relation,$alias,$tablename=''){
    	$tables = $tablename;
    	
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
//include_once("inc/base_mysql.php");
?>