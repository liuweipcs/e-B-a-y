<?php

/**
 * @package Ueb.modules.users.controllers
 * 
 * @author Bob <Foxzeng>
 */
class UsersController extends UebController {

    public $modelClass = 'User';

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
        				'actions' => array('change','queryoldpwd')
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
        $model = UebModel::model('user'); 
        //$model->setScenario('search');
		$departmentId = !empty(Yii::app()->request->getParam('department_id',''))?Yii::app()->request->getParam('department_id',''):"";
		if(!$departmentId){
			$departmentId = !empty(Yii::app()->user->department_id) ? Yii::app()->user->department_id:"";
		}
        $this->render('list', array(
            'model' => $model,'departmentId'=>$departmentId,'pages'=>100
        )); 
    }

    //职位体系开窗列表添加职员
    public function actionJobuserslist()
    {
        $model = new User();
        $this->render('jobuserslist', array(
            'model' => $model
        ));
    }

    /**
	  菜单加权限功能
	*/
    public function actionList1() {   
        $model = UebModel::model('user'); 
        $model->setScenario('search');
		$departmentId = !empty(Yii::app()->request->getParam('department_id',''))?Yii::app()->request->getParam('department_id',''):"";
		if(!$departmentId){
			$departmentId = !empty(Yii::app()->user->department_id) ? Yii::app()->user->department_id:"";
		}
        $this->render('listmen', array(
            'model' => $model,'departmentId'=>$departmentId,'pages'=>100
        )); 
    }
    
    
    /*  
     * 添加用户外网访问权限//
     */
    public function actionIntranetpass(){
    	$model = UebModel::model('user');
        if(Yii::app()->request->isAjaxRequest && isset($_REQUEST['ids'])){
        	try {$ids=explode(',',$_REQUEST['ids']);
        	foreach ($ids as $id){
        		$flag=$model->updateByPk($id, array('is_intranet'=>1));
        	}
        	}catch (Exception $e){
        		$flag=false;
        	}
        	if($flag!=false){
        		$jsonData=array(
        				'message'=>'设置成功',
        				'navTabId' => 'page' . Menu::model()->getIdByUrl('/users/user/list'),
        		);
        		echo $this->successJson($jsonData);
        	}else{
        		$jsonData=array(
        				'message'=>'设置失败!',       
        		);
        		echo $this->failureJson($jsonData);
        	}
        }
    	
    	
    }
  
    public function actionIntranetnotgo(){
    	$model = UebModel::model('user');
    	if(Yii::app()->request->isAjaxRequest && isset($_REQUEST['ids'])){
    		try {$ids=explode(',',$_REQUEST['ids']);
    		foreach ($ids as $id){
    			$flag=$model->updateByPk($id, array('is_intranet'=>0));
    		}
    		}catch (Exception $e){
    			$flag=false;
    		}
    		if($flag!=false){
    			$jsonData=array(
    					'message'=>'设置成功',
    					'navTabId' => 'page' . Menu::model()->getIdByUrl('/users/user/list'),
    			);
    			echo $this->successJson($jsonData);
    		}else{
    			$jsonData=array(
    					'message'=>'设置失败!',
    			);
    			echo $this->failureJson($jsonData);
    		}
    	}
    	 
    	 
    }
    /**
     * add users
     */
    public function actionCreate() {
        $model = new User();
        if (Yii::app()->request->isAjaxRequest && isset($_POST['User'])) {
            $_POST['User']['user_password']=  $model->getCryptPassword($_POST['User']['user_password']);
			if($_POST['User']['department_id']==0 || $_POST['User']['department_id']==""){
				$_POST['User']['department_id'] = Yii::app()->user->department_id;
			}
			//检查登录名是否重复
			$user_name=$_POST ['User']['user_name'];
			if (! empty ( $user_name ) && ! empty ( $model->find ( "user_name = :user_name", [
					'user_name' => $user_name
			] ) )) {
				echo $this->failureJson ( array (
						'message' => Yii::t ( 'system', '用户登录名重复!' )
				) );
				Yii::app ()->end ();
			}
			//检查用户英文名是否重复
			$enName = $_POST['User']['en_name'] = $_POST['User']['en_name'];
			if (!empty($enName) && !empty($model->find("en_name = :en_name", ['en_name' => $enName])))
			{
			    echo $this->failureJson(array('message' => Yii::t('system', '用户英文名称已经存在')));
			    Yii::app()->end();		    
			}
            $model->attributes = $_POST['User'];
            if ($model->validate()) {   
                try {
                    $flag = $model->save();
                    if( $flag ){
	                    //为每个用户创建一个角色
	                    $auth = Yii::app()->authManager;
	                    $auth->createRole($_POST['User']['user_name'], $model->role_mark.'_'.$_POST['User']['user_full_name']);
	                    //分配角色给自己
	                    $auth->assign($_POST['User']['user_name'],$model->attributes['id']);
                    }
                } catch (Exception $e) { 
                    //echo $e->getMessage();
                    $flag = false;
                }
                if ($flag) {
                    $jsonData = array(
                        'message' => Yii::t('system', 'Add successful'),
                        'forward' => '/users/users/index',
                        'navTabId' => 'page' . User::getIndexNavTabId(),
                        'callbackType' => 'closeCurrent'
                    );
                    echo $this->successJson($jsonData);
                }
            } else {
                $flag = false;
            }
            if (! $flag) {
                echo $this->failureJson(array('message' => Yii::t('system', 'Add failure')));
            }
            Yii::app()->end();
        }
        $departmentId = Yii::app()->request->getParam('department_id','');
        if (!empty($departmentId)) $model->department_id = $departmentId;
        $this->render('create', array('model' => $model));
    }
    
    /**
     * update users list
     * 
     * @param type $id
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        if (Yii::app()->request->isAjaxRequest && isset($_POST['User'])) {
            $model->attributes = $_POST['User'];
            if ($model->validate()) {
                $flag = $model->save();
                if ($flag) {
                    $jsonData = array(
                        'message' => Yii::t('system', 'Save successful'),
                        'forward' => '/users/users/index',
                        'navTabId' => 'page' . User::getIndexNavTabId(),
                        'callbackType' => 'closeCurrent'
                    );
                }
                echo $this->successJson($jsonData);
            } else {
                $flag = false;
            }
            if (!$flag) {
                echo $this->failureJson(array(
                    'message' => Yii::t('system', 'Save failure')));
            }
            Yii::app()->end();
        }   
        $this->render('update', array('model' => $model));
    }
    
    /**
     * delete users
     *
     * @param type $id
     */
    public function actionDelete() {
    	if (Yii::app()->request->isAjaxRequest && isset($_REQUEST['ids'])) {
    		try {
    			$flag = Yii::app()->db->createCommand()
    			->delete(User::model()->tableName(), " id IN({$_REQUEST['ids']})");
    			if ( ! $flag ) {
    				throw new Exception('Delete failure');
    			}
    			$jsonData = array(
    					'message' => Yii::t('system', 'Delete successful'),
                                        'forward' => '/users/users/index',
                                        'navTabId' => 'page' . User::getIndexNavTabId(),
                                        //'callbackType' => 'closeCurrent'
    			);
    			echo $this->successJson($jsonData);
    		} catch (Exception $exc) {
    			$jsonData = array(
    					'message' => Yii::t('system', 'Delete failure')
    			);
    			echo $this->failureJson($jsonData);
    		}
    		Yii::app()->end();
    	}
    }
    
    
    /**
     * 批量启用 或停用用户的状态
     * @throws Exception
     */
    public function actionBatchchangestatus(){
    
    	if (Yii::app()->request->isAjaxRequest && isset($_REQUEST['ids'])) {
//    		var_dump($_REQUEST['ids']);
    		$flag = Yii::app()->request->getParam('type')=='0' ? false :true;
//    		var_dump($flag);exit('1111111111');
    		try{
    			$flag = UebModel::model($this->modelClass)->changeUserStatus($_REQUEST['ids'],$flag);
    			if (!$flag) {
    				throw new Exception('Oprate failure');
    			}
    			$jsonData = array(
    					'message' => Yii::t('system', 'Operate Successful'),
    			);
    			echo $this->successJson($jsonData);
    		}catch (Exception $exc) {
    			$jsonData = array(
    					'message' => Yii::t('system', 'Operate failure')
    			);
    			echo $this->failureJson($jsonData);
    		}
    		Yii::app()->end();
    	}
    }
    /**
     * access role users action
     */

    public function actionUlist() {
        if (isset($_REQUEST['roleId'])) {
            $roleId = $_REQUEST['roleId'];
        }
        if (isset($_REQUEST['id'])) {
            $auth = Yii::app()->authManager;
            $authItem = new CAuthItem($auth, $roleId, 2);
            $userIds = explode(",", $_REQUEST['id']);
            foreach ($userIds as $userId) {
                if (!$authItem->isAssigned($userId)) {
                    $authItem->assign($userId);
                }
            }
        }
        if (isset($_REQUEST['ids'])) {
            $transaction = Yii::app()->db->beginTransaction();
            try {
                $auth = Yii::app()->authManager;
                $authItem = new CAuthItem($auth, $roleId, 2);
                $userIds = explode(",", $_REQUEST['ids']);
                foreach ($userIds as $userId) {
                    if ($authItem->isAssigned($userId)) {
                        $authItem->revoke($userId);
                    }
                }
                $transaction->commit();
                $jsonData = array(
                    'message' => Yii::t('system', 'Delete successful'),
                    'ids' => $_REQUEST['ids']
                );
                echo $this->successJson($jsonData);
            } catch (Exception $exc) {
                $transaction->rollback();
                $jsonData = array(
                    'message' => Yii::t('system', 'Delete failure')
                );
                echo $this->failureJson($jsonData);
            }
            Yii::app()->end();
        }
        $models = AuthAssignment::model()->getUlist($roleId);
        $this->render('ulist', array(
            'models' => $models,
        	'role' => $roleId,
        ));
    }
    
    /**
     * change user password
     */
    public function actionChange($id) {              
         if (Yii::app()->request->isAjaxRequest && isset($_POST['User'])) {
             $model = $this->loadModel($id);
             $model->attributes = $_POST['User'];
             if( $_POST['User']['new_password'] != NULL) {                
                try {
                    $model->setAttribute('user_password', $model->getCryptPassword($_POST['User']['new_password']));
                    $flag = $model->update();
                } catch (Exception $e) {                  
                    $flag = false;
                }
                if ($flag) {
                    $jsonData = array(
                        'message' => Yii::t('system', 'Save successful'),                        
                        'callbackType' => 'closeCurrent'
                    );
                    echo $this->successJson($jsonData);
                }
            } else {
                $flag = false;
            }
            if (!$flag) {
                echo $this->failureJson(array('message' => Yii::t('system', 'Save failure')));
            }
            Yii::app()->end();
         }
         $model = new User('change');
         $info = User::model()->findByPk((int) $id);
         $model->setAttribute('user_password', $info->user_password);
         $model->setAttribute('id', $info->id);
         $this->render('change', array(
            'model' => $model,
        ));
    }
    
    /**
     * change user password
     */
    public function actionReset($id) {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['User'])) {
            $model = $this->loadModel($id);
            $model->attributes = $_POST['User'];
            if( $_POST['User']['new_password'] != NULL) {
                try {
                    $model->setAttribute('user_password', $model->getCryptPassword($_POST['User']['new_password']));
                    $flag = $model->update();
                } catch (Exception $e) {
                    $flag = false;
                }
                if ($flag) {
                    $jsonData = array(
                        'message' => Yii::t('system', 'Save successful'),
                        'callbackType' => 'closeCurrent'
                    );
                    echo $this->successJson($jsonData);
                }
            } else {
                $flag = false;
            }
            if (!$flag) {
                echo $this->failureJson(array('message' => Yii::t('system', 'Save failure')));
            }
            Yii::app()->end();
        }
        $model = new User('reset');
        $info = User::model()->findByPk((int) $id);
        $model->setAttribute('user_password', $info->user_password);
        $model->setAttribute('id', $info->id);
        $this->render('reset', array(
            'model' => $model,
        ));
    }    
    
    /**
     * 复制权限
     */
    public function actionCopyauth(){
    	$uid = 0;
    	if( isset($_REQUEST['uid']) ){
    		$uid = $_REQUEST['uid'];
    	}
    	
    	if( isset($_POST['copyAuth']) && $_POST['copyAuth']==1 ){//保存权限
    		$fromId = isset($_POST['from_user_id']) ? $_POST['from_user_id'] : 0;
    		$toIds = isset($_POST['to_user_id']) ? explode(',',$_POST['to_user_id']) : array();
    		if( !$fromId || empty($toIds) ){
    			throw new CException('No User To Operate!');
    		}
    		$userInfo = UebModel::model('user')->getUserNameById($fromId);
    		//取出被选择的用户的所有操作权限
    		$resouce = UebModel::model('AuthItem')->getResourcesByRoleId($userInfo['user_name']);
    		$auth = Yii::app()->authManager;
    		$transaction = Yii::app()->db->beginTransaction();
    		try {
    			foreach($toIds as $id){
    				$userInfo = UebModel::model('user')->getUserNameById($id);
    				$authItem = new CAuthItem($auth, $userInfo['user_name'], 2);
    				$authItem->revoke($id);//解除角色绑定
    				UebModel::model('AuthItemChild')->deleteAll('parent = "'.$userInfo['user_name'].'" AND child LIKE "resource_"');//解除授权
    				foreach($resouce as $item){
    					$authItem->addChild($item);//角色添加授权
    				}
    				$authItem->assign($id);//添加角色绑定
    			}
    			$transaction->commit();
    			$jsonData = array(
    					'message' => Yii::t('system', 'Save successful'),
    					'callbackType' => 'closeCurrent'
    			);
    			echo $this->successJson($jsonData);
    		} catch (Exception $e){
    			$transaction->rollback();
    			echo $this->failureJson(array(
    					'message' => Yii::t('system', 'Save failure'))
    			);
    		}
    		Yii::app()->end();
    	}
    	if( !$uid ){
    		throw new CException('Please Choose A User');
    	}
    	$model = UebModel::model('user');
    	$this->render('copyauth', array(
    			'model' => $model,
    			'uid' 	=> $uid
    	));
    }
    
    /**
     * 获取用户ID
     */
    public function actionGetuserid(){
    	$id = Yii::app()->request->getParam('id');
    	if ( empty($id) ) exit;
    	$ids = explode(",", $id);
    	$userArr = array();
    	foreach($ids as $item){
    		$userName = MHelper::getUsername($item);
    		$userArr[] = array(
    				'id' => $item,
    				'name' => $userName, 
    		);
    	}
    	echo json_encode($userArr);
    	exit;
    }
    
    public function loadModel($id) {      
        $model = User::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        return $model;
    }
    /*
     * 查询旧密码
     * */
    public function actionQueryoldpwd(){
        $user_id = Yii::app()->request->getParam('user_id');
        $old_password = Yii::app()->request->getParam('old_password');
        $userinfo = User::model()->findByPk($user_id);
        $oldPassword = '';
        $jsonData = array(
            'msg' => '原密码有误！',
            'status' => -1,
        );
        if($userinfo->validatePassword($old_password)){
            $jsonData = array(
                'msg' => '原密码正确！',
                'status' => 1,
            );
        }
        echo $this->successJson($jsonData);
        exit;
    }
    
    //检查登录的token
    public function actionAccess(){
    	$id = Yii::app()->request->getParam('id');
    	$platformModel = new UserExtraPlatform();
    	$user = UebModel::model('User')->findByPk($id);
    	$platform = $platformModel->find('user_id=:u',array(
				':u'=>$id
		));
    	if(empty($platform)){
    		//新建
    		$data = array(
    				'user_id'=>$id,
    				'token'=>$platformModel->token($user->user_name),
    				'is_login'=>1,
    				'express_time'=>date('Y-m-d H:i:s',time()+3600*24),
    				'login_time'=>date('Y-m-d H:i:s'),
    		);
    		isset($_POST['purchase']) && $data['is_purchase'] = 1;
    		isset($_POST['wms']) && $data['is_wms'] = 1;
    		isset($_POST['customer_service']) && $data['is_customer_service'] = 1;
    		isset($_POST['logistics']) && $data['is_logistics'] = 1;
    		if($platformModel->getDbConnection()->createCommand()->insert($platformModel->tableName(), $data)){
    			echo $this->successJson(array(
    					'message'=>'授权成功'
    			));
    		}else{
    			echo $this->failureJson(array(
    					'message'=>'确认授权失败,请重试'
    			));
    		}
    	}else{
    		$platform->is_purchase = isset($_POST['purchase'])?1:0;
    		$platform->is_wms = isset($_POST['wms'])?1:0;
    		$platform->is_customer_service = isset($_POST['customer_service'])?1:0;
    		$platform->is_logistics = isset($_POST['logistics'])?1:0;
    		$platform->express_time = date('Y-m-d H:i:s',time()+3600*24);
    		if($platform->update()){
    			echo $this->successJson(array(
    					'message'=>'授权成功'
    			));
    		}else{
    			echo $this->failureJson(array(
    					'message'=>'确认授权失败,请重试'
    			));
    		}
    	}
    }


}
