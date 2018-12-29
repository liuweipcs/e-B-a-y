<?php

/**
 * @package Ueb.models
 * 
 * @author Bob <Foxzeng>
 */
class UebModel extends CActiveRecord {
    
    public $criteria = null;
    
    public $pages = null;
    
    /**
     * operation log message
     * 
     * @var string 
     */
    public static $logMsg = array();
    
    /**
     * before save info
     * 
     * @var array
     */
    public $beforeSaveInfo = null;
    
    /**
     * db key config
     * 
     * @var array 
     */
    public static $dbKeyConfig = array();
    
    /**
     *  is need log flag
     * 
     * @var boolean 
     */
    public $isNeedLog = 1;
    
    public  $modify_time=null;
    public  $modify_user_id=0;
    
    /**
     * filter fields when save the log
     */
    public $filterFields = array();
    
    public $list = array();
    
//     public $authCriteria = array();
    
    public $oldErpDB = '';

    protected $_observers = [];         //观察者对象数组
    
    public $oldAttributes = null;
    
    /**
     * extends parent class construct
     * 
     * @param type $scenario
     */
    public function __construct($scenario = 'insert') {
        parent::__construct($scenario);
    }   
    
    /**
     * model factory 
     * @return CActiveRecord the static model class
     */
    public static function model($className = __CLASS__) {
        $className = ucfirst($className);
        return parent::model($className);
    }
    
    /**
     * Returns the database connection used by active record.
     * By default, the "db" application component is used as the database connection.
     * You may override this method if you want to use a different database connection.
     * @return CDbConnection the database connection used by active record.
     */
    public function getDbConnection() {
        $dbKey = $this->getDbKey();
        if (!empty(self::$dbKeyConfig[$dbKey])) {
            return self::$dbKeyConfig[$dbKey];
        } else {
            self::$dbKeyConfig[$dbKey] = Yii::app()->getComponent($dbKey);
            if (self::$dbKeyConfig[$dbKey] instanceof CDbConnection)
                return self::$dbKeyConfig[$dbKey];
            else
                throw new CDbException(Yii::t('yii', 'Active Record requires a "db" CDbConnection application component.'));
        }
    }
    
    /**
     * @desc 加载页面所需脚本
     */
    public static function registerCoreScripts(){
    	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    	$cs = Yii::app()->clientScript;
    	$baseUrlLocal = Yii::app()->baseUrl;
    	$baseUrl = 'http://www.downorder.com';

    	$cs->registerCssFile($baseUrl.'/themes/default/style.css', 'screen');
    	$cs->registerCssFile($baseUrl.'/themes/css/core.css', 'screen');
    	$cs->registerCssFile($baseUrl.'/themes/css/mouseright.css', 'screen');
    	$cs->registerCssFile($baseUrl.'/themes/css/print.css', 'print');
    	$cs->registerCssFile($baseUrl.'/themes/css/pageform.css', 	'screen');
    	$cs->registerCssFile($baseUrl.'/js/kindeditor-4.1.7/themes/default/default.css');
    	
    	$cs->registerCssFile($baseUrl.'/js/colorbox/colorbox.css');
    	$cs->registerCssFile($baseUrl.'/js/pikachoose/base.css');
    	$cs->registerCssFile($baseUrl.'/js/pikachoose/left-without.css');
    	
    	$cs->registerScriptFile($baseUrl.'/js/core/jquery-1.7.2.min.js', CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl.'/js/core/jquery.cookie.js', CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl.'/js/core/jquery.bgiframe.js',CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl.'/js/core/jquery.validate.js',CClientScript::POS_HEAD);
    	
    	$cs->registerScriptFile($baseUrl . "/js/colorbox/jquery.colorbox.js", CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl . "/js/pikachoose/jquery.jcarousel.min.js", CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl . "/js/pikachoose/jquery.pikachoose.min.js", CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl . "/js/pikachoose/jquery.pikachoose.min.js", CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl . '/js/core/jquery.yiilistview.js',CClientScript::POS_HEAD);
    	

    	$cs->registerScriptFile($baseUrl.'/js/core/dwz.min.js', CClientScript::POS_HEAD);
    	
    	$lang = !empty(Yii::app()->language) ? Yii::app()->language : 'en';
    	$cs->registerScriptFile($baseUrl.'/js/lang/ueb.regional.'.$lang.'.js',CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl.'/js/custom/ueb.system.js', CClientScript::POS_HEAD);
    	$cs->registerScriptFile($baseUrl.'/js/custom/ueb.common.js', CClientScript::POS_HEAD);
    	
    	$cs->registerCssFile($baseUrl.'/css/chosen.css', 'screen');
    	$cs->registerScriptFile($baseUrl.'/js/custom/chosen.jquery.js');
    	$cs->registerScriptFile($baseUrlLocal.'/js/custom/ueb.preload.js', CClientScript::POS_HEAD);
    	$cs->registerCssFile($baseUrlLocal.'/css/addition.css', 'screen');
    }

    /**
     * init criteria
     * see {@link CDbCriteria }
     */
    protected function _initCriteria() {
        $this->criteria = new CDbCriteria();
        $orderField = isset($_REQUEST['orderField']) ?  $_REQUEST['orderField'] : '';        
        $orderDirection = isset($_REQUEST['orderDirection']) ? $_REQUEST['orderDirection'] : 'desc';       
        if (! empty($orderField)) {           
            $this->criteria->order = $orderField .' '.$orderDirection;
        }        
    }
    
    /**
     * init Pagination
     * see {@link CPagination}
     * @param CDbCriteria $criteria
     * 
     */
    protected function _initPagination(CDbCriteria $criteria) {     
        $count = $this->count($criteria);      
        $this->pages = new CPagination($count);
        $pageSize = isset($_REQUEST['numPerPage']) ? $_REQUEST['numPerPage'] : Yii::app()->params['per_page_num'];       
        $currentPage = isset($_POST['pageNum'])? $_POST['pageNum']-1 : 0 ;        
        $this->pages->setPageSize($pageSize);
        $this->pages->setCurrentPage($currentPage);
        $this->pages->applyLimit($criteria); 
    }
    
    /**
     * search info
     * 
     * @param type $model
     * @param type $sort
     * @return \CActiveDataProvider
     */
    public function search($model, $sort = array(), $with = array(),$CDbCriteria = null) {
        if ( empty($model) ) {
            $model = get_class($this);          
        }
        $curUrls = Yii::app()->request->getUrl();
        $arr = explode('?_',$curUrls);
        $curUrl = $arr[0];
        $userId = Yii::app()->user->id;
        /**
        if ( Yii::app()->request->getIsPostRequest() ) {
        	$clearRequest = array();//$this->clearFilterOptionsToSaveSearch();//过滤某些查询条件方便保存,add by ethan 2014.7.18
//             self::model('searchCondition')->dataSave($model,$clearRequest);//改成url+user_id存取
            self::model('searchCondition')->dataSave($curUrl,$model,$clearRequest);
        } else {
            //$searchCondition = self::model('searchCondition')->getSearchConditionByModelName($model);//改成url+user_id获取
            $searchCondition = self::model('searchCondition')->getSearchConditionByModelName($curUrl,$model);
            
            $_REQUEST = !empty($searchCondition) ? array_merge($searchCondition, $_REQUEST) : $_REQUEST;
        }
        */
        /***/
        if($CDbCriteria ==null){
        	$criteria = new CDbCriteria;
        }else{
        	$criteria = $CDbCriteria;
        } 
        
        $filterCondition = '';

        if( $checkAuth = Yii::app()->session->get('specialAuth') ){
        	$columnArr = array();
        	foreach($this->filterOptions() as $item){
        		$columnArr[$item['name']] = $item;
        	}
        	foreach($checkAuth as $column=>$item){
        		if($item){
	        		if( isset($columnArr[$column]) ){
		        		$filterCondition .= ' '.(isset($columnArr[$column]['alias']) ? $columnArr[$column]['alias'].'.' : '').$column.' IN ("'.implode('","', $item).'")';
	        		}
        		}
        	}
        	
        	if( $criteria->condition && $filterCondition ){
        		$criteria->condition .= ' AND ';
        	}
        	$criteria->condition .= $filterCondition;
        }
        
        $desc = '';
        $defaultOrder = isset($sort->attributes['defaultOrder']) ? $sort->attributes['defaultOrder'] : '';
        $defaultDirection = isset($sort->attributes['defaultDirection']) ? $sort->attributes['defaultDirection'] : 'desc';
        if (! isset($_REQUEST['orderField']) || empty($_REQUEST['orderField'])) {
            $_REQUEST['orderField'] = $defaultOrder;
            $desc = $defaultOrder.' '.$defaultDirection;
        }else{
        	$orderField = $_REQUEST['orderField'];
	        if (! isset($_REQUEST['orderDirection']) || empty($_REQUEST['orderDirection']) ) {
	            $_REQUEST['orderDirection'] = $defaultDirection;
	        }
	        $orderDirection =  $_REQUEST['orderDirection'];
	        if($orderField === $defaultOrder) {
	        	$desc = $orderField .' '.$orderDirection;
	        }else{
	        	$desc = $orderField .' '.$orderDirection.','.$defaultOrder.' '.$defaultDirection;
	        }
        }
        if (! empty($desc)) {
            $criteria->order = $desc;
        }
        if (! empty($with) ) {
            $criteria->with = $with;
        }
        
        $pageSize = isset($_REQUEST['numPerPage']) ? $_REQUEST['numPerPage'] : Yii::app()->params['per_page_num'];
        $this->addConditions($criteria);
        //根据条件取所有符合条件的数据，不取分页条件，适合统计类。两种方法，
        /**1,把条件都缓存下来，优点:少内在，缺点:不知道有多少条件
       
        Yii::app()->session->add(get_class($this).'_params', $criteria->params);
        Yii::app()->session->add(get_class($this).'_having', $criteria->having);
        **/
        //2,把整个条件全缓存下来，可以根据需求取所有或里面的某一项(可打印看有什么项)
        Yii::app()->session->add(get_class($this).'_criteria', $criteria);
        Yii::app()->session->add(get_class($this).'_condition', $criteria->condition);
        Yii::app()->session->add(get_class($this).'_order', $criteria->order);
        Yii::app()->session->add(get_class($this).'_numPerPage', $pageSize);
        
		//echo $criteria.'dd'; exit;
		$obj =  new CActiveDataProvider($model, array(  
            'criteria' => $criteria,
            'sort' => $sort,
            'pagination' => array(
                'pageSize'      => $pageSize, 
                'currentPage'   => isset($_POST['pageNum'])? $_POST['pageNum']-1 : 0,
            ),
        ));  
		//var_dump($criteria);exit;
        return $obj;
    }
    

    
    /**
     * check login user access resources 
     * 
     * @param type $resourceId
     * @return boolean
     */
    public static function checkAccess($resourceId) {
        $auth = Yii::app()->authManager;
        if ( $auth->checkAccess($resourceId, Yii::app()->user->id) || User::isAdmin()) {
            return true;
        }        
        return false;
    }
    
    /**
     * get select data pairs
     * 
     * @param type $tables
     * @param type $fields
     * @param type $conditions
     * @param type $params
     * @return type
     */
    public function queryPairs($columns, $conditions = array(), $params=array()) {
        if (! is_array($columns)) {
            $columns=preg_split('/\s*,\s*/',trim($columns),-1,PREG_SPLIT_NO_EMPTY);
        }
        
        $moreColumns = false;
        $dbkey = $this->getDbKey();
        $selectObj = Yii::app()->$dbkey->createCommand() 
			->select($columns)
			->from($this->tableName());	
        if (! empty($conditions) )  {
            $selectObj->where($conditions, $params);
        }
        $list = $selectObj->queryAll();  
        if(empty($list)) return $list;
        return count($columns)>2?array_column($list, null,$columns[0]):array_column($list, $columns[1],$columns[0]);
        $data = array();
        if ( count($columns) > 2 ) $moreColumns = true;
        
        foreach ($list as $val) {  
           
            if ( $moreColumns) {
                $data[$val[$columns[0]]] = $val;
            } else {                            
                $data[$val[$columns[0]]] = $val[$columns[1]];                
            }           
        }
        return $data;
    }
    /**
     * query all data by the field name
     * @param array $conditions
     * @param array $params
     * @param string $columns
     * @param number $index:is the first table
     * @param array $order:order by field
     * @return array data
     */
    public function queryByField($conditions = array(), $params=array(), $columns = "*", $order = array()) {
    
    	if ( is_array($columns) ) {
    		$columns = implode(",", $columns);
    	}
    
    	$dbkey = $this->getDbKey();
    	$selectObj = Yii::app()->$dbkey->createCommand()
    	->select($columns)
    	->from($this->tableName());
    	
    	if (! empty($conditions) )  {
    		$selectObj->where($conditions, $params);
    	}
    
    	if (! empty($order) )  {
    		$selectObj->order($order);
    	}

    	if ( strpos($columns, ",") === false &&
    	strpos($columns, "*") === false ) {
    		$result = $selectObj->queryColumn();
    	} else {
    		$result = $selectObj->queryAll();
    	}
    	return $result;
    }
    
	/**
	 * query all data by the field name
	 * @param array $conditions
	 * @param array $params
	 * @param string $columns
	 * @param array $joinTable :join table
	 * @param boolean $isGetTotal
	 * @param array $order:order by field
	 * @return array data
	 */
    public function getDataByJoin($conditions = array(), $params=array(), $columns = "*", $joinTable = array() ,$isGetTotal=false,$orderBy = array()) {
    	if ( is_array($columns) ) {
            $columns = implode(",", $columns);
        }
        $columns = rtrim($columns,',');

        $excelSchemeDataObj = ExcelSchemeData::getInstance();
        $sortTableNames = $excelSchemeDataObj->getSortTableNames();
        $dbkey = $this->getDbKey();
        $env = new Env();
        $dbName = $env->getDbNameByDbKey($dbkey);

        $selectObj = Yii::app()->$dbkey->createCommand()
			->select($columns)
			->from($dbName.'.'.$this->tableName());
        
        if (!empty($joinTable)) {
	        foreach ($joinTable as $key=>$joinMap){
	        	foreach ($joinMap as $table=>$map){
	        		$selectObj->leftjoin($table,$map);
	        	}
	        }
        }
        
        $conditionCount = $boxCount = 0;
        foreach ($sortTableNames as $tableName) {
        	foreach ($_POST['is_condition'][$tableName] as $val) {
        		if ($val != '') {
        			$conditionCount++;
        		}
        	}
        	foreach ($_POST['is_checkbox'][$tableName] as $val) {
        		if ($val != '') {
        			$boxCount++;
        		}
        	}
        }
        
        if ( empty($conditions)  || $boxCount > 0 || $conditionCount>0 ) {//使用了搜索则初始条件失效
        	$conditions = ' 1 ';
        }
        $groupBy = $having = $orderBy = '';
        
//         foreach($sortTableNames as $tableName){
//         	if($_POST['is_checkbox']){
// 	        	if ($_POST['is_checkbox'][$tableName]) {
// 	        		$conditions .= ' and ( ';
// 	        		foreach ($_POST['is_checkbox'][$tableName] as $key=>$val){
// 	        			if (is_array($val)){
// 	        				foreach ($val as $k=>$v){
// 	        					$conditions .= " $tableName.$key = '".$v."' or";
// 	        				}
// 	        			}
// 	        		}
// 	        		$conditions = rtrim($conditions,'or');
// 	        		$conditions .= ' ) ';
// 	        	}
//         	}
//         }
        
        foreach($sortTableNames as $tableName){
        	
        	if($_POST['is_checkbox']){
        		if ($_POST['is_checkbox'][$tableName]) {
        			$conditions .= ' AND ( ';
        			foreach ($_POST['is_checkbox'][$tableName] as $key=>$val){
        				if (is_array($val)){
        					foreach ($val as $k=>$v){
        						$conditions .= " $tableName.$key = '".$v."' OR";
        					}
        				}
        			}
        			$conditions = rtrim($conditions,'OR');
        			$conditions .= ' ) ';
        		}
        	}
        	
        	if ($_POST['is_condition'][$tableName]) {
        		foreach ($_POST['is_condition'][$tableName] as $key=>$val){
        			if (is_array($val)){
        				if(!empty($val[0])){
        					if (isset($val[2]))
        						$conditions .= " AND $tableName.$key >= '".$val[0]."'";
        					else 
        						$conditions .= " AND $tableName.$key >= $val[0]";
        				}
        				if(!empty($val[1])){
        					if (isset($val[2]))
        						$conditions .= " AND $tableName.$key <= '".$val[1]."'";
        					else 
        						$conditions .= " AND $tableName.$key <= $val[1]";
        				}
        			}else{
        				if ($val != ''){
	        				//$params[$key] = $val;
	        				$conditions .= " AND $tableName.$key = '".$val."'";
        				}
        			}
        		}
        	}

        	if ($_POST['is_group'][$tableName]) {
        		foreach ($_POST['is_group'][$tableName] as $key=>$val){
        			if(!is_array($val)){
        				$groupBy .= "$tableName.$val,";
        			}else{
        				foreach($val as $timeType=>$timeTypeValue){
        					if (!isset($val[0])) {
        						continue;
        					}
        					if ($timeType ==='day_type'){
        						$format = MHelper::getDateFormat($timeTypeValue);
        						$groupBy .= "DATE_FORMAT($tableName.$key,'".$format."'),";
        						$orderBy .= "DATE_FORMAT($tableName.$key,'".$format."'),";
        					}
        				}
        			}
        		}
        	}
        	
        }
        //var_dump($conditions);

        if ($_POST['is_having']) {
        	foreach ($_POST['is_having'] as $table_name=>$column_name){
        		foreach ($column_name as $key=>$val){
        			if ( !empty($val['name_key']) && !empty($val['symbol']) && !empty($val['symbol_value']) ){
        				if (empty($having)) {
        					$having .= $val['name_key'].$val['symbol'].$val['symbol_value'];
        				}else{
        					$having .= ' and '.$val['name_key'].$val['symbol'].$val['symbol_value'];
        				}
        			}
        		}
        	}
        }
        
        //获取搜索条件
        $selectObj->where($conditions, $params);
        $groupBy = rtrim($groupBy,',');
        $groupBy = trim($groupBy);

        if (!empty($groupBy)) {
        	$selectObj->group($groupBy);
        	$having = rtrim($having,',');
        	$having = trim($having);
        	if (!empty($having)) {
	        	$selectObj->having($having);
        	}
        }
        $orderBy = rtrim($orderBy,',');
        $orderBy = trim($orderBy);
        if (! empty($orderBy) )  {
        	$selectObj->order($orderBy);
        }
        
        if(!$isGetTotal){
	        $currentPage = $_POST['pageNum'] ? $_POST['pageNum']-1 :0;
	        $numPerPage = $_POST['numPerPage'] ? $_POST['numPerPage'] : 100;//report 100 rows
	        $selectObj->limit($numPerPage,$currentPage*$numPerPage);
        }else {
        	$selectObj->limit(10000);//test
        }
		//echo $selectObj->text;

        if ( strpos($columns, ",") === false && 
                    strpos($columns, "*") === false ) {
            $result = $selectObj->queryColumn();
        } else {
            $result = $selectObj->queryAll();
        }
        //return !$isGetTotal ? $result : count($result);
        return $result;
    }
    
    /**
     * query row by the field name
     * 
     * @param string $conditions
     * @param array $params
     * @param mixed $columns
     * @return null | array 
     */
    public function queryRowByField($conditions = array(), $params=array(), $columns = "*") {
        if ( is_array($columns) ) {
            $columns = implode(",", $columns);
        }
        
        $dbkey = $this->getDbKey();
        $selectObj = Yii::app()->$dbkey->createCommand() 
			->select($columns)
			->from($this->tableName());	
        if (! empty($conditions) )  {
            $selectObj->where($conditions, $params);
        }
        
        $result = $selectObj->queryRow();
        if ( empty($result) ) return null;
        
        if ( strpos($columns, ",") === false && 
                    strpos($columns, "*") === false ) {
            $result = $result[$columns];     
        } 
        
        return $result;        
    }


    /**
     * get fiter label
     * 
     * @param CActiveRecord $model
     * @return string $filter
     */
    public function filter() {  
        $filterOptions = $this->filterOptions();
        $attributeLabels = $this->attributeLabels();
        $filter = '';
        $on = $value = Yii::app()->request->getParam('on');
        foreach ( $filterOptions as $filterOption ) {
            if ( $filterOption['type'] == 'param') {
                continue;
            }
            
            if ( $on && isset($filterOption['on']) && 
                    !in_array($on, $filterOption['on'])) {
                continue;
            }
            
            if ( empty($filterOption['name']) || empty($filterOption['type'])) {
                continue;
            } else {
                $name = $filterOption['name'];
            }
            
            if (! isset($filterOption['label']) ) {
                $filterOption['label'] = $attributeLabels[$name];
            }
            $value = isset($filterOption['value']) ? $filterOption['value'] : '';
            //$value = isset($_REQUEST[$name]) && !empty($_REQUEST[$name]) ? $_REQUEST[$name] : $value;
            $value = isset($_REQUEST[$name]) && trim($_REQUEST[$name]) !== '' ? $_REQUEST[$name] : $value;
            
            if (! isset($filterOption['htmlOptions']) ) {
                $filterOption['htmlOptions'] = array();
            }
            
            if( $checkAuth = Yii::app()->session->get('specialAuth') ){//筛除没有权限的select选项
            	if( isset($filterOption['data']) && isset($checkAuth[$name]) ){
            		$notAllowArr = array_diff(array_keys($filterOption['data']), $checkAuth[$name]);
            		foreach($filterOption['data'] as $key=>$item){
            			if( in_array($key,$notAllowArr) ){
            				unset($filterOption['data'][$key]);
            			}
            		}
            	}
            }

            switch ($filterOption['type']) {
                case 'text':
                    if ( isset($filterOption['search']) && 
                            strtoupper($filterOption['search']) == 'RANGE' ) {
                        $filter .= '<div class="left h25 ml10" style="">';
                        $filter .=  $filterOption['label'] . ':'; 
                        $filter .=  CHtml::textField($name.'[0]', isset($value[0]) ? $value[0] : '', $filterOption['htmlOptions']);
                        $filter .=  ' - '.CHtml::textField($name.'[1]', isset($value[1]) ? $value[1] : '', $filterOption['htmlOptions']);
                        $filter .= '</div>';
                    } elseif ( strtoupper($filterOption['search']) == 'BETWEEN_HAVING' ) {
                    	$filter .= '<div class="left h25 ml10" style="">';
                    	$filter .=  $filterOption['label'] . ':';
                    	$filter .=  CHtml::textField($name.'[0]', isset($value[0]) ? $value[0] : '', $filterOption['htmlOptions']);
                    	$filter .=  ' - '.CHtml::textField($name.'[1]', isset($value[1]) ? $value[1] : '', $filterOption['htmlOptions']);
                    	$filter .= '</div>';
                    } else {                      
                        $filter .=  '<div class="left h25 ml10" style="">'.$filterOption['label'] . ':'; 
                        $filter .=  CHtml::textField($name, $value, $filterOption['htmlOptions']);    
                        $filter .= '</div>';
                        if ( isset($filterOption['lookup']) ) {                          
                        	$filter .=  '<div class="left h25 ml10" >'.Html::lookup($filterOption['lookup']).'</div>';
                        }                        
                    }                   
                    break;

                case 'textarea':
                    if ( isset($filterOption['search']) &&
                        strtoupper($filterOption['search']) == 'RANGE' ) {
                        $filter .= '<div class="left ml10" style="">';
                        $filter .=  $filterOption['label'] . ':';
                        $filter .=  CHtml::textArea($name.'[0]', isset($value[0]) ? $value[0] : '', $filterOption['htmlOptions']);
                        $filter .=  ' - '.CHtml::textArea($name.'[1]', isset($value[1]) ? $value[1] : '', $filterOption['htmlOptions']);
                        $filter .= '</div>';
                    } elseif ( strtoupper($filterOption['search']) == 'BETWEEN_HAVING' ) {
                        $filter .= '<div class="left ml10" style="">';
                        $filter .=  $filterOption['label'] . ':';
                        $filter .=  CHtml::textArea($name.'[0]', isset($value[0]) ? $value[0] : '', $filterOption['htmlOptions']);
                        $filter .=  ' - '.CHtml::textArea($name.'[1]', isset($value[1]) ? $value[1] : '', $filterOption['htmlOptions']);
                        $filter .= '</div>';
                    } else {
                        $filter .=  '<div class="left ml10" style="">'.$filterOption['label'] . ':';
                        $filter .=  CHtml::textArea($name, $value, $filterOption['htmlOptions']);
                        $filter .= '</div>';
                        if ( isset($filterOption['lookup']) ) {
                            $filter .=  '<div class="left ml10" >'.Html::lookup($filterOption['lookup']).'</div>';
                        }
                    }
                    break;

//                case 'textarea':
//					$filter .= '<div class="left ml10" style="">';
//                    	$filter .=  $filterOption['label'] . ':';
//                    	$filter .=  CHtml::textArea($name.'[0]', isset($value[0]) ? $value[0] : '', $filterOption['htmlOptions']);
//                    	$filter .= '</div>';
//                    break;

                case 'dropDownList':
                	$param = array( 'empty' => Yii::t('system', 'All'));
                	if(isset($filterOption['htmlOptions'])){
                		$param=array_merge($param,$filterOption['htmlOptions']);
                	}
                    $filter .= '<div class="left h25 ml10" style="">';
                    $filter .=  $filterOption['label'] . ':';
                    $filter .=  CHtml::dropDownList($name, $value, $filterOption['data'], $param);
                    $filter .= '</div>';
                    break;   
				case 'radioButtonList':
                	$param = array( 'empty' => Yii::t('system', 'All'));
                	if(isset($filterOption['htmlOptions'])){
                		$param=array_merge($param,$filterOption['htmlOptions']);
                	}
                    $filter .= '<div class="left h25 ml10" style="display:none;">';
					$filter .=  CHtml::radioButtonListD($name, $value, $filterOption['data']);
					$filter .= "</div></div>";
                    break;
                case 'checkBoxList':
                    $hide = '';                                            
                    if ( $filterOption['hide'] ) {
                        $hide = 'hide';
                    }
		            if ( isset($filterOption['clear']) ) {
                        $filter .='<div class="clear '. $hide .' "></div>';
                    }
                    $filter .= '<div class="left h25 ml10 '. $hide .' filterToggle" >';
                    $filter .=  $filterOption['label'] . '：'; 
                    $select = Yii::app()->request->getParam($name, array());                   
                    $filter .= CHtml::checkBoxList($name.'[]', $select, $filterOption['data'], $filterOption['htmlOptions']);
                    $filter .= '</div>';
                    break;
                case 'hidden':
                    $filter .=  CHtml::hiddenField($name, $value);
                    break;
                case 'listButton':    
                    if ( isset($filterOption['clear']) ) {
                        $filter .='<div class="clear hide"></div>';
                    }
                    $filter .= Html::listButton($filterOption['label'], $name, $value, $filterOption['data'], $filterOption['htmlOptions']);
                    break;  
                case 'multiSelect':
					if( !isset($filterOption['dialog']) ){
						break;
					}
					$filter .= '<div class="left h25 ml10" style="">';
					$filter .=  $filterOption['label'] . ':';
					$filter .= CHtml::dropDownList('', '', array(''=>'All','-1'=>Yii::t('system','Please Select')), array(
							'onChange' => 'if($(this).val()!=""){$(this).next().click()}else{$("#'.$name.'").val("");$(this).parent().find("span").remove()}',
					));
// 					$filter .= CHtml::button(Yii::t('system','Please Select'),array(
// 							'onClick' => '$(this).next().click()',
// 					));
					$filter .= CHtml::link('',$filterOption['dialog'].'/target/dialog/callback/'.get_class($this).'_'.$name,array_merge(
							array('style'=>'display:none;','target'=>'dialog','rel'=>'product-grid','mask'=>'1'),
							$filterOption['htmlOptions']
					));
							
					$filter .=  CHtml::hiddenField($name, $value,array('id'=>get_class($this).'_'.$name));
					$filter .= '</div>';
					break;
            }                       
        }
        return $filter;
    }
    /**
     * 去除保存搜索条件里某些不需要保存的字段[type=hidden]
     * 函数原因：当选择搜索按纽时，会把所有的查询条件全部保存到数据表里方便下次直接调用，
     * 			但有些条件字段是我们不想保存的如隐藏域字段type=hidden等，故设置此函数过滤掉这些字段
     * @return multitype:unknown
     */
    public function clearFilterOptionsToSaveSearch(){
    	$clearQuery = array();
    	$filterOptions = $this->filterOptions();
    	if ($filterOptions) {
    		foreach ($filterOptions as $key=>$filterOption){
    			
    			if ( empty($filterOption['name']) ) {
    				continue;
    			}
    			if ( isset($filterOption['value']) && empty($filterOption['value']) ) {
    				continue;
    			}
    			if ($filterOption['type'] == 'hidden' ) {
    				$clearQuery[$filterOption['name']] = $_REQUEST[$filterOption['name']];
    				//unset($_REQUEST[$filterOption['name']]);
    				continue;
    			}
//     			if (!empty($filterOption['rel']) && $_REQUEST[$filterOption['name']]!='') {
//     				$clearQuery[$filterOption['name']] = $filterOption['value'] ? $filterOption['value'] : $_REQUEST[$filterOption['name']];
//     				continue;
//     			}
    		}
    	}
    	return $clearQuery;
    }
    
    /**
     * add search conditions
     * 
     * @param type $criteria
     */
    public function addConditions(&$criteria) {
        $filterOptions = $this->filterOptions();
        
        foreach ( $filterOptions as $filterOption ) {
            if (! empty($filterOption['rel']) ) {
                continue;
            }
            
            if ( empty($filterOption['name']) ) {
                continue;
            } else {
                $name = $filterOption['name'];
            }
            
        	$checkAuth = Yii::app()->session->get('specialAuth');
            if( $checkAuth = Yii::app()->session->get('specialAuth') ){//筛除没有权限的select选项
            	if( isset($filterOption['data']) && isset($checkAuth[$name]) ){
            		$notAllowArr = array_diff(array_keys($filterOption['data']), $checkAuth[$name]);
            		foreach($filterOption['data'] as $key=>$item){
            			if( in_array($key,$notAllowArr) ){
            				unset($filterOption['data'][$key]);
            			}
            		}
            	}
            }
            // add by Tom 2014-02-21 对字段增加表别名设置 
            if(isset($filterOption['alias'])&&$filterOption['alias']){
            	$table = $filterOption['alias'].'.';
            }else{
            	$table = '';
            }
            
            $value = isset($filterOption['value']) ? $filterOption['value'] : '';
            //$value = isset($_REQUEST[$name]) && !empty($_REQUEST[$name]) ? $_REQUEST[$name] : $value;
            $value = isset($_REQUEST[$name]) && trim($_REQUEST[$name]) !== '' ? $_REQUEST[$name] : $value;
            if ( isset($_REQUEST['search'][$name])) {               
                $filterOption['search'] = 'IN';
                $value = $_REQUEST['search'][$name];
            }
            
            if ( $value === "" || $value === null ) { 
                $value = '';                       
            } else {
                $value = is_string($value) ? trim($value) : $value;
            }

            // add by Tom 2014-02-21 对字段增加表别名设置 
            $name = $table.$name;
            if ( $value !== '' && (empty($filterOption['search']) || $filterOption['search'] == '=') ) {
                $criteria->addCondition($name . " = '{$value}'");
            } else if ( $value !== '' ) {
                switch ( $filterOption['search'] ) {
                	case 'MULTI FIELD':
                		if ( isset($filterOption['field']) && is_array($filterOption['field']) ) {
                			foreach ($filterOption['field'] as $k=>$fieldName){
                				if ($k==0){
                					$criteria->addCondition($fieldName."='".$value."'");
                				}else{
                					$criteria->addCondition($fieldName."='".$value."'","OR");
                				}
                			}
                		} else {
                			$criteria->addCondition(" $name IN ('".$value."')");
                		}
                		break;
                    case 'IN': 				
                        if (is_array($value) ) {
                            if( count($value) == 1 && $value[0] === '' ){
                                //pass;
                            }else{
                                $criteria->addInCondition($name, $value);
                            }
                        } else {
                            $criteria->addCondition(" $name IN ('".$value."')");
                        }                       
                        break;
                        
                    case 'NOT IN':
                        $criteria->addNotInCondition($name, $value);
                        break;
                        
                    case 'LIKE':
                    	$prefix = false;
                    	if(isset($filterOption['prefix'])){
                    		if($filterOption['prefix']==true){
                    			$prefix = true;
                    		}
                    	}
                        $criteria->addSearchCondition($name, $value,$prefix, 'AND', 'LIKE');
                        break;
                        
					case 'INSTR':
						$criteria->addCondition(" INSTR ($name, '".$value."')");
						break;                        
                    case '<':
                    case '>':
                    case '<>':
                    case '>=':
                    case '<=':
                        $criteria->compare($name, ' '.$filterOption['search']. $value);
                        break;
                    case 'RANGE':
                    	$time = '';
                    	if (! empty($value[0]) && ! empty($value[1]) ) {
                    		if($value[0] > $value[1]){
                    			$_REQUEST[$name][0] = '';
                    			$value[0] = '';
                    		}
                    	}
                        if (! empty($value[0]) ) {
                            $criteria->compare($name, ' >='. $value[0]);
                        }
                        if (! empty($value[1]) ) {
                            $criteria->compare($name, ' <='. $value[1]);
                        }
                        break;
                    case 'BETWEEN_HAVING':
                    	$having = '';
                    	if (! empty($value[0]) && ! empty($value[1]) ) {
                    		if($value[0] > $value[1]){
                    			$_REQUEST[$name][0] = '';
                    			$value[0] = '';
                    		}
                    	}
                    	if (! empty($value[0]) ) {
                    		$having .= $name. ' >='. $value[0];
                        }
                        if (! empty($value[1]) ) {
                        	if (! empty($value[0]) ) {
                        		$having .= ' and ';
                        	}
                            $having .= $name. ' <='. $value[1];
                        }
                        if($having){
                        	$criteria->having = $having;
                        }
                        break;
                }
            }
        }
    }
    
    /**
     *  Is need record log
     * 
     * @param boolean $flag
     */
    public function isNeedLog($flag) {
        $this->isNeedLog = $flag;
    }
    
    /**
     * addition attributes
     */
    public function additionAttributes() {
        $userId = Yii::app()->user->id;    
        $time = date('Y-m-d H:i:s');
        if ( $this->getIsNewRecord()) {
            if ( !isset($this->create_user_id) ) {
                $this->setAttribute('create_user_id', $userId);
            }
            
            if ( !isset($this->create_time) ) {
                $this->setAttribute('create_time', $time);
            }
        }
        if ( !isset($this->modify_user_id) ) {
	    	$this->setAttribute('modify_user_id', $userId);
	    } 
	    if ( !isset($this->modify_time)  || $this->modify_time==null ) {
	        $this->setAttribute('modify_time', $time);
	    }
    }

    /**
	 * This method is invoked before saving a record (after validation, if any).
	 * The default implementation raises the {@link onBeforeSave} event.
	 * You may override this method to do any preparation work for record saving.
	 * Use {@link isNewRecord} to determine whether the saving is
	 * for inserting or updating record.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @return boolean whether the saving should be executed. Defaults to true.
	 */
    protected function beforeSave() {
        $flag = parent::beforeSave();
        if ( $this->isNeedLog ) {
            $pk = $this->getPrimaryKey();
            if (! empty($pk) ) {
                $this->beforeSaveInfo = $this->findByPk($pk);
            }
        }
        if($flag){
        	if($this->isNewRecord){
        		if ( !isset($this->create_time) ) {
        			$this->create_time = date('Y-m-d H:i:s');
        		}
        		if ( !isset($this->create_user_id) ) {
        			$this->create_user_id = Yii::app()->user->id;
        		}
        	}
        	if ( !isset($this->modify_time) || $this->modify_time==null ) {
        		$this->modify_time = date('Y-m-d H:i:s');
        	}
        	if ( !isset($this->modify_user_id) ) {
        		$this->modify_user_id = Yii::app()->user->id;
        	}
        	return true;
        }else{
        	return false;
        }
        
        //return $flag;
    }
    
    /**
     *  get log message
     * 
     * @return string
     */
    public static function getLogMsg() {
    	return self::$logMsg;
    }
    
    /**
     * add log message
     * 
     * @return
     */
    public static function addLogMsg($msg) {
         self::$logMsg[] = $msg;
    }
    /**
     * destory log message
     * 
     * @return
     */
    public static function destoryLogMsg(){
    	self::$logMsg = array();
    }
    /**
     * the filter fields when save update log 
     * @param array $fields 
     */
    public function addFilterFields($fields=array()){
    	if(empty($fields)) return ;

    	$this->filterFields = !is_array($fields) ? explode(',',$fields) : $fields;
    	
    }
    
    
    /**
     * after save
     */  
    public function afterSave() {
        parent::afterSave();
        if ( $this->isNeedLog ) {          
            $msg = $this->setRecordLog();
            if (! empty($msg) ) {
                $this->addLogMsg($msg);
            }          
        }
        $this->notify();             
    }
    
    /**
     * get record log message
     * 
     * @return null
     */
    public function setRecordLog() {
    	$msg = '';
    	$defaultFilterFields = array( 'id', 'modify_user_id' ,'modify_time', 'create_user_id', 'create_time','check_user_id','check_time');
    	$addFilterFields = !empty($this->filterFields) ? $this->filterFields : array();
    	$filterFields = array_merge($defaultFilterFields,$addFilterFields);
    	foreach ( $this->getAttributes() as $key => $val ) {
    		if ( ! $this->getIsNewRecord() && $val == $this->beforeSaveInfo[$key] ) {
    			continue;
    		}
//    		$label = $this->getAttributeLabel($key);
            $label = get_class($this) . ':'.$key;   //by shenll
    		if (in_array($key, $filterFields)) {
    			continue;
    		}else {
    			if ( $this->getIsNewRecord() ) {
    				$msg .= MHelper::formatInsertFieldLog($label, $val);
    			} else {
    				$msg .= MHelper::formatUpdateFieldLog($label, $this->beforeSaveInfo[$key], $val);
    			}
    		}
    	}
    //	return $msg;
    	$this->addLogMsg($msg);
    }

    /**
     * replace log
     * @return string
     * @author shenll
     */
    public function getRecordLog($msg='') {
//        return nl2br($msg);
//        {model:attr} {oldVal} to {newVal}

        $logArr = array();
        if (!empty($msg)){
            $msg = htmlspecialchars_decode($msg);
            $logArr = explode("<br/>",$msg);
         } else {
            $logArr = self::$logMsg;
        }

        $_log='';
        $i=1;
        foreach($logArr as $log){
            if (!empty($log)){
                $formatLog = MHelper::formatLog($log);
                $_log .= ( $i>1 ? "<br>" : '') . $formatLog;
                $i++;
            }
        }

        return $_log;
    }
    /**
     * order options
     * 
     * @return array 
     */
    public function orderOptions() {
        $result = array();
        $attributeLabels = $this->attributeLabels();
        if (! method_exists($this, 'orderFieldOptions')) {
            return $result;
        }
        $orderFields = $this->orderFieldOptions();
        if ( empty($orderFields) ) return $result;        
        foreach ( $orderFields as $key => $val ) {
            if ( is_numeric($key) ) {
                $result[$val] = $attributeLabels[$val];
            } else {
                $result[$key] = $val;
            }            
        }
        
        return $result;
    }

    /**
     * get validate errors
     * 
     * @return string
     */
    public function getValidateErrors() {
    	$msg = '';
    	$errors = $this->getErrors();
    	foreach( $errors as $key => $value ){
    		foreach ($value as $val){
    			$msg .= $key.' : '.$val.'<br>';
    		}
    	}
    	return $msg;
    }
    
    /**
     * get the data by the scheme id
     *  
     * @param int $schemeId
     * @param string $conditions
     * @return array $data
     */
    public function getDataBySchemeId($schemeId, $conditions,$className,$isGetToal=false) {
        $excelSchemeDataObj = ExcelSchemeData::getInstance();
        $className = isset($className) && !empty($className) ? $className : get_class($this);
        $result = $excelSchemeDataObj->setSchemeId($schemeId)
                ->setConditions($conditions)
                ->setModelName($className)
                ->setSortTableNames()
                ->setAdditionColumns()
                ->getSchemeJoinData($isGetToal);
                //->getSchemeData();
        return $result;           
    }
    
    /**
     * get the data by the scheme id
     *
     * @param int $schemeId
     * @param string $conditions
     * @param boolean $isGetTotal
     * @return array $data
     */
    public function getJoinDataBySchemeId($schemeId, $conditions,$className,$queryColumn,$isGetTotal = false) {
    	$excelSchemeDataObj = ExcelSchemeData::getInstance();
    	$className = isset($className) && !empty($className) ? $className : get_class($this);
    	$result = $excelSchemeDataObj->setSchemeId($schemeId)
    		->setConditions($conditions)
    		->setModelName($className)
    		->setQueryColumn($queryColumn)
    		->setSortTableNames()
    		->setAdditionColumns()
    		->getSchemeJoinData($isGetTotal);
    	return $result;
    }
    /**
     * get the data total by the scheme id
     *  
     * @param int $schemeId
     * @param string $conditions
     * @return array $data
     */
    public function getDataTotalBySchemeId($schemeId, $conditions,$className) {
        $excelSchemeDataObj = ExcelSchemeData::getInstance();
        $className = isset($className) && !empty($className) ? $className : get_class($this);
        $result = $excelSchemeDataObj->setSchemeId($schemeId)
                ->setConditions($conditions)
                ->setModelName($className)
                ->setSortTableNames()
                ->setAdditionColumns()
                ->getSchemeDataTotal();
        
        return $result;           
    }
    /**
     * 报表方案字段分组：只在统计报表时用
     * 根据字段的类型将数据分成条件字段、分组字段、统计字段、求和字段、计数字段等
     * @param array $columnNameArr:方案所有字段数组，二维
     * @return 分组后数组：键值为 :is_condition、is_group、is_sum、is_value、is_count、is_avg
     */
    public function reportColumnGroup($columnNameArr = array()){
    	$data = array();
    	if(empty($columnNameArr)) return $data;
    	foreach ($columnNameArr as $key=>$val){
    		if ($val['is_condition'] == ExcelSchemeColumn::IS_CONDITION){
    			if( trim($val['data_type']) !='' ){
    				$searchType = $val;
    				$searchType['show_html'] = VHelper::showHtmlByDataType($val);
    			}
    			$data['is_condition'][] = $searchType;
    		}

    		if ( $this->checkIn(ExcelSchemeColumn::_IS_GROUP,trim($val['calculate_type'])) ){
    			$data['is_group'][$key] = $val;
    			if ($val['data_type'] == ExcelSchemeColumn::_DATETIME){
    				$data['is_group'][$key]['time'] = true;
    			}
    		}

    		if ( $this->checkIn(ExcelSchemeColumn::_IS_SUM,trim($val['calculate_type']) ) ){
    			$data['is_sum'][] = $val;
    		}

    		if ( $this->checkIn(ExcelSchemeColumn::_IS_COUNT,trim($val['calculate_type']) ) ){
    			$data['is_count'][] = $val;
    		}
    		if ( $this->checkIn(ExcelSchemeColumn::_IS_AVG,trim($val['calculate_type']) ) ){
    			$data['is_avg'][] = $val;
    		}
    		if ( $this->checkIn(ExcelSchemeColumn::_IS_HAVING,trim($val['calculate_type']) ) ){
    			$data['is_having'][] = $val;
    		}

    		if ($val['is_value'] == ExcelSchemeColumn::IS_VALUE){
    			$data['is_value'][] = $val;
    		}

    	}
    	return $data;
    }
    public function checkIn($needle,$haystack){
    	if (false !== stripos($haystack, $needle)) {
    		return true;
    	}
    	return false;
    	
    }
    
    
    public function reportSearch(){
    	$schemeId = Yii::app()->request->getParam('scheme_id');
    	$ids = Yii::app()->request->getParam('id');
    	$field = Yii::app()->request->getParam('field', 'id');
    	$titles = UebModel::model('ExcelSchemeColumn')->getColumnTitlePairsBySchemeId($schemeId);
    	if (! empty($ids) ) {
    		$conditions = " $field IN($ids)";
    	} else {
    		$conditions = Yii::app()->session->get($className .'_condition');
    	}
    	$data = UebModel::model($className)->getDataBySchemeId($schemeId, $conditions);
    	
    }

    public function uniqueArrayField($array,$key)
    {
        $new = array();
        foreach($array as $v)
            $new[$v[$key]][] = $v;
        return $new;
    }
    
    /**
     * @desc 注册观察者
     * @param IObserver $observer
     * @return UebModel
     */
    public function registerObserver(IObserver $observer)
    {
        if (!in_array($observer, $this->_observers))
            $this->_observers[] = $observer;
        return $this;
    }
    
    public function notify()
    {
        foreach ($this->_observers as $observer)
            $observer->update($this);
    }

    public function getOperationUserList($fieldUser)
    {
        if(!in_array($fieldUser,array('create_by','modify_by')))
            return array();
        $users = array_column(VHelper::selectAsArray($this,$fieldUser,'',true),$fieldUser);
        if(!empty($users))
            $users = array_column(VHelper::selectAsArray('User','id,user_full_name','id in ("'.implode('","',$users).'")',true,'','user_full_name ASC'),'user_full_name','id');
        return $users;
    }


    /**
     * search info
     * @param type $model
     * @param type $sort
     * @return \CActiveDataProvider
     */
    public function searchs($model, $sort = array(), $with = array(),$CDbCriteria = null) {
        if ( empty($model) ) {
            $model = get_class($this);
        }
        $curUrls = Yii::app()->request->getUrl();
        $arr = explode('?_',$curUrls);
        $curUrl = $arr[0];
        $userId = Yii::app()->user->id;

        /***/
        if($CDbCriteria ==null){
            $criteria = new CDbCriteria;
        }else{
            $criteria = $CDbCriteria;
        }

        $filterCondition = '';

        if( $checkAuth = Yii::app()->session->get('specialAuth') ){
            $columnArr = array();
            foreach($this->filterOptions() as $item){
                $columnArr[$item['name']] = $item;
            }

            foreach($checkAuth as $column=>$item){
                if($item){
                    if( isset($columnArr[$column]) ){
                        $filterCondition .= ' '.(isset($columnArr[$column]['alias']) ? $columnArr[$column]['alias'].'.' : '').$column.' IN ("'.implode('","', $item).'")';
                    }
                }
            }

            if( $criteria->condition && $filterCondition ){
                $criteria->condition .= ' AND ';
            }
            $criteria->condition .= $filterCondition;
        }

        $desc = '';
        $defaultOrder = isset($sort->attributes['defaultOrder']) ? $sort->attributes['defaultOrder'] : '';
        $defaultDirection = isset($sort->attributes['defaultDirection']) ? $sort->attributes['defaultDirection'] : 'desc';
        if (! isset($_REQUEST['orderField']) || empty($_REQUEST['orderField'])) {
            $_REQUEST['orderField'] = $defaultOrder;
            $desc = $defaultOrder.' '.$defaultDirection;
        }else{

            $orderField = $_REQUEST['orderField'];
            if (! isset($_REQUEST['orderDirection']) || empty($_REQUEST['orderDirection']) ) {
                $_REQUEST['orderDirection'] = $defaultDirection;
            }
            $orderDirection =  $_REQUEST['orderDirection'];
            if($orderField === $defaultOrder) {
                $desc = $orderField .' '.$orderDirection;
            }else{
                if ($defaultOrder) {
                    $desc = $orderField .' '.$orderDirection.','.$defaultOrder.' '.$defaultDirection;
                } else {
                    $desc = $orderField .' '.$orderDirection;
                }
            }
        }
        if (! empty($desc) && strtolower(trim($desc)) != 'desc') {
            $criteria->order = $criteria->order .','. $desc;
        }
        if (! empty($with) ) {
            $criteria->with = $with;
        }

        $pageSize = isset($_REQUEST['numPerPage']) ? $_REQUEST['numPerPage'] : 50;//Yii::app()->params['per_page_num'];
        $this->addConditions($criteria);
        //根据条件取所有符合条件的数据，不取分页条件，适合统计类。两种方法，
        /**1,把条件都缓存下来，优点:少内在，缺点:不知道有多少条件

        Yii::app()->session->add(get_class($this).'_params', $criteria->params);
        Yii::app()->session->add(get_class($this).'_having', $criteria->having);
         **/
        //2,把整个条件全缓存下来，可以根据需求取所有或里面的某一项(可打印看有什么项)
        Yii::app()->session->add(get_class($this).'_criteria', $criteria);
        Yii::app()->session->add(get_class($this).'_condition', $criteria->condition);
        Yii::app()->session->add(get_class($this).'_order', $criteria->order);
        Yii::app()->session->add(get_class($this).'_numPerPage', $pageSize);

        // Yii::p($criteria);
        // echo $criteria.'dd'; exit;
        $obj =  new CActiveDataProvider($model, array(
            'criteria' => $criteria,
            'sort' => $sort,
            'pagination' => array(
                'pageSize'      => $pageSize,
                'currentPage'   => isset($_POST['pageNum'])? $_POST['pageNum']-1 : 0,
            ),
        ));
        //var_dump($criteria);exit;
        return $obj;
    }

    /**
     * 批量插入记录的通用方法
     * @author cxy
     * @param String $tableName 表名
     * @param Array $key 键值
     * @param Array $value 值,二维数组
     * @return Bool true成功 false失败
     */
    public function batchInsertAll($tableName = '', $key = array(), $value = array())
    {

        if (empty($tableName) || empty($key) || empty($value)) return false;

        //设置要插入的字段
        $keys = " (" . implode(', ', $key) . ")";

        //设置要插入的字段值
        $values = '';

        foreach ($value as $val) {
            $values .= "(" . $this->addQuotes($val) . "),";
        }

        //批量插入
        $sql = "INSERT INTO " . $tableName . $keys . " VALUES " . rtrim($values, ',');
        return $this->getDbConnection()->createCommand($sql)->execute();

    }

    /**
     * 批量插入、更新辅助函数
     * 为了防止数组的值包含有逗号，导致1个value值变为多个
     * @author cxy
     * @desc 2017-12-25
     * str_replace(",,,", "','", implode(',,,', $params))
     */
    public function addQuotes($params = array())
    {

        if (empty($params)) return false;

        return "'" . str_replace(",,,", "','", implode(',,,', $params)) . "'";

    }
}
?>
