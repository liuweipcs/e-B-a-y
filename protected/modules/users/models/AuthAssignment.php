<?php

class AuthAssignment extends UsersModel
{
	const ROLE_PURCHASE 		= 'purchaser';//跟单人
	const EBAY_USER				='ebay_user';	  //市场专员
	const PRODUCT_DEVELOPERS	='directordev';//产品开发人员
	const PURCHASE_PRICE_USERS	='purchase_price_user';//成本人格化人员	
	const ROLE_WAREHOUSE_PEOPLE = 'inventory_people'; //盘点人
	const ROLE_ACCOUNTING 		= 'accounting'; //会计	
	const ROLE_CASHIER 			= 'cashier'; //出纳	
	const ROLE_RECIEVE_PACK 	= 'recieve_pack_user';//物流部收货包装
	const ROLE_RECIEVE_CONFIRM	= 'recieve_confirm_user';//物流部收货人
	const PRODUCT_HAI_DEVELOPERS	='haiwaicangdev';//海外仓房产品开发人员
	
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
		return 'ueb_auth_assignment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{	
		return array();
	}
	

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array();
	} 
     
    
    /**
     * get user list
     *
     * @param type $roleId
     * * @$type >0 return mutlit array('userid'=>'user_name');
     * @return type
     */
    public static function getUlist($roleId,$type=0) {
   
    	$data = array();
    	$joinTable = User::model()->tableName();
    	$selectObj = Yii::app()->db->createCommand()
    	->select('*')
    	->from(self::tableName().' a')
    	->join( $joinTable .' u', "a.`userid` = u.id")
    	->where("u.user_status = 1");
    	if ( is_array($roleId) ) {
    		$selectObj->andWhere(array('In', 'itemname', $roleId));
    	} else {
    		$selectObj->andWhere(" itemname = '{$roleId}'");
    	}
    	$data = $selectObj->queryAll();
    	if($type){
    		if($data){
    			$arr = $data;
    			unset($data);
//     			$data = array(''=>Yii::t('system','Please Select'));
    			$data = array();
    			foreach($arr as $key=>$val){
    				$data[$val['userid']] =$val['user_full_name'];
    			}
    			unset($arr);
    		}
    	}
    	return $data;
    }
    
	public static function getAuthUserInfo($userid){
		 return Yii::app()->db->createCommand() 
			->select('*')
			->from(self::tableName())
			->where("userid = '{$userid}'")        
			->queryAll();
	}
	
    /**
     * get user list
     * 
     * @param type $roleId
     * @return type
     */
    public static function getUserIdsByRoleId($roleId) {
        $joinTable = User::model()->tableName();
        $selectObj = Yii::app()->db->createCommand() 
			->select('u.id')
			->from(self::tableName().' a')	
            ->join( $joinTable .' u', "a.`userid` = u.id")
            ->where("u.user_status = 1");     
        if ( is_array($roleId) ) {
            $selectObj->andWhere(array('In', 'itemname', $roleId));  
        } else {
            $selectObj->andWhere(" itemname = '{$roleId}'");
        }        
        $list = $selectObj->order("u.user_name Asc")
			->queryColumn();  
        
        return $list;
    }
}