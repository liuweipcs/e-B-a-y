<?php
/**
 * @desc Ebay Message 通讯
 * @since 2015-04-29
 * @author Gordon
 */
class EbayMessageApi extends EbayApiAbstract {
	
	public $_itemID = '';
	public $_questionType = '';
	public $_emailCopyToSender = '';
	public $_recipientID = '';
	public $_subject = '';
	public $_body = '';
	
	public function _setParam($params=array()){
		if( !empty($params) ){
			extract($params);
		}
		$this->_itemID 				= isset($itemID) ? $itemID : '';
		$this->_questionType 		= isset($questionType) ? $questionType : 'General';
		$this->_emailCopyToSender 	= isset($emailCopyToSender) ? $emailCopyToSender : 'true';
		$this->_recipientID 		= isset($recipientID) ? $recipientID : '';
		$this->_subject 			= isset($subject) ? $subject : '';
		$this->_body 				= isset($body) ? '<![CDATA['.$body.']]>' : '';
	}
	
    /**
     * Send Request
     * @see ApiInterface::setRequest()
     */
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
        	'ItemID' => $this->_itemID,
			'MemberMessage' => array(
					'QuestionType' 		=> $this->_questionType,
					'EmailCopyToSender' => $this->_emailCopyToSender,
					'RecipientID' 		=> $this->_recipientID,
					'Subject' 			=> $this->_subject,
					'Body' 				=> $this->_body,
            ),
        );

        $this->request = $request;
        return $this;
    }
    
    /**
     * Request XML Body
     */
    public function requestXmlBody() {        
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
            ->push('AddMemberMessageAAQToPartnerRequest', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))       
            ->buildXMLFilter($this->getRequest()) 	  		
            ->pop();
		return $xmlObj->getXml(); 
    }
}
?>