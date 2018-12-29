<?php

/**
 * @package Ueb.modules.users.controllers
 * 
 * @author Bob <Foxzeng>
 */
class RolesController extends UebController {

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


    public function actionListauth() {
		// var_dump($_POST);
		$ids=explode('_',$_POST['roleId']);
		$menuid=$ids[1];
		$roleId = AuthItem::model()->getResourcesByMenuid('menu_'.$menuid);
        $this->render('listauth',array('menuid' => $menuid,'hasroleIds' => $roleId));
    }

	public function actionListuser() {
		// var_dump($_POST);
		$ids=explode('_',$_POST['roleId']);
		$menuid=$ids[1];
		$roleId = AuthItem::model()->getResourcesByMenuid('menu_'.$menuid);
        $this->render('listuser',array('menuid' => $menuid,'hasroleIds' => $roleId));
    }
    /**
     * created a new role or a child role
     */
    public function actionCreate() {    
        $model = new Role();              
        if (isset($_GET['parent'])) {
           $model->parent = $_GET['parent'];         
        }     
        if (Yii::app()->request->isAjaxRequest && isset($_POST['Role'])) { 
            $model->attributes = $_POST['Role'];
            $model->setAttribute('name', Role::filterName($_POST['Role']['name']));
            $model->clearCache();
            if ($model->validate()) {
                $transaction = Yii::app()->db->beginTransaction();
                try {
                    $auth = Yii::app()->authManager;
                    $name = $model->getAttribute('name');
                    $auth->createRole($name, $_POST['Role']['description']);   
                    if ( 'all' != $_POST['Role']['parent'] ) {
                        $authItem = new CAuthItem($auth, $_POST['Role']['parent'], 2);
                        if (! $authItem->hasChild($name) ) {
                            $authItem->addChild($name);
                        }
                    }                    
                    $transaction->commit(); 
                    $flag = true;
                } catch (Exception $e) {
                    $transaction->rollback();
                    $flag = false;
                }        
                if ( $flag ) {                  
                    $jsonData = array(                    
                        'message' => Yii::t('system', 'Add successful'),
                        'forward' => '/users/access/index',
                        'navTabId' => 'page'.Role::getIndexNavTabId(),
                        'callbackType' => 'closeCurrent'
                    );
                    echo $this->successJson($jsonData);
                }             
            } else {               
                $flag = false;
            }
            if (! $flag) {
                echo $this->failureJson(array( 'message' => Yii::t('system', 'Add failure')));
            }
            Yii::app()->end();
        }
        $this->render('create', array('model' => $model));
    }

    /**
     * update role name or code, allow to change parent role
     * 
     * @param type $id
     */
    public function actionUpdate($id) {      
        $model = $this->loadModel($id);                 
        if (Yii::app()->request->isAjaxRequest && isset($_POST['Role'])) {          
            $model->attributes = $_POST['Role'];
            $model->setAttribute('name', Role::filterName($_POST['Role']['name']));
            if ($model->validate()) {
                $transaction = Yii::app()->db->beginTransaction();
                try {
                   $model->modifyRole($id); 
                   $transaction->commit(); 
                   $flag = true;
                } catch (Exception $e ) {
                    $transaction->rollback();                   
                    $flag = false;
                }                               
                if ($flag) {
                    $jsonData = array(                     
                        'message' => Yii::t('system', 'Save successful'),
                        'forward' => '/users/access/index',
                        'navTabId' => 'page'.Role::getIndexNavTabId(),
                        'callbackType' => 'closeCurrent'
                    );
                    echo $this->successJson($jsonData);
                }               
            } else {
                $flag = false;
            }
            if (! $flag) {
                echo $this->failureJson(array(                   
                    'message'    => Yii::t('system', 'Save failure')));
            }
            Yii::app()->end();
        } else {
           $model->parent = $model->getParentByRoleId($id);
           $model->name = $model->backFilterName($model->name); 
        }      
        $this->render('update', array('model' => $model));
    }

    public function actionDelete($id) {        
        $model= $this->loadModel($id);      
        if(Yii::app()->request->isAjaxRequest) {          
            $flag = $model->delete();
            if ( $flag ) {
                $jsonData = array(                  
                    'message' => Yii::t('system', 'Delete successful'),               
                    'navTabId' => 'page'.Role::getIndexNavTabId(),                  
                ); 
                 echo $this->successJson($jsonData);
            } else {
                $jsonData = array(                 
                    'message'    => Yii::t('system', 'Delete failure')
                ); 
                echo $this->failureJson($jsonData);
            }          
            Yii::app()->end();
        }
    }
    
    
    /**
     * users or role resources
     */
    public function actionUlist() {   
        if ( isset($_REQUEST['roleId']) ) {
            $resources = AuthItem::model()->getRoleResources($_REQUEST['roleId']);                   
        } else {
            $resources = null;         
        } 
        if ( isset($_REQUEST['resources'])) {
           $resources = empty($_REQUEST['resources']) ? array() : explode(",", $_REQUEST['resources']);          
           $auth=Yii::app()->authManager; 
           try {
               $flag = AuthItem::model()->addRoleResources($_REQUEST['roleId'], $resources);
           } catch ( Exception $e) {
               $flag = false;
           }                              
            if ( $flag ) {
                $jsonData = array(                  
                    'message' => Yii::t('system', 'Save successful'),                                                
                ); 
                 echo $this->successJson($jsonData);
            } else {
                $jsonData = array(                 
                    'message'    => Yii::t('system', 'Save failure')
                ); 
                echo $this->failureJson($jsonData);
            }           
            Yii::app()->end();          
        }
        $this->render('ulist', array( 'resources' => $resources,));
    }
    
    public function actionRdelete($id) {   
        $model = $this->loadModel($id);          
        if ( Yii::app()->request->isAjaxRequest) {          
            $flag = $model->delete();
            if ( $flag ) {
                $jsonData = array(                  
                    'message' => Yii::t('system', 'Delete successful'),               
                    'navTabId' => 'page'.  Menu::getIndexNavTabId(),                  
                ); 
                 echo $this->successJson($jsonData);
            } else {
                $jsonData = array(                 
                    'message'    => Yii::t('system', 'Delete failure')
                ); 
                echo $this->failureJson($jsonData);
            }          
            Yii::app()->end();
        }
    }


    public function loadModel($id) {       
        $model = Role::model()->findByPk($id);     
        if ($model === null)
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        return $model;
    }
   
	public function actionUserList(){
		$roleId = $_REQUEST['roleId'];
		$uList = UebModel::model('AuthAssignment')->getUlist($roleId,1);
		echo json_encode($uList);exit;
	}
}
