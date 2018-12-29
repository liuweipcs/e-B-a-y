<?php
/*
 * @desc Amazon API
 * @author ellan
 * @2013-3-22
 */
define ('DATE_FORMAT', 'Y-m-d H:i:s');
 
class AmazonAPI
{
	/* @var */
	private static $_instance;
	
	/* @var */
	protected $serviceUrl;
	
	/* @model */
	public $model;
	
	/* @object*/
	protected $_service;
	
	/* @request*/
	//protected $_request;
	
	/* @array*/
	protected $config;
	
	/* merchant id*/
	protected $merchant_id;
	
	/* marketplace id*/
	protected $marketplace_id;
	
	/* @access_key_id*/
	protected $aws_access_key_id;
	
	/* @aws_sercret_access_key*/
	protected $aws_secret_access_id;
	
    /* @appication name set default "vakind_amazon" */    
	protected $application_name="vakind_app";
	
	/* @application version set default '1.0'*/
	protected $application_version ='1.0';
	
	/* @*/
	public $fulfill_channel;
	
	public $cron_log_params = array();
	
	/**
	 * @desc init attribute 
	 */
	public function __construct(){}

	/*
	 * @get instance of self
	 * */
	public static function getInstance()
	{
		if(!self::$_instance instanceof self){
			self::$_instance = new self();
		}
		
		return self::$_instance; 
	}
	
    /*
     * set service based on model
     * */
    public function setService()
	{
    	switch ($this->model){
    		case 'order':
    		case 'seller':
    			$this->_service = new MarketplaceWebServiceOrders_Client(
	    				$this->aws_access_key_id,
	    				$this->aws_secret_access_id,
	    				$this->application_name,
	    				$this->application_version,
		    			$this->config
	    			);
    			break;
    		case 'get_price':
    			$this->_service = new MarketplaceWebServiceProducts_Client(
	    				$this->aws_access_key_id,
	    				$this->aws_secret_access_id,
	    				$this->application_name,
	    				$this->application_version,
		    			$this->config
	    			);
    			break;
    		case 'tracking':
    		case 'inventory':
    		case 'listing_sold':
    		case 'listing_sell':
    		case 'fba_fee_report':
    		case 'nfba_fee_report':
    		case 'request_list':
    		case 'settlement':
    			$this->_service = new MarketplaceWebService_Client(
	    				$this->aws_access_key_id,
	    				$this->aws_secret_access_id,
	    				$this->config,
	    				$this->application_name,
	    				$this->application_version
	    		);
    			break;
	    	 case 'list_inventory':
	    	$this->_service = new FBAInventoryServiceMWS_Client(
	    				$this->aws_access_key_id,
	    				$this->aws_secret_access_id,
	    				$this->config,
	    				$this->application_name,
	    				'2010-10-01'
	    		);	    		
	    	break;		
    			
    		default:
    			break;
    	}
    }
    
    /*
     * list order response
     * */
    public function ListOrders($nextToken = null)
    {
    	try{
    	$list = array();
    	if(is_null($nextToken)){
    		$request = new MarketplaceWebServiceOrders_Model_ListOrdersRequest();
    		$request->setSellerId($this->merchant_id);
    		// List all orders udpated after a certain date
	 		
	 		// Set the marketplace_id condition
	 		$marketplaceIdList = new MarketplaceWebServiceOrders_Model_MarketplaceIdList();
	 		$marketplaceIdList->setId(array($this->marketplace_id));
	 		
			//set channel condition
	 		$fulfillmant_channel = new MarketplaceWebServiceOrders_Model_FulfillmentChannelList();
	 		$fulfillmant_channel->setChannel(array($this->fulfill_channel));
	 		
	 		//set order status condition
	 		$orderstatuses = new MarketplaceWebServiceOrders_Model_OrderStatusList();
	 		switch($this->fulfill_channel){
	 			case 'MFN':
	 				$orderstatuses->setStatus(array('Unshipped','PartiallyShipped'));
	 				break;
	 			case 'AFN':
	 				$orderstatuses->setStatus(array('Shipped'));
	 				break;
	 		}
	 		
//	 		$timestamp = $this->cron_log_params['base_atime'];
	 		//中国时间减去9小时
//	 		$timestamp = date('Y-m-d H:i:s',strtotime("-1 day",strtotime($timestamp)));
			$timestamp = date('Y-m-d H:i:s',strtotime("-5 day"));
	 		$this->cron_log_params['exec_time'] = $timestamp;
	 		$timestamp = new DateTime($timestamp, new DateTimeZone('UTC'));
	 		$request->setLastUpdatedAfter($timestamp);
	 		$request->setMarketplaceId($marketplaceIdList);
			$request->setFulfillmentChannel($fulfillmant_channel);
	 		$request->setOrderStatus($orderstatuses);
	 		$response = $this->_service->listOrders($request);
	 		if ($response->isSetListOrdersResult())
	 		{
	 			$listOrdersResult = $response->getListOrdersResult();
	 			if ($listOrdersResult->isSetOrders()) 
	            {
	            	$orders = $listOrdersResult->getOrders();
	                $orderList = $orders->getOrder();
	            	$list = $this->getOrderArr($orderList);
	            }
	 			if ($listOrdersResult->isSetNextToken()) 
	            {
	            	$list = array_merge($list,$this->listOrders($listOrdersResult->getNextToken()));
	            }
	 		}
    	}else{
    		$request = new MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenRequest();
 			$request->setSellerId($this->merchant_id);
 			$request->setNextToken($nextToken);
 			$response = $this->_service->listOrdersByNextToken($request);
 			if ($response->isSetListOrdersByNextTokenResult()) {
 				$listOrdersByNextTokenResult = $response->getListOrdersByNextTokenResult();
                
	 			if ($listOrdersByNextTokenResult->isSetOrders()) 
		        {
		        	$orders = $listOrdersByNextTokenResult->getOrders();
		            $orderList = $orders->getOrder();
		            $list = $this->getOrderArr($orderList);
		        }
 				if ($listOrdersByNextTokenResult->isSetNextToken()) 
                {
                	//print_R($this->listOrders($listOrdersByNextTokenResult->getNextToken()));
                	$list = array_merge($list,$this->listOrders($listOrdersByNextTokenResult->getNextToken()));
                }
 			}
    	}
    	}catch(MarketplaceWebServiceOrders_Exception $ex){
			$list['flag'] = '0';
			$list['message']  = "Caught Exception: " . $ex->getMessage() . "\n";
    		$list['message'] .= "Response Status Code: " . $ex->getStatusCode() . "\n";
    		$list['message'] .= "Error Code: " . $ex->getErrorCode() . "\n";
    		$list['message'] .= "Error Type: " . $ex->getErrorType() . "\n";
    		$list['message'] .= "Request ID: " . $ex->getRequestId() . "\n";
    		$list['message'] .= "XML:  " . $ex->getXML() . "\n";
    		$list['message'] .= "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n";
		}
    	return $list;
    }
    

    
    
    
    /*
     * */
    protected function getOrderArr($orderList){
    	$amazonArr = array();
    	foreach ($orderList as $order) {
			$am_oid = $order->getAmazonOrderId();
	        $amazonArr[$am_oid]['platform_code'] = 'AMAZON';
	        if ($order->isSetAmazonOrderId()) 
	        {
				$amazonArr[$am_oid]['amazon_order_id'] =  $order->getAmazonOrderId();
	        }
	        if ($order->isSetSellerOrderId()) 
	        {
				$amazonArr[$am_oid]['seller_order_id'] = $order->getSellerOrderId();
			}
	        if ($order->isSetPurchaseDate()) 
	        {
				$amazonArr[$am_oid]['purchase_date'] = $order->getPurchaseDate();
	        }
	        if ($order->isSetLastUpdateDate()) 
	        {
				$amazonArr[$am_oid]['last_update_time'] = $order->getLastUpdateDate();
	        }
	        if ($order->isSetOrderStatus()) 
	        {
				$amazonArr[$am_oid]['order_status'] = $order->getOrderStatus();
	        }
	        if ($order->isSetFulfillmentChannel()) 
	        {
				$amazonArr[$am_oid]['amazon_fulfill_channel'] = $order->getFulfillmentChannel();
	        }
	        if ($order->isSetSalesChannel()) 
	        {
				$amazonArr[$am_oid]['amazon_sales_channel'] = $order->getSalesChannel();
	        }
	        if ($order->isSetOrderChannel()) 
	        {
				$amazonArr[$am_oid]['amazon_order_channel'] = $order->getOrderChannel();
	        }
	        if ($order->isSetShipServiceLevel()) 
	        {
				$amazonArr[$am_oid]['ship_service_level'] = $order->getShipServiceLevel();
	        }
	        if ($order->isSetShippingAddress()) { 
				$shippingAddress = $order->getShippingAddress();
				if ($shippingAddress->isSetName()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_name'] = $shippingAddress->getName();
	            }
	            if ($shippingAddress->isSetAddressLine1()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_addr1'] = $shippingAddress->getAddressLine1();
	            }
	            if ($shippingAddress->isSetAddressLine2()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_addr2'] = $shippingAddress->getAddressLine2();
	            }
	            if ($shippingAddress->isSetAddressLine3()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_addr3'] = $shippingAddress->getAddressLine3();
	            }
	            if ($shippingAddress->isSetCity()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_city'] = $shippingAddress->getCity();
	            }
	            if ($shippingAddress->isSetCounty()) 
				{
					$amazonArr[$am_oid]['customer']['ship_country'] =  $shippingAddress->getCounty();
	            }
	            if ($shippingAddress->isSetDistrict()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_district'] = $shippingAddress->getDistrict();
	            }
	            if ($shippingAddress->isSetStateOrRegion()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_state'] = $shippingAddress->getStateOrRegion();
	            }
	            if ($shippingAddress->isSetPostalCode()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_post_code'] = $shippingAddress->getPostalCode();
	            }
	            if ($shippingAddress->isSetCountryCode()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_country_code'] = $shippingAddress->getCountryCode();
	            }
	            if ($shippingAddress->isSetPhone()) 
	            {
					$amazonArr[$am_oid]['customer']['ship_phone'] = $shippingAddress->getPhone();
	            }
	        }
	        if ($order->isSetOrderTotal()) { 
				$orderTotal = $order->getOrderTotal();
				if ($orderTotal->isSetCurrencyCode()) 
				{
					$amazonArr[$am_oid]['currency_code'] = $orderTotal->getCurrencyCode();
				}
				if ($orderTotal->isSetAmount()) 
				{
					$amazonArr[$am_oid]['order_total'] = $orderTotal->getAmount();
				}
	       } 
	       if ($order->isSetNumberOfItemsShipped()) 
	       {
				$amazonArr[$am_oid]['num_item_shipped'] =  $order->getNumberOfItemsShipped();
	       }
	       if ($order->isSetNumberOfItemsUnshipped()) 
	       {
				$amazonArr[$am_oid]['num_item_unshipped'] = $order->getNumberOfItemsUnshipped();
	       }
	       if ($order->isSetPaymentExecutionDetail()) { 
				$paymentExecutionDetail = $order->getPaymentExecutionDetail();
				$paymentExecutionDetailItemList = $paymentExecutionDetail->getPaymentExecutionDetailItem();
				$i =0;	
	            foreach ($paymentExecutionDetailItemList as $paymentExecutionDetailItem) {
					if ($paymentExecutionDetailItem->isSetPayment()) { 
						$payment = $paymentExecutionDetailItem->getPayment();
						if ($payment->isSetCurrencyCode()) 
						{
							$amazonArr[$am_oid]['payment'][$i]['currency_code'] = $payment->getCurrencyCode();
						}
	                    if ($payment->isSetAmount()) 
	                    {
							$amazonArr[$am_oid]['payment'][$i]['pay_amount'] = $payment->getAmount();
	                    }
	                }
	                if ($paymentExecutionDetailItem->isSetPaymentMethod()) 
	                {
						$amazonArr[$am_oid]['payment'][$i]['payment_method'] = $paymentExecutionDetailItem->getPaymentMethod();
	                }
	           }
	           $i++;
	        }
	        if ($order->isSetPaymentMethod()) 
	        {
				$amazonArr[$am_oid]['payment_method'] = $order->getPaymentMethod();
	        }
	        if ($order->isSetMarketplaceId()) 
	        {
				$amazonArr[$am_oid]['market_place_id'] = $order->getMarketplaceId();
			}
	        if ($order->isSetBuyerEmail()) 
	        {
				$amazonArr[$am_oid]['customer']['buyer_email'] = $order->getBuyerEmail();
	        }
	        if ($order->isSetBuyerName()) 
	        {
				$amazonArr[$am_oid]['customer']['buyer_name'] = $order->getBuyerName();
	        }
	        if ($order->isSetShipmentServiceLevelCategory()) 
	        {
				$amazonArr[$am_oid]['shipment_level_category'] = $order->getShipmentServiceLevelCategory();
	        }
	        if ($order->isSetShippedByAmazonTFM()) 
	        {
				$amazonArr[$am_oid]['ship_by_amazon_tfm'] = $order->getShippedByAmazonTFM();
	        }
	        if ($order->isSetTFMShipmentStatus()) 
	        {
				$amazonArr[$am_oid]['ship_status'] = $order->getTFMShipmentStatus();
	        }
	    }//foreachs
		return $amazonArr;
    }
    
   /**
     * 
     * @param $amazonOrderId
     * @param $nextToken
     */
    public function listOrderItems($amazonOrderId,$nextToken = null){
    	try{
    	$list = array();
    	if(is_null($nextToken)){
    		$request = new MarketplaceWebServiceOrders_Model_ListOrderItemsRequest();
	 		$request->setSellerId($this->merchant_id);
	 		$request->setAmazonOrderId($amazonOrderId);
	 		$response = $this->_service->listOrderItems($request);
    		if ($response->isSetListOrderItemsResult()) {
				$listOrderItemsResult = $response->getListOrderItemsResult();
    			if ($listOrderItemsResult->isSetOrderItems()) {
                	$orderItems = $listOrderItemsResult->getOrderItems();
                    $orderItemList = $orderItems->getOrderItem();
                    $list = $this->getOrderItemArr($orderItemList);
				}
				if ($listOrderItemsResult->isSetNextToken()){
					$list = array_merge($list,$this->listOrderItems($listOrderItemsResult->getNextToken()));
				}
    		}
    	}else{
    		$request = new MarketplaceWebServiceOrders_Model_ListOrderItemsByNextTokenRequest();
 			$request->setSellerId($this->merchant_id);
 			$response = $this->_service->listOrderItemsByNextToken($request);
 			if($response->isSetListOrderItemsByNextTokenResult()) {
 				$listOrderItemsByNextTokenResult = $response->getListOrderItemsByNextTokenResult();
				if ($listOrderItemsByNextTokenResult->isSetOrderItems()) {
                	$orderItems = $listOrderItemsByNextTokenResult->getOrderItems();
                    $orderItemList = $orderItems->getOrderItem();
                    $list = $this->getOrderItemArr($orderItemList);
				}
 				if ($listOrderItemsByNextTokenResult->isSetNextToken()){
					$list = array_merge($list,$this->listOrderItems($listOrderItemsByNextTokenResult->getNextToken()));
				}
 			}
    	}
    	}catch(MarketplaceWebServiceOrders_Exception $ex){
			$list['flag'] = '0';
			$list['message']  = "Caught Exception: " . $ex->getMessage() . "\n";
    		$list['message'] .= "Response Status Code: " . $ex->getStatusCode() . "\n";
    		$list['message'] .= "Error Code: " . $ex->getErrorCode() . "\n";
    		$list['message'] .= "Error Type: " . $ex->getErrorType() . "\n";
    		$list['message'] .= "Request ID: " . $ex->getRequestId() . "\n";
    		$list['message'] .= "XML:  " . $ex->getXML() . "\n";
    		$list['message'] .= "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n";
		}
    	return $list;
    }
    
    public function getOrderItemArr($orderItemList){
    	$orderItemArr = array();
    	$i = 0;
      	foreach ($orderItemList as $orderItem){
      		//print_r($orderItem);
			if ($orderItem->isSetASIN())
            {
				$orderItemArr[$i]['asin'] = $orderItem->getASIN();
            }
            if ($orderItem->isSetSellerSKU()) 
            {
				$orderItemArr[$i]['seller_sku'] = $orderItem->getSellerSKU();
            }
            if ($orderItem->isSetOrderItemId()) 
            {
				$orderItemArr[$i]['item_id'] = $orderItem->getOrderItemId();
            }
            if ($orderItem->isSetTitle()) 
            {
				$orderItemArr[$i]['title'] = $orderItem->getTitle();
            }
            if ($orderItem->isSetQuantityOrdered()) 
            {
				$orderItemArr[$i]['qty_ordered'] = $orderItem->getQuantityOrdered();
            }
            if ($orderItem->isSetQuantityShipped()) 
            {
				$orderItemArr[$i]['qty_shipped'] = $orderItem->getQuantityShipped();
            }
            if ($orderItem->isSetItemPrice()) 
            {
				$itemPrice = $orderItem->getItemPrice();
				if ($itemPrice->isSetCurrencyCode()){
					$orderItemArr[$i]['item_currency_code'] = $itemPrice->getCurrencyCode();
                }
                if ($itemPrice->isSetAmount()){
					$orderItemArr[$i]['item_price_amount'] = $itemPrice->getAmount();
                }
            }
            if ($orderItem->isSetShippingPrice()) {
				$shippingPrice = $orderItem->getShippingPrice();
				if ($shippingPrice->isSetCurrencyCode()) 
				{
					$orderItemArr[$i]['item_shipping_currency_code'] = $shippingPrice->getCurrencyCode();
				}
                if ($shippingPrice->isSetAmount()) 
                {
					$orderItemArr[$i]['item_shipping_amount'] = $shippingPrice->getAmount();
                }
            }
            if ($orderItem->isSetGiftWrapPrice()) {
				$giftWrapPrice = $orderItem->getGiftWrapPrice();
				if ($giftWrapPrice->isSetCurrencyCode()) 
				{
					$orderItemArr[$i]['giftwrap_currency_code'] = $giftWrapPrice->getCurrencyCode();
				}
				if ($giftWrapPrice->isSetAmount()) 
				{
					$orderItemArr[$i]['giftwrap_amount'] = $giftWrapPrice->getAmount();
				}
            }
            if ($orderItem->isSetItemTax()) {
                $itemTax = $orderItem->getItemTax();
				if ($itemTax->isSetCurrencyCode()) 
				{
					$orderItemArr[$i]['item_tax_currency_code'] = $itemTax->getCurrencyCode();
				}
				if ($itemTax->isSetAmount()) 
				{
					$orderItemArr[$i]['item_tax_amount'] = $itemTax->getAmount();
				}
            }
            if ($orderItem->isSetShippingTax()) {
				$shippingTax = $orderItem->getShippingTax();
				if ($shippingTax->isSetCurrencyCode()) 
				{
					$orderItemArr[$i]['shipping_tax_currency_code'] = $shippingTax->getCurrencyCode();
				}
                if ($shippingTax->isSetAmount()) 
                {
					$orderItemArr[$i]['shipping_tax_amount'] = $shippingTax->getAmount();
				}
			}
            if ($orderItem->isSetGiftWrapTax()) {
				$giftWrapTax = $orderItem->getGiftWrapTax();
				if ($giftWrapTax->isSetCurrencyCode()) 
                {
					$orderItemArr[$i]['giftwrap_tax_currency_code'] = $giftWrapTax->getCurrencyCode();
                }
                if ($giftWrapTax->isSetAmount()) 
                {
					$orderItemArr[$i]['giftwrap_tax_amount'] = $giftWrapTax->getAmount();
                }
            }
            if ($orderItem->isSetShippingDiscount()) {
				$shippingDiscount = $orderItem->getShippingDiscount();
				if ($shippingDiscount->isSetCurrencyCode()) 
				{
					$orderItemArr[$i]['shipping_discount_currency_code'] = $shippingDiscount->getCurrencyCode();
                }
                if ($shippingDiscount->isSetAmount()) 
                {
					$orderItemArr[$i]['shipping_discount_amount'] = $shippingDiscount->getAmount();
                }
            }
           /*if($orderItem->isSetConditionNote()){
            	$orderItemArr[$i]['condition_note'] = $orderItem->getConditionNote();
            }*/
            if ($orderItem->isSetPromotionDiscount()) {
				$promotionDiscount = $orderItem->getPromotionDiscount();
				if ($promotionDiscount->isSetCurrencyCode()) 
				{
					$orderItemArr[$i]['promotion_discount_currency_code'] = $promotionDiscount->getCurrencyCode();
				}
                if ($promotionDiscount->isSetAmount()) 
                {
					$orderItemArr[$i]['promotion_discount_amount'] = $promotionDiscount->getAmount();
                }
            }
            if ($orderItem->isSetPromotionIds()) {
				$promotionIds = $orderItem->getPromotionIds();
				$promotionIdList  =  $promotionIds->getPromotionId();
				foreach ($promotionIdList as $promotionId) { 
					$orderItemArr[$i]['promotionId'][] = $promotionId;
				}	
            }
            if ($orderItem->isSetCODFee()) {
				$CODFee = $orderItem->getCODFee();
				if ($CODFee->isSetCurrencyCode()) 
                {
					$orderItemArr[$i]['codfee_currency_code'] = $CODFee->getCurrencyCode();
                }
            	if ($CODFee->isSetAmount()) 
				{
					$orderItemArr[$i]['codfee_amount'] = $CODFee->getAmount();
				}
			}
            if ($orderItem->isSetCODFeeDiscount()) {
				$CODFeeDiscount = $orderItem->getCODFeeDiscount();
            	if ($CODFeeDiscount->isSetCurrencyCode()) 
                {
					$orderItemArr[$i]['codfee_discount_currency_code'] = $CODFeeDiscount->getCurrencyCode();
                }
            	if ($CODFeeDiscount->isSetAmount()) 
				{
					$orderItemArr[$i]['codfee_discount_amount'] = $CODFeeDiscount->getAmount();
				}
			}
            if ($orderItem->isSetGiftMessageText()) 
			{
				$orderItemArr[$i]['gift_message_text'] = $orderItem->getGiftMessageText();
            }
            if ($orderItem->isSetGiftWrapLevel()) 
            {
				$orderItemArr[$i]['gift_wrap_level'] = $orderItem->getGiftWrapLevel();
            }
            if ($orderItem->isSetInvoiceData()) {
                $invoiceData = $orderItem->getInvoiceData();
            	if ($invoiceData->isSetInvoiceRequirement()) 
                {
					$orderItemArr[$i]['invoice']['requirement'] = $invoiceData->getInvoiceRequirement();
                }
            	if ($invoiceData->isSetBuyerSelectedInvoiceCategory()) 
                {
                    $orderItemArr[$i]['invoice']['buyer_sel_category'] = $invoiceData->getBuyerSelectedInvoiceCategory();
                }
            	if ($invoiceData->isSetInvoiceTitle()) 
                {
					$orderItemArr[$i]['invoice']['title'] = $invoiceData->getInvoiceTitle();
                }
            	if ($invoiceData->isSetInvoiceInformation()) 
                {
					$orderItemArr[$i]['invoice']['information'] = $invoiceData->getInvoiceInformation();
                }
            }
           $i++;
      }
      return $orderItemArr;
    }
    
   /**
    * 
    */ 
   public function SubmitFeed($feed,$feedType = '_POST_ORDER_FULFILLMENT_DATA_'){
   		$result = array();
   		try{	
  			$feedHandle = @fopen('php://temp', 'rw+');
			fwrite($feedHandle, $feed);
			rewind($feedHandle);
			$parameters = array (
			  'Merchant' => $this->merchant_id,
			  'MarketplaceIdList' => array("Id" => array($this->marketplace_id)),
			  'FeedType' => $feedType,
			  'FeedContent' => $feedHandle,
			  'PurgeAndReplace' => false,
			  'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
			);
			rewind($feedHandle);
			$request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
			
			$response = $this->_service->submitFeed($request);
			
			if ($response->isSetSubmitFeedResult()) {
				$submitFeedResult = $response->getSubmitFeedResult();
				$feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
				if ($feedSubmissionInfo->isSetFeedSubmissionId()){
					$result['FeedSubmissionId'] = $feedSubmissionInfo->getFeedSubmissionId();
				}
				if ($feedSubmissionInfo->isSetFeedType()){
					$result['FeedType'] = $feedSubmissionInfo->getFeedType();
				}
				if ($feedSubmissionInfo->isSetSubmittedDate()){
					$result['SubmittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
				}
				if ($feedSubmissionInfo->isSetFeedProcessingStatus()){
					$result['FeedProcessingStatus'] = $feedSubmissionInfo->getFeedProcessingStatus();
				}
				if ($feedSubmissionInfo->isSetStartedProcessingDate()){
					$result['StartedProcessingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT);
				}
				if ($feedSubmissionInfo->isSetCompletedProcessingDate()){
					$result['CompletedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT);
				}
			}
			
			@fclose($feedHandle);
   		}catch(MarketplaceWebService_Exception $ex){
			$result['flag'] = '0';
			$result['message']  = "Caught Exception: " . $ex->getMessage() . "\n";
    		$result['message'] .= "Response Status Code: " . $ex->getStatusCode() . "\n";
    		$result['message'] .= "Error Code: " . $ex->getErrorCode() . "\n";
    		$result['message'] .= "Error Type: " . $ex->getErrorType() . "\n";
    		$result['message'] .= "Request ID: " . $ex->getRequestId() . "\n";
    		$result['message'] .= "XML:  " . $ex->getXML() . "\n";
    		$result['message'] .= "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n";
		}
   		
		return $result;
   }
    
   
   public function GetFeedSubmissionResult($FeeSubmissionId){
   		$result = array();
   		try{
	   		$request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
			$request->setMerchant($this->merchant_id);
			$request->setFeedSubmissionId($FeeSubmissionId);
			$feefile = @fopen('php://temp', 'rw+');
			$request->setFeedSubmissionResult($feefile);
			$response = $this->_service->getFeedSubmissionResult($request);
			
			rewind($feefile); 
 			$str =  stream_get_contents($feefile); 
			$result['exec_res'] = simplexml_load_string($str); 
   		}catch(MarketplaceWebService_Exception $ex){
			$result['flag'] = '0';
			$result['code'] = $ex->getStatusCode();
			$result['message']  = "Caught Exception: " . $ex->getMessage() . "\n";
    		$result['message'] .= "Response Status Code: " . $ex->getStatusCode() . "\n";
    		$result['message'] .= "Error Code: " . $ex->getErrorCode() . "\n";
    		$result['message'] .= "Error Type: " . $ex->getErrorType() . "\n";
    		$result['message'] .= "Request ID: " . $ex->getRequestId() . "\n";
    		$result['message'] .= "XML:  " . $ex->getXML() . "\n";
    		$result['message'] .= "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n";
		}
		return $result;
   }
  
   
   /*
    * 和REPORT LIST一样，这个可以根据状态来查询
    * */
   public function GetReportRequestList($nextToken = null,$reportType = null,$fromDate = null,$toDate = null,$requestIDArr = null,$status = null){
   		$list = array();
   		try{
   			//if token,则递归调用报告
   			if(is_null($nextToken)){
   				$request = new MarketplaceWebService_Model_GetReportRequestListRequest();
				
   				$request->setMerchant($this->merchant_id);
   				
				if($reportType != null){
					$reportTypObj = new MarketplaceWebService_Model_TypeList();
					$reportTypObj->setType(array($reportType));
					$request->setReportTypeList($reportTypObj);
				}
				if($requestIDArr != null){
					$requestID = new MarketplaceWebService_Model_IdList();
					$requestID->setID($requestIDArr);
					$request->setReportRequestIdList($requestID);
				}
   				if($status != null){
					$statusObj = new MarketplaceWebService_Model_StatusList();
					$statusObj->setStatus($status);
					$request->setReportProcessingStatusList($statusObj);
				}
				if($fromDate != null){
					//北京时间准备标准时间是减去8小时
					//$fromDate = date('Y-m-d H:i:s',strtotime("-8 hours",strtotime($fromDate)));
					$fromDate = date('Y-m-d H:i:s',strtotime($fromDate));
					$fromDate = new DateTime($fromDate, new DateTimeZone('UTC'));
					$request->setRequestedFromDate($fromDate);
				}
				if($toDate != null){
					//北京时间准备标准时间是减去8小时
					$toDate = date('Y-m-d H:i:s',strtotime("-8 hours",strtotime($toDate)));
					$toDate = new DateTime($toDate, new DateTimeZone('UTC'));
					$request->setRequestedToDate($toDate);
				}
				//print_r($request);
				$response = $this->_service->getReportRequestList($request);
				if ($response->isSetGetReportRequestListResult()) {
					$getReportRequestListResult = $response->getGetReportRequestListResult();
					
					$reportRequestInfoList = $getReportRequestListResult->getReportRequestInfoList();
					$list = $this->getReportRequestListArr($reportRequestInfoList);
					
					if ($getReportRequestListResult->isSetNextToken()){
						$list = array_merge($list,$this->GetReportRequestList($getReportRequestListResult->getNextToken()));	
					}
				}
   			}else{//取nextToken
   				$request = new MarketplaceWebService_Model_GetReportRequestListByNextTokenRequest();
				$request->setMerchant($this->merchant_id);
			   	$request->setNextToken($nextToken);
			   	$response = $this->_service->getReportRequestListByNextToken($request);
			   	if ($response->isSetGetReportRequestListByNextTokenResult()) { 
			   		$getReportRequestListByNextTokenResult = $response->getGetReportRequestListByNextTokenResult();
			   		$reportRequestInfoList = $getReportRequestListByNextTokenResult->getReportRequestInfoList();
                   	$list = $this->getReportRequestListArr($reportRequestInfoList);
					
			   		if ($getReportRequestListByNextTokenResult->isSetNextToken()){
			   			$list = array_merge($list,$this->GetReportRequestList($getReportRequestListByNextTokenResult->getNextToken()));
			   		}
			   	}
			   	
   			}
		}catch(MarketplaceWebService_Exception $ex){
			$list['flag'] = '0';
			$list['code'] = $ex->getStatusCode();
			$list['message']  = "Caught Exception: " . $ex->getMessage() . "\n";
    		$list['message'] .= "Response Status Code: " . $ex->getStatusCode() . "\n";
    		$list['message'] .= "Error Code: " . $ex->getErrorCode() . "\n";
    		$list['message'] .= "Error Type: " . $ex->getErrorType() . "\n";
    		$list['message'] .= "Request ID: " . $ex->getRequestId() . "\n";
    		$list['message'] .= "XML:  " . $ex->getXML() . "\n";
    		$list['message'] .= "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n";
		}
		
		return $list;
   }
   
   private function getReportRequestListArr($reportRequestInfoList){
   		$i = 0;
   		$list = array();
   		
		foreach ($reportRequestInfoList as $reportRequestInfo) {
			//print_r($reportRequestInfo);exit;
			$list[$i]['report_request_id'] 	= $reportRequestInfo->getReportRequestId(); 
			$list[$i]['report_type'] 		= $reportRequestInfo->getReportType(); 
			$list[$i]['start_date'] 		= $reportRequestInfo->getStartDate()->format(DATE_FORMAT); 
			$list[$i]['end_date'] 			= $reportRequestInfo->getEndDate()->format(DATE_FORMAT); 
			$list[$i]['submit_date'] 		= $reportRequestInfo->getSubmittedDate()->format(DATE_FORMAT); 
			$list[$i]['processing_status'] 	= $reportRequestInfo->getReportProcessingStatus(); 
			$list[$i]['report_id'] 			= $reportRequestInfo->getGeneratedReportId();
			$i++; 
		}
		return $list;
   }
   
   
   public function GetReport($ReportId){
   		$result = array();
   		try{
	   		$request = new MarketplaceWebService_Model_GetReportRequest();
			$request->setMerchant($this->merchant_id);
			$tmp_file = @fopen('php://memory', 'rw+');
			$request->setReport($tmp_file);
			$request->setReportId($ReportId);
			$response = $this->_service->getReport($request);
 			$result['report'] =  stream_get_contents($tmp_file);
		}catch(MarketplaceWebService_Exception $ex){
			$result['flag'] = '0';
			$result['code'] = $ex->getStatusCode();
			$result['message']  = "Caught Exception: " . $ex->getMessage() . "\n";
    		$result['message'] .= "Response Status Code: " . $ex->getStatusCode() . "\n";
    		$result['message'] .= "Error Code: " . $ex->getErrorCode() . "\n";
    		$result['message'] .= "Error Type: " . $ex->getErrorType() . "\n";
    		$result['message'] .= "Request ID: " . $ex->getRequestId() . "\n";
    		$result['message'] .= "XML:  " . $ex->getXML() . "\n";
    		$result['message'] .= "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n";
		}
		return $result;
   }
   
   /* request report
    * 申请创建报告计划，暂时不做
    * _GET_MERCHANT_LISTINGS_DATA_  【所有商品】一个小时一个
    * _GET_CONVERGED_FLAT_FILE_SOLD_LISTINGS_DATA_【已经出售商品】
    * */
   public function RequestReport(){}
   
   
   /*获取产品的类别*/
   public function getProductCatalog(){}
   
   /** 
    * 设定AMAZON报告的SCHEDULE 序列，目前程序不做，用测试环境创建，会自动创建报表
    * _GET_MERCHANT_LISTINGS_DATA_  【所有商品】一个小时一个
    * _GET_CONVERGED_FLAT_FILE_SOLD_LISTINGS_DATA_【已经出售商品】
    * 以后做一个管理
    */
   public function ManageReportSchedule(){}
   public function GetReportList(){}
   
   /*获取产品的价格
    * @最大20个ASIN
    * */
   public function GetMyPriceForASIN($asinArr){
   		$result = array();
   		try{
   			$asinList = new MarketplaceWebServiceProducts_Model_ASINListType();
   			$asinList->setASIN($asinArr);
   			
   			$request = new MarketplaceWebServiceProducts_Model_GetMyPriceForASINRequest();
    		$request->setSellerId($this->merchant_id);
    		$request->setMarketplaceId($this->marketplace_id);
    		$request->setASINList($asinList);
   			$response = $this->_service->getMyPriceForASIN($request);
   		
   			$getMyPriceForASINResultList = $response->getGetMyPriceForASINResult();
           	
   			foreach ($getMyPriceForASINResultList as $getMyPriceForASINResult) {
   				$asin = $getMyPriceForASINResult->getASIN();
            	
            	$result[$asin]['status'] = $getMyPriceForASINResult->getStatus();
            	
            	if ($getMyPriceForASINResult->isSetProduct()) {
            		$product = $getMyPriceForASINResult->getProduct();
            		//product ignore,请参考SDK
            		$identifiers = $product->getIdentifiers();
            		if ($identifiers->isSetMarketplaceASIN()) {
            			$marketplaceASIN = $identifiers->getMarketplaceASIN();
            			if ($marketplaceASIN->isSetMarketplaceId()) 
                        {
                          $result[$asin]['marketplace_id'] =$marketplaceASIN->getMarketplaceId();
                          $result[$asin]['m_asin'] = $marketplaceASIN->getASIN();
                        }
            		}
            		
            		if ($product->isSetCompetitivePricing()) {
            			$competitivePricing = $product->getCompetitivePricing();
            			if ($competitivePricing->isSetCompetitivePrices()) {
            				$competitivePrices = $competitivePricing->getCompetitivePrices();
                            $competitivePriceList = $competitivePrices->getCompetitivePrice();
                            $i =0;
                            foreach ($competitivePriceList as $competitivePrice) {
                            	$result[$asin]['competitive'][$i]['condition'] =$competitivePrice->getCondition();
                            	$result[$asin]['competitive'][$i]['subcondition'] =$competitivePrice->getSubcondition();
                            	$result[$asin]['competitive'][$i]['belongsToRequester'] = $competitivePrice->getBelongsToRequester();
                            	$result[$asin]['competitive'][$i]['competitivePriceId'] = $competitivePrice->getCompetitivePriceId();
                            	
                            	$price = $competitivePrice->getPrice();
                            	
                            	$landedPrice = $price->getLandedPrice();
                            	$result[$asin]['competitive'][$i]['landed_currencyCode'] =$landedPrice->getCurrencyCode();
                            	$result[$asin]['competitive'][$i]['landed_amount'] 	 	 =$landedPrice->getAmount();
                            	
                            	$listingPrice = $price->getListingPrice();
                            	$result[$asin]['competitive'][$i]['listing_currencyCode'] = $listingPrice->getCurrencyCode();
                            	$result[$asin]['competitive'][$i]['listing_amount'] 	  = $listingPrice->getAmount();
                            	
                            	$shipping = $price->getShipping();
                            	$result[$asin]['competitive'][$i]['shipping_currencyCode'] =$shipping->getCurrencyCode();
                            	$result[$asin]['competitive'][$i]['shipping_amount'] 	  =$shipping->getAmount();
                            	
                            	$i++;
                            }
                            
                            if ($competitivePricing->isSetNumberOfOfferListings()) {
            					$numberOfOfferListings = $competitivePricing->getNumberOfOfferListings();
                                $offerListingCountList = $numberOfOfferListings->getOfferListingCount();
								$i =0;
                                foreach ($offerListingCountList as $offerListingCount) {
                                	$result[$asin]['competitive'][$i]['offerlistingcount_codition'] = $offerListingCount->getCondition();
                                	$result[$asin]['competitive'][$i]['offerlistingcount_codition'] = $offerListingCount->getValue();
                                	$i++;
                                }
                            }
                            
                            if ($competitivePricing->isSetTradeInValue()) {
                            	$tradeInValue = $competitivePricing->getTradeInValue();
                            	$result[$asin]['competitive'][$i]['trade_CurrencyCode'] = $tradeInValue->getCurrencyCode();
                            	$result[$asin]['competitive'][$i]['trade_amount'] = $tradeInValue->getAmount();
                            }
                            
                            
            			}
            		}//end CompetitivePricing
            		 if ($product->isSetSalesRankings()) {
            		 	$salesRankings = $product->getSalesRankings();
                        $salesRankList = $salesRankings->getSalesRank();
                        $i =0;
                        foreach ($salesRankList as $salesRank) {
                        	$result[$asin]['salerank'][$i]['category_id'] = $salesRank->getProductCategoryId();
                        	$result[$asin]['salerank'][$i]['rank'] = $salesRank->getRank();
                        	$i++;
                        }
            		 }//
            		 if ($product->isSetLowestOfferListings()) {
            		 	$lowestOfferListings = $product->getLowestOfferListings();
                        $lowestOfferListingList = $lowestOfferListings->getLowestOfferListing();
                        $i =0;
                        foreach ($lowestOfferListingList as $lowestOfferListing) {
                        	$qualifiers = $lowestOfferListing->getQualifiers();
                        	$result[$asin]['lowestOfferListing'][$i]['Condition'] = $qualifiers->getItemCondition();
                        	$result[$asin]['lowestOfferListing'][$i]['Subcondition'] = $qualifiers->getItemSubcondition();
                        	$result[$asin]['lowestOfferListing'][$i]['FulfillmentChannel'] = $qualifiers->getFulfillmentChannel();
                        	$result[$asin]['lowestOfferListing'][$i]['FulfillmentChannel'] = $qualifiers->getShipsDomestically();
                        	if ($qualifiers->isSetShippingTime()) {
                        		$shippingTime = $qualifiers->getShippingTime();
                        		$result[$asin]['lowestOfferListing'][$i]['shippingTime_max'] = $shippingTime->getMax();
                        	}
                        	$result[$asin]['lowestOfferListing'][$i]['SellerPositiveFeedbackRating'] = $qualifiers->getSellerPositiveFeedbackRating();
                        	$result[$asin]['lowestOfferListing'][$i]['NumberOfOfferListingsConsidered'] = $lowestOfferListing->getNumberOfOfferListingsConsidered();
                        	$result[$asin]['lowestOfferListing'][$i]['SellerFeedbackCount'] = $lowestOfferListing->getSellerFeedbackCount();
                        	if ($lowestOfferListing->isSetPrice()) { 
                        		$price1 = $lowestOfferListing->getPrice();
                        		if ($price1->isSetLandedPrice()) { 
	                        		$landedPrice1 = $price1->getLandedPrice();
	                        		$result[$asin]['lowestOfferListing'][$i]['landed_currencyCode'] = $landedPrice1->getCurrencyCode();
	                        		$result[$asin]['lowestOfferListing'][$i]['landed_amount'] = $$landedPrice1->getAmount();
                        		}
                        		if ($price1->isSetListingPrice()) {
	                        		$listingPrice1 = $price1->getListingPrice();
	                        		$result[$asin]['lowestOfferListing'][$i]['listing_currencyCode'] = $listingPrice1->getCurrencyCode();
	                        		$result[$asin]['lowestOfferListing'][$i]['listing_amount'] = $listingPrice1->getAmount();
                        		}
                        		if ($price1->isSetShipping()) {
                        		 	$shipping1 = $price1->getShipping();
                        		 	$result[$asin]['lowestOfferListing'][$i]['shipping_currencyCode'] = $shipping1->getCurrencyCode();
                        		 	$result[$asin]['lowestOfferListing'][$i]['shipping_amount'] = $shipping1->getAmount();
                        		}
                        	}
                        	if ($lowestOfferListing->isSetMultipleOffersAtLowestPrice()){
                        		$result[$asin]['lowestOfferListing'][$i]['MultipleOffersAtLowestPrice'] =  $lowestOfferListing->getMultipleOffersAtLowestPrice();
                        	}
                        	
                        	$i++;
                        }
            		 }
            		 if ($product->isSetOffers()) {
            		 	$offers = $product->getOffers();
                        $offerList = $offers->getOffer();
                        $i=0;
                        foreach ($offerList as $offer) {
                        	if ($offer->isSetBuyingPrice()) {
                        		$buyingPrice = $offer->getBuyingPrice();
                                if ($buyingPrice->isSetLandedPrice()) {
                                	$landedPrice2 = $buyingPrice->getLandedPrice();
                                	$result[$asin]['offer'][$i]['landed_currencyCode'] = $landedPrice2->getCurrencyCode();
                                	$result[$asin]['offer'][$i]['landed_amount'] 	   = $landedPrice2->getAmount();
                                }
                                if ($buyingPrice->isSetListingPrice()) {
                                	$listingPrice2 = $buyingPrice->getListingPrice();
                                	$result[$asin]['offer'][$i]['listing_currencyCode'] = $listingPrice2->getCurrencyCode();
                                	$result[$asin]['offer'][$i]['listing_amount'] = $listingPrice2->getAmount();
                                }
                        		if ($buyingPrice->isSetShipping()) {
                                	$shipping2 = $buyingPrice->getShipping();
                                	$result[$asin]['offer'][$i]['shipping_currencyCode'] = $shipping2->getCurrencyCode();
                                	$result[$asin]['offer'][$i]['shipping_amount'] = $shipping2->getAmount();
                                }
                        	}
                        	if ($offer->isSetRegularPrice()) {
		            		 	$regularPrice = $offer->getRegularPrice();
		            		 	$result[$asin]['offer'][$i]['regular_currencyCode'] = $regularPrice->getCurrencyCode();
		            		 	$result[$asin]['offer'][$i]['regular_amount'] = $regularPrice->getAmount();
            			 	}
            		 		if ($offer->isSetFulfillmentChannel()){
            		 			$result[$asin]['offer'][$i]['FulfillmentChannel'] = $offer->getFulfillmentChannel();
            		 		}
            		 		if ($offer->isSetItemCondition()){
            		 			$result[$asin]['offer'][$i]['ItemCondition'] = $offer->getItemCondition();
            		 		}
            		 		if ($offer->isSetItemSubCondition()){
            		 			$result[$asin]['offer'][$i]['ItemSubCondition'] = $offer->getItemSubCondition();
            				 }
            				if ($offer->isSetSellerId()){
            					$result[$asin]['offer'][$i]['seller_id'] = $offer->getSellerId();
            				}
            				if ($offer->isSetSellerSKU()){
            					$result[$asin]['offer'][$i]['seller_sku'] = $offer->getSellerSKU();
            				}
                        	$i++;
                        }
            		 }//offers
            	}
            }
   		}catch(MarketplaceWebService_Exception $ex){
			$result['flag'] = '0';
			$result['code'] = $ex->getStatusCode();
			$result['message']  = "Caught Exception: " . $ex->getMessage() . "\n";
    		$result['message'] .= "Response Status Code: " . $ex->getStatusCode() . "\n";
    		$result['message'] .= "Error Code: " . $ex->getErrorCode() . "\n";
    		$result['message'] .= "Error Type: " . $ex->getErrorType() . "\n";
    		$result['message'] .= "Request ID: " . $ex->getRequestId() . "\n";
    		$result['message'] .= "XML:  " . $ex->getXML() . "\n";
    		$result['message'] .= "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n";
		}
		return $result;
   }
   
   /*获取产品的价格
    * @最大20个seller_sku
    * */
   public function GetMyPriceForSku($skuArr){
   		$result = array();
   		try{
   			$skuList = new MarketplaceWebServiceProducts_Model_SellerSKUListType();
   			$skuList->setSellerSKU($skuArr);
   			
   			$request = new MarketplaceWebServiceProducts_Model_GetMyPriceForSKURequest();
    		$request->setSellerId($this->merchant_id);
    		$request->setMarketplaceId($this->marketplace_id);
    		$request->setSellerSKUList($skuList);
   			$response = $this->_service->getMyPriceForSKU($request);
   			$getMyPriceForSKUResultList = $response->getGetMyPriceForSKUResult();
           	$j =0;
   			foreach ($getMyPriceForSKUResultList as $getMyPriceForSKUResult) {
   				$asin = $getMyPriceForSKUResult->getSellerSKU();
            	
            	$result[$asin]['status'] = $getMyPriceForSKUResult->getStatus();
            	
            	if ($getMyPriceForSKUResult->isSetProduct()) {
            		$product = $getMyPriceForSKUResult->getProduct();
            		//product ignore,请参考SDK
            		$identifiers = $product->getIdentifiers();
            		if ($identifiers->isSetMarketplaceSellerSKU()) {
            			$marketplaceSellerSKU = $identifiers->getMarketplaceSellerSKU();
            			if ($marketplaceSellerSKU->isSetMarketplaceId()) 
                        {
                          $result[$asin]['marketplace_id'] =$marketplaceSellerSKU->getMarketplaceId();
                          $result[$asin]['m_asin'] = $marketplaceSellerSKU->getSellerSKU();
                        }
            		}
            		
            		if ($product->isSetCompetitivePricing()) {
            			$competitivePricing = $product->getCompetitivePricing();
            			if ($competitivePricing->isSetCompetitivePrices()) {
            				$competitivePrices = $competitivePricing->getCompetitivePrices();
                            $competitivePriceList = $competitivePrices->getCompetitivePrice();
                            $i =0;
                            foreach ($competitivePriceList as $competitivePrice) {
                            	$result[$asin]['competitive'][$i]['condition'] =$competitivePrice->getCondition();
                            	$result[$asin]['competitive'][$i]['subcondition'] =$competitivePrice->getSubcondition();
                            	$result[$asin]['competitive'][$i]['belongsToRequester'] = $competitivePrice->getBelongsToRequester();
                            	$result[$asin]['competitive'][$i]['competitivePriceId'] = $competitivePrice->getCompetitivePriceId();
                            	
                            	$price = $competitivePrice->getPrice();
                            	
                            	$landedPrice = $price->getLandedPrice();
                            	$result[$asin]['competitive'][$i]['landed_currencyCode'] =$landedPrice->getCurrencyCode();
                            	$result[$asin]['competitive'][$i]['landed_amount'] 	 	 =$landedPrice->getAmount();
                            	
                            	$listingPrice = $price->getListingPrice();
                            	$result[$asin]['competitive'][$i]['listing_currencyCode'] = $listingPrice->getCurrencyCode();
                            	$result[$asin]['competitive'][$i]['listing_amount'] 	  = $listingPrice->getAmount();
                            	
                            	$shipping = $price->getShipping();
                            	$result[$asin]['competitive'][$i]['shipping_currencyCode'] =$shipping->getCurrencyCode();
                            	$result[$asin]['competitive'][$i]['shipping_amount'] 	  =$shipping->getAmount();
                            	
                            	$i++;
                            }
                            
                            if ($competitivePricing->isSetNumberOfOfferListings()) {
            					$numberOfOfferListings = $competitivePricing->getNumberOfOfferListings();
                                $offerListingCountList = $numberOfOfferListings->getOfferListingCount();
								$i =0;
                                foreach ($offerListingCountList as $offerListingCount) {
                                	$result[$asin]['competitive'][$i]['offerlistingcount_codition'] = $offerListingCount->getCondition();
                                	$result[$asin]['competitive'][$i]['offerlistingcount_codition'] = $offerListingCount->getValue();
                                	$i++;
                                }
                            }
                            
                            if ($competitivePricing->isSetTradeInValue()) {
                            	$tradeInValue = $competitivePricing->getTradeInValue();
                            	$result[$asin]['competitive'][$i]['trade_CurrencyCode'] = $tradeInValue->getCurrencyCode();
                            	$result[$asin]['competitive'][$i]['trade_amount'] = $tradeInValue->getAmount();
                            }
                            
                            
            			}
            		}//end CompetitivePricing
            		 if ($product->isSetSalesRankings()) {
            		 	$salesRankings = $product->getSalesRankings();
                        $salesRankList = $salesRankings->getSalesRank();
                        $i =0;
                        foreach ($salesRankList as $salesRank) {
                        	$result[$asin]['salerank'][$i]['category_id'] = $salesRank->getProductCategoryId();
                        	$result[$asin]['salerank'][$i]['rank'] = $salesRank->getRank();
                        	$i++;
                        }
            		 }//
            		 if ($product->isSetLowestOfferListings()) {
            		 	$lowestOfferListings = $product->getLowestOfferListings();
                        $lowestOfferListingList = $lowestOfferListings->getLowestOfferListing();
                        $i =0;
                        foreach ($lowestOfferListingList as $lowestOfferListing) {
                        	$qualifiers = $lowestOfferListing->getQualifiers();
                        	$result[$asin]['lowestOfferListing'][$i]['Condition'] = $qualifiers->getItemCondition();
                        	$result[$asin]['lowestOfferListing'][$i]['Subcondition'] = $qualifiers->getItemSubcondition();
                        	$result[$asin]['lowestOfferListing'][$i]['FulfillmentChannel'] = $qualifiers->getFulfillmentChannel();
                        	$result[$asin]['lowestOfferListing'][$i]['FulfillmentChannel'] = $qualifiers->getShipsDomestically();
                        	if ($qualifiers->isSetShippingTime()) {
                        		$shippingTime = $qualifiers->getShippingTime();
                        		$result[$asin]['lowestOfferListing'][$i]['shippingTime_max'] = $shippingTime->getMax();
                        	}
                        	$result[$asin]['lowestOfferListing'][$i]['SellerPositiveFeedbackRating'] = $qualifiers->getSellerPositiveFeedbackRating();
                        	$result[$asin]['lowestOfferListing'][$i]['NumberOfOfferListingsConsidered'] = $lowestOfferListing->getNumberOfOfferListingsConsidered();
                        	$result[$asin]['lowestOfferListing'][$i]['SellerFeedbackCount'] = $lowestOfferListing->getSellerFeedbackCount();
                        	if ($lowestOfferListing->isSetPrice()) { 
                        		$price1 = $lowestOfferListing->getPrice();
                        		if ($price1->isSetLandedPrice()) { 
	                        		$landedPrice1 = $price1->getLandedPrice();
	                        		$result[$asin]['lowestOfferListing'][$i]['landed_currencyCode'] = $landedPrice1->getCurrencyCode();
	                        		$result[$asin]['lowestOfferListing'][$i]['landed_amount'] = $$landedPrice1->getAmount();
                        		}
                        		if ($price1->isSetListingPrice()) {
	                        		$listingPrice1 = $price1->getListingPrice();
	                        		$result[$asin]['lowestOfferListing'][$i]['listing_currencyCode'] = $listingPrice1->getCurrencyCode();
	                        		$result[$asin]['lowestOfferListing'][$i]['listing_amount'] = $listingPrice1->getAmount();
                        		}
                        		if ($price1->isSetShipping()) {
                        		 	$shipping1 = $price1->getShipping();
                        		 	$result[$asin]['lowestOfferListing'][$i]['shipping_currencyCode'] = $shipping1->getCurrencyCode();
                        		 	$result[$asin]['lowestOfferListing'][$i]['shipping_amount'] = $shipping1->getAmount();
                        		}
                        	}
                        	if ($lowestOfferListing->isSetMultipleOffersAtLowestPrice()){
                        		$result[$asin]['lowestOfferListing'][$i]['MultipleOffersAtLowestPrice'] =  $lowestOfferListing->getMultipleOffersAtLowestPrice();
                        	}
                        	
                        	$i++;
                        }
            		 }
            		 if ($product->isSetOffers()) {
            		 	$offers = $product->getOffers();
                        $offerList = $offers->getOffer();
                        $i=0;
                        foreach ($offerList as $offer) {
                        	if ($offer->isSetBuyingPrice()) {
                        		$buyingPrice = $offer->getBuyingPrice();
                                if ($buyingPrice->isSetLandedPrice()) {
                                	$landedPrice2 = $buyingPrice->getLandedPrice();
                                	$result[$asin]['offer'][$i]['landed_currencyCode'] = $landedPrice2->getCurrencyCode();
                                	$result[$asin]['offer'][$i]['landed_amount'] 	   = $landedPrice2->getAmount();
                                }
                                if ($buyingPrice->isSetListingPrice()) {
                                	$listingPrice2 = $buyingPrice->getListingPrice();
                                	$result[$asin]['offer'][$i]['listing_currencyCode'] = $listingPrice2->getCurrencyCode();
                                	$result[$asin]['offer'][$i]['listing_amount'] = $listingPrice2->getAmount();
                                }
                        		if ($buyingPrice->isSetShipping()) {
                                	$shipping2 = $buyingPrice->getShipping();
                                	$result[$asin]['offer'][$i]['shipping_currencyCode'] = $shipping2->getCurrencyCode();
                                	$result[$asin]['offer'][$i]['shipping_amount'] = $shipping2->getAmount();
                                }
                        	}
                        	if ($offer->isSetRegularPrice()) {
		            		 	$regularPrice = $offer->getRegularPrice();
		            		 	$result[$asin]['offer'][$i]['regular_currencyCode'] = $regularPrice->getCurrencyCode();
		            		 	$result[$asin]['offer'][$i]['regular_amount'] = $regularPrice->getAmount();
            			 	}
            		 		if ($offer->isSetFulfillmentChannel()){
            		 			$result[$asin]['offer'][$i]['FulfillmentChannel'] = $offer->getFulfillmentChannel();
            		 		}
            		 		if ($offer->isSetItemCondition()){
            		 			$result[$asin]['offer'][$i]['ItemCondition'] = $offer->getItemCondition();
            		 		}
            		 		if ($offer->isSetItemSubCondition()){
            		 			$result[$asin]['offer'][$i]['ItemSubCondition'] = $offer->getItemSubCondition();
            				 }
            				if ($offer->isSetSellerId()){
            					$result[$asin]['offer'][$i]['seller_id'] = $offer->getSellerId();
            				}
            				if ($offer->isSetSellerSKU()){
            					$result[$asin]['offer'][$i]['seller_sku'] = $offer->getSellerSKU();
            				}
                        	$i++;
                        }
            		 }//offers
            	}
            	$j++;
            }
   		}catch(MarketplaceWebService_Exception $ex){
			$result['flag'] = '0';
			$result['code'] = $ex->getStatusCode();
			$result['message']  = "Caught Exception: " . $ex->getMessage() . "\n";
    		$result['message'] .= "Response Status Code: " . $ex->getStatusCode() . "\n";
    		$result['message'] .= "Error Code: " . $ex->getErrorCode() . "\n";
    		$result['message'] .= "Error Type: " . $ex->getErrorType() . "\n";
    		$result['message'] .= "Request ID: " . $ex->getRequestId() . "\n";
    		$result['message'] .= "XML:  " . $ex->getXML() . "\n";
    		$result['message'] .= "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n";
		}
		return $result;
   }
   
   
	/**
	 * 通过ASIN获取该商品卖家组各自的最低价格
	 */
    public function GetLowestOfferListingsForASINResult(array $asinArr,$type){	
    	try{
	    	$asinList = new MarketplaceWebServiceProducts_Model_ASINListType();
	    	$asinList->setASIN($asinArr);
	    	$flag = false;
	    	$request = new MarketplaceWebServiceProducts_Model_GetLowestOfferListingsForASINRequest();
	    	$request->setSellerId($this->merchant_id);
	    	$request->setMarketplaceId($this->marketplace_id);
	    	$request->setASINList($asinList);
	    	$request->setExcludeMe(true);
	    	
	    	$response = $this->_service->getLowestOfferListingsForASIN($request);
	    	$result = $response->getGetLowestOfferListingsForASINResult();
	    	
	    	if(!empty($result)){
	    		
	    		$data = array();
	    		include_once("inc/utility_all.php");
	    		
	    		
		    	foreach($result as $res){
		    		
		    		if($res->isSetProduct()){
		    			
		    			$product = $res->getProduct();
		    			if($product->isSetIdentifiers()){
		    				$temp_asin = $product->getIdentifiers()->getMarketplaceASIN()->getASIN();
		    			
			    			if($product->isSetLowestOfferListings()){
			    				$lowestOfferListings = $product->getLowestOfferListings();
			    				
			    				if($lowestOfferListings->isSetLowestOfferListing()){
			    					$lowestOfferListing = $lowestOfferListings->getLowestOfferListing();
			    					
			    					if(!empty($lowestOfferListing)){
			    						foreach ($lowestOfferListing as $one){
			    							if($one->isSetQualifiers()){
			    								$temp = array();
			    								$qualifiers = $one->getQualifiers();
			    								$temp['ItemCondition'] = $qualifiers->getItemCondition();
			    								$temp['ItemSubcondition'] = $qualifiers->getItemSubcondition();
			    								$temp['FulfillmentChannel'] = $qualifiers->getFulfillmentChannel();
			    								if($qualifiers->getShipsDomestically()){
			    									$temp['ShipsDomestically'] = 1;
			    								}else{
			    									$temp['ShipsDomestically'] = 0;
			    								}
			    								$temp['MaxShippingTime'] = $qualifiers->getShippingTime()->getMax();
			    								$temp['SellerPositiveFeedbackRating'] = $qualifiers->getSellerPositiveFeedbackRating();
			    								$temp['NumberOfOfferListingsConsidered'] = $one->getNumberOfOfferListingsConsidered();
			    								$temp['SellerFeedbackCount'] = $one->getSellerFeedbackCount();
			    							}
			    
			    							if($one->isSetPrice()){
			    								$prices = $one->getPrice();			    							
			    								if($prices->isSetLandedPrice()){
			    									$temp['LandedPrice'] = $prices->getLandedPrice()->getAmount();
			    									$temp['LandedCurrencyCode'] = $prices->getLandedPrice()->getCurrencyCode();
			    								}else{
			    									$temp['LandedPrice'] =  $prices->getListingPrice()->getAmount();
			    									$temp['LandedCurrencyCode'] = '';
			    								}
			    								if($prices->isSetListingPrice()){
			   	    								$temp['ListingPrice'] = $prices->getListingPrice()->getAmount();
			    									$temp['ListingCurrencyCode'] = $prices->getListingPrice()->getCurrencyCode();
			    								}else{
			    									continue;
			    								}
			    						
			    								if($prices->isSetShipping()){
				    								$temp['Shipping'] = $prices->getShipping()->getAmount();
				    								$temp['ShipCurrencyCode'] = $prices->getShipping()->getCurrencyCode();
				    								
			    								}else{
			    									$temp['shipping'] = 0;
			    									$temp['ShipCurrencyCode'] = '';
			    								}
			    							}
			    							if($one->isSetMultipleOffersAtLowestPrice()){
			    								if($one->getMultipleOffersAtLowestPrice()){
			    									if(mb_substr_count($one->getMultipleOffersAtLowestPrice(),'True')){
			    										$temp['MultipleOffersAtLowestPrice'] = 1;
			    									}else if(mb_substr_count($one->getMultipleOffersAtLowestPrice(),'False')){
			    										$temp['MultipleOffersAtLowestPrice'] = 0;	
			    									}
			    									else{
			    										$temp['MultipleOffersAtLowestPrice'] = 2;
			    											
			    									}
			    								}
			    							
			    							}
			    							$temp['asin'] = $temp_asin;
			    							$temp['account_id'] = $this->account_id;
			    							$temp['type'] = $type;
			    							$data[$temp_asin][] = $temp;
			    				
			    						}
			    					}
			    				}
			    			}
		    			}
		    		}
		    			
		    	}
	    	}
	    	return $data;
    	}catch(MarketplaceWebServiceProducts_Exception $e){
    		$message = $e->getMessage();
    		$this->logError('api_price','AMAZONAPI: GetLowestOfferListingsForASINResult',$message);
    		return false;
    	}
    	
    }
    
    
    public function changePrice($filename,$account_id,$asins=array(),$ids=array(),$adjust_type =1){
    	
		try{

			$feedHandle = @fopen(getModel('amazon_adjust_asin_price')->dirName.'/'.$filename, 'r+');
	    	$request = new MarketplaceWebService_Model_SubmitFeedRequest();
	    	$request->setMerchant($this->merchant_id);
	    	$request->setMarketplace($this->marketplace_id);
	    	$request->setPurgeAndReplace(false);
	    	$request->setFeedType('_POST_FLAT_FILE_PRICEANDQUANTITYONLY_UPDATE_DATA_');
			$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
	    	$request->setFeedContent($feedHandle);
	    	$response = $this->_service->submitFeed($request);
	    	@fclose($feedHandle);
	    	if ($response->isSetSubmitFeedResult()) {
	    			$amazonEditModel = getModel('amazon_edit_asin_price');
					$submitFeedResult = $response->getSubmitFeedResult();
					$feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
					if ($feedSubmissionInfo->isSetFeedSubmissionId()){
						$feedSubmissionId = $feedSubmissionInfo->getFeedSubmissionId();
						foreach($ids as $key=> $id){
							$condition = array('id'=>$id);
							$data = array();
							$data['submission_id'] = $feedSubmissionId;
							$data['file_name'] = $filename;
							$amazonEditModel->update($data,$condition);
						}
						return true;
					}
	    	}
	    	
    	
		}catch(MarketplaceWebService_Exception $ex){
		  	$error_message = '';
    		$error_message .= "Caught Exception: " . $ex->getMessage().';';
    		$error_message .= "Response Status Code: " . $ex->getStatusCode() . ";";
	        $error_message .= "Error Code: " . $ex->getErrorCode() . ";";
	        $error_message .= "Error Type: " . $ex->getErrorType() . ";";
	        $error_message .= "Request ID: " . $ex->getRequestId() . ";";
	        $error_message .= "XML: " . $ex->getXML() . ";";
	        
	       foreach($ids as $key=> $id){
				$condition = array('id'=>$id);
				$data = array();
				$data['is_success'] = 2;
				$amazonEditModel->update($data,$condition);
			} 
	        
			$this->logError('SubmitFeedResult', 'amazonAPI::changePrice', $message);
		}
    }
    

    
    public function GetAmazonFeedSubmissionResult($submission_id,$fileName){
    	try{
	    	$request =  new MarketplaceWebService_Model_getFeedSubmissionResultRequest();
	    	$request->setMerchant($this->merchant_id);
	    	$request->setMarketplace($this->marketplace_id);
	    	$request->setFeedSubmissionId($submission_id);
	    	$fileName = getModel('amazon_adjust_asin_price')->dirName.'/'.$fileName;
	    	$handle = @fopen($fileName, 'rw+');
	    	$request->setFeedSubmissionResult($handle);
	    	$response = $this->_service->getFeedSubmissionResult($request);
	   		@fclose($handle);
	   		$result_data = array();
            if($response->isSetGetFeedSubmissionResultResult()){
             	$getFeedSubmissionResultResult = $response->getGetFeedSubmissionResultResult(); 
                if ($getFeedSubmissionResultResult->isSetContentMd5()){
                 	$content = file_get_contents($fileName);
                 
               
                	if(mb_substr_count($content,"sku\t\price\r\n")||mb_substr_count($content,'not ready')||mb_substr_count($content,'Internal Error')){
                 		$result_data['ready'] = true;
                 	}elseif(mb_substr_count($content,'error-message')){                 		
                 		$result_data['error_sku'] =	$this->_submissionFailHandle($fileName);
                 	}else{
                 		$result_data['success'] = true;
                 	}
                }
          }
          return $result_data;		
    	}catch (MarketplaceWebService_Exception $ex){
    		$error_message = '';
    		$error_message .= "Caught Exception: " . $ex->getMessage().';';
    		$error_message .= "Response Status Code: " . $ex->getStatusCode() . ";";
	        $error_message .= "Error Code: " . $ex->getErrorCode() . ";";
	        $error_message .= "Error Type: " . $ex->getErrorType() . ";";
	        $error_message .= "Request ID: " . $ex->getRequestId() . ";";
	        $error_message .= "XML: " . $ex->getXML() . ";";
	         $this->logError('GetFeedSubmissionResult','AmazonAPI::GetAmazonFeedSubmissionResult', $error_message);
	         return false;
    	}
    }
    
    
    protected function _submissionFailHandle($fileName){
    		$handle = @fopen($fileName,'r+');
			$fail_seller_skus = array();
			while(!feof($handle)){
				$temp = array();
				$line = fgets($handle);
				if(strpos($line,'Error')&&!mb_substr_count($line,'the same as previous')){
					$temp = explode("\t",$line);
					array_push($fail_seller_skus, $temp[1]);
				}
			}
			if(!empty($fail_seller_skus)){
				$fail_seller_skus = array_unique($fail_seller_skus);			
			}
			return $fail_seller_skus;
    }
    
    
	public function getListInventorySupply($seller_sku,$sku){
    	try{
    	
	    	$request = new FBAInventoryServiceMWS_Model_ListInventorySupplyRequest();
	    	new FBAInventoryServiceMWS_Mock();
	    	$request->setSellerId($this->merchant_id);
			$sellerSkuslistInventorySupplyRequest = new FBAInventoryServiceMWS_Model_SellerSkuList();	
	    	$sellSkurMembers = $sellerSkuslistInventorySupplyRequest->setmember($seller_sku);
	    	
	    
	    	$request->setSellerSkus($sellSkurMembers);
	    	$request->setResponseGroup('Basic');
	    	$fbaStockModel = getModel('abroad_stock');
	    	$listingSellingModel = getModel('amazon_listing_selling_main');
	    	$response = $this->_service->listInventorySupply($request);
	    	if($response->isSetListInventorySupplyResult()){
	    		$listInventorySupplyResult = $response->getListInventorySupplyResult();
	    		if($listInventorySupplyResult->isSetInventorySupplyList()){
	    			$inventorySupplyList = $listInventorySupplyResult->getInventorySupplyList();
	    			if($inventorySupplyList->isSetMember()){
	    				
	    				$fbaStockModel->startTrans();
	    				$time = time();
	    				$members = $inventorySupplyList->getMember();
	    				
	    				foreach($members as $key=>$member){
	    					$data = array();
	    					if($member->isSetTotalSupplyQuantity()){
	    						 $data['validQty'] = $data['qty'] = $member->getTotalSupplyQuantity(); 
	    						 $data['pro_code'] = $sku[$key];
	    						 $data['pro_key'] = $member->getSellerSKU();
	    						 $data['last_update_time'] = time();
	    						 $data['area_code'] = 'AFN';
	    					
	    						 if($fbaStockModel->checkSellerSkuIsExist($data['pro_key'])){
	    						 	$fbaStockModel->update($data,array('area_code'=>'AFN','pro_key'=>$data['pro_key']));
	    						 }else{
	    						 	$data['realQty'] = 0;
	    						 	 $fbaStockModel->addFbaStock($data);
	    						 }
	    						
	    						 $listingSellingModel->update(array('fba_update_time'=>$time),array('account_id'=>$this->account_id,'seller_sku'=>$member->getSellerSKU()));
	    					
	    					}
	    				}
	    				$fbaStockModel->commit();
	    				return true;
	    			}
	    			
	    	
	    		}
	    	}
	    	
    	}catch(Exception $e){
    		$error_message = $e->getMessage();
    		$fbaStockModel->rollback();
    		$this->logError('getListInventorySupply','AmazonAPI::getListInventorySupply', $error_message);
    		return false;
    		
    	}
    	
    }
   
    
   public function logError($apiName,$tag, $error) {
	   	$localIp = isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "CLI";
	   	$logger = getModel('amazon_error_log');
	   	$logger->LOGDIR .=$apiName.'/';
	   	if ( is_dir($_SERVER['DOCUMENT_ROOT'] . '/' . $logger->LOGDIR) ) {
	   		$loggerDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $logger->LOGDIR;
	   	} else {
	   		$loggerDir = $logger->LOGDIR;
	   	}
	    if ( ! is_dir($loggerDir)) {
	   		mkdir($loggerDir, 0777, true);
	   	}
	  
	   	if ( ! is_dir($loggerDir . date("Y-m-d"))) {
	   		mkdir($loggerDir . date("Y-m-d"), 0777, true);
	   	}
	   	$loggerDir = $loggerDir . date("Y-m-d");
	   	$logger->conf["log_file"] = rtrim($loggerDir, '\\/') . '/' ."api_error.log";
	   	$logger->conf["separator"] = "^_^";
	   	$logData = array(
	   			date("Y-m-d H:i:s"),
	   			$tag,
	   			$localIp,
	   			PHP_OS,
	   			$error,
	   	);
	
	   	$logger->log($logData);
   }
   
   
  public function GetOrderByAmazonOrderId($orderId){
    
    	 $request = new MarketplaceWebServiceOrders_Model_GetOrderRequest();
 		 $request->setSellerId($this->merchant_id);
 		 $orderIdModel = new MarketplaceWebServiceOrders_Model_OrderIdList();
 		 $id = $orderIdModel->withId($orderId);
 		 $request->setAmazonOrderId($id); 
 		 $oneAmazonOrder = array();		
 		 $response = $this->_service->getOrder($request);
 		 if ($response->isSetGetOrderResult()) {
 		 	$getOrderResult = $response->getGetOrderResult();
 		 	$oneAmazonOrder[$orderId]['platform_code'] = 'AMAZON';
 		  if ($getOrderResult->isSetOrders()) { 
              
             $orders = $getOrderResult->getOrders();
             $orderList = $orders->getOrder();
             foreach ($orderList as $order) {
                        
              if ($order->isSetAmazonOrderId()) 
              {
                $oneAmazonOrder[$orderId]['amazon_order_id'] = $order->getAmazonOrderId();   
              }
              if ($order->isSetSellerOrderId()) 
              {
                 $oneAmazonOrder[$orderId]['seller_order_id'] = $order->getSellerOrderId();
              }
              if ($order->isSetPurchaseDate()) 
              {
        
           		$oneAmazonOrder[$orderId]['purchase_date'] = $order->getPurchaseDate() ;
              }
                            if ($order->isSetLastUpdateDate()) 
                            {
                               
                               $oneAmazonOrder[$orderId]['last_update_time'] = $order->getLastUpdateDate();
                            }
                            if ($order->isSetOrderStatus()) 
                            {
                           
                               $oneAmazonOrder[$orderId]['order_status'] = $order->getOrderStatus();
                            }
                            if ($order->isSetFulfillmentChannel()) 
                            {
                
                                $oneAmazonOrder[$orderId]['amazon_fulfill_channel'] = $order->getFulfillmentChannel();
                            }
                            if ($order->isSetSalesChannel()) 
                            {
                         
                                $oneAmazonOrder[$orderId]['amazon_sales_channel'] =  $order->getSalesChannel();
                            }
                            if ($order->isSetOrderChannel()) 
                            {
                           
                                $oneAmazonOrder[$orderId]['amazon_order_channel'] = $order->getOrderChannel();
                            }
                            if ($order->isSetShipServiceLevel()) 
                            {
                                $oneAmazonOrder[$orderId]['ship_service_level'] = $order->getShipServiceLevel();
                            }
                            if ($order->isSetShippingAddress()) { 
                 
                                $shippingAddress = $order->getShippingAddress();
                                if ($shippingAddress->isSetName()) 
                                {
                                   
                                    $oneAmazonOrder[$orderId]['customer']['ship_name'] = $shippingAddress->getName();
                                }
                                if ($shippingAddress->isSetAddressLine1()) 
                                {
                                    
                                	 $oneAmazonOrder[$orderId]['customer']['ship_addr1'] = $shippingAddress->getAddressLine1();
                                }
                                if ($shippingAddress->isSetAddressLine2()) 
                                {
                                 	$oneAmazonOrder[$orderId]['customer']['ship_addr2'] = $shippingAddress->getAddressLine2();
                                
                                }
                                if ($shippingAddress->isSetAddressLine3()) 
                                {
                                 	$oneAmazonOrder[$orderId]['customer']['ship_addr3'] = $shippingAddress->getAddressLine3();
                                	
                                }
                                if ($shippingAddress->isSetCity()) 
                                {
                                   
                                  $oneAmazonOrder[$orderId]['customer']['ship_city'] = $shippingAddress->getCity();
                                }
                                if ($shippingAddress->isSetCounty()) 
                                {
                                 
                                    $oneAmazonOrder[$orderId]['customer']['ship_country'] = $shippingAddress->getCounty();
                                }
                                if ($shippingAddress->isSetDistrict()) 
                                {
                                 
                                   $oneAmazonOrder[$orderId]['customer']['ship_district'] = $shippingAddress->getDistrict();
                                }
                                if ($shippingAddress->isSetStateOrRegion()) 
                                {
                                   
                                   $oneAmazonOrder[$orderId]['customer']['ship_state'] = $shippingAddress->getStateOrRegion();
                                }
                                if ($shippingAddress->isSetPostalCode()) 
                                {
                                  
                                    $oneAmazonOrder[$orderId]['customer']['ship_post_code'] = $shippingAddress->getPostalCode();
                                }
                                if ($shippingAddress->isSetCountryCode()) 
                                {
                                    
                                    $oneAmazonOrder[$orderId]['customer']['ship_country_code'] = $shippingAddress->getCountryCode();
                                }
                                if ($shippingAddress->isSetPhone()) 
                                {
                                   
                                   $oneAmazonOrder[$orderId]['customer']['ship_phone'] = $shippingAddress->getPhone();
                                }
                            } 
                            if ($order->isSetOrderTotal()) { 
                             
                                $orderTotal = $order->getOrderTotal();
                                if ($orderTotal->isSetCurrencyCode()) 
                                {
                                 
                                   $oneAmazonOrder[$orderId]['currency_code'] = $orderTotal->getCurrencyCode();
                                }
                                if ($orderTotal->isSetAmount()) 
                                {
                        
                                   $oneAmazonOrder[$orderId]['order_total'] = $orderTotal->getAmount();
                                }
                            } 
                            if ($order->isSetNumberOfItemsShipped()) 
                            {
                         
                                $oneAmazonOrder[$orderId]['num_item_shipped'] = $order->getNumberOfItemsShipped();
                            }
                            if ($order->isSetNumberOfItemsUnshipped()) 
                            {
                               
                                $oneAmazonOrder[$orderId]['num_item_unshipped'] = $order->getNumberOfItemsUnshipped();
                            }
                            if ($order->isSetPaymentExecutionDetail()) { 
                              
                                $paymentExecutionDetail = $order->getPaymentExecutionDetail();
                                $paymentExecutionDetailItemList = $paymentExecutionDetail->getPaymentExecutionDetailItem();
                                $i = 0;
                                foreach ($paymentExecutionDetailItemList as $paymentExecutionDetailItem) {
                                  
                                    if ($paymentExecutionDetailItem->isSetPayment()) { 
                                       
                                        $payment = $paymentExecutionDetailItem->getPayment();
                                        if ($payment->isSetCurrencyCode()) 
                                        {
                                           
                                          $oneAmazonOrder[$orderId]['payment'][$i]['currency_code'] = $payment->getCurrencyCode();
                                        }
                                        if ($payment->isSetAmount()) 
                                        {
                                           
                                           $oneAmazonOrder[$orderId]['payment'][$i]['pay_amount'] = $payment->getAmount();
                                        }
                                    } 
                                    if ($paymentExecutionDetailItem->isSetPaymentMethod()) 
                                    {
                                       
                                        $oneAmazonOrder[$orderId]['payment'][$i]['payment_method'] = $paymentExecutionDetailItem->getPaymentMethod();
                                    }
                                    $i++;
                                }
                            } 
                            if ($order->isSetPaymentMethod()) 
                            {
                            
                          		$oneAmazonOrder[$orderId]['payment_method'] = $order->getPaymentMethod();
                            }
                            if ($order->isSetMarketplaceId()) 
                            {
                            
                                 $oneAmazonOrder[$orderId]['market_place_id'] = $order->getMarketplaceId();
                            }
                            if ($order->isSetBuyerEmail()) 
                            {
                           
                                $oneAmazonOrder[$orderId]['customer']['buyer_email'] = $order->getBuyerEmail();
                            }
                            if ($order->isSetBuyerName()) 
                            {
                                
                               $oneAmazonOrder[$orderId]['customer']['buyer_name'] = $order->getBuyerName();
                            }
                            if ($order->isSetShipmentServiceLevelCategory()) 
                            {
                           
                               $oneAmazonOrder[$orderId]['shipment_level_category'] = $order->getShipmentServiceLevelCategory();
                            }
                            if ($order->isSetShippedByAmazonTFM()) 
                            {
                               
                               $oneAmazonOrder[$orderId]['ship_by_amazon_tfm'] = $order->getShippedByAmazonTFM();
                            }
                            if ($order->isSetTFMShipmentStatus()) 
                            {
                               
                               $oneAmazonOrder[$orderId]['ship_status'] = $order->getTFMShipmentStatus();
                            }
                        }
                    } 
 		 }
             
            return $oneAmazonOrder;

    }
    
   
   
   
/*  
private function getContentMd5($data) {
	$md5Hash = null;
	if (is_string($data)) {
		$md5Hash = md5($data, true);
	} else if (is_resource($data)) {
		// Assume $data is a stream.
		$streamMetadata = stream_get_meta_data($data);
		if ($streamMetadata['stream_type'] === 'MEMORY' || $streamMetadata['stream_type'] ==='TEMP') {
			$md5Hash = md5(stream_get_contents($data), true);
		} else {
			$md5Hash = md5_file($streamMetadata['uri'], true);
		}
	}
	return Base64_encode($md5Hash);
}
*/

/*
private function verifyContentMd5($receivedMd5Hash, $streamHandle) {
	rewind($streamHandle);
	$expectedMd5Hash = $this->getContentMd5($streamHandle);
	rewind($streamHandle);
	if(!($receivedMd5Hash === $expectedMd5Hash)) {
		//throw new Exception(
		print_R($expectedMd5Hash);
		//);
	}
}*/

}