<?php

/**
 * @package Ueb.modules.users.controllers
 * 
 * @author Bob <Foxzeng>
 */
class UserlogController extends UebController { 

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array();
    }

    /**
     * users management
     */
    public function actionIndex() {      
        Yii::apilog('hello', 'hello', 'ebay', 'failure', 11111111);
        Yii::apilog('hello', 'hello', 'ebay', 'failure', 22222222);
        Yii::apilog('hello', 'hello', 'ebay', 'failure');
        die('ok');
        $this->render('index');
    }   
}
