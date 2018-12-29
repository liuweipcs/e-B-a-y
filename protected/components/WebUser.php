<?php

/**
 * 获取用户表其他信息
 * 调用方式：1,Yii::app()->user->user_full_name,user_email...
 * 2,写函数调用，繁琐，如： function getFull_name()，调用全名
 * 
 * @author ethan
 */
class WebUser extends CWebUser { 

  // Store model to not repeat query. 
	private $_model;
	public $user_full_name='';
	public $user_name='';
	

	/*Return full name. 
	access it by Yii::app()->user->full_name 
 	* *
 	*/
	function getFull_name(){ 
    	$user = $this->loadUser(Yii::app()->user->id); 
    	return $user->user_full_name;
	}

	// Load user model. 
	protected function loadUser($id=null) 
    { 
        if($this->_model===null) 
        { 
            if($id!==null) 
                $this->_model=User::model()->findByPk($id); 
        } 
        return $this->_model; 
    }
    
    
    public function __get($name)
    {
    	if ($this->hasState('__userInfo')) {
    		$user=$this->getState('__userInfo',array());
    		if (isset($user[$name])) {
    			return $user[$name];
    		}
    	}
    	 
    	return parent::__get($name);
    }
     
    public function login($identity, $duration=0) {
    	$this->setState('__userInfo', $identity->getUser());
    	parent::login($identity, $duration);
    }
    
}