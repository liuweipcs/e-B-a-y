<?php
class UserExtraPlatform extends UsersModel{
	
	const SITE_WMS = 'wms';
	const SITE_PURCHASE = 'purchase';
	const SITE_CUSTOMER_SERVICE = 'customer_service';
	const SITE_LOGISTICS = 'logistics';
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'ueb_user_extra_platform';
	}
	
	public function addLoginInfo($token){
		$model = $this->find('user_id=:u',array(
				':u'=>Yii::app()->user->id
		));
	}
	
	//手动添加token
	public function getAccessplatform($userId){
		$extra = $this->find('user_id=:u',array(
				':u'=>$userId
		));
		$result = '';
		if(!empty($extra)){
			$result =  '<form><input type="checkbox" name="purchase" value="1" ';
			$extra->is_purchase==1 && $result.= 'checked="checked"';
			$result .= '>采购系统';
			$result .=  '<input type="checkbox" name="wms" value="1"  ';
			$extra->is_wms==1 && $result .= 'checked="checked"';
			$result .= ' >wms系统';
			$result .=  '<input type="checkbox" name="customer_service" value="1"  ';
			$extra->is_customer_service==1 && $result .= 'checked="checked"';
			$result .='>客服系统';
			$result .=  '<input type="checkbox" name="logistics" value="1"  ';
			$extra->is_logistics==1 && $result .= 'checked="checked"';
			$result .='>物流系统</form>';
		}else{
			$result =  '<form><input type="checkbox" name="purchase" value="1">采购系统';
			$result .=  '<input type="checkbox" name="wms" value="1">wms系统';
			$result .=  '<input type="checkbox" name="customer_service" value="1">客服系统';
			$result .=  '<input type="checkbox" name="logistics" value="1">物流系统</form>';
		}
		return $result;
	}
	
	public function token($userName){
// 		5E17C4488C2AC591
		return sha1($userName . '5E17C4488C2AC591' . md5(microtime(true)));
	}
	
	public function checkToken($userId){
		$one = $this->find('user_id=:u',array(
				':u'=>$userId
		));
		$user = User::model()->findByPk($userId);
		if(empty($one)){
			$one = new self();
			$one->user_id = $userId;
			$one->token = $this->token($user->user_name);
			$one->is_login = 1;
			$one->login_time = date('Y-m-d H:i:s');
			$one->express_time = date('Y-m-d H:i:s',time()+3600*24);
			return $one->insert();
		}else{
			$one->token = $this->token($user->user_name);
			$one->is_login = 1;
			$one->login_time = date('Y-m-d H:i:s');
			$one->express_time = date('Y-m-d H:i:s',time()+3600*24);
			return $one->update();
		}
	}
	//手动添加token
	public function getAccessplatforms($userId,$platform){
		static $userArray;
		if(!isset($userArray[$userId])){
			$userArray[$userId] =  $this->find('user_id=:u',array(
				':u'=>$userId
			));
		}
		$result = '';
		if(in_array($platform, array(
				self::SITE_WMS,self::SITE_PURCHASE,self::SITE_LOGISTICS,self::SITE_CUSTOMER_SERVICE
		))){
			$result = '<input type="checkbox" name="'.$platform.'" value="1"';
			$key = 'is_'.$platform;
			$userArray[$userId]->$key==1 && $result.= 'checked="checked"';
			$result .='/>';
			return $result;
		}
		return $result;
	}
}