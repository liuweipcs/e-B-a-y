<?php
/**
 * @package Ueb.modules.services.models/ebayGetOrder.php
 *
 * @author Tom
 */
class EbayGetOrder{
	protected $_totalPage = 1;
	protected $_totalNumber = 0;
	protected $_orderInfo = array();
	protected $_ebayOrder = null;
	protected $_taskId = 0;
	protected $_account_id = '';
	static protected $_SuccessCount = 0;
//		static protected $testflag = 0;

	const  TOTAL_DOWNLOAD_TIME = 2;
	const  CHECK_ORDER_PAY_NOT = 0;
	const  CATCH_ORDER_NEW = 1;

	const  NOT_PAY_ORDER_CHECK_BY_HOUR = 4;
	const  NOT_PAY_ORDER_CHECK_BY_MINUTE = 15;

	const  SUB_DAY = '-10 days';

	public function getOrdersByAccountNew($platformOrdId='', $sortOrder = 1)
	{
		$ebayApiTaskModel = UebModel::model('EbayOrderApiTask');
		//if($ebayApiTaskModel->checkOrderByAccountNew(1)){//可以拉取该账号订单
			// add order task for this period
			$timeArr['start_time'] = time();
			$timeArr['end_time'] = time();
			if($taskId = $ebayApiTaskModel->addOrderTask(1,$timeArr)){
				$this->_setEbayOrderTaskId($taskId);//记录TaskID
				//$this->_setEayAccountId($account);//记录AccountID
				$this->getOrderFromMiddle($platformOrdId, $sortOrder);
			}
/*		}
		else
		{
			exit('Task Running');
		}*/
	}

	public function getOrdersByTansaction($star_time,$end_time)
	{
		#  echo '开始时间:'.date('Y-m-d H:i:s',time() );
		$page = $_GET['page'];
		$this->getOrderFromMiddleTansaction($star_time,$end_time,$page);
	}

	public function getOrderFromMiddleTansaction($star_time,$end_time,$page=1)
	{
		$apiConfig = ConfigFactory::getConfig('order_api');
		$baseUrl = isset($apiConfig['baseUrl']) ? $apiConfig['baseUrl'] : '';
		$token = isset($apiConfig['token']) ? $apiConfig['token'] : '';

		$url= $baseUrl . '?api=order.getorderList&token=' . $token . '&platform=ebay&page='.$page.'&pageSize=2000&beginTime='.$star_time.'&endTime='.$end_time;

		$curl = new Curl();
		$curl->init();
		$response = $curl->get($url);
		$responseJson = json_decode($response);
		if($responseJson && $responseJson->totalPage >= 1 )
		{
			$orders = $responseJson->orders;
			foreach ($orders as $row)
			{
				$result = $row->order;
				$order = UebModel::model('OrderEbay')->find('platform_order_id=:platform_order_id',[':platform_order_id'=>$result->OrderID]);

				$order_id = $order->order_id;

				$transaction_seling_number = $result->ShippingDetails->SellingManagerSalesRecordNumber;

				if($order_id && $transaction_seling_number){
					UebModel::model('OrderEbayTransactionNumber')->addData($order_id,$transaction_seling_number);
				}
			}
			if($responseJson->totalPage > $page){
				exit(json_encode([
					'page'=>$page,
					'totalPage'=>$responseJson->totalPage,
				]) );
//                    echo '结束时间:'.date('Y-m-d H:i:s',time() );
//                    echo '--------------------获取数据页:'.$page.'<br/>';
//                    ob_flush();
//                    flush();
				#$this->getOrderFromMiddleTansaction($star_time,$end_time,$page=$page+1);
			}
		}
	}

	public function getOrderFromMiddle($platformOrdId='', $sortOrder = 1)
	{
		$apiConfig = ConfigFactory::getConfig('order_api');
		$baseUrl = isset($apiConfig['baseUrl']) ? $apiConfig['baseUrl'] : '';
		$token = isset($apiConfig['token']) ? $apiConfig['token'] : '';
		$sortOrder = $sortOrder == 1 ? 1 : -1;
		$numbers = 1000;
		$curl = new Curl();
		$curl->init();
		$successList = [];
		$url= $baseUrl . '?api=order.getorders&token=' . $token . '&sort_order=' . $sortOrder . '&platform=ebay&number=' . $numbers.'&order_id='.$platformOrdId;
		$response = $curl->get($url);
		$responseJson = json_decode($response);
		if (empty($responseJson) || !isset($responseJson->success) || $responseJson->success != true)
		{
			UebModel::model('EbayOrderApiTask')->updateByPk($this->_taskId, [
				'complete_time' => date('Y-m-d H:i:s'),
				'task_status' => EbayOrderApiTask::TASK_STATUS_ERROR,
			]);
			return false;
		}
		$orders = $responseJson->orders;
		if (empty($orders))
		{
			UebModel::model('EbayOrderApiTask')->updateByPk($this->_taskId, [
				'complete_time' => date('Y-m-d H:i:s'),
				'task_status' => EbayOrderApiTask::TASK_STATUS_SUCCESS,
			]);
			return true;
		}
		$accountList = [];
		echo sizeof($orders);
		foreach ($orders as $row)
		{
			$order = $row->order;
			$bid = $row->bid;
			$accountId = $row->accountId;
			$this->_account_id = $accountId;
			if (!in_array($accountId, $accountList))
				$accountList[] = $accountId;
			$flag = $this->paserIntoOrderNew($order);
			if ($flag)
				$successList[] = $bid;
		}
		//foreach ($accountList as $accountId)
		//{
		//$transactions  = UebModel::model('PaypalTransaction')->checkPaypalErrorRequest($accountId,array('order'=>'OrderEbay'));
		//if($transactions){
		//$this->_sysncPaypal($transactions);
		//}
		//}
		//调用接口将插入成功的订单回传到拉单程序


    	if (!empty($successList))
		{
			$postData = 'token=' . $token;
			foreach ($successList as $bid)
				$postData .='&orderList[]=' . $bid;
			$url= $baseUrl . '?api=order.setordersync';
			$response = $curl->post($url, $postData);
		}


		UebModel::model('EbayOrderApiTask')->updateByPk($this->_taskId, [
			'complete_time' => date('Y-m-d H:i:s'),
			'task_status' => EbayOrderApiTask::TASK_STATUS_SUCCESS,
		]);
		return true;
	}

	/**
	 *
	 * parse data to order
	 * @param Object $orders
	 */
	public function paserIntoOrderNew($order){
		$orderTransaction = UebModel::model('OrderEbay')->getDbConnection()->getCurrentTransaction();

		if($orderTransaction == null){
			$orderTransaction = UebModel::model('OrderEbay')->getDbConnection()->beginTransaction();
		}

		try{
			$this->_ebayOrder = array();
			$this->_orderInfo = array();
			$this->_setEbayOrderInfo($order);
			/* 		                if($this->_ebayOrder->OrderStatus != 'Completed'){
                                        return true;
                                    } */
			/*$externalTransactions = isset($this->_ebayOrder->ExternalTransaction) ?
              $this->_ebayOrder->ExternalTransaction : [];
             if($this->checkPayPalTransactionIsExist($externalTransactions)){
                return true;
            }
            if($this->checkOrderIsRefund($this->_ebayOrder->Total)){
                return true;
            } */

			$replaceFlag = false;
			if($this->_getOrderInfoByPlatformOrderId($this->_ebayOrder->OrderID)){
				if((UebModel::model('OrderEbay')->checkOrderStatusByStatus($this->_orderInfo['payment_status'],UebModel::model('OrderEbay')->getNotPayedOrderStatus()))){
				    $replaceFlag = true;
				}
				//$replaceFlag = false;
			}else{
				$replaceFlag = true;
			}
			$orderObj = $this->_getEbayOrderObj();
			/*如果订单是第一次拉取添加日志*/
			if(empty(UebModel::model('OrderEbay')->findByPk($orderObj->order_id))){
				/*订单第一次拉取时添加下载时间*/
				/***********************添加日志******************************************/
				$updateMsg = '';
				$updateMsg = '<span>订单下载时间</span></br>';
				$updateLogInfo = array(
					'order_id'			=> $orderObj->order_id,
					'update_content'	=> $updateMsg,
					'create_time'		=> date('Y-m-d H:i:s',time()),
					'create_user_id'	=> Yii::app()->user->id

				);
				if($updateMsg != ''){
					UebModel::model('OrderUpdateLog')->saveUpdateLog($updateLogInfo);
				}
				/*订单创建时间添加到节点*/
				UebModel::model('OrderNode')->insertData($orderObj->order_id,$orderObj->created_time,OrderNode::ORDER_GENERATION);
				/*订单付款时间添加到节点*/
				UebModel::model('OrderNode')->insertData($orderObj->order_id,$orderObj->paytime,OrderNode::ORDER_PAYMENT);
				/*****************************************************************/
			}
			if($replaceFlag){
				if(UebModel::model('OrderEbay')->saveOrderByOrderInfo($orderObj)){
					//save customer information
					UebModel::model('Customer')->saveCustomerByCustomerInfo(
						$this->_getCustomerObj()
					);

					// delete orderDetail
					UebModel::model('OrderEbayDetail')->getDbConnection()->createCommand()
						->delete(OrderEbayDetail::model()->tableName(), "order_id='" . $orderObj->order_id . "'");
					$finalValueFee = 0;//total Transaction costs for order
					$weightArr = array();
					$weightTotal = 0;//init weight for total
					//save order_detail
					$details = [];
					if (is_array($this->_ebayOrder->TransactionArray->Transaction))
						$details = $this->_ebayOrder->TransactionArray->Transaction;
					else
						$details[] = $this->_ebayOrder->TransactionArray->Transaction;

					$selingNumber = $this->_ebayOrder->ShippingDetails->SellingManagerSalesRecordNumber;
					if($selingNumber){
						UebModel::model('OrderEbayTransactionNumber')->addData($orderObj->order_id,$selingNumber,$this->_account_id);
					}
					
					//获取产品总重量用于平摊运费
					$skuCostArr = $skuWeightArr = array();
					$productTitleArr = array(); //存放产品title
					$allSkus = array();
					$emptyWgt = false; //是否存在sku重量为空
					$emptyCost = array(); //是否存在sku产品成本为空
					
					$totalItemProductCost = array(); //当前orderLine产品总成本
					$totalItemQty = array(); //当前orderLine产品总数量
					$totalSkuWgt = 0; //所有产品总重量
					$totalSkuQty = 0; //所有产品数量
					
					foreach ($details as $transaction){
						$skuAndTitle = $this->_getSkuAndTitle($transaction);
						$sellerSku = $skuAndTitle['sku'];
						$sellerTitle = $skuAndTitle['title'];
						$sellerQuantity = intval($transaction->QuantityPurchased);
                        $realSkus = MHelper::getBindSkuMap( $sellerSku );//获取实际sku
                        
                        if (!empty($realSkus)){
                        	foreach ($realSkus as $skuRow){
                        		$sku = $skuRow['sku'];
                        		$allSkus[] = $sku;
                        		$quantity = $skuRow['quantity'];
                        		$productObj = UebModel::model('Product')->getBySku($sku);
                        		$newQuantity = (int)$quantity * $sellerQuantity;
                        		$skuCostArr[$sku] = $productObj->product_cost;
                        		$skuWeightArr[$sku] = $productObj->product_weight;
                        		$totalItemProductCost[$transaction->OrderLineItemID] += $skuCostArr[$sku]*$newQuantity;
                        		$totalItemQty[$transaction->OrderLineItemID] += $newQuantity;
                        		$totalSkuWgt += $skuWeightArr[$sku]*$newQuantity;
                        		$totalSkuQty += $newQuantity;
                        		if( empty($skuCostArr[$sku]) || $skuCostArr[$sku] == 0.00 ) $emptyCost[$transaction->OrderLineItemID] = true;
                        		if( empty($skuWeightArr[$sku]) || $skuWeightArr[$sku] == 0.00 ) $emptyWgt = true;
                        	}
                        }else{ //没有找到映射产品
                        	$emptyCost[$transaction->OrderLineItemID] = true;
                        	$emptyWgt = true;
                        }
					}
					
					if($allSkus) $productTitleArr = UebModel::model('Productdesc')->getSkuTitles(array_unique($allSkus)); //获取title
					$totalShipPrice = floatval($this->_ebayOrder->ShippingServiceSelected->ShippingServiceCost);
					
					foreach($details as $transaction){
						$salePrice = $transaction->TransactionPrice;
						
						$skuAndTitle = $this->_getSkuAndTitle($transaction);
						$sellerSku = $skuAndTitle['sku'];
						$sellerTitle = $skuAndTitle['title'];
						$sellerQuantity = intval($transaction->QuantityPurchased);
						
						$lineTotalSalePrice = $salePrice*$sellerQuantity;
						
						$realSkus = MHelper::getBindSkuMap( $sellerSku );//获取实际sku
						$detailSaveFlag = true;
						if (!empty($realSkus)){
							foreach ($realSkus as $skuRow){
								$sku = $skuRow['sku'];
								$quantity = $skuRow['quantity'];
								$newQuantity = (int)$quantity * $sellerQuantity;
								
								if( $emptyCost[$transaction->OrderLineItemID] ){
									$currSalePrice = round($lineTotalSalePrice*($newQuantity/$totalItemQty[$transaction->OrderLineItemID])/$newQuantity,2);
								}else{
									if($totalItemProductCost[$transaction->OrderLineItemID]) $currSalePrice = round($lineTotalSalePrice*($skuCostArr[$sku]*$newQuantity/$totalItemProductCost[$transaction->OrderLineItemID])/$newQuantity,2);
								}
								if( $emptyWgt ){
									$currShipPrice = round($totalShipPrice*($newQuantity/$totalSkuQty)/$newQuantity,2);
								}else{
									if($totalSkuWgt) $currShipPrice = round($totalShipPrice*($skuWeightArr[$sku]*$newQuantity/$totalSkuWgt)/$newQuantity,2);
								}
								
								$detailObj = $this->_getOrderDetailObjByTransaction($transaction,$sellerSku,$sellerSku);
								$detailObj->sku = $sku;
								$detailObj->quantity = $newQuantity;
								$detailObj->qs = $newQuantity;
								$detailObj->title = $productTitleArr[$sku]['english']?$productTitleArr[$sku]['english']:$sellerTitle;
								$detailObj->sale_price = $currSalePrice;
								$detailObj->total_price = floatval($currSalePrice)*intval($newQuantity)+floatval($currShipPrice)*intval($newQuantity);
								$detailObj->ship_price = $currShipPrice;
								$detailId = UebModel::model('OrderEbayDetail')->saveOrderDetailByOrderDetailInfo($detailObj,true);
								if(!$detailId){
									$orderMessage = 'order_id:'.$orderObj->order_id.' save order detail fail!';
									throw new Exception($orderMessage);
								}
								$detailSaveFlag = $detailSaveFlag && $detailId;
							}
							if($detailSaveFlag){
								$finalValueFee += floatval($transaction->FinalValueFee);
							}
							
						}else{
							$detailObj = $this->_parseIntoOrderDetail($transaction);
							if($detailId = UebModel::model('OrderEbayDetail')->saveOrderDetailByOrderDetailInfo($detailObj,true)){
								$finalValueFee += floatval($transaction->FinalValueFee);
								$productInfo = UebModel::model('Product')->getBySku($detailObj->sku);
								if(!$productInfo){
									//throw new Exception('SKU:'.$detailObj->sku.' not find in system!');
								}
								$productWeight = floatval($productInfo->product_weight) * intval($detailObj->quantity);
								$weightTotal += $productWeight;
								$weightArr[$detailId] = !empty($productWeight) ? $productWeight : 0;
							
							}else{
								$orderMessage = 'order_id:'.$orderObj->order_id.' save order detail fail!';
								throw new Exception($orderMessage);
							}
						}
					}

					//update finalValueFee for order
					if($orderModel = UebModel::model('OrderEbay')->findByPk($orderObj->order_id)){
						$result = UebModel::model('OrderEbay')->updateByPk($orderObj->order_id,
							array('final_value_fee'=>$finalValueFee));
						if(!$result){
							throw new Exception('the order:'.$orderObj->order_id.' update final_value_fee='.$finalValueFee.' fail!');
						}
					}else{
						throw new Exception('the order:'.$orderObj->order_id.' does not exist in the ueb_order');
					}

					//update ship price
					if($weightArr) $this->_saveEbayOrderShipPrice($totalShipPrice, $weightArr, $weightTotal, $detailId);

					$serviceSelected = trim($this->_ebayOrder->ShippingServiceSelected->ShippingService);
					// save local ship service for customer



					foreach($this->_ebayOrder->ShippingDetails->ShippingServiceOptions as $option) {

						if(trim($option->ShippingService)==$serviceSelected){
							$serviceObj = UebModel::model('OrderEbayShippingSelect')->getLocalShippingObj($orderObj->order_id,$option);
							$result = UebModel::model('OrderEbayShippingSelect')->saveEbayShippingSelect($serviceObj);
							if(!$result){
								throw new Exception('order_id:'.$orderObj->order_id.' save local shippingType fail!');
							}
							break;
						}
					}

					// save interantional ship service for customer
					foreach ($this->_ebayOrder->ShippingDetails->InternationalShippingServiceOption as $option){
						if(trim($option->ShippingService)==$serviceSelected){
							if(floatval($option->ShippingServiceCost)!=$totalShipPrice && ($totalShipPrice==0 || floatval($option->ShippingServiceCost)==0) ){
								continue;
							}

							$serviceObj = UebModel::model('OrderEbayShippingSelect')->getInternationShippingObj($orderObj->order_id,$option);
							$result = UebModel::model('OrderEbayShippingSelect')->saveEbayShippingSelect($serviceObj);
							if(!$result){
								throw new Exception('order_id:'.$orderObj->order_id.' save International shippingType fail!');
							}
						}
					}

					//删除订单留言
					UebModel::model('OrderEbayNote')->getDbConnection()->createCommand()
						->delete(OrderEbayNote::model()->tableName(), "order_id='" . $orderObj->order_id . "'");
					//判断是否有订单留言,如果有则保存订单留言,并更改该订单为待处理状态
					if (isset($this->_ebayOrder->BuyerCheckoutMessage) && !empty($this->_ebayOrder->BuyerCheckoutMessage))
					{
						$orderNoteModel = new OrderEbayNote();
						$orderNoteModel->order_id = $orderObj->order_id;
						$orderNoteModel->note = $this->_ebayOrder->BuyerCheckoutMessage;
						$flag = $orderNoteModel->save(false);
						if (!$flag)
						{
							throw new Exception('save aliexpress order:'.$orderObj->order_id.' note fail!');
						}
					}

				}else{
					throw new Exception('Order_id:'.$orderObj->order_id.' save failed!');
				}
			}

			// delete orderTransaction
			/* 		                UebModel::model('OrderEbayTransaction')->getDbConnection()->createCommand()
                                    ->delete(OrderEbayTransaction::model()->tableName(), "order_id='" . $orderObj->order_id . "'"); */
			//to get all transactions sort by transaction time
			$externalTransactions = $this->_getTransactionsByTransactionTime($this->_ebayOrder->ExternalTransaction);
			//	$first = $replaceFlag?true:false;
			// check and save ebay order transations
			if (empty($externalTransactions))
				$externalTransactions = [];
			foreach($externalTransactions as $externalTransaction){
				if (empty($externalTransaction)) continue;
				$transactionId = trim($externalTransaction->ExternalTransactionID);
				//if first pay fail but success by anther pay
				if(!(UebModel::model('OrderEbayTransaction')->checkTransactionIsExistByOrderId($transactionId,$orderObj->order_id))){
					/*		                        $transactionArr = array(
                     'order_id' => $this->_orderInfo['order_id'],
                     'transaction_id' => $transactionId,
                     'account_id' => $this->_account_id,
                     'platform_code' => UebModel::model('Platform')->getEbayPlatformCode(),
                     'status' => UebModel::model('OrderEbayTransaction')->getTransactionStatusDefault(),
                     'last_update_time' => MHelper::getNowTime(),
                    );*/
					$transactionObj = new stdClass();
					$transactionObj->transaction_id = $transactionId;
					$transactionObj->order_id = $orderObj->order_id;
					$transactionObj->platform_code = UebModel::model('Platform')->getEbayPlatformCode();
					$transactionObj->status = UebModel::model('OrderEbayTransaction')->getTransactionStatusEnd();
					//$transactionObj->status = UebModel::model('OrderEbayTransaction')->getTransactionStatusDefault();
					$transactionObj->order_pay_time = str_replace('T', ' ', substr($externalTransaction->ExternalTransactionTime, 0, 19));
					$transactionObj->last_update_time = MHelper::getNowTime();
					$transactionObj->amt = $externalTransaction->PaymentOrRefundAmount;
					$transactionObj->fee_amt = $externalTransaction->FeeOrCreditAmount;
					$transactionObj->account_id = $orderObj->account_id;
					$transactionObj->currency = $orderObj->currency;
					$transactionObj->payment_status = UebModel::model('OrderEbay')->getStatusComplete();
					//$transactionObj = UebModel::model('OrderEbayTransaction')->getTransactionObj($this->_orderInfo['order_id'],$transactionArr);
					if(!(UebModel::model('OrderEbayTransaction')->saveTransaction($transactionObj))){
						throw new Exception('transaction_id:'.$transactionId.' AND order_id:'.$orderObj->order_id.' save failed!');
					}
				}
			}
			//同步paypal交易信息
			/*$transactions  = UebModel::model('PaypalTransaction')->getOrderTransactionInfoByOrderId($orderObj->order_id);
           if (!empty($transactions))
             $this->_sysncPaypal($transactions);*/
           //更新成交费信息
           $details = [];
           if (is_array($this->_ebayOrder->TransactionArray->Transaction))
               $details = $this->_ebayOrder->TransactionArray->Transaction;
           else
               $details[] = $this->_ebayOrder->TransactionArray->Transaction;
           $finalValueFeeCurrencyCode = isset($this->_ebayOrder->FinalValueFeeCurrencyCode) ?
           $this->_ebayOrder->FinalValueFeeCurrencyCode : '';
           $finalValueFee = 0;
           foreach($details as $transaction)
               $finalValueFee += floatval($transaction->FinalValueFee);
           if (!empty($finalValueFeeCurrencyCode) && !empty($finalValueFee))
           {
               $orderExtModel = UebModel::model('OrderEbayExt')->findByOrderId($orderObj->order_id);
               if (empty($orderExtModel))
               {
                   $orderExtModel = new OrderEbayExt();
                   $orderExtModel->id = null;
                   $orderExtModel->order_id = $orderObj->order_id;
               }
               $orderExtModel->final_value_fee = $finalValueFee;
               $orderExtModel->fvf_currency_code = $finalValueFeeCurrencyCode;
               $orderExtModel->save(true, ['final_value_fee', 'fvf_currency_code', 'order_id']);
           }
           
			if($orderTransaction!==null){
				$orderTransaction->commit();
				if(1||$replaceFlag){
					//update total num for this time
					$taskModel = UebModel::model('EbayOrderApiTask')->findByPk($this->_taskId);
					if(!$taskModel){
						throw new Exception('ebay_task_id:'.$this->_taskId.' not find!');
					}
				}
			}
			//file_put_contents('D:\testContent\sqlexec.log',self::$testflag.'se'.$ordercount.'|',FILE_APPEND);
		}catch (Exception $e){echo $e->getMessage();
			//						file_put_contents('D:\testContent\error.log','('.self::$testflag.')error('.$ordercount.')'.$e->getMessage().$e->getFile().$e->getLine().'|',FILE_APPEND);
			if($orderTransaction!==null){
				$orderTransaction->rollback();
			}
			return false;
		}
		return true;
	}

	public function getOrdersByAccount($account){
		$ebayApiTaskModel = UebModel::model('EbayOrderApiTask');
		if($ebayApiTaskModel->checkOrderByAccount($account)){//可以拉取该账号订单
			$timeArr = $ebayApiTaskModel->getDownLoadTimeByAccount($account);//获取要拉取的订单的开始时间和结束时间
			// add order task for this period
			if($taskId = $ebayApiTaskModel->addOrderTask($account,$timeArr)){
				$this->_setEbayOrderTaskId($taskId);//记录TaskID
				$this->_setEayAccountId($account);//记录AccountID
				// start to catch order
				$shortName = UebModel::model('EbayAccount')->getAccountList($account);
				$this->getOrderResponseByShortName($shortName,$timeArr);
			}
		}


	}

	/**
	 *
	 * get orders from ebay api by shortName
	 * @param String $shortName
	 */
	public function getOrderResponseByShortName($shortName,$timeArr){
		$orderObj = new GetOrders();
		$totalDownload = self::TOTAL_DOWNLOAD_TIME;
		$requestError = false;
		while($totalDownload--){
			if($totalDownload!=self::CATCH_ORDER_NEW){ //check not pay order

				if($this->_isCheckNotPayOrder()){
					continue;
				}

				if(!($unPayOrders = UebModel::model('OrderEbay')->getNotPayOrderByPlatformCode(
					UebModel::model('Platform')->getEbayPlatformCode(),$this->_account_id,
					$this->_getCatchNotPayOrderStartTime(),date('Y-m-d H:i:s',time())
				))){
					continue;
				}else{
					$unPayOrders = MHelper::createKeyValue($unPayOrders,'platform_order_id');
				}
			}


			while($orderObj->getPageNumber() <= $this->_totalPage){ // if the current page less than totalPage
				$logDate = date('Y-m-d H:i:m');
				file_put_contents(Yii::app()->BasePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'get_ebay_order.log',PHP_EOL ."[{$logDate}] [account:{$shortName}]",FILE_APPEND);
//					self::$testflag++;
//					file_put_contents('D:\testContent\cont.log',self::$testflag.'--',FILE_APPEND);
				if($totalDownload!=self::CATCH_ORDER_NEW){
					// set not pay orders for this period

					$orderObj->setOrderIDArray($unPayOrders);
				}else{
					// set start time for this period
					$orderObj->setMoveTimeFrom($timeArr['start_time']);
					// set end time for this period
					$orderObj->setMoveTimeTo($timeArr['end_time']);
				}

				$response = $orderObj->setShortName($shortName)
					->setVerb('GetOrders')
					->setRequest()
					->sendHttpRequest()
					->getResponse();
//					findClass($response,1);
				if($this->handleResponse($response)){
					$orderObj->setPageNumber($orderObj->getPageNumber()+1);
				}else{
					$requestError = true;
					break 2;
				}

			}
		}
//			exit('aa');

		if(!$requestError){
			//更新最终抓到的订单数目
			try{
				if(!(UebModel::model('EbayApiTask')->updateTaskStatusByPk($this->_taskId,
					UebModel::model('EbayApiTask')->getTaskStatusSuccess(),self::$_SuccessCount
				))){
					throw new Exception('ebay_task_id:'.$this->_taskId.' save total_num='.self::$_SuccessCount.' fail!');
				}

			}catch(exception $e){
				echo $e->getMessage();
				exit;
			}
		}


		$i = 1;
		while($i--){
			$transactions  = UebModel::model('PaypalTransaction')->checkPaypalErrorRequest($this->_account_id,array('order'=>'OrderEbay'));
			if($transactions){
				$this->_sysncPaypal($transactions);
			}
		}

	}


	/*
     * 同步paypal数据
     */
	protected function _sysncPaypal($transactions){
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
						//$orderObj->complete_status = UebModel::model('OrderEbay')->getOrderStatusPengding();
						// insert note
						if(!(UebModel::model('OrderEbayNote')->saveOrderNote($noteObj,$noteObj->order_id))){
							throw new Exception('order_id:'.$noteObj->order_id.' note save not success');
						}
						$noteMsg = UebModel::getLogMsg();

						if (! empty($noteMsg)) {
							Yii::ulog($noteMsg, Yii::t('ordernote', 'Order Note'),
								'order_note'
							);
						}
					}
					// update order information
					if(!(UebModel::model('OrderEbay')->saveOrderByOrderInfo($orderObj,true))){
						throw new Exception('order_id:'.$orderObj->order_id.' update not success');
					}else{
						$orderMsg = UebModel::getLogMsg();

						if (! empty($orderMsg)) {
							Yii::ulog($orderMsg, Yii::t('order', 'Order Info'),
								'order'
							);
						}
					}

					// update order status if the order is not pengding
					//if($orderObj->complete_status!=UebModel::model('OrderEbay')->getOrderStatusPengding()){
					UebModel::model('OrderEbay')->updateByPk($orderObj->order_id,
						array('complete_status'=>Order::COMPLETE_STATUS_INIT)
					);
					//}


					// insert paypalTransaction record
					if(UebModel::model('PaypalTransactionRecord')->savePaypalTransactionRecord(
						$recordObj->transaction_id,$recordObj->order_id,
						$recordObj
					)){
						$paypalTransactionRecordMsg = UebModel::getLogMsg();

						if (! empty($paypalTransactionRecordMsg)) {
							Yii::ulog($paypalTransactionRecordMsg, 'paypal交易记录',
								'paypalTransactionRecord'
							);
						}

						$transactionObj->status = UebModel::model('OrderEbayTransaction')->getTransactionStatusEnd();
						if(!(UebModel::model('PaypalTransaction')->savePaypalTransaction($transactionObj,$transactionObj->transaction_id))){
							throw new Exception('PaypalTransaction_id:'.$transactionObj->transaction_id.' save not success');
						}else{
							$transactionMsg = UebModel::getLogMsg();

							if (!empty($transactionMsg)){
								Yii::ulog($transactionMsg, 'paypal交易',
									'paypalTransaction'
								);
							}

						}

					}else{
						throw new Exception('PaypalTransaction_id:'.$recordObj->transaction_id.' Record save not success');
					}

					if($result['customer'] != null){
						if(!(UebModel::model('Customer')->saveCustomerByCustomerInfo(
							$result['customer']
						))){
							throw new Exception('customer information save not success');
						}else{
							$customerMsg = UebModel::getLogMsg();

							if (! empty($customerMsg)) {
								Yii::ulog($customerMsg, 'paypal交易',
									'paypalTransaction'
								);
							}
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



	protected function _isCheckNotPayOrder(){
		$flag = false;
		if(date('H')%self::NOT_PAY_ORDER_CHECK_BY_HOUR!=0 || date('i')>self::NOT_PAY_ORDER_CHECK_BY_MINUTE){
			$flag = true;
		}
		return $flag;
	}

	/**
	 * parse order message  ...
	 * @param object $response
	 */
	public function	handleResponse($response){
		$logOrderLength = count($response->OrderArray->Order);
		file_put_contents(Yii::app()->BasePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'get_ebay_order.log',"[Ack:{$response->Ack}] [orderLength:{$logOrderLength}][TotalNumberOfPages:{$response->PaginationResult->TotalNumberOfPages}][TotalNumberOfEntries:{$response->PaginationResult->TotalNumberOfEntries}]",FILE_APPEND);
		if(isset($response->Ack)&&($response->Ack=='Success' || $response->Ack=='Warning')){
			if($response->PaginationResult->TotalNumberOfEntries > 0) {
				$this->_totalNumber = $response->PaginationResult->TotalNumberOfEntries;
				$this->_totalPage = $response->PaginationResult->TotalNumberOfPages;
				if(count($response)){
					$this->paserIntoOrder($response->OrderArray->children());
				}
				return true;
			}

		}else{
			// set this period for catching order failed

			UebModel::model('EbayApiTask')->updateTaskStatusByPk(
				$this->_taskId,UebModel::model('EbayApiTask')->getTaskStatusError(),
				self::$_SuccessCount
			);

			return false;
		}
	}


	/**
	 *
	 * parse data to order
	 * @param Object $orders
	 */
	public function paserIntoOrder($orders){
		$successCount = 0;
		if($orders){
//				$ordercount = 0;
			foreach($orders as $order){
//					$ordercount++;
//					file_put_contents('D:\testContent\ordercount.log',self::$testflag.'-'.$ordercount.':',FILE_APPEND);
				$orderTransaction = UebModel::model('OrderEbay')->getDbConnection()->getCurrentTransaction();

				if($orderTransaction == null){
					$orderTransaction = UebModel::model('OrderEbay')->getDbConnection()->beginTransaction();
				}


				try{
					$this->_ebayOrder = array();
					$this->_orderInfo = array();

					$this->_setEbayOrderInfo($order);


					if($this->checkPayPalTransactionIsExist($this->_ebayOrder->ExternalTransaction)){
						continue;
					}

					if($this->checkOrderIsRefund($this->_ebayOrder->Total)){
						continue;
					}

					$replaceFlag = false;
					if($this->_getOrderInfoByPlatformOrderId($this->_ebayOrder->OrderID)){
						if((UebModel::model('OrderEbay')->checkOrderStatusByStatus($this->_orderInfo['payment_status'],UebModel::model('OrderEbay')->getNotPayedOrderStatus()))){
							$replaceFlag = true;
						}
					}else{
						$replaceFlag = true;
					}
					if($replaceFlag){

						$orderObj = $this->_getEbayOrderObj();
						/*如果订单是第一次拉取添加日志*/
						if(empty(UebModel::model('OrderEbay')->findByPk($orderObj->order_id))){
							/*订单第一次拉取时添加下载时间*/
							/***********************添加日志******************************************/
							$updateMsg = '';
							$updateMsg = '<span>订单下载时间</span></br>';
							$updateLogInfo = array(
								'order_id'			=> $orderObj->order_id,
								'update_content'	=> $updateMsg,
								'create_time'		=> date('Y-m-d H:i:s',time()),
								'create_user_id'	=> Yii::app()->user->id

							);
							if($updateMsg != ''){
								UebModel::model('OrderUpdateLog')->saveUpdateLog($updateLogInfo);
							}
							/*订单创建时间添加到节点*/
							UebModel::model('OrderNode')->insertData($orderObj->order_id,$orderObj->created_time,OrderNode::ORDER_GENERATION);
							/*订单付款时间添加到节点*/
							UebModel::model('OrderNode')->insertData($orderObj->order_id,$orderObj->paytime,OrderNode::ORDER_PAYMENT);
							/*****************************************************************/
						}

						if(UebModel::model('OrderEbay')->saveOrderByOrderInfo($orderObj)){
							//save customer information
							UebModel::model('Customer')->saveCustomerByCustomerInfo(
								$this->_getCustomerObj()
							);

							// delete orderDetail
							UebModel::model('OrderEbayDetail')->getDbConnection()->createCommand()
								->delete(OrderEbayDetail::model()->tableName(), "order_id='{$this->_orderInfo['order_id']}'");
							$finalValueFee = 0;//total Transaction costs for order
							$weightArr = array();
							$weightTotal = 0;//init weight for total

							//save order_detail
							foreach($this->_ebayOrder->TransactionArray->Transaction as $transaction){
								$detailObj = $this->_parseIntoOrderDetail($transaction);
								if($detailId = UebModel::model('OrderEbayDetail')->saveOrderDetailByOrderDetailInfo($detailObj,true)){
									$finalValueFee += floatval($transaction->FinalValueFee);
									//TODO 测试数据没有线上真实的SKU号,这里写死一个
									//$detailObj->sku = 'JY00227';

									$productInfo = UebModel::model('Product')->getBySku($detailObj->sku);
									if(!$productInfo){
										//throw new Exception('SKU:'.$detailObj->sku.' not find in system!');
									}
									$productWeight = floatval($productInfo->product_weight) * intval($detailObj->quantity);
									$weightTotal += $productWeight;
									$weightArr[$detailId] = !empty($productWeight) ? $productWeight : 0;

								}else{
									$orderMessage = 'order_id:'.$this->_orderInfo['order_id'].' save order detail fail!';
									throw new Exception($orderMessage);
								}
							}

							//update finalValueFee for order
							if($orderModel = UebModel::model('OrderEbay')->findByPk($this->_orderInfo['order_id'])){
								$result = UebModel::model('OrderEbay')->updateByPk($this->_orderInfo['order_id'],
									array('final_value_fee'=>$finalValueFee));
								if(!$result){
									throw new Exception('the order:'.$this->_orderInfo['order_id'].' update final_value_fee='.$finalValueFee.' fail!');
								}
							}else{
								throw new Exception('the order:'.$this->_orderInfo['order_id'].' does not exist in the ueb_order');
							}

							//update ship price
							$totalShipPrice = floatval($this->_ebayOrder->ShippingServiceSelected->ShippingServiceCost);
							$this->_saveEbayOrderShipPrice($totalShipPrice, $weightArr, $weightTotal, $detailId);

							$serviceSelected = trim($this->_ebayOrder->ShippingServiceSelected->ShippingService);
							// save local ship service for customer



							foreach($this->_ebayOrder->ShippingDetails->ShippingServiceOptions as $option) {

								if(trim($option->ShippingService)==$serviceSelected){
									$serviceObj = UebModel::model('OrderEbayShippingSelect')->getLocalShippingObj($this->_orderInfo['order_id'],$option);
									$result = UebModel::model('OrderEbayShippingSelect')->saveEbayShippingSelect($serviceObj);
									if(!$result){
										throw new Exception('order_id:'.$this->_orderInfo['order_id'].' save local shippingType fail!');
									}
									break;
								}
							}


							// save interantional ship service for customer
							foreach ($this->_ebayOrder->ShippingDetails->InternationalShippingServiceOption as $option){
								if(trim($option->ShippingService)==$serviceSelected){
									if(floatval($option->ShippingServiceCost)!=$totalShipPrice && ($totalShipPrice==0 || floatval($option->ShippingServiceCost)==0) ){
										continue;
									}

									$serviceObj = UebModel::model('OrderEbayShippingSelect')->getInternationShippingObj($this->_orderInfo['order_id'],$option);
									$result = UebModel::model('OrderEbayShippingSelect')->saveEbayShippingSelect($serviceObj);
									if(!$result){
										throw new Exception('order_id:'.$this->_orderInfo['order_id'].' save International shippingType fail!');
									}
								}
							}
						}else{
							throw new Exception('Order_id:'.$this->_orderInfo['order_id'].' save failed!');
						}
					}

					//to get all transactions sort by transaction time
					$externalTransactions = $this->_getTransactionsByTransactionTime($this->_ebayOrder->ExternalTransaction);

					//	$first = $replaceFlag?true:false;
					// check and save ebay order transations

					foreach($externalTransactions as $externalTransaction){

						$transactionId = trim($externalTransaction->ExternalTransactionID);
						//print_r($transactionId);exit;
						//if first pay fail but success by anther pay
						if(!(UebModel::model('OrderEbayTransaction')->checkTransactionIsExistByOrderId($transactionId,$this->_orderInfo['order_id']))){
							$transactionArr = array(
								'order_id' => $this->_orderInfo['order_id'],
								'transaction_id' => $transactionId,
								'account_id' => $this->_account_id,
								'platform_code' => UebModel::model('Platform')->getEbayPlatformCode(),
								'status' => UebModel::model('OrderEbayTransaction')->getTransactionStatusDefault(),
								'last_update_time' => MHelper::getNowTime(),
							);

							$transactionObj = UebModel::model('OrderEbayTransaction')->getTransactionObj($this->_orderInfo['order_id'],$transactionArr);
							if(!(UebModel::model('OrderEbayTransaction')->saveTransaction($transactionObj))){
								throw new Exception('transaction_id:'.$transactionId.' AND order_id:'.$this->_orderInfo['order_id'].' save failed!');
							}
						}

					}

					if($orderTransaction!==null){
						$orderTransaction->commit();

						if(1||$replaceFlag){
							//update total num for this time
							$taskModel = UebModel::model('EbayOrderApiTask')->findByPk($this->_taskId);
							if(!$taskModel){
								throw new Exception('ebay_task_id:'.$this->_taskId.' not find!');
							}else{

								if($orderObj->log_id==$this->_taskId){
									++$successCount;
								}
							}
						}
					}
					//file_put_contents('D:\testContent\sqlexec.log',self::$testflag.'se'.$ordercount.'|',FILE_APPEND);
				}catch (Exception $e){
//						file_put_contents('D:\testContent\error.log','('.self::$testflag.')error('.$ordercount.')'.$e->getMessage().$e->getFile().$e->getLine().'|',FILE_APPEND);
					if($orderTransaction!==null){
						$orderTransaction->rollback();
					}

				}
			}
			$this->_setSuccessCount($successCount);
			//
		}
	}




	/**
	 *
	 * calcaute ship price for very order detail
	 * @param float $totalShipPrice
	 * @param Array $weightArr
	 * @param int $detailId
	 */
	protected function _saveEbayOrderShipPrice($totalShipPrice,$weightArr,$weightTotal,$detailId){
		if($totalShipPrice>0){
			if(count($weightArr)==1 && $detailId){// one order
				if($orderDetailModel = UebModel::model('OrderEbayDetail')->findByPk($detailId)){
					$result = UebModel::model('OrderEbayDetail')->updateByPk($detailId,
						array('ship_price'=>$totalShipPrice)
					);
					$this->_errorMsg(!$result,'orderDetailId:'.$detailId.' save ship_price='.$totalShipPrice.' fail!');

				}else{
					$this->_errorMsg(true,'orderDetailId:'.$detailId.' does not exist ueb_order_detail!');
				}

			}else{
				foreach ($weightArr as $orderDetailId => $weight){
					$rate = $weight / $weightTotal;
					$shipPrice = floor($totalShipPrice*$rate*100)/100;
					if($orderDetailModel = UebModel::model('OrderEbayDetail')->findByPk($orderDetailId)){
						$result = UebModel::model('OrderEbayDetail')->updateByPk($orderDetailId,
							array('ship_price'=>$shipPrice)
						);
						$this->_errorMsg(!$result,'orderDetailId:'.$orderDetailId.' save ship_price='.$shipPrice.' fail!');

					}else{
						$this->_errorMsg(true,'orderDetailId:'.$orderDetailId.' does not exist ueb_order_detail!');
					}

				}
			}
		}
	}


	/**
	 *
	 * Enter description here ...
	 * @param blooen $flag
	 * @param string $message
	 * @throws Exception
	 */
	protected function _errorMsg($flag,$message){
		if($flag){
			throw new Exception($flag);
		}
	}

	protected  function _parseIntoOrderDetail($detailObj){
		$skuAndTitle = $this->_getSkuAndTitle($detailObj);
		$onlineSku = $skuAndTitle['sku'];
		$title = $skuAndTitle['title'];

		//   $matchProductCode = getModel('ebay_product_title')->getProductCodeByTitle(addslashes($title),trim($onlineSku));

//			 if($match_product_code==$sku_online){
//				$new_info = getModel('product')->getRealProduct($sku_online,$Transaction->QuantityPurchased);
//			 }else{
//				$order_model->update(array('complete_status'=>getModel('order')->COMPLETE_STATUS_PENGDING),array('orderid'=>$orderid));
//
//				$new_info = array(
//					'product_code' => getModel('order')->UNKNOW,
//					'quantity' => $Transaction->QuantityPurchased,
//				);
//			}

		return $this->_getOrderDetailObjByTransaction($detailObj,$title,$onlineSku);
	}


	/**
	 *
	 * get orderDetail Ojbect
	 * @param Object $transaction
	 * @param String $title
	 * @param String $sku
	 * @return Object
	 */
	protected function _getOrderDetailObjByTransaction($transaction,$title,$sku){
		/*获取捆绑sku对应具体sku***********************************/
//				$decodeSku = MHelper::getBindSkuMap($sku);
//				$sku = !empty($decodeSku)?$decodeSku:$sku;
		/************************************/
		$obj = null;
		$obj->transaction_id = $transaction->TransactionID;
		$obj->order_id = $this->_orderInfo['order_id'];
		$obj->platform_code = UebModel::model('Platform')->getEbayPlatformCode();
		$obj->item_id = $transaction->Item->ItemID;
		$obj->title = $title;
		$obj->sku_old = $sku;
		$obj->sku = $sku;
		//$obj->product_code = trim($new_info['product_code']);
		$obj->site = $transaction->Item->Site;
		$obj->quantity_old = $transaction->QuantityPurchased;
		//$obj->quantity = $new_info['quantity'];
		$obj->quantity = $transaction->QuantityPurchased;
		$obj->qs = $obj->quantity;
		$obj->sale_price = $transaction->TransactionPrice;
		$obj->total_price = floatval($transaction->TransactionPrice)*intval($transaction->QuantityPurchased);
		$obj->currency = $this->_ebayOrder->currencyCode;
		$obj->final_value_fee = $transaction->FinalValueFee;
		return $obj;
	}

	/**
	 *
	 * to get SKU and title for product
	 * @param Object $transaction
	 * @param return array
	 */
	protected function _getSkuAndTitle($transaction){
		$result = array();
		if(trim($transaction->Variation->SKU)!=''){
			//$result['sku'] = MHelper::getRealSku($transaction->Variation->SKU);
			$result['sku'] = $transaction->Variation->SKU;
			$result['title'] = trim($transaction->Variation->VariationTitle);
		}else{
			//$result['sku'] = MHelper::getRealSku($transaction->Item->SKU);
			$result['sku'] = $transaction->Item->SKU;
			$result['title'] = trim($transaction->Item->Title);
		}
		$skuStack = UebModel::model('EbayListing')->decryptSku($result['sku']);
		if (isset($skuStack['sku']) && !empty($skuStack['sku']))
		    $result['sku'] = $skuStack['sku'];
		return $result;
	}


	protected function _setEbayOrderInfo($order){
		$this->_ebayOrder = $order;
	}

	protected  function _getOrderInfoByPlatformOrderId($platformOrderId){
		return $this->_orderInfo = UebModel::model('OrderEbay')->checkOrderExistByPlatformInfo(UebModel::model('Platform')->getEbayPlatformCode(),$platformOrderId);
	}

	/**
	 *
	 *  skip order already catched by payplay
	 * @param Object $externalTransactions
	 */
	public function checkPayPalTransactionIsExist($externalTransactions){
		foreach($externalTransactions as $ExternalTransaction) {
			$transactionId = trim($ExternalTransaction->ExternalTransactionID);

			if(UebModel::model('OrderCheck')->checkTransactionIsExist($transactionId)){
				return true;
			}

		}
		return false;
	}


	/**
	 *
	 * skip the refund order from ebay (totalAmount<=0.00)
	 * @param float $totalAmount
	 */
	public function checkOrderIsRefund($totalAmount){
		if(floatval($totalAmount)<=0.00){
			return true;
		}
	}


	/**
	 *
	 * get totalPate for current order to catch
	 */
	public function getTotalPage(){
		return $this->_totalPage;
	}

	/**
	 *
	 * get totalNumber for current order to catch
	 */
	public function getTotalNumber(){
		return $this->_totalNumber;
	}

	/**
	 *
	 * to get the email message for order
	 */
	protected  function _getOrderEmail(){
		$result = '';
		if(isset($this->_orderInfo['email'])){
			$result = $this->_orderInfo['email'];
		}else if(isset($this->_ebayOrder->TransactionArray->Transaction->Buyer->Email)){
			if ($this->_ebayOrder->TransactionArray->Transaction->Buyer->Email!='Invalid Request')
				$result = $this->_ebayOrder->TransactionArray->Transaction->Buyer->Email;
			else
				$result = '';
		} else if (isset($this->_ebayOrder->TransactionArray->Transaction[0]) &&
			isset($this->_ebayOrder->TransactionArray->Transaction[0]->Buyer->Email))
		{
			$result = $this->_ebayOrder->TransactionArray->Transaction[0]->Buyer->Email;
		}
		return trim($result);
	}

	/**
	 *
	 * to get the shipPhone message for order
	 */
	protected function _getOrderShipPhone(){
		$result = '';
		if(isset($this->_orderInfo['ship_phone']) && !empty($this->_orderInfo['ship_phone'])){
			$result = $this->_orderInfo['ship_phone'];
		}elseif(isset($this->_ebayOrder->ShippingAddress->Phone)&&($this->_ebayOrder->ShippingAddress->Phone!='Invalid Request')){
			$result = !empty($this->_ebayOrder->ShippingAddress->Phone) ? $this->_ebayOrder->ShippingAddress->Phone : '';
		}
		return trim($result);
	}

	/**
	 *
	 * to get order_id
	 */
	protected function _getSytemOrderId(){
		$result = '';
		if(isset($this->_orderInfo['order_id'])){
			$result = $this->_orderInfo['order_id'];
		}else{

			$result = $this->_orderInfo['order_id'] = UebModel::model('autoCode')->getCode('order_ebay');

		}

		return $result;
	}


	/**
	 *	to get all transactions sort by transaction time
	 */
	protected function _getTransactionsByTransactionTime($externalTransactions){
		$order_transactions = array();
		if (!is_array($externalTransactions))
			$order_transactions[strtotime($ExternalTransaction->ExternalTransactionTime)] = $externalTransactions;
		else
			foreach($externalTransactions as $ExternalTransaction) {
				$order_transactions[strtotime($ExternalTransaction->ExternalTransactionTime)] = $ExternalTransaction;
			}
		ksort($order_transactions);
		return $order_transactions;
	}

	/**
	 *
	 * to get customer object
	 */
	protected function _getCustomerObj(){
		$customerObj = new stdClass();
		$customerObj->customer_name = !empty($this->_ebayOrder->ShippingAddress->Name) ? $this->_ebayOrder->ShippingAddress->Name : '';
		$customerObj->buyer_id = !empty($this->_ebayOrder->BuyerUserID) ? $this->_ebayOrder->BuyerUserID : '';
		$customerObj->country = !empty($this->_ebayOrder->ShippingAddress->CountryName) ? $this->_ebayOrder->ShippingAddress->CountryName : '';
		$customerObj->email = $this->_getOrderEmail();
		$customerObj->ship_to_name = !empty($this->_ebayOrder->ShippingAddress->Name) ? $this->_ebayOrder->ShippingAddress->Name : '';
		$customerObj->tel = $this->_getOrderShipPhone();
		$customerObj->address1 = !empty($this->_ebayOrder->ShippingAddress->Street1) ? $this->_ebayOrder->ShippingAddress->Street1 : '';
		$customerObj->address2 = !empty($this->_ebayOrder->ShippingAddress->Street2) ? $this->_ebayOrder->ShippingAddress->Street2 : '';
		$customerObj->city = !empty($this->_ebayOrder->ShippingAddress->CityName) ? $this->_ebayOrder->ShippingAddress->CityName : '';
		$customerObj->state_province = !empty($this->_ebayOrder->ShippingAddress->StateOrProvince) ? $this->_ebayOrder->ShippingAddress->StateOrProvince : '';
		$customerObj->zip = !empty($this->_ebayOrder->ShippingAddress->PostalCode) ? $this->_ebayOrder->ShippingAddress->PostalCode : '';
		$customerObj->datafrom = UebModel::model('Customer')->getDataFromPaypal();
		$customerObj->update_time = date('Y-m-d H:i:s',time());
		return $customerObj;
	}

	/**
	 *
	 * to get ebay order
	 */
	protected function _getEbayOrderObj(){
		date_default_timezone_set('Asia/Shanghai');
		$orderObj = new stdClass();
		$orderObj->email = $this->_getOrderEmail();
		$orderObj->ship_phone = $this->_getOrderShipPhone();
		$orderObj->platform_code = UebModel::model('Platform')->getEbayPlatformCode();
		$orderObj->platform_order_id = $this->_ebayOrder->OrderID;
		$orderObj->order_id = $this->_getSytemOrderId();
		$orderObj->log_id = $this->_taskId;
		$orderObj->account_id = $this->_account_id;
		$orderObj->order_status = $this->_ebayOrder->OrderStatus;
		$orderObj->buyer_id = $this->_ebayOrder->BuyerUserID;
		$orderObj->timestamp = date('Y-m-d H:i:s');
		$orderObj->created_time = date('Y-m-d H:i:s',strtotime($this->_ebayOrder->CreatedTime));
		$orderObj->last_update_time = $this->_ebayOrder->CheckoutStatus->LastModifiedTime;

		//支付方式
		$orderObj->payment_method =$this->_ebayOrder->PaymentMethods;


		$orderObj->paytime = date('Y-m-d H:i:s',strtotime($this->_ebayOrder->PaidTime));
		$orderObj->ship_zip = !empty($this->_ebayOrder->ShippingAddress->PostalCode) ? $this->_ebayOrder->ShippingAddress->PostalCode : '';
		$orderObj->ship_city_name = !empty($this->_ebayOrder->ShippingAddress->CityName) ? $this->_ebayOrder->ShippingAddress->CityName : '';
		$orderObj->ship_stateorprovince = !empty($this->_ebayOrder->ShippingAddress->StateOrProvince) ? $this->_ebayOrder->ShippingAddress->StateOrProvince : '';
		$orderObj->ship_name = !empty($this->_ebayOrder->ShippingAddress->Name) ? trim($this->_ebayOrder->ShippingAddress->Name) : '';
		$orderObj->ship_street1 = !empty($this->_ebayOrder->ShippingAddress->Street1) ? trim($this->_ebayOrder->ShippingAddress->Street1) : '';
		$orderObj->ship_street2 = !empty($this->_ebayOrder->ShippingAddress->Street2) ? trim($this->_ebayOrder->ShippingAddress->Street2) : '';
		//如果订单已经添加过，保留之前的complete_status值
		$orderObj->complete_status = isset($this->_orderInfo['complete_status']) ? $this->_orderInfo['complete_status'] : Order::COMPLETE_STATUS_INIT;
		$orderObj->ship_cost = $this->_ebayOrder->ShippingServiceSelected->ShippingServiceCost;
		$orderObj->subtotal_price = $this->_ebayOrder->Subtotal;
		$orderObj->total_price = $this->_ebayOrder->Total;
		$orderObj->currency = $this->_ebayOrder->currencyCode;
		$orderObj->payment_status =	 isset($this->_ebayOrder->PaidTime) && !empty($this->_ebayOrder->PaidTime) ?
			UebModel::model('OrderEbay')->getPayedOrderStatus() : UebModel::model('OrderEbay')->getNotPayedOrderStatus();
		//$orderObj->payment_status = UebModel::model('OrderEbay')->getNotPayedOrderStatus();
		$orderObj->ship_country = !empty($this->_ebayOrder->ShippingAddress->Country) ? trim($this->_ebayOrder->ShippingAddress->Country) : '';
		$orderObj->ship_country_name = !empty($this->_ebayOrder->ShippingAddress->CountryName) ? $this->_ebayOrder->ShippingAddress->CountryName : '';
		//客户选择的运输方式
		$orderObj->buyer_option_logistics = '';
		if (isset($this->_ebayOrder->ShippingServiceSelected) && isset($this->_ebayOrder->ShippingServiceSelected->ShippingService))
			$orderObj->buyer_option_logistics = $this->_ebayOrder->ShippingServiceSelected->ShippingService;
		$orderObj->is_ebay_plus = 0;
		if (isset($this->_ebayOrder->ContainseBayPlusTransaction) && $this->_ebayOrder->ContainseBayPlusTransaction)
			$orderObj->is_ebay_plus = 1;
		return $orderObj;

	}

	/**
	 *
	 * to set task_id for order
	 * @param $taskId
	 *
	 */
	protected function _setEbayOrderTaskId($taskId){
		$this->_taskId = $taskId;
	}

	/**
	 *
	 * set account_id
	 * @param string $account
	 */
	protected function _setEayAccountId($account){
		return $this->_account_id = $account;
	}

	protected function _getCatchNotPayOrderStartTime(){
		return date('Y-m-d H:i:s',strtotime(self::SUB_DAY));
	}
	protected function _setSuccessCount($count){
		self::$_SuccessCount += $count;
	}

	public function checkPaypalTransaction($account)
	{
		$PaypalTransaction = UebModel::model('PaypalTransaction');
		$transactions = $PaypalTransaction->checkPaypalErrorRequest($account);
		if (!empty($transactions))
		{
			$this->_sysncPaypal($transactions);
		}
		return true;
	}

}
?>
