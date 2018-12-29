<?php

/**
 * @package Ueb.modules.users.controllers
 * 
 * @author Bob <Foxzeng>
 */
class OrderaccesscontrolController extends UebController {

    //public $modelClass = 'User';

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
        		array(
        				'allow',
        				'users' => array('*'),
        				'actions' => array('list','update','orderaccess')
        		),
        );
    }

    /**
     * users management
     */
    public function actionIndex() {
        $this->render('index');
    }

    /**
     * users list
     */
    public function actionList() {
        $model = UebModel::model('Useraccess');
        $model->setScenario('search');
		$departmentId = !empty(Yii::app()->request->getParam('department_id',''))?Yii::app()->request->getParam('department_id',''):"";
		if(!$departmentId){
			$departmentId = !empty(Yii::app()->user->department_id) ? Yii::app()->user->department_id:"";
		}
        $this->render('list', array(
            'model' => $model,'departmentId'=>$departmentId,
        )); 
    }

    /**
     * update users list
     * 
     * @param type $id
     */
    public function actionUpdate($id) {
        $result = UebModel::model('Orderaccesscontrol')->checkDatas($id);
        $model = $this->loadModel($id);
        $department_id = $_REQUEST['department_id'];
        $this->render('update', array('model' => $model,'departmentId' => $department_id,'result' => $result));
    }
    /**
     *
     * @param type $id
     */
    public function actionCheck($ids) {
        $result = UebModel::model('Orderaccesscontrol')->checkDatas($ids);
        $model = $this->loadModel($ids);
        $department_id = $_REQUEST['department'];
        $this->render('check', array('model' => $model,'departmentId' => $department_id,'result' => $result));
    }


    public function actionOrderaccess(){
        $useId = $_POST['userid'];
        $Accountname = $_POST['Accountname'];
        foreach($Accountname as $k => $v){
            $platformCode = $k;
            $accountIds = implode(',',$v);
            if($platformCode=='ALI'){
                foreach ($v as $vl){
                    $configModel = new Aliexpressaccountconfig();
                    /*保存店铺配置*/
                    if(empty($configModel->findAllByAttributes(array('account_id'=>$vl,'user_id'=>$useId)))){
                        $configModel->account_id = $vl;
                        $configModel->user_id = $useId;
                        $configModel->add_time = time();
                        $configModel->operator = Yii::app()->user->id;
                        $configModel->save();
                    }
                }
            }
            $return = UebModel::model('Orderaccesscontrol')->checkData($useId,$platformCode);
            if(!empty($return)){
                $result = UebModel::model('Orderaccesscontrol')->updateData($return['id'],$accountIds);
            }else{
                $result = UebModel::model('Orderaccesscontrol')->insertData($useId,$platformCode,$accountIds);
            }
        }
        if(!empty($result)){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function loadModel($id) {      
        $model = User::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        return $model;
    }

}
