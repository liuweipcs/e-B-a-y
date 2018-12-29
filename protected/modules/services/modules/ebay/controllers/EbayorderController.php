<?php
/**
 * @package Ueb.modules.services.controllers
 * @author Gordon 
 */

class EbayorderController extends UebController {

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array();
	}
    
	/**
	 * Ebay拉取订单
	 */
    public function actionGetorders(){
    	if(isset($_REQUEST['account'])){
			set_time_limit(36000);
    		$account = trim($_REQUEST['account']);
			$orderHandleObj = new EbayGetOrder();
    		$orderHandleObj->getOrdersByAccount($account);
    	}else{
    		$ebayAccounts = UebModel::model('EbayAccount')->getEbayAccountList();
    		if(!empty($ebayAccounts)){
    			foreach ($ebayAccounts as $id=>$val){
    				MHelper::runThreadSOCKET('/services/ebay/ebayorder/getorders/account/'.$id);
					sleep(2);
    			}
    		}else{
    			die('there are no any account!');
    		}
    	}
    }

    public function actionGetfinalvaluefees()
    {
        set_time_limit(300);
        if(isset($_GET['line']))
        {
            $startTime = time();

            //$from = 0;
            //$to = ($_GET['line'] + 1) * 100;

            $orders = (new OrderEbay())->findAll(array(
                'select'=> 'order_id,platform_order_id,account_id,count_final_value_fee',
                'condition' => 'right(order_id,1)='.$_GET['line'].' and  (platform_code = "EB" and ((account_id="53" and paytime >= "2017-09-13 11:05:00")
                        or (account_id = "49" and paytime >= "2017-09-13 11:18:00")
                        or (account_id = "52" and paytime >= "2017-09-19 14:01:00")
                        or (account_id = "9" and paytime >= "2017-09-21 20:12:00")
                        or (account_id = "22" and paytime >= "2017-10-18 18:00:00")
                        or (account_id = "28" and paytime >= "2017-10-18 18:00:00")
                        or (account_id = "4" and paytime >= "2017-11-01 17:33:00")
                        or (account_id = "14" and paytime >= "2017-11-01 17:33:00")
                        or (account_id = "21" and paytime >= "2017-11-01 17:33:00")
                        or (account_id = "33" and paytime >= "2017-11-01 17:33:00")

                        or (account_id = "13" and paytime >= "2017-11-14 18:08:00")
                        or (account_id = "67" and paytime >= "2017-11-14 18:08:00")
                        or (account_id = "16" and paytime >= "2017-11-14 18:08:00")
                        or (account_id = "8" and paytime >= "2017-11-14 18:08:00")
                        or (account_id = "50" and paytime >= "2017-11-14 18:08:00")
                    )) and order_type = 1 and order_status <> 40 and final_value_fee = 0',
//                'order' => 'created_time DESC',
                'order' => 'count_final_value_fee ASC,created_time DESC',
                'limit'=>200
            ));
            $token = [];
            if(empty($orders))
                exit('没有要更新的订单');
            foreach($orders as $order)
            {
                if(time()-$startTime > 290)
                {
                    exit('已到5分钟。');
                }
                $order->calculateProfitRate($order->order_id,true);
                echo '<hr/>',$order->platform_order_id,'<br/>';
                if(!isset($token[$order->account_id]))
                {
                    $token[$order->account_id] = (new Ebay())->findByPk($order->account_id)->user_token;
                }
                $api = new TradingAPI();
                $api->setUserToken($token[$order->account_id]);
                $api->xmlTagArray = [
                    'GetOrderTransactionsRequest'=>[
                        'IncludeFinalValueFees'=>'true',
                        'OrderIDArray'=>[
                            'OrderID'=>[
                                $order->platform_order_id
                            ]
                        ]
                    ]
                ];
                $response = $api->send()->response;
                if(in_array($response->Ack->__toString(),array('Success','Warning')))
                {
                    if(isset($response->OrderArray->Order))
                    {

                        $finalValueFee = 0;
                        foreach($response->OrderArray->Order as $apiOrder)
                        {
                            foreach($apiOrder->TransactionArray->Transaction as $transaction)
                            {
                                if(isset($transaction->FinalValueFee))
                                {
                                    $finalValueFee += $transaction->FinalValueFee->__toString();
                                    echo 'FinalValueFee','<br/>';
                                }
                                else if(isset($transaction->Item->SellingStatus->FinalValueFee))
                                {
                                    $finalValueFee += $transaction->Item->SellingStatus->FinalValueFee->__toString();
                                    echo 'Item->SellingStatus->FinalValueFee','<br/>';
                                }
                                else
                                {
                                    echo '无成交费字段。','<br/>';
                                    file_put_contents('log/finalvaluefees_error.log','无成交费字段：'.$order->platform_order_id.'  '.$order->order_id.PHP_EOL,FILE_APPEND);

                                }
                            }
                        }
                        echo $finalValueFee,'<br/>';
                        $order->updateByPk($order->order_id,['final_value_fee'=>$finalValueFee]);
                    }
                    else
                    {
                        echo '无订单信息','<br/>';
                        file_put_contents('log/finalvaluefees_error.log','无订单信息：'.$order->platform_order_id.'  '.$order->order_id.PHP_EOL,FILE_APPEND);
                    }
                }
                else
                {
                    echo 'ACK:',$response->Ack->__toString(),'<br/>';
                    file_put_contents('log/finalvaluefees_error.log','ACK返回'.$response->Ack->__toString().'：'.$order->platform_order_id.'  '.$order->order_id.PHP_EOL,FILE_APPEND);
                }
                $order->updateByPk($order->order_id,['count_final_value_fee'=>$order->count_final_value_fee + 1]);
                ob_flush();
                flush();
            }
        }
        else
        {
            $socketNum = 10;
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebayorder/getfinalvaluefees/line/'.$socketNum);
                sleep(2);
            }
        }
    }

    public function actionTest()
    {
        $token = 'AgAAAA**AQAAAA**aAAAAA**twgTWA**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wCmIalCJmCoQqdj6x9nY+seQ**sHQDAA**AAMAAA**041JWONJzRAtnt3CGTHrrRh3NhGf9zJUv5ud3EsG4o3T7gkVfk0r9p9pp6VhU5MLg5AD8GYTVVecMPm2hdoW8D6PsQk0p9KzJdvEpmCrj9gtYGk+3ARUTYHIsXMcdiSfsdOy4luN7iivId5chnqKmsFSD4ToFx/5RznOsuTOzg0L8O8szFXPIVARI4IdKovC/BoRWbFKs3QEE/J8S2lk7qokXHujmUesqZMDUD12K8KuzYAHz57hinSy4s4COvtbMVsWWVONF8742hXt2W72LHrsOwzU5/alldVY9m4/Y5ymloNNIsPSJ8vK3ceAWFbn4Phbj3tMgKR6fDbwSZOxAUr83xEusDWOgtoB1i2B+ovtkv+H+YuSZLNvPQTHhuIKJX7SLpNw8dwoAnA3dE3mFeq4oxK1iiOuB9TCm9/pYuL4kqMEl9zvLVqYer7/MIOUWnHxGiVONTZ12NPTm/PtKNh/CVXz4n2ymYc+4fFwHQ3998PzQslekR3z/MtI/b9S4uXKIIwo925BP6igVJG07UZi/p71IWG/J4b22pHe0yXI6zqKff58x6lPjf77gihjOjD8crw05rbOP0OdrGA5pBAoNbtkxVg2fKYfQoNqVO6S5lXQMnMl7/Jki0GN7V60QA7MH/rC0RAF0vleZ6RMtoWqRciE0fYsnddYh342ed5Zldeih3tvJ34yCMOLddhrKUw/wraENBqVFqKsAWNF3e5IYW50i0/s1+OOA2/XC8CwbnjoRGYiCrUq8owCz52d';
        $api = new TradingAPI();
        $api->setUserToken($token);
        $api->xmlTagArray = [
            'GetOrderTransactionsRequest'=>[
                'IncludeFinalValueFees'=>'true',
                'OrderIDArray'=>[
                    'OrderID'=>[
                        '400644963965-675704253027'
                    ]
                ]
            ]
        ];
        $api->send();
    }
}