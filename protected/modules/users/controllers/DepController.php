<?php

/**
 * @package Ueb.modules.users.controllers
 * 
 * @author ethnahu
 */
class DepController extends UebController {

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function depRules() {
        return array();
    }

    public function actionIndex() {
      $this->render('index');
    }      

    /**
     * created a new role or a child role
     */
    public function actionCreate() {    
        $model = new Dep();
        if (isset($_GET['parent'])) {
           $model->parent = $_GET['parent'];
        }
        if (Yii::app()->request->isAjaxRequest && isset($_POST['Dep'])) { 
            $model->attributes = $_POST['Dep'];
            $model->setAttribute('department_parent_id', Dep::filterName($_POST['Dep']['parent']));
            $model->setAttribute('department_description', Dep::filterName($_POST['Dep']['department_description']));
            $model->setAttribute('department_status', 1);
           
            if($_POST['Dep']['parent']==0){
            	$model->setAttribute('department_level', 1);
            	$max_order = $model::get_max_order($_POST['Dep']['parent']);
            	$model->setAttribute('department_order', $max_order + 1);
            }else{
            	$model->setAttribute('department_level', 2);
            	$model->setAttribute('department_order', 1);
            }
            if ($model->validate()) {
                $transaction = Yii::app()->db->beginTransaction();
                try {
                	$model->save();
                    $auth = Yii::app()->authManager;
                    $transaction->commit();
                    $flag = true;
                } catch (Exception $e) {
                    $transaction->rollback();
                    $flag = false;
                } 
                if ( $flag ) {                  
                    $jsonData = array(                    
                        'message' => Yii::t('system', 'Add successful'),
                        'forward' => '/users/users/index',
                        'navTabId' => 'page'.Dep::getIndexNavTabId(),
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
    
    public function actionUpdate($id) {
    	$model = $this->loadModel($id);
    	if (Yii::app()->request->isAjaxRequest && isset($_POST['Dep'])) {
    		$model->attributes = $_POST['Dep'];
    		$model->setAttribute('department_description', trim($_POST['Dep']['department_description']));
    		$model->setAttribute('department_parent_id', trim($_POST['Dep']['parent']));
    		//查找父部门
    		$parentDep = UebModel::model('Dep')->find("id = " . (int)$_POST['Dep']['parent']);
    		$level = 0;
    		if (!empty($parentDep))
    		    $level = $parentDep->department_level + 1;
    		$model->setAttribute('department_level', $level);
    		if ($model->validate()) {
    			$flag = $model->save();
    			if ($flag) {
    				$jsonData = array(
    						'message' => Yii::t('system', 'Save successful'),
    						'forward' => '/users/users/index',
    						'navTabId' => 'page' . Dep::getIndexNavTabId(),
    						'callbackType' => 'closeCurrent'
    				);
    				echo $this->successJson($jsonData);
    			}
    			
    		} else {
    			$flag = false;
    		}
    		if (!$flag) {
    			echo $this->failureJson(array(
    					'message' => Yii::t('system', 'Save failure')));
    		}
    		Yii::app()->end();
    	} else {
    		$cur_arr = $model->getParentBydepId($id);//['department_parent_id'];
    		$model->parent_id = $cur_arr['department_parent_id'];
            $model->id = $id;
            
        } 
        $this->render('update', array('model' => $model));
    }
    

    public function actionDelete($id) {  
    	$model= $this->loadModel($id);      
        if(Yii::app()->request->isAjaxRequest) {
        	//判断是否有下级
        	$arr = $model->getchilddep($id);
        	if($arr['num']>0) {
        		echo $this->failureJson( array('message' => Yii::t('users', 'Dep required')) );
        		Yii::app()->end();
        	}
        	$flag = false;
        	$flag = $model->delete();
            if ( $flag ) {
                $jsonData = array(
                    'message' => Yii::t('system', 'Delete successful'),               
                    'navTabId' => 'page'.Dep::getIndexNavTabId(),                  
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


    public function loadModel($id) {       
        $model = Dep::model()->findByPk($id);     
        if ($model === null)
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        return $model;
    }
   

}
