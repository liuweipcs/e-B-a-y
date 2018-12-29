<?php

/**
 * @package Ueb.modules.users.controllers
 * 
 * @author Bob <Foxzeng>
 */
class AccessController extends UebController {

    public $modelClass = 'User';

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array();
    }

    public function actionIndex() {       
      	$this->render('index');
    }
    /*
     * get users by roleCode
     */
    public function actionSelectOwn() {
    	$this->render('select_owner');
    }
    /*
     * select role and users by role code
     */
    public function actionSelectUser($role_name,$role_code){
    	$arr = AuthAssignment::model()->getUlist($role_code,1);
    	unset($arr['']);
    	$arr_role['role_code']=$role_code;
    	$arr_role['role_name']=$role_name;
    	$this->render('user_all',array('arr'=>$arr,'arr_role'=>$arr_role));
    }
}
