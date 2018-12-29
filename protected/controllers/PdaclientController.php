<?php
/** 
 * Base generic controller class
 * 
 * @package PDA.controllers
 * 
 * @author Gordon
 */
class PdaclientController extends UebController {

	public function actionLogin(){
		
		$this->layout = '//layouts/pdaLogin';
		$model = new PdaLoginForm;
		$msg = '';
		if (isset($_POST['LoginForm'])) {          
            $model->attributes = $_POST['LoginForm'];  
            if ( $model->validate() && $model->login()){
                $this->redirect(Yii::app()->user->returnUrl.'?uid='.md5($model->user_name).'&pw='.md5($model->user_password));
            }else{
            	$message = json_decode(CActiveForm::validate($model));
            	$msg = $message->PdaLoginForm_user_password[0];
            }
        }
		$this->render('login', array('model' => $model,'msg' => $msg));
	}
	
	public function actionIndex(){
		if( !isset($_GET['uid']) || !isset($_GET['pw']) ){
			Yii::app()->user->returnUrl = Yii::app()->request->requestUri;
			$this->redirect($this->createUrl('/pdaclient/login'));
		}
		$this->layout = '//layouts/pdamain';
		
		$menuList = UebModel::model('menu')->getPdaMenu();
		$this->render('index', array('menuList'=>$menuList));
	}
}