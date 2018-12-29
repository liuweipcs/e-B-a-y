<?php

class SiteController extends UebController {

    public $layout = 'main';
    public $id;
    
    public $accessIP = array('127.0.0.1', '172.16.*', '192.168.*');

    /**
     * Declares class-based actions.
     */
    public function actions() {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class'         => 'CCaptchaAction',
                'backColor'     => 0xFFFFFF,
                'minLength'     => 4,  //min length
                'maxLength'     => 4,   //max length
                'testLimit'     => 3,
                'transparent'   => true, 
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        $this->layout = false;
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    public function actionCheck(){
    	$user_name=Yii::app()->request->getParam('user_name');
    	if ($user_name){
    		$model=UebModel::model('User');
    		$data=$model->find('user_name=:user_name',array(':user_name'=>$user_name));
    		
	    		if(!empty($data)){
		    			if($data->is_intranet==0){
		    				$josnData = array(
		    						//'msg'=>'你要在右侧内网登录!',
									'msg'=>'你的ERP帐号不允许在公司以外的网络登录，如有需要，请联系上级主管开通外网登录权限。',
		    						'status'=>1
		    				);
		    			}else {
		    				$josnData = array(
		    						'msg'=>'通过!',
		    						'status'=>2
		    				);
		    			}
	    		}else {
	    			$josnData = array(
	    					'msg'=>'账号不存在!!',
	    					'status'=>3
	    			);
	    		}
    		}
    		
    		echo json_encode($josnData);exit;
    	
    }
    /**
     * Displays the login page
     */
    public function actionLogin() {
    	if(!$this->ipAccess()){
    		echo 'error';exit;
    	}
		
        $this->layout = '//layouts/login';
        if (!defined('CRYPT_BLOWFISH') || !CRYPT_BLOWFISH) {
             throw new CHttpException(500, "This application requires that PHP was compiled with Blowfish support for crypt().");
        }    
        if(Yii::app()->user->isInitialized && !Yii::app()->user->isGuest){           
            $this->redirect(Yii::app()->homeUrl);
            return;
        }
        $model = new LoginForm;    
        $model->useCaptcha = false;
        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        // collect user input data
        if (isset($_POST['LoginForm'])) {  
            $model->attributes = $_POST['LoginForm'];
            
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login())
// 				if(Yii::app()->user->is_intranet==0){
// // 					Yii::app()->user->logout();
// // 					$this->redirect('http://192.168.10.17/index.php');
// 					$error = array('message'=>'请到右侧内网地址登录');
//                     echo $error['message'];
//                     Yii::app()->end();
//             	}
                $this->redirect(Yii::app()->user->returnUrl);
        }
//         // display the login form
         $this->render('login', array('model' => $model));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        Yii::ulog(
            Yii::t('excep', 'The user:{user_name} logout success.', array('{user_name}' =>  Yii::app()->user->name)), 
            Yii::t('system', 'Logout'),    
            'operation', 
            ULogger::LEVEL_SUCCESS                     
        );
        Yii::app()->user->logout();        
        $this->redirect(Yii::app()->homeUrl);
    }

    // display the index form
    public function actionIndex() {  
    	if(!$this->ipAccess()){
    		echo 'error';exit;
    	}
    	
        if ( isset($_SESSION['registerScript']) ) {
            unset($_SESSION['registerScript']);
        }
        if ( Yii::app()->user->isGuest ) {
            $this->redirect(array('/site/login'));
        }
        //===========get login role config ===============//
        $LoginUserRoles = User::getLoginUserRoles();      
          
        $dids=array();
        if (user::isAdmin()){       	
            $dids = DashBoardRole::getDashboardByAdmin();   //get admin dashboard config           
        }else{       	
            $dids = DashBoardRole::getDashboardByRoleId($LoginUserRoles);   //get role dashboard config
        }
        $userConfig = DashBoardRole::getUserDashboardConfig(); //get user dashboard config       
        $areas=array();
        $showIds=array();
        $i=1;
      
        foreach ($dids as $did){
        	$showIds[]=$did->dashboard_id;
            //===========get login user config ===============//
            if (isset($userConfig['areaConfig']['hidd']) && in_array($did->dashboard_id, $userConfig['areaConfig']['hidd']) ){  //don't show dashboard in 'hidd' config
            	continue;
            }            
            $at='';     //左右显示
            $sort=$i;   //排序            
            if (isset($userConfig['areaConfig']['sort'][$did->dashboard_id])){       	
//                echo '<pre>';print_r($userConfig['areaConfig']['sort']);exit;
                if(isset($userConfig['areaConfig']['sort'][$did->dashboard_id]['left']) && $userConfig['areaConfig']['sort'][$did->dashboard_id]['left'] == 222){               
                	$at = 'left';
                }else{               
                    $at = 'right';
                }
                $sort = isset($userConfig['areaConfig']['sort'][$did->dashboard_id]['top']) ? $userConfig['areaConfig']['sort'][$did->dashboard_id]['top'] : $i;
            }else{            
                $at='left';
                if ($i%2 == 0) $at='right';
            }          
            $areas[$at][$sort] = DashBoard::model()->findByPk($did->dashboard_id);                      
            ksort($areas[$at]);
            $i++;
        }
             
        //===========get all databoard to show===============//
        //right config list area        
        $criteria =new CDbCriteria;
       	$criteria->addCondition("status=".DashBoard::DASHBOARD_STATUS_YES);
       	$criteria->addCondition("is_global=".DashBoard::DASHBOARD_GLOBAL_NO);       
        $criteria->addInCondition('id', $showIds);       		
        $dataProvider=new CActiveDataProvider('dashboard', array(
            'criteria'=>$criteria,
        ));             
        $this->render('index',array(
            'dataProvider'=>$dataProvider,
            'areas'=>$areas,       	
        ));
    }
    
    public function ipAccess(){
	/*
    	$userIP = Yii::app()->request->userHostAddress;
    	$accessIP = $this->accessIP;
    	$access = false;
    	foreach($accessIP as $ip){
    		if( strpos($userIP, str_replace('*','',$ip))!==false ){
    			$access = true;
    			break;
    		}
    	}
    	return $access;
		*/
		return true;
    }
}