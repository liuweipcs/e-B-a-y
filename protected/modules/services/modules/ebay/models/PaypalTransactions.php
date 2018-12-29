<?php 
/**
 * @package Ueb.modules.services.models/paypalTransactions.php
 * 
 * @author Tom
 */
class PaypalTransactions{
	protected $_orderObj = null;
	protected $_noteObj = null;
	protected $_paypalDetailObj = null;
	protected $_transactionObj = null;
	protected $_customerObj = null;
	/** 
	 * Enter description here ...
	 * @param $transactionId
	 * @param $coerce
	 */
	function downloadPaypalTransaction($transactionId,$coerce=0){
		
	
		$transactionInfo = UebModel::model('OrderEbayTransaction')
						   ->getOrderTransactionInfoByTransactionId($transactionId,'transaction_id,order_id,status');
	
		if(!$transactionInfo){
			
			return;
		}
		
		$orderInfo = UebModel::model('OrderEbay')->getOrderInfoByOrderId($transactionInfo['order_id']);
	
	
		if($orderInfo['payment_status']==UebModel::model('OrderEbay')->getPayedOrderStatus()){		
			return ;
		}
	
		$paypalAccounts = UebModel::model('paypalAccount')->getAccountsEmail(UebModel::model('Platform')->getEbayPlatformCode(), $orderInfo['account_id']);
		$resultArr = array();
		if($paypalAccounts){
			if($transactionInfo['status']==UebModel::model('OrderEbayTransaction')->getTransactionStatusDefault()){
				foreach($paypalAccounts as $account){
					if($transactionObj = $this->getDetailByTransactionId($transactionId, $account['id'])){

						if(!$coerce && $transactionObj->PaymentTransactionDetails->PaymentInfo->PaymentStatus!=
							UebModel::model('PaypalTransaction')->getPaymentStatusCompleted()
						){
							return '';
						}
						
						$this->setPaypalDetailObj($transactionObj->PaymentTransactionDetails,$transactionId,$transactionInfo['order_id']);
						$this->setPaypalTransactionObj($transactionObj->PaymentTransactionDetails,$account['id'],$transactionId);
						$this->setOrderInfoObj($transactionObj->PaymentTransactionDetails,$orderInfo['paytime'],$transactionInfo['order_id'],$orderInfo['complete_status']);
						$this->setOrderNoteObj($transactionObj->PaymentTransactionDetails->PaymentItemInfo,$transactionInfo['order_id']);
						if($this->_transactionObj->amt){
							$this->setCustomer($transactionObj->PaymentTransactionDetails);
						}
						$resultArr['flag'] = true;
						break;
					}
				}
			}else{
				return ;
			}

		}
		if(isset($resultArr['flag'])&&$resultArr['flag']){
			$resultArr['order'] = $this->_orderObj;
			$resultArr['note'] = $this->_noteObj;
			$resultArr['detail'] = $this->_paypalDetailObj;
			$resultArr['transaction'] = $this->_transactionObj;
			$resultArr['customer'] = $this->_customerObj;
		}	
		return $resultArr;
	}
	
	
	/**
	 * 
	 * 
	 * @param Object $detailObj
	 */
	public function setOrderInfoObj($detailObj,$payTime,$orderId,$completeStatus){
		$orderObj = null;
		$payerAddressInfo = $detailObj->PayerInfo->Address;
		$orderObj->order_id = $orderId;
		$orderObj->ship_street1 = $payerAddressInfo->Street1;
		$orderObj->ship_street2 = $payerAddressInfo->Street2;
		$orderObj->ship_zip = $payerAddressInfo->PostalCode;
		$orderObj->ship_city_name = $payerAddressInfo->CityName;
		$orderObj->ship_stateorprovince = $payerAddressInfo->StateOrProvince;
		$orderObj->ship_country = isset($payerAddressInfo->Country)?$payerAddressInfo->Country
								:$detailObj->PayerInfo->PayerCountry;
		$orderObj->ship_country_name = $payerAddressInfo->CountryName;	
		$orderObj->complete_status = $completeStatus;	
		$payTime = strtotime($payTime)>0 ? $payTime : $this->_paypalDetailObj->order_time;
	
		if($this->_paypalDetailObj->payment_type == UebModel::model('PaypalTransaction')->PAYMENT_TYPE_ECHECK){
			$payTime = date('Y-m-d H:i:s',time()-8*3600);
		}
		
		$orderObj->paytime = $payTime;
		$orderObj->payment_status = UebModel::model('OrderEbay')->getPayedOrderStatus();
		$this->_orderObj = $orderObj;
	}
	
	public function setOrderNoteObj($transactionNoteObj,$orderId){
		
		if(isset($transactionNoteObj->Memo)&&$transactionNoteObj->Memo){
			 $this->_noteObj->note = $transactionNoteObj->Memo;
			 $this->_noteObj->order_id = $orderId;
		}
		
	}
	
	public function setCustomer($detailObj){
		$this->_customerObj->customer_name = $detailObj->PayerInfo->Address->Name;
		$this->_customerObj->address1 = $detailObj->PayerInfo->Address->Street1;
		$this->_customerObj->address2 = $detailObj->PayerInfo->Address->Street2;
		$this->_customerObj->buyer_id = $detailObj->PaymentItemInfo->Auction->BuyerID;
		$this->_customerObj->email = $detailObj->PayerInfo->Payer;
		$this->_customerObj->country = $detailObj->PayerInfo->Address->CountryName;
		$this->_customerObj->ship_to_name = $detailObj->PayerInfo->Address->Name;
		$this->_customerObj->tel = '';
		$this->_customerObj->city = $detailObj->PayerInfo->Address->CityName;
		$this->_customerObj->state_province = $detailObj->PayerInfo->Address->StateOrProvince;
		$this->_customerObj->zip = $detailObj->PayerInfo->Address->PostalCode;
		$this->_customerObj->add_time = date('Y-m-d H:i:s');
		$this->_customerObj->datafrom = UebModel::model('Customer')->getDataFromPaypal();
		$this->_customerObj->update_time = date('Y-m-d H:i:s');
	}
	
	
	public function setPaypalTransactionObj($transactionObj,$account_id,$transactionId){
		$this->_transactionObj->order_pay_time = date('Y-m-d H:i:s',strtotime($transactionObj->PaymentInfo->PaymentDate)-8*3600);
		$this->_transactionObj->status = UebModel::model('OrderEbayTransaction')->getTransactionStatusDefault();
		if(isset($transactionObj->PaymentInfo->FeeAmount->_)){
			$this->_transactionObj->fee_amt = $transactionObj->PaymentInfo->FeeAmount->_;
		}
		$this->_transactionObj->amt = $transactionObj->PaymentInfo->GrossAmount->_;
		$this->_transactionObj->currency = $transactionObj->PaymentInfo->GrossAmount->currencyID;
		$this->_transactionObj->platform_code = UebModel::model('Platform')->getEbayPlatformCode(); 
		$this->_transactionObj->account_id = $account_id;
		$this->_transactionObj->transaction_id = $transactionId;
		$this->_transactionObj->payment_status = $transactionObj->PaymentInfo->PaymentStatus;
	}
	
	public function setPaypalDetailObj($detailObj,$transactionId,$orderId){
		$this->_paypalDetailObj->transaction_id = $transactionId;
		$this->_paypalDetailObj->order_id = $orderId;
		$this->_paypalDetailObj->receiver_business = $detailObj->ReceiverInfo->Business;
	//	$this->_paypalDetailObj->receiver_email = $detailObj->
		$this->_paypalDetailObj->receiver_id = $detailObj->ReceiverInfo->ReceiverID;
		$this->_paypalDetailObj->receiver_email = $detailObj->ReceiverInfo->Receiver;
		$this->_paypalDetailObj->payer_id = $detailObj->PayerInfo->PayerID;
		$this->_paypalDetailObj->payer_email = $detailObj->PayerInfo->Payer;
		$payNameObj = $detailObj->PayerInfo->PayerName;
		$payName = $payNameObj->FirstName;
		$payNameObj->MiddleName && $payName .= ' '.$payNameObj->MiddleName;
		$payNameObj->LastName && $payName .= ' '.$payNameObj->LastName;
		$this->_paypalDetailObj->payer_name = $payName;
		//$this->_paypalDetailObj->payer_email = $detailObj->
		$this->_paypalDetailObj->payer_status = $detailObj->PayerInfo->PayerStatus;
		$this->_paypalDetailObj->parent_transaction_id = $detailObj->PaymentInfo->ParentTransactionID;
		$this->_paypalDetailObj->transaction_type = $detailObj->PaymentInfo->TransactionType; 
		$this->_paypalDetailObj->payment_type = $detailObj->PaymentInfo->PaymentType;
		$this->_paypalDetailObj->order_time = date('Y-m-d H:i:s',strtotime($detailObj->PaymentInfo->PaymentDate)-8*3600);
		$this->_paypalDetailObj->amt = $detailObj->PaymentInfo->GrossAmount->_;
		if(isset($detailObj->PaymentInfo->FeeAmount->_)){
			$this->_paypalDetailObj->fee_amt = $detailObj->PaymentInfo->FeeAmount->_;
		}
		$this->_paypalDetailObj->tax_amt = $detailObj->PaymentInfo->TaxAmount->_;
		$this->_paypalDetailObj->currency = $detailObj->PaymentInfo->GrossAmount->currencyID;
		$this->_paypalDetailObj->payment_status = $detailObj->PaymentInfo->PaymentStatus;
		$this->_paypalDetailObj->note = $detailObj->PaymentItemInfo->Memo;
	}
	
	
	/**
	 * to get detail for transaction
	 * @param String $transactionId
	 * @param String $paypalAccountId
	 * @return Object
	 */
	public function getDetailByTransactionId($transactionId,$paypalAccountId){
		$obj = new GettransactionDetails;
		$respone = $obj->getDetailByTransactionId($transactionId,$paypalAccountId);
        $ack = strtoupper($respone->Ack); 
		if($ack!="SUCCESS" && $ack!="SUCCESSWITHWARNING"){
			return false;
		}else{
			return $respone;
		}
	}
	

}

?>