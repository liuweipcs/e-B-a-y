<?php

/**
 * @package Ueb.modules.OrderSet.models
 * 
 * @author ethanhu
 */
class Ebay extends SystemsModel {
   
    /**
     * Returns the static model of the specified AR class.
     * @return CActiveRecord the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
    	return 'ueb_ebay_account';
    }
    
    /**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{	      
		return array(
			array('user_name, store_name,short_name,user_token,platform', 'required'),
            array('platform','checkPlatform'),
            array('image_host','url')
		);
	}

	public function checkPlatform($attribute)
    {
        if(array_intersect($this->$attribute,array('ebay','ebayout')) == $this->$attribute)
        {
            sort($this->$attribute);
            $this->$attribute = implode('|',$this->$attribute);
        }
        else
        {
            $this->addError($attribute,'平台值填写错误');
        }
    }
	/**
	 * get index nav tab id
	 *
	 * @return type
	 */
	public static function getIndexNavTabId() {
		return Menu::model()->getIdByUrl('/systems/ebay/index');
	}
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(			
			'id'								=>Yii::t('system', 'No.'),
    		'user_name'							=>Yii::t('system', '帐号名'),
    		'store_name'						=>Yii::t('system', '店铺名称'),
			'short_name'						=>Yii::t('system', '账号名简称'),
			'user_token'						=>Yii::t('system', '用户Token'),
			'email'								=>Yii::t('system', '邮箱'),
			'email_host'						=>Yii::t('system', 'HOST'),
			'email_port'						=>Yii::t('system', 'PORT'),
			'email_password'					=>Yii::t('system', 'PASSWORD'),
			'status'							=>Yii::t('system', '状态'),
			'is_lock'							=>Yii::t('system', '锁定'),
			'group_id'							=>Yii::t('system', '分组'),
			'user_token_endtime'				=>Yii::t('system', 'TOKEN到期时间'),
            'platform'                          =>'平台',
            'image_host'                        => '图片域名',
		);
	}
	
	/**
     * filter search options
     * @return type
     */
    public function filterOptions() {
    	$result = array(
			array(
    			'label'        	=> Yii::t('system', '帐号名'),
    			'name'         	=> 'user_name',
    			'type'       	=> 'text',
    			'search'      	=> '=',
    			'htmlOptions'   => array()
    		),
			array(
    			'label'        	=> Yii::t('system', '店铺名称'),
    			'name'         	=> 'store_name',
    			'type'       	=> 'text',
    			'search'      	=> '=',
    			'htmlOptions'   => array()
    		),
			array(
    			'label'        	=> Yii::t('system', '账号名简称'),
    			'name'         	=> 'short_name',
    			'type'       	=> 'text',
    			'search'      	=> '=',
    			'htmlOptions'   => array()
    		),
			array(
				'label'         => Yii::t('system', '状态'),
         		'name'          => 'status',
         		'type'          => 'dropDownList',
         		'search'        => '=',
         		'data'          => UebModel::model('Ebay')->getEbayAccountStatus(),
         		'htmlOptions'   => array(),
				'alias'			=> 't',
			),
			array(
				'label'         => Yii::t('system', '锁定'),
         		'name'          => 'is_lock',
         		'type'          => 'dropDownList',
         		'search'        => '=',
         		'data'          => UebModel::model('Ebay')->getEbayAccountLock(),
         		'htmlOptions'   => array(),
				'alias'			=> 't',
			),
			/*array(
				'label'         => Yii::t('system', '分组'),
         		'name'          => 'group_id',
         		'type'          => 'dropDownList',
         		'search'        => '=',
         		'data'          => UebModel::model('Ebay')->getEbayAccountGroup(),
         		'htmlOptions'   => array(),
				'alias'			=> 't',
			),*/
		);
    	$this->addFilterOptions($result);
    	
    	return $result;
    }
    
    public function addFilterOptions(&$result){
    	
    }
     /**
     * get search info
     */
    public function search() {
    	$sort = new CSort();
    	$sort->attributes = array(
    			'defaultOrder'  => 'short_name',
    			'id',
    	);
    	$dataProvider= parent::search(get_class($this), $sort);
    	return $dataProvider;
    }
	/**
     * order field options
     * @return $array
     */
    public function orderFieldOptions() {
    	return array(
    			'id','user_name','short_name'
    	);
    }
	
	public function getEbayAccountStatus($type=null){
		$typeArr=array(
    		1 => Yii::t('system', '启用'),
    		2 => Yii::t('system', '停用')
    	);
    	if($type!=null){
    		return $typeArr[$type];
    	}else{
    		return $typeArr;
    	}
	}
	public function getEbayAccountLock($type=null){
		$typeArr=array(
    		1 => Yii::t('system', '是'),
    		2 => Yii::t('system', '否')
    	);
    	if($type!=null){
    		return $typeArr[$type];
    	}else{
    		return $typeArr;
    	}
	}
	public function getEbayAccountGroup($groupId=null){
        if($groupId!=null){
            return UebModel::model('EbayAccountGroup')->getGroup($groupId);
        }else{
            return UebModel::model('EbayAccountGroup')->getGroupList();
        }
	}
	public function getEbayAccountInfo($user_name){
		return $this->getDbConnection()->createCommand()
    	->select('user_name')
    	->from(self::tableName())	
    	->where("user_name='{$user_name}'")
    	->queryRow();
	}

    public static function platformCondition(array $array)
    {
        sort($array);
        $string = implode('|',$array);
        switch($string)
        {
            case 'ebay':
                return 'platform in ("ebay","ebay|ebayout")';
            case 'ebayout':
                return 'platform in ("ebayout","ebay|ebayout")';
            case 'ebay|ebayout':
                return 'platform in ("ebay","ebayout","ebay|ebayout")';
            default:
                return null;
        }
    }

    //是否已设置销售计划
    public function isSetNotification()
    {
        $ebayNotificationPreferences = UebModel::model('EbayNotificationPreferences')->find('account_id='.$this->id);
        if(empty($ebayNotificationPreferences))
        {
            return false;
        }
        else
        {
            return $ebayNotificationPreferences->isSetNotification();
        }
    }

    public function getAllAccountList()
    {
        return array_column(VHelper::selectAsArray($this,'id,user_name','status=1',true,'','user_name ASC'),'user_name','id');
    }

}