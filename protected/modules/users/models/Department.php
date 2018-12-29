<?php

class Department extends UsersModel
{	   
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
        $rules = array();        
        return $rules;
    }
    
    /**
     * Declares attribute labels.
     * @return array
     */
    public function attributeLabels() {
        return array(
            'department_name'     => Yii::t('system', 'Menu Name'),
            'menu_url'              => Yii::t('system', 'Menu URL'),
            'menu_description'      => Yii::t('system', 'Menu Description'),
            'menu_status'           => Yii::t('system', 'Status'),
            'menu_order'            => Yii::t('system', 'Order'),
            'menu_is_menu'          => Yii::t('system', 'Whether it is the menu'),
            'department_parent_id'        => Yii::t('system', 'The parent menu'),
        );
    }
     
    public static function getTreeList() {
        $list = $this->getDepartmentList();               
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
        return $data[0]['subdept'];
    }
    /**
     * get Department List
     * @return Ambigous <CDbDataReader, mixed>
     */
    public function getDepartmentList(){
    	return $list = Yii::app()->db->createCommand()
	    	->select('*')
	    	->from(self::tableName())
	    	->where("department_status = 1")
	    	->order("department_level Desc, department_order Asc")
	    	->query();
    }
    /**
     * get Department
     * @param $partmentId
     * @return multitype:unknown
     */
    public function getDepartment($partmentId = null){
    	$data = array();
    	$list = $this->getDepartmentList();
    	if ($list){
    		foreach ($list as $key=>$val){
    			$data[$val['id']] = $val['department_name'];
    		}
    	}
    	if ($partmentId !== null) return $data[$partmentId];
    	return $data;
    }
       
    public static function getIndexNavTabId() {
        return Menu::model()->getIdByUrl('/users/users/index');
    }
    /*获取下级部门
     * @param1 $id number|array 部门的id或id数组
     * @param2 $recursion boolean 是否递归子部门
     * @return array 查询不到时返回空数组array()
    */
    public function getChildById($id,$recursion = false)
    {
        if(is_array($id))
        {
            $condition = "department_parent_id in ('".implode("','",$id)."')";
        }
        else
        {
            $condition = "department_parent_id={$id}";
        }
        $child = VHelper::selectAsArray($this,'*',$condition);
        if($recursion)
        {
            if(!empty($child))
            {
                return array_merge($child,$this->getChildById(array_column($child,'id'),$recursion));
            }
        }
        return $child;
    }


}