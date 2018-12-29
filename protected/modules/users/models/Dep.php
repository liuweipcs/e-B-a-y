<?php

class Dep extends UsersModel
{	 
	public $parent = '';
	public $parent_id = '';	
	public $description = '';
	public $name = '';
	public $name_code = '';
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
		return 'ueb_department';
	}
    
    public function rules() {
        $rules = array(         
            array( 'department_name, department_code, department_parent_id', 'required'),    
            array( 'department_name, department_code', 'unique'),
        	array( 'department_name, department_code', 'length', 'max' => 50),
        	array('parent', 'length', 'max' => 50),
        );        
        return $rules;
    }
    
    /**
     * Declares attribute labels.
     * @return array
     */
    public function attributeLabels() {
        return array(
            'department_name'     => Yii::t('users', 'Dep Name'),
            'menu_url'              => Yii::t('system', 'Menu URL'),
            'menu_description'      => Yii::t('system', 'Menu Description'),
            'menu_status'           => Yii::t('system', 'Status'),
            'menu_order'            => Yii::t('system', 'Order'),
            'menu_is_menu'          => Yii::t('system', 'Whether it is the menu'),
            'department_parent_id'        => Yii::t('system', 'The parent menu'),
        	//'name'                  => Yii::t('users', 'Dep Name'),
        	'department_code'                  => Yii::t('users', 'Dep Code'),
        	'department_description'           => Yii::t('users', 'Dep desc'),
        	'parent'                => Yii::t('users', 'Parent Dep'),
        );
    }
    /**
	 * custom Validate
	 * @author Nick 2013-11-18
	 */
	public function validate(){
		$return = true;
		return $return;
	}
    /**
     * filter role code, blank space replace with _
     * @return String $name
     */
    public static function filterName($name) {
    	$name = str_replace("  ", " ", $name);
    	$name = str_replace(" ", "_", $name);
    
    	return $name;
    }
    public static function backFilterName($name) {
    	return str_replace("_", " ", $name);
    }
     
    public static function getTreeList() {
		$depshow = UebModel::model('Users')->getUserInfoShow();
		$where = "";
		if($depshow['dep_show']==0){
			$userInfo = UebModel::model('User')->getUserNameById(Yii::app()->user->id);
			if($depshow['managementid'] != ""){
				$ids = $depshow['managementid'].','.$userInfo['department_id'];
				$where = " and id in (".$ids.")";
			}else{
				if($userInfo['department_id']){
					$where = " and id=".$userInfo['department_id'];
				}
			}
		}
        $list = Yii::app()->db->createCommand()
			->select('id,department_parent_id,department_name,department_code,department_description,department_status,department_level,department_order')
			->from(self::tableName())	
            ->where("department_status = 1".$where)
            ->order("department_level Desc, department_order Asc")
			->queryAll();
        $data = array();
        foreach ($list as $key => $val) {		
           if (  isset($data[$val['id']]) ) {
               $subdept = $data[$val['id']]['subdept'];
               unset($data[$val['id']]['subdept']);
               $data[$val['department_parent_id']]['subdept'][$val['id']] =  array(
                   'id'                     => $val['id'],
                   'name'                   => $val['department_name'],
                   'department_parent_id'   => $val['department_parent_id'],                 
                   'subdept'                => $subdept);              
           } else {
               $data[$val['department_parent_id']]['subdept'][$val['id']] = array(
                   'id'                     => $val['id'], 
                   'name'                   => $val['department_name'],
                   'department_parent_id'   => $val['department_parent_id'],                 
                   'subdept'    => array()); 
           }                  
        }
		if(!$data[0]['subdept']){
			$vid = 0;
			foreach($data as $vkey=>$vals){
				$vid = $vkey;
			}
			return $data[$vid]['subdept'];
		}
		if($depshow['managementid'] != ""){
			$vid = 0;
			$newdep = array();
			foreach($data as $vkey=>$vals){
// 				$newdep = array_merge($newdep,$vals['subdept']);
				isset($vals['subdept']) && $newdep +=$vals['subdept']; 
			}
			return $newdep;
		}
        return $data[0]['subdept'];
    }
    
    public static function get_max_order($pid) {
    	$arr = Yii::app()->db->createCommand()
    	->select('max(department_order) as max_order')
    	->from(self::tableName())
    	->where("department_parent_id = '{$pid}'")
    	->queryColumn();
    	return $arr[0];
    }
    
    public static function getParentBydepId($id) {
    	return Yii::app()->db->createCommand()
              ->select('department_parent_id,department_name,department_description,department_code')
              ->from(self::tableName())
              ->where(" id = ".$id)
              ->queryRow();
    	
    }
    
    public static function getchilddep($id) {
    	return Yii::app()->db->createCommand()
    	->select('count(*) as num')
    	->from(self::tableName())
    	->where(" department_parent_id = ".$id)
    	->queryRow();
    	 
    }
       
    public static function getIndexNavTabId() {
        return Menu::model()->getIdByUrl('/users/users/index');
    } 
    
    public function getDepIdByCode($code) {
    	$model = $this->findByAttributes('',"department_code='{$code}'");
    	return $model->attributes;
    }

}