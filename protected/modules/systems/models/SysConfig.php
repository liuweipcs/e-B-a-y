<?php
/**
 * @package Ueb.modules.systems.models
 * 
 * @author Bob <Foxzeng>
 */
class  SysConfig extends SystemsModel
{	
    public $type  = null;
    public $types = null;
    
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'ueb_sys_config';
	}
    
    public function setType($type) {
        $this->type = $type;
        return $this;
    }
    public function  setTypes($types)
    {
        $this->types= $types;
        return $this;
    }
    public function getTypes()
    {
        if (is_null($this->types)) {
            throw new CException('Config type is not allow empty');
        }
        return $this->types;

    }
        public function getType() {
        if ( is_null($this->type) ) {
            throw new CException('Config type is not allow empty');
        }
       
        return $this->type;
    }
    
    /*
     * 保存产品显示字段
     */
    public function productColumnDisplaySave($arr=array()){
    	try {
    		$info = Yii::app()->db->createCommand()
    		->select('id')
    		->from(self::tableName())
    		->where( 'config_key=:config_key', array(':config_key'=>$arr['config_key']))
    		->andWhere('config_type=:config_type', array(':config_type' => $arr['config_type']))
    		->queryRow();
    		if ( empty($info['id']) ) {
    			Yii::app()->db->createCommand()->insert(self::tableName(), $arr);
    		} else {
    			Yii::app()->db->createCommand()->update(self::tableName(), $arr,'id=:id', array(':id' => $info['id']));
    		}
    		$flag = true;
    	}catch(Exception $e){
    		$flag = false;
    	}
    	return $flag;
    }

        /**
     * batch save data
     * 
     * @param type $vars
     * @return boolean
     */
    public function batchSave($vars) {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $type = $this->getType();echo $type;exit;
           // $types = $this->getTypes();
            $types = rand(1,9);
            //delete all first
            if ($this->findAll('config_type=:config_type',array(':config_type'=>$type)))
            {
                $flag = $this->deleteAll('config_type=:config_type', array(':config_type' => $type));
            }
            foreach ($vars as $key => $val) {
            	$data = array(
                    'config_key'        => $key,
                    'config_value'      => $val,
                    'config_type'       => $type,
                    'types'             => $types
                );
            	Yii::app()->db->createCommand()->insert(self::tableName(), $data);
            	//2014.8.7这里改成先清除所有类别的再全部重新添加
                /*$info = Yii::app()->db->createCommand()
                            ->select('id')
                            ->from(self::tableName())
                            ->where( 'config_key=:config_key', array(':config_key'=>$key))                           
                            ->andWhere('config_type=:config_type', array(':config_type' => $type))
                            ->andWhere('types=:types', array(':types' => $types))
                            ->queryAll();
                           
                if ( empty($info['id']) ) {
                   Yii::app()->db->createCommand()->insert(self::tableName(), $data);                  
                } else {                       
                   Yii::app()->db->createCommand()->update(self::tableName(), $data,
                    'id=:id', array(':id' => $info['id'])); 
                }*/

            }                               
            $transaction->commit();
            $flag = true;
        } catch (Exception $e) { 
        	print_r($e->getMessage());
            $transaction->rollback();
            $flag = false;
        }
       
        return $flag;
    }   
    
    /**
     * get list by type
     * 
     * @param type $type
     * @return null | array $data
     */
    public static function getPairByType($type) {
         $list = Yii::app()->db->createCommand()
                        ->select('*')
                        ->from(self::tableName())
                        ->where(" config_type = '{$type}'")
                        ->query();
        $data = array();
        foreach ($list as $val) {
            $data[$val['config_key']] = $val['config_value'];
        }
        return $data;
    }
    
    
    /**
     * get system config cache by type
     * 
     * @param string $type
     * @return array $data
     */
    public static function getConfigCacheByType($type) {
        $key = 'sysconfig'.$type;
        $data = Yii::app()->cache->get($key); 
        if ( $data === false )
        {
            $data = self::getPairByType($type);
            Yii::app()->cache->set($key, $data, 60*60*24);
        } 
        return $data;
    }
    
    /**
     * 根据key值获取系统配置
     * @param string $key
     * @return string|boolean
     */
    public static function getConfigByKey($key){
    	$info = SysConfig::model()->find('config_key = "'.$key.'"');
    	if( !$info ){
    		return false;
    	}
    	return $info->config_value;
    }
}