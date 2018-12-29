<?php
/**
 * @package Ueb.modules.services.controllers
 * @author Gordon 
 */

class EbaymessageController extends UebController {

	public function accessRules() {
		return array();
	}
    
	/**
	 * @desc 发送ebay站内信
	 * @param string $buyerId
	 * @param string $itemID
	 * @param string $userToken
	 * @param string $subject
	 * @param string $content
	 */
    public function actionSendMessage($buyerId,$itemID,$userToken,$subject,$content){ 
    	
    }
    
}