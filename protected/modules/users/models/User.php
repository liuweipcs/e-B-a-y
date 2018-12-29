<?php

class User extends UsersModel
{
    public $new_password = null;

    public $role_mark = 'roleSelf';//个人角色识别标志
    public $onUser = 1;
    public $stopUser = 0;
    public $intranet= 1;
    public $not_intranet = 0;
    public $old_password = '';
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ueb_user';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
                    array('new_password', 'required', 'on' => 'change'),
                    array('user_name,en_name,user_full_name,user_password,department_id','required'),
                    array('user_status,user_email,user_tel,is_intranet','safe'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			//'posts' => array(self::HAS_MANY, 'Post', 'author_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
            //'id'                => Yii::t('system', 'No.'),
	    	'user_password'     => Yii::t('system', 'Password'),
            'new_password'      => Yii::t('users', 'New Password'),
            'user_name'         => Yii::t('system', 'Login Name'),
			'en_name'         	=> Yii::t('users', 'Englist Name'),
            'user_full_name'    => Yii::t('system', 'Full Name'),
            'user_email'        => Yii::t('system', 'Email'),
            'user_tel'          => Yii::t('system', 'Tel'),
            'user_status'       => Yii::t('system', 'Status'),
            'department_id'     => Yii::t('system', 'Department'),
			'is_intranet'=>'外网访问',
			'intranet'	=>Yii::t('user', '允许'),
			'not_intranet'	=>Yii::t('user', '不允许'),
            'old_password'	=>Yii::t('user', '原密码'),
		);
	}

    /**
     * get search info
     */

    public function search($condition = null)
    {
        $sort = new CSort();
        $sort->attributes = array(
            'defaultOrder'  => 'id',
            'user_name',
        );
        return parent::search(get_class($this), $sort,array(),$this->_setCDbCriteria($condition));
    }

    public function _setCDbCriteria($condition = null)
    {
        $criteria = new CDbCriteria;
        if(is_string($condition)){
            $criteria->addCondition($condition);
        }
        return $criteria;
    }


	/**
	 * Checks if the given password is correct.
	 * @param string the password to be validated
	 * @return boolean whether the password is valid
	 */
	public function validatePassword($password)
	{
		return crypt($password,$this->user_password)===$this->user_password;
	}

	/**
	 * Generates the password hash.
	 * @param string password
	 * @return string hash
	 */
	public function hashPassword($password)
	{
		return crypt($password, $this->generateSalt());
	}

	/**
	 * Generates a salt that can be used to generate a password hash.
	 *
	 * The {@link http://php.net/manual/en/function.crypt.php PHP `crypt()` built-in function}
	 * requires, for the Blowfish hash algorithm, a salt string in a specific format:
	 *  - "$2a$"
	 *  - a two digit cost parameter
	 *  - "$"
	 *  - 22 characters from the alphabet "./0-9A-Za-z".
	 *
	 * @param int cost parameter for Blowfish hash algorithm
	 * @return string the salt
	 */
	protected function generateSalt($cost=10)
	{
		if(!is_numeric($cost)||$cost<4||$cost>31){
			throw new CException(Yii::t('Cost parameter must be between 4 and 31.'));
		}
		// Get some pseudo-random data from mt_rand().
		$rand='';
		for($i=0;$i<8;++$i)
			$rand.=pack('S',mt_rand(0,0xffff));
		// Add the microtime for a little more entropy.
		$rand.=microtime();
		// Mix the bits cryptographically.
		$rand=sha1($rand,true);
		// Form the prefix that specifies hash algorithm type and cost parameter.
		$salt='$2a$'.str_pad((int)$cost,2,'0',STR_PAD_RIGHT).'$';
		// Append the random salt string in the required base64 format.
		$salt.=strtr(substr(base64_encode($rand),0,22),array('+'=>'.'));
		return $salt;
	}

    /**
     * change user password
     *
     * @param type $password
     * @return type
     */
    public function getCryptPassword($password) {
        return crypt($password, crypt($password));
    }

     public function filterOptions() {
        return array(
            array(
                'label'         => Yii::t('users', 'Username'),
                'name'          => 'user_name',
                'type'          => 'text',
                'search'        => 'LIKE',
                'prefix'        => true,
                'htmlOptions'   => array(),
            ),
        	array(
        		'label'         => Yii::t('system', 'Full Name'),
        		'name'          => 'user_full_name',
        		'type'          => 'text',
        	    'prefix'        => true,
        		'search'        => 'LIKE',
        		'htmlOptions'   => array(),
        	),
        	array(
                'name'          => 'department_id',
                'type'          => 'dropDownList',
                'search'        => '=',
        		'data'			=> UebModel::model('Department')->getDepartment(),
				'value'			=> '',
                'htmlOptions'   => array(),
            ),
        	array(
        		'name'          => 'user_status',
        		'type'          => 'dropDownList',
        		'value'			=> Yii::app()->request->getParam('user_status'),
        		'search'        => '=',
        		'data'			=> UebModel::model('user')->getUserStatusList(),
        		'htmlOptions'   => array(),
        	),
        		array(
        				'name'          => 'is_intranet',
        				'type'          => 'dropDownList',
        				'value'			=>  UebModel::model('user')->is_intranet,
        				'search'        => '=',
        				'data'			=> UebModel::model('user')->getUserIs_intranet(),
        				'htmlOptions'   => array(),
        		),
        );
    }

    /**
     * order field options
     * @return $array
     */
    public function orderFieldOptions() {
    	return array(
    			'user_name','user_status'
    	);
    }

    /**
     * get page list
     *
     * @return array
     */
    public function getPageList() {
        $this->_initCriteria();
        if (isset($_REQUEST['department_id'])) {
            $departmentId = $_REQUEST['department_id'];
        }
        if (! empty($_REQUEST['user_name']) ) {
            $userName = trim($_REQUEST['user_name']);
            $this->criteria->addCondition("user_name = '{$userName}'");
        }
        $this->_initPagination( $this->criteria);
        $models = $this->findAll($this->criteria);
        return array($models, $this->pages);
    }

    public function getUlist() {
        $this->_initCriteria();
        if (! empty($_REQUEST['user_name']) ) {
            $userName = trim($_REQUEST['user_name']);
            $this->criteria->addCondition("user_name = '{$userName}'");
        }
        $models = $this->findAll($this->criteria);
        return $models;
    }

    /**
     * get user list by department id
     */
    public function getUlistByDepId($depId) {
    	$models = $this->findAllByAttributes(array('department_id'=>$depId));
    	return $models;
    }

    /*
     * 根据部门id获取部门及下级部门的所有成员
     * @param $dep int|string 部门id或部门名称department_name
     * @return array 查询不到时，返回空数组array()
    */
    public function getAllMemberByDepId($dep)
    {
        $departmentModel = UebModel::model('Department');
        if(!is_numeric($dep))
        {
            $dep = $departmentModel->find('department_name=:department_name',array(':department_name'=>$dep))->id;
            if(empty($dep))
                return array();
        }
        $childrenDep = $departmentModel->getChildById($dep,true);
        if(empty($children))
        {
            $condition = "department_id=$dep and user_status=1";
        }
        else
        {
            $depIds = array_merge(array($dep),array_column($childrenDep,'id'));
            $condition = "department_id in ('".implode("','",$depIds)."') and user_status=1";
        }
        return VHelper::selectAsArray($this,'*',$condition);
    }

    public function checkuser($username){
		$row = $this->getDbConnection()->createCommand()
			->select('*')
			->from(self::tableName())
			->where("user_name = '".$username."' or en_name = '".$username."'")
			->queryRow();
		return $row['user_status'];
	}

     public function getAllname(){
        $result = UebModel::model('User')->findAll();
        $user = array(''=>'请选择');
        foreach ($result as $key => $value) {
            $user[$value->id] = $value->user_name;
        }
        //var_dump($user);
       
        return $user;
    }
    /**
     * check if it is a super user
     *
     * @return bool
     */
    public static function isAdmin() {
		if(Yii::app()->user->name == 'admin' || Yii::app()->user->name == 'Fox' || Yii::app()->user->name == '庄俊超' || Yii::app()->user->name == 'zhuang'){
			return true;
		}
        return false;
    }

    /**
     *
     * user status list
     */
    public function getUserStatusList(){
    	$status=array(
    		$this->onUser	=>Yii::t('system', 'Enable'),
    		$this->stopUser	=>Yii::t('system', 'Disable'),
    	);
    	return $status;
    }
    public function getUserIs_intranet(){
        $is_intranet=array(
    		$this->intranet	=>Yii::t('user', '允许'),
    		$this->not_intranet	=>Yii::t('user', '不允许'),
    	);
    	return $is_intranet;
    }
    /**
     * get login user roles
     *
     * @return array
     */
    public static function getLoginUserRoles($uid=null){
		$userId = Yii::app()->user->id;
		if($uid !==null) $userId = $uid;
        return Yii::app()->db->createCommand()
			->select('itemname')
			->from(AuthAssignment::model()->tableName())
			->where("userid = '{$userId}'")
			->queryColumn();
    }
    /**
     * batch change user the status
     * @param warehouseIds $oprationIds
     */
    public function changeUserStatus($oprationIds,$beginUsing=true){
    	$updateData = array(
    			'user_status' => $beginUsing ? $this->onUser : $this->stopUser,
    	);
    	$flag = $this->updateAll($updateData,"id IN (".$oprationIds.")");
    	return $flag;
    }
    /**
     * get id - user name map
     *
     * @return array
     */
    public function getPairs() {
        return UebModel::model('user')
                ->queryPairs('id,user_full_name');
    }
    public static function getIndexNavTabId() {
        return Menu::model()->getIdByUrl('/users/users/index');
    }

    public function getUserNameById($id){
    	return Yii::app()->db->createCommand()
					->select('*')
					->from(self::tableName())
					->where("id = ".$id)
					->queryRow();
    }
    public static function getNameById($id){
        if(!$id){
            return '-';
        }else {
            $result= UebModel::model('User')->findByPk($id)->user_name;
            return $result;
        }
        
    }

	/**
	 * 根据id取用户名用于字段显示
	 * @param $id
	 * @return array
	 */
	public function getUserNameAndFullNameById($id,$type=''){
	    if($type=='uname'){
            $user = $this->getUserNameById($id);
            return array($id => $user['user_name']);
        }else{
            $user = $this->getUserNameById($id);
            $userName = empty($user)? '':( empty($user['user_full_name']) ? $user['user_name']:$user['user_name'].'('.$user['user_full_name'].')');
            return array($id => $userName);
        }
	}

    /**
     * 显示erp登录者和部门
     * @param $uid
     * @return string
     */
	public static function getUserNameAndDepartmentById($user){
	    if($user){
            if (is_numeric($user)){
                $userinfo=User::model()->findByPk($user);
                if($userinfo){
                    $department = UebModel::model("Department")->getDepartment($userinfo->department_id);
                    $ud=$userinfo->user_full_name.'('.$department.')';
                }else{
                    return $ud=$user;
                }
            }else{
                $userinfo=User::model()->find('user_full_name=:ufn',[':ufn'=>$user]);
                if($userinfo){
                    $department = UebModel::model("Department")->getDepartment($userinfo->department_id);
                    $ud=$userinfo->user_full_name.'('.$department.')';
                }else{
                    $ud=$user;
                }
            }
            return $ud;
        }else{
	        return '';
        }
    }

    public function getUserNameArrById($id){
    	$result = array();
    	$data = Yii::app()->db->createCommand()
					->select('id,user_full_name')
					->from(self::tableName())
					->where("id = ".$id)
					->queryRow();
    	if ($data) $result[$data['id']] = $data['user_full_name'];
    	return $result;
    }

    public function getUserIdByName($name){
    	$user_info =  Yii::app()->db->createCommand()
					->select('id')
					->from(self::tableName())
					->where("user_name = '".$name."' or en_name = '".$name."'")
					->queryRow();
		return $user_info['id'];
    }

    public function getUserFullNameByName($name){
    	$user_info =  Yii::app()->db->createCommand()
    	->select('*')
    	->from(self::tableName())
    	->where("user_name = '".$name."' or en_name = '".$name."'")
    	->queryRow();
    	return $user_info['user_full_name'];
    }

    public function getIdByUserName($userName){
        $data = $this->findAll('user_name=:user_name', array(':user_name'=>$userName));
                if($data){
                    foreach ($data as $key=>$val){
                        $list[$val['id']]=$val['id'];
                    }
                }
                return $list;
    }

    /**
     * wms 包裹出货传入英文名转换成id
     * @return unknown
     */
    public function getIdByUserEnName($enName){
    	$data = $this->findAll('en_name=:en_name', array(':en_name'=>$enName));
    	if(count($data)>1 || empty($data))return '';
    	return $data[0]['id'];
    }

	public function SaveUserInfoLog($user,$str){
		if($user == 'admin'){
			file_put_contents('./upload/excel/admin.log', var_export($str,true));
		}
	}

	/**
     * 保存用户
     *
     */
    public function saveUserInfo($username){
		$result= $this->getDbConnection()
    	->createCommand()
    	->select('id')
    	->from(self::tableName())
    	->where("user_full_name = '".$username."' or user_name='".$username."'")
    	->queryRow();
    	if($result){
    		return $result['id'];
    	}else{
			$model = new self();
			$model->setAttribute('user_name', $username);
			$model->setAttribute('en_name', $username);
			$model->setAttribute('user_password', $username);
			$model->setAttribute('user_full_name', $username);
			$model->setAttribute('user_email', '');
			$model->setAttribute('user_tel', '');
			$model->setAttribute('department_id', 0);
			$model->setAttribute('user_status', 1);
			$model->setIsNewRecord(true);
			$model->save();
			return $model->id;
		}
    }

    public function getSelectByDepartments(array $ids){
    	return $this->queryPairs('id,user_name',
				array('IN','department_id',$ids)
    			);
    }

    /**
     * @param array $ids
     * @return type
     * @desc 根据ID数组得到用户列表
     */
    public function getUserListByIdArr(array $ids){
        return $this->queryPairs('id,user_full_name',
            array('IN','id',$ids)
        );
    }
}