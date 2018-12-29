<?php
/**
 * @desc Ebay Message
 * @author Gordon
 * @since 2015-04-29
 */
class EbayMessage{
	
	const TYPE_EUB_GIFT = 1;//调用eub gift模板
	
	public $_tpl = array();
	
	public function __construct(){
		$this->_tpl = array(
				self::TYPE_EUB_GIFT => 'EUB GIFT模板',
		);
	}
	
	/**
	 * 根据订单号发送站内信
	 * @param string $orderId
	 */
	public function sendMessageByOrderId($orderId, $param=array()){
		if( !empty($param) ){
			extract($param);
		}
		if( !isset($itemID) ) $itemID = '';//Itemid
		if( !isset($subject) ) $subject = '';//主题
		if( !isset($sendContent) ) $sendContent = '';//发送内容
		if( !isset($tpl) ) $tpl = 0;//调用模板
		$orderInfo = UebModel::model('OrderEbay')->dbConnection->createCommand()->select('*')->from(OrderEbay::model()->tableName())->where('order_id = "'.$orderId.'"')->queryRow();
		if( empty($orderInfo) ){
			return false;
		}
		if(!$itemID){
			$detail = UebModel::model('orderEbayDetail')->dbConnection->createCommand()->select('item_id')->from(OrderEbayDetail::model()->tableName())->where('order_id = "'.$orderId.'" AND item_id != ""')->queryRow();
			$itemID = $detail['item_id'];
		}
		$accountInfo = UebModel::model('ebayAccount')->getAccountInfoById($orderInfo['account_id']);
		if( is_numeric($tpl) &&  in_array($tpl,array_keys($this->_tpl)) ){
			$tplObj = new MessageTpl($tpl);
			$sendContent = $tplObj->_content;
			$subject = $tplObj->_subject;
		}
		return $this->sendMessage($orderInfo['buyer_id'], $itemID, $accountInfo, $subject, $sendContent);
	}
	
	/**
	 * @desc 发送ebay站内信
	 * @author Gordon
	 * @since 2015-04-29
	 */
	public function sendMessage($buyerId,$itemID,$accountInfo,$subject,$content){
		$siteId = 0;
		$messageApiObj = new EbayMessageApi();
		$messageApiObj->_setParam(array(
				'itemID' 		=> $itemID,
				'recipientID'	=> $buyerId,
				'subject'		=> $subject,
				'body'			=> $content,
		));
		$response = $messageApiObj->setShortName($accountInfo['short_name'])
					->setSiteId($siteId)
					->setVerb('AddMemberMessageAAQToPartner')
					->setRequest()
					->sendHttpRequest()
					->getResponse();
		if( $messageApiObj->getIfSuccess() ){
			return true;
		}else{
			throw new CException('Send Message Failed.Error Message:'.$messageApiObj->getErrorMsg());
		}
	}
}