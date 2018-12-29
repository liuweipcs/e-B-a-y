<?php
/**
 * @package Ueb.modules.services.controllers
 * @author Tom 
 */

//测试页面 add By Tom 

class EbayController extends UebController {

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array();
	}
    
    public function actionIndex(){ 
        $xml = new XmlGenerator();      
        $xml->XmlWriter();
	    $xml->push('trovit');	          
        $xml->push('ad', array('id' => 12));
        $xml->element('name', 'prezzo');
        $xml->element('link', $this->createUrl('properties/view',array('id'=>12)));
        $xml->element('food', 'data_inserimento');
        $xml->pop();	  
		$xml->pop();
		echo $xml->getXml(); 
		exit;
        die('ebay');     
    }
    
    /**
     * @desc 从拉单程序拉去订单
     */
    public function actionGetordersnew()
    {
        set_time_limit(3600);
        $platformOrdId = Yii::app()->request->getParam('platform_order_id',''); //平台订单号
        $sortOrder = Yii::app()->request->getParam('sort_order',1); //拉单排序顺序1=正序 -1=倒序
        $orderHandleObj = new EbayGetOrder();
        $orderHandleObj->getOrdersByAccountNew($platformOrdId, $sortOrder);
        exit('DONE');    
    }

    public function actionGetordersNum()
    {
        set_time_limit(3600);
        $orderHandleObj = new EbayGetOrder();
        $orderHandleObj->getOrdersByTansaction('2017-11-01 00:00:00','2017-11-21 23:59:59');
        exit('DONE');
    }
    
    public function actionGetorders() {exit('DONE');
	set_time_limit(3600);
        if(isset($_REQUEST['account'])){
        	$account = trim($_REQUEST['account']);
        	$orderHandleObj = new ebayGetOrder();
        	$orderHandleObj->getOrdersByAccount($account);	
        }else{
        	$ebayAccounts = UebModel::model('ebayAccount')->getAccountList();
        	if(!empty($ebayAccounts)){
        		foreach ($ebayAccounts as $id=>$val){
        			MHelper::runThreadSOCKET('http://localhost/services/ebay/ebay/getorders/account/'.$id);
        			sleep(2);
        		}
        	}else{
        		die('there are no any account!');
        	}
        }      
    }
    
    
    public function actionA(){

    	$orders = UebModel::model('orderPackage')->creteaNormalPackages();    	
    	exit;
//    	echo $orderIdStr = MHelper::simplode($ids);
		$orders = UebModel::model('OrderEbay')->getOrderInfos($ids);
		print_r($orders);
    	exit;
   		yii::apiDbLog('error',2,'getOrder');
   		
    	exit;
    	
    	$orderObj = null;
       	$orderObj->order_id = 'P140107004';
    	UebModel::model('OrderEbay')->updateByPk($orderObj->order_id,
    		array('complete_status'=>1)
    	);	
    	
    	echo $Msg = UebModel::getLogMsg();
    	exit;
 
       $orderObj->platform_code ='123';
       UebModel::model('OrderEbay')->saveOrderByOrderInfo($orderObj,true); 	
       $Msg = UebModel::getLogMsg();

                    if (! empty($Msg)) {
                    	echo $Msg;
                    	Yii::ulog($Msg, Yii::t('products', 'Product attribute'),'aaa');
                    }
                    
    }
    
	public function actionTest(){
		set_time_limit(3600);
		
		
	
		$transactions  = UebModel::model('PaypalTransaction')->checkPaypalErrorRequest(1);
	    
//	    echo date('Y-m-d H:i:s',strtotime('2014-02-07T11:00:33Z'));
//	    exit; 

        if($transactions){
        	$paypalObj = new PaypalTransactions();
        	foreach ($transactions as $transaction){
				$result = $paypalObj->downloadPaypalTransaction($transaction['transaction_id']);
				if($result){
					$orderTransaction = UebModel::model('OrderEbay')->getDbConnection()->getCurrentTransaction();
					
					if($orderTransaction !== null){
						$orderTransaction = null;
					}else{
						$orderTransaction = UebModel::model('OrderEbay')->getDbConnection()->beginTransaction();
					}
					try{
						$orderObj = $result['order'];
						$transactionObj = $result['transaction'];
						$recordObj = $result['detail'];
						$customerObj = $result['customer'];
					   if($result['note']!=null){
					   	  $noteObj = $result['note'];
					   	  $noteObj->create_date = date('Y-m-d H:i:s');
					   	  $orderObj->complete_status = UebModel::model('OrderEbay')->getOrderStatusPengding();
						  // insert note 
					   	  if(!(UebModel::model('OrderEbayNote')->saveOrderNote($noteObj,$noteObj->order_id))){
					   	  	throw new Exception('order_id:'.$noteObj->order_id.' note save not success');
					   	  }
					   }
						// update order information
						if(!(UebModel::model('OrderEbay')->saveOrderByOrderInfo($orderObj,true))){
							throw new Exception('order_id:'.$orderObj->order_id.' update not success');
						}
						
					    UebModel::model('OrderEbay')->updateByPk($orderObj->order_id,
					    	array('complete_status'=>UebModel::model('OrderEbay')->getOrderStatusDefault())
					    ,'complete_status != :complete_status'
					    ,array(':complete_status'=>UebModel::model('OrderEbay')->getOrderStatusPengding()));

					    
					    // insert paypalTransaction record
					   if(UebModel::model('PaypalTransactionRecord')->savePaypalTransactionRecord(
					   		$recordObj->transaction_id,$recordObj->order_id,
					   		$recordObj
					   )){
					   		$transactionObj->status = UebModel::model('OrderEbayTransaction')->getTransactionStatusEnd();
					   		if(!(UebModel::model('PaypalTransaction')->savePaypalTransaction($transactionObj,$transactionObj->transaction_id))){
					   			throw new Exception('PaypalTransaction_id:'.$transactionObj->transaction_id.' save not success');	
					   		}	
					   		
					   }else{
					   		throw new Exception('PaypalTransaction_id:'.$recordObj->transaction_id.' Record save not success');
					   }
					   
					   if($result['customer'] != null){
					   	 	if(!(UebModel::model('Customer')->saveCustomerByCustomerInfo(
								$result['customer']
							))){
								throw new Exception('customer information save not success');
							}
					   }
					   if($orderTransaction!==null){
					   		$orderTransaction->commit();
					   }
					   
					}catch(Exception $e){
						
						$orderTransaction->rollback();
						print_r($e);
						exit;
					}
				}
			}
			
        }
	
//		$ueb_order_id = 'P140107008';
//		$result = UebModel::model('orderDetail')->getTotalAmountByOrderId($ueb_order_id);
//		print_r($result);
//		exit;
//		$transactionId = '1CJ50028AT766243Y';
//		$result = UebModel::model('PaypalTransaction')->getDetailByTransactionId($transactionId,'EB',1);
//		print_r($result);
		exit;
		
		
		if(!empty($result)){
			$i = 0;
			foreach($result as $r){
				$i=$i+1;
				echo $r->transaction_id;
				echo '<br>';
			}
			echo $i;
		}
	
		//	print_r($result);
	
		exit;
		$list = UebModel::model('OrderEbay')->getNotPayOrderByPlatformCode('EB',1,'2013-11-18','2013-11-22');
		print_r($list);
		exit;
		$result = UebModel::model('OrderEbayTransaction')->checkTransactionIsExistByOrderId('4VJ33618C12971358','3');
		print_r($result);
		exit;
		
			$customerObj = null;
	  		$customerObj->customer_name = 1;
            $customerObj->buyer_id = 2;
    		$customerObj->country = 5;
        	$customerObj->email = 8;
        	$customerObj->ship_to_name = 5;
            $customerObj->tel = 6;
    		$customerObj->address1 = 7;
        	$customerObj->address2 = 8;
        	$customerObj->city = 9;
            $customerObj->state_province = 10;
    		$customerObj->zip = 11;
        	$customerObj->datafrom = 'ebay';
        	$customerObj->update_time = date('Y-m-d H:i:s',time());
		$result =  UebModel::model('Customer')->getDbConnection()->createCommand()
    			->delete(Customer::model()->tableName(), "email='9'");
    	$sql = "delete from ueb_customer where email='4'";		
    	$result =  UebModel::model('Customer')->getDbConnection()->createCommand($sql)->execute()->num_rows();
	
	}
   
    public function actionGetBalance() {
    	$obj = new GetBalance();
        $response = $obj->setEmail('111@gmail.com')         
          ->setRequest()
          ->sendHttpRequest()
          ->getResponse();  
         Yii::p($response);
    }

    
    /**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		
	}	
	
	public function actionCheckpaypaltransaction() {
	    ini_set('display_errors', true);
	    error_reporting(E_ERROR);
	    set_time_limit(3600);
	    if(isset($_REQUEST['account'])){
	        $account = trim($_REQUEST['account']);
	        $EbayGetOrder = new EbayGetOrder;
            $EbayGetOrder->checkPaypalTransaction($account);
            exit('DONE');
	    }else{
	        $ebayAccounts = UebModel::model('ebayAccount')->getAccountList();
	        if(!empty($ebayAccounts)){
	            foreach ($ebayAccounts as $id=>$val){
	                MHelper::runThreadSOCKET('/services/ebay/ebay/checkpaypaltransaction/account/'.$id);
	                sleep(10);
	            }
	        }else{
	            die('there are no any account!');
	        }
	    }
	}

}
