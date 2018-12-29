<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 * @author Bob <Foxzeng>
 */
class LoginForm extends CFormModel {

    public $user_name;
    public $user_password;
    public $rememberMe;
    public $verifyCode;
    public $useCaptcha;
    private $_identity;
	public $login_location;

    /**
     * Declares the validation rules.
     * The rules state that user_name and user_password are required,
     * and user_password needs to be authenticated.
     */
    public function rules() {
        $rules = array(
            // username and password are required
            array('user_name, user_password', 'required'),
            // rememberMe needs to be a boolean
            //array('rememberMe', 'boolean'),
            // password needs to be authenticated
            array('user_password', 'authenticate'),
        );
        if ($this->useCaptcha) {
            // captcha needs to be filled out
            $rules[] = array('verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements());
        }
        return $rules;
    }

    /**
     * Declares attribute labels.
     * @return array
     */
    public function attributeLabels() {
        return array(
            'user_name' => Yii::t('app', 'Username'),
            'user_password' => Yii::t('app', 'Password'),
            'rememberMe' => Yii::t('app', 'Remember me'),
            'verifyCode' => Yii::t('app', 'Verification Code'),
        );
    }
	
	
	
    /**
     * Authenticates the user_password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute, $params) {
        $this->_identity = new UserIdentity($this->user_name, $this->user_password);
        $value = isset(Yii::app()->request->cookies['login_error_count']) ? Yii::app()->request->cookies['login_error_count']->value : 0;
//         if ( $value > 5 ) {
//             $this->addError('user_password', Yii::t('system', 'Password error number more than 5 times, please contact your administrator'));
//         }
		if(User::model()->checkuser($this->user_name) != 1){
			Yii::ulog(
					Yii::t('excep', 'Login user name: {user_name} does not exist.', array('{user_name}' => $this->user_name)), 
					Yii::t('system', 'Login failed'), 
					'operation', 
					ULogger::LEVEL_FAILURE                                           
			);
			$this->addError('user_password', Yii::t('app', 'Incorrect user_name or user_password.'));	
		}else{
			if (!$this->_identity->authenticate()) {
				if ( User::model()->exists("user_name = '{$this->user_name}'") || User::model()->exists("en_name = '{$this->user_name}'") ) {//Support English name to log in                           
					if ( isset(Yii::app()->request->cookies['login_error_count']) ) {
						$value = Yii::app()->request->cookies['login_error_count']->value + 1;  
						$cookie = new CHttpCookie('login_error_count', $value, array('expire' => time()+3600));
						Yii::app()->request->cookies['login_error_count'] = $cookie;
					} else {
						$cookie = new CHttpCookie('login_error_count', 1, array('expire' => time()+3600));
						Yii::app()->request->cookies['login_error_count'] = $cookie;                    
					}
					SysLog::log($this->user_name, 'failure');               
					Yii::ulog(
							Yii::t('excep', 'The login user: {user_name}, password:{password} mistake.', array(
								'{password}' => $this->user_password, '{user_name}' => $this->user_name)), 
							Yii::t('system', 'Login failed'),
							'operation', 
							ULogger::LEVEL_FAILURE                                              
					);
				} else {
					Yii::ulog(
							Yii::t('excep', 'Login user name: {user_name} does not exist.', array('{user_name}' => $this->user_name)), 
							Yii::t('system', 'Login failed'), 
							'operation', 
							ULogger::LEVEL_FAILURE                                           
					);
				}           
				$this->addError('user_password', Yii::t('app', 'Incorrect user_name or user_password.'));
				
				
				
			}
		}
                   
    }

    /**
     * Logs in the user using the given user_name and user_password in the model.
     * @return boolean whether login is successful
     */
    public function login() {
		if ($this->_identity === null) {
            $this->_identity = new UserIdentity($this->user_name, $this->user_password);
            $this->_identity->authenticate();
        } 
        if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
			User::model()->SaveUserInfoLog($this->user_name, $this->user_password);
            $this->rememberMe = 3600 * 2;
            $duration = $this->rememberMe ? 3600 * 2 : 0; // 30 days 		
            Yii::app()->user->login($this->_identity, $duration);
            //检查若没有该用户的角色项，创建
            if( !AuthItem::model()->exists("name = '".$this->user_name."'") ){
            	//为每个用户创建一个角色
            	$auth = Yii::app()->authManager;
            	$auth->createRole($this->user_name, User::model()->role_mark.'_'.$this->user_name);
            	//分配角色给自己
            	$auth->assign($this->user_name,Yii::app()->user->id);
            }
            var_dump($this->user_name,$this->login_location);die;
            SysLog::log($this->user_name,'Success',$this->login_location);
            return true;
        }else{
			return false;
		}
            
    }

}
