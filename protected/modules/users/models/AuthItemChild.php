<?php

class AuthItemChild extends UsersModel
{	
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__) 
	{
		return parent::model($className);
	}


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ueb_auth_item_child';
	}	
	/**
	 * 
	 * @param array $parent
	 * @return multitype:
	 */
	public function getChildRoleByParent($parent){
		$authArray = is_array($parent)?$parent:array($parent);
		$result = array();
		while (!empty($authArray)){
			$list = Yii::app()->db->createCommand()
	              ->select('child')
	              ->from(self::tableName())
	              ->where(array('in', 'parent', $authArray))
	              ->andwhere(array('not like', 'child', array('menu_%','resource_%')))
	              ->queryAll();
			$authArray = !empty($list)?array_column($list, 'child'):array(); 
			!empty($authArray) && $result = array_merge($result,$authArray);
		}
		return $result;
	}
    /**
	 * 
	 * @param array $parent
	 * @return multitype:
	 */
	public function getChildRoleByMenuenu($parent){
		$list = Yii::app()->db->createCommand()
              ->select('child')
              ->from(self::tableName())
              ->where(array('in', 'parent', $parent))
              ->andwhere(array('like', 'child', array('menu_%')))
              ->queryAll();
        $result = !empty($list)?array_column($list, 'child'):array();
        return array_unique($result);//去重
	}
	
    public static function getChildByParent($parent) {
        return Yii::app()->db->createCommand()
              ->select('child')
              ->from(self::tableName())
              ->where(" parent = '{$parent}'")
              ->queryColumn();
    }
    
    public static function getAll() {
        return Yii::app()->db->createCommand()
              ->select('child,parent')
              ->from(self::tableName())           
              ->queryAll();    
    }
    
    public static function getAllAccess(){
    	return AuthItemChild::model()->getDbConnection()->createCommand()
    				->select('child,parent')
    				->from(self::tableName())
    				->where(array('NOT LIKE','child',array('menu_%','resource_%')))
    				->queryAll();
    }

    //根据menu_url来判断权限
    public static function checkUrlAccess($url){
    	static $authMenu = array();
    	empty($authMenu) && $authMenu = self::getAuthMenuAll();
    	return in_array($url, $authMenu);
    }
    
    //根据menu_id验证权限
    public static function checkMenuAccess($menuId){
    	static $authMenu = array();
    	empty($authMenu) && $authMenu = self::getAuthMenuAll();
    	return isset($authMenu[$menuId]);
    }
    
    //获取当前用户的所有权限
    public static function getAuthMenuAll(){
    	$authass = $_SESSION['authass'];
    	if(empty($authass)){
    		$authass = Yii::app()->authManager->getAuthAssignments(Yii::app()->user->id);
    		$_SESSION['authass'] = $authass = array_keys($authass);
    	}
    	$result = array();
    	if(!empty($authass)){
    		foreach ($authass as $auth){
    			$result += self::getAuthMenu($auth);
    		}
    	}
    	return $result;
    }
    
    //获取单个角色的权限
    public static function getAuthMenu($auth){
    	$authResult = array();
    	$authResult = Yii::app()->cache->get('r_'.$auth);
    	if(empty($authResult)){
	    	$authAll = AuthItemChild::model()->getChildRoleByParent($auth);
	    	array_push($authAll, $auth);
	    	$authlist = AuthItemChild::model()->getChildRoleByMenuenu($authAll);
	    	$authIds = implode(',', $authlist);
	    	$authIds = str_replace('menu_', '', $authIds);
	    	$authResult =  Menu::model()->queryPairs('id,menu_url',array(
	    			'IN','id',explode(',', $authIds)
	    	));
	    	Yii::app()->cache->set('r_'.$auth, $authResult,3600*6);
    	}
    	return $authResult;
    }
    
    public static function deleteCache($auth){
    	$rolelist = self::getParentsByChild($auth);
    	!in_array($auth, $rolelist) && array_push($rolelist, $auth);
    	if(!empty($rolelist)){
    		foreach ($rolelist as $val){
    			Yii::app()->cache->delete('r_'.$val);
    		}
    	}
    }
    
    /**
     * get child parents
     * 
     * @param type $child
     * @return array $data
     */
    public static function getParentsByChild($child) {
        $data = array();
        $parent = self::getParentByChild($child);        
        while(!empty($parent) ) {
            $data[] = $parent[0];          
            $parent = self::getParentByChild($parent[0]);
        }
        return $data;
    }

    public static function getParentByChild($child) {      
        return Yii::app()->db->createCommand()
              ->select('parent')
              ->from(self::tableName())
              ->where(" child = '{$child}'")
              ->queryColumn();
    }
    
    /**
     * @desc 取消菜单的资源绑定
     * @param int $menuId
     */
    public function cancelMenuAssign($menuId=0){
    	$where = '';
    	if( $menuId > 0 ){
    		$where .= ' AND parent = "menu_'.$menuId.'"';
    	}else{
    		$where .= ' AND parent LIKE "menu_%"';
    	}
    	return $this->deleteAll('child LIKE "resource_%"'.$where);
    }
}