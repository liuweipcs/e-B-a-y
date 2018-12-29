<?php
class MessageTpl{
	public $_tpl = 0;
	public $_subject = '';
	public $_content = '';
	
	public function __construct($tpl){
		$this->_tpl = $tpl;
		$this->setTpl();
	}
	
	private function setTpl(){
		if( !$this->_tpl ){
			return false;
		}
		switch ($this->_tpl){
			case EbayMessage::TYPE_EUB_GIFT:
				$this->_subject = 'Please contact us via ebay message if you receive one GIFT first,item bought later';
				$this->_content = "Dear Valued Customer.

Have a nice day!

Thanks for your purchasing from our store.We have arranged a small gift to you for your support of our store .The gift and the item you bought have been sent out using 2 packages,so you may receive them at the different time.

We have informed you that if you receive one parcel missing the item what you ordered, please don't worry, that's the gift we sent to you, and your item ordered is approaching to you.

Hope the item/items could reach at you on time and satisfy you.

If you face any challeges, just feel free to contact us via 'eBay Message'. We will do whatever necessary to satisfy you.

Yours faithfully,
Customer service representative";
				break;
		}
		return true;
	}
}