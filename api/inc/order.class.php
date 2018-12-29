<?php
class order{
	function __construct(){
		$this->config =  new config();
		$this->config_new = new config(1);
	}
	function get_time($start_time,$key){

		$data = $this->config_new->getRowBySimple("opration_date >='".date('Y-m-d H:i:s',$start_time)."' and opration_date <='".date('Y-m-d H:i:s',$start_time+24*3600)."' and key_val = '".$key."'","*","id desc",$this->config_new->UORD.".ueb_order_sync_log");

		$endtime = $data['opration_date']?$data['opration_date']:date('Y-m-d H:i:s',$start_time);
		$endtime = date('Y-m-d H:i:s',strtotime($endtime)-2);
//		echo $endtime.'<br/>';
		return $endtime;
	}
	function synchron_order($start_time,$end_time,$key,$num =50,$limit=0){
		$syn_start_time = time();
		$failure_num = $success_num = $insert_num = $update_num = 0;
		$data = array();
		$data_check = array();
			switch($key){
				case 'ueb_order':

					$data_check = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order');
					if($data_check[0]['count'] == 0 ){
						return false;	
					}
					$data = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order');
					//数据对应关系
					foreach($data as $k => $v){
						$order = array(
								'orderid'   => $v['order_id'],
							    'platform_code'  => $v['platform_code'],
							    'platform_orderid'=> $v['platform_order_id'],
							    'accountid'=> $v['account_id'],
							    'logid' => $v['log_id'],
								'orderstatus' => $v['order_status'],
								'email'=> $v['email'],
								'buyerid'=> $v['buyer_id'],
								'timestamp'=> $v['timestamp'],
								'createdtime'=> $v['created_time'],
								'lastupdatetime'=> $v['last_update_time'],
								'paytime'=> $v['paytime'],
								'ship_name'=> $v['ship_name'],
								'ship_street1'=> $v['ship_street1'],
								'ship_street2'=> $v['ship_street2'],
								'ship_zip'=> $v['ship_zip'],
								'ship_cityname'=> $v['ship_city_name'],
								'ship_stateorprovince'=> $v['ship_stateorprovince'],
								'ship_country'=> $v['ship_country'],
								'ship_countryname'=> $v['ship_country_name'],
								'ship_phone'=> $v['ship_phone'],
								'print_remark'=> $v['print_remark'],
								'ship_cost'=> $v['ship_cost'],
								'subtotal_price'=> $v['subtotal_price'],
								'total_price'=> $v['total_price'],
								'currency'=> $v['currency'],
								'finalvaluefee'=> $v['final_value_fee'],
								'package_nums'=> $v['package_nums'],
								'repeat_nums'=> $v['repeat_nums'],
								'payment_status'=> $v['payment_status'],
								'ship_status'=> $v['ship_status'],
								'refund_status'=> $v['refund_status'],
								'ship_type'=> $v['ship_code'],
								'complete_status'=> $v['complete_status'],
								'opration_id'=> $v['opration_id'],
								'opration_date'=> $v['opration_date'],
								'service_remark'=> $v['service_remark'],
								'islock'=> $v['is_lock'],
								'abnormal'=> $v['abnormal'],
	//							'amazon_abroad_status'=> $v[''],
								'insurance_amount'=> $v['insurance_amount'],
							);
						//详情
						$order_details = $this->config_new->getCollectionBySimple("order_id ='".$v['order_id']."'","*","id",'',$this->config_new->UORD.'.ueb_order_detail');
						foreach($order_details as $detail){
							$order_detail = array(
									'order_detailid' => $detail['id'],
									'platform_code' => $detail['platform_code'],
									'transactionid' => $detail['transaction_id'],
									'orderid' => $detail['order_id'],
									'itemid' => $detail['item_id'],
									'title' => $detail['title'],
									'product_code_old' => $detail['sku_old'],
									'quantity_old' => $detail['quantity_old'],
									'product_code' => $detail['sku'],
									'site' => $detail['site'],
									'quantity' => $detail['quantity'],
									'qs' => $detail['qs'],
									'sale_price' => $detail['sale_price'],
									'ship_price' => $detail['ship_price'],
									'total_price' => $detail['total_price'],
									'currency' => $detail['currency'],
									'finalvaluefee' => $detail['final_value_fee'],
									'opration_id' => $detail['opration_id'],
									'opration_date' => $detail['opration_date'],
									'status' => $detail['status'],
									'pengding_status' => $detail['pending_status'],
									'islock' => $detail['is_lock'],
									'ischeck' => $detail['is_check'],
									'order_item_id' => $detail['order_item_id'],	
							);
							
							$check_detail = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'" and product_code = "'.$v['sku'].'"','*','',$this->config->OLDDB.".order_detail");
							if($check_detail){
								$this->config->update($order_detail,'orderid = "'.$v['order_id'].'"',$this->config->OLDDB.".order_detail");	
							}else{
								$this->config->insert($order_detail,$this->config->OLDDB.".order_detail",false);
							}	
						}	
						//判断update or insert
						$check_order = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'"','*','',$this->config->OLDDB.".order_main");
						if($check_order){
							$this->config->update($order,'orderid = "'.$v['order_id'].'"',$this->config->OLDDB.".order_main");
							$update_num++;
							$success_num++;
						}else{
							$r = $this->config->insert($order,$this->config->OLDDB.".order_main",false);
							if($r){
								$insert_num++;
								$success_num++;
							}else{
								$insert_num++;
								$failure_num++;
							}		
						}
					}
					break;
				case 'ueb_order_note':
					$data_check = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order_note');
					if($data_check[0]['count'] == 0 ){
						return false;
					}
					$data = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order_note');
					
					foreach($data as $k => $note){
						$order_note = array(
							'orderid' => $note['order_id'],
							'note' => $note['note'],
							'create_date' => $note['create_time'],
							'status' => $note['status'],
							'opration_date' => $note['modify_time'],
							'update_id' => $note['create_user_id'],
							'opration_id' => $note['modify_user_id'],
						);
						$note_check = $this->config->getRowBySimple('orderid = "'.$note['order_id'].'"','*','',$this->config->OLDDB.".order_note");
						if($note_check){
							$this->config->update($order_note,'orderid = "'.$note['order_id'].'"',$this->config->OLDDB.".order_note");
							$update_num++;
							$success_num++;
						}else{
							$r = $this->config->insert($order_note,$this->config->OLDDB.".order_note",false);
							if($r){
								$insert_num++;
								$success_num++;
							}else{
								$insert_num++;
								$failure_num++;
							}
						}
					}
					
					break;	
				case 'ueb_order_refund_log':		
					$data_check = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order_refund_log');
					if($data_check[0]['count'] == 0 ){
						return false;
					}
					$data = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order_refund_log');
					//数据对应关系
					foreach($data as $k => $v){
						//退款数据
						$refundData=array(
							'orderid'		=> $va['order_id'],
							'currency'		=> $va['currency'],
							'amt'			=> $va['refund_amt'],
							'email'			=> $va['payer_email'],
							'buyerid'		=> $va['buyer_id'],
							'transactionid'	=> $va['return_transaction_id'],
							'cause'			=> $va['refund_reason'],
							'remark'		=> $va['refund_remark'],
							'type'			=> $va['refund_type'],
							'opration_id'	=> $va['opration_id'],
							'opration_date'	=> $va['opration_date']							
						);
						//退款详情
						$order_details = $this->config_new->getCollectionBySimple("order_refundid ='".$v['id']."'","*","id",'',$this->config_new->UORD.'.ueb_order_refund_detail');
						foreach($order_details as $detail){
							$refundDetailData=array(
								'order_refundid'	=>$detail['id'],
								'platform_code'		=>$detail['platform_code'],
								'order_detailid'	=>$this->getOrderDetailId($detail['order_id'],$detail['sku']),
								'orderid'			=>$detail['order_id'],
								'product_code'		=>$detail['sku'],
								'quantity'			=>$detail['quantity'],
								'amt'				=>$detail['amt'],
								'amtfee'			=>$detail['amt_fee'],
								'upload_to_newfrog'	=>$detail['upload_to_newfrog'],
							);
							$check_order = $this->config->getRowBySimple('order_refundid = "'.$detail['id'].'" and product_code="'.$detail['sku'].'"','*','',$this->config->OLDDB.".order_refund_detail");
							if($check_order){
								$this->config->update($refundDetailData,'order_refundid = "'.$detail['id'].'" and product_code="'.$detail['sku'].'"',$this->config->OLDDB.".order_refund_detail");
								$update_num++;
								$success_num++;
							}else{
								$r = $this->config->insert($refundDetailData,$this->config->OLDDB.".order_refund_detail",false);
								if($r){
									$insert_num++;
									$success_num++;
								}else{
									$insert_num++;
									$failure_num++;
								}
							}
						}						
						$check_order = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'" and transactionid="'.$v['return_transaction_id'].'"','*','',$this->config->OLDDB.".order_refund_log");
						if($check_order){
							$this->config->update($refundData,'orderid = "'.$v['order_id'].'" and transactionid="'.$v['return_transaction_id'].'"',$this->config->OLDDB.".order_refund_log");
							$update_num++;
							$success_num++;
						}else{
							$r = $this->config->insert($refundData,$this->config->OLDDB.".order_refund_log",false);
							if($r){
								$insert_num++;
								$success_num++;
							}else{
								$insert_num++;
								$failure_num++;
							}
						}
						
					}							
						
					break;
				case 'ueb_order_repeat':	
					$data_check = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order_repeat');
					if($data_check[0]['count'] == 0 ){
						return false;
					}
					$data = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order_repeat');
					//数据对应关系
					foreach($data as $k => $v){	
						$repeatData=array(
							'orderid' 		=> $v['order_id'],
							'cause' 		=> $v['cause_id'],
							'remark' 		=> $v['remark'],
							'opration_id' 	=> $v['create_user_id'],
							'opration_date' => $v['carete_time'],
						);
						$check_order = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'"','*','',$this->config->OLDDB.".order_repeat_log");
						if($check_order){
							$this->config->update($repeatData,'orderid = "'.$v['order_id'].'"',$this->config->OLDDB.".order_repeat_log");
							$update_num++;
							$success_num++;
						}else{
							$r = $this->config->insert($repeatData,$this->config->OLDDB.".order_repeat_log",false);
							if($r){
								$insert_num++;
								$success_num++;
							}else{
								$insert_num++;
								$failure_num++;
							}
						}
					}										
					break;
					
				case 'ueb_order_transaction':
					$data_check = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.order_transaction');
					if($data_check[0]['count'] == 0 ){
						return false;
					}
					$data = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.order_transaction');
					foreach($data as $k => $tran){
						$record = $this->config_new->getRowBySimple('transaction_id = "'.$tran['transaction_id'].'"','*','',$this->config_new->UORD.".ueb_paypal_transaction_record");
						$order_tran = array(
							'transactionid' => $record['transaction_id'],
							'orderid' => $record['order_id'],
							'accountid' => $tran['account_id'],
							'receive_type' => $record['receive_type'],
							'first' => $tran['is_first_transaction'],
							'receiver_business' => $record['receiver_business'],
							'receiver_email' => $record['receiver_email'],
							'receiver_id' => $record['receiver_id'],
							'payer_id' => $record['payer_id'],
							'payer_name' => $record['payer_name'],
							'payer_email' => $record['payer_email'],
							'payer_status' => $record['payer_status'],
							'parent_transactionid' => $record['parent_transaction_id'],
							'transaction_type' => $record['transaction_type'],
							'payment_type' => $record['payment_type'],
							'order_time' => $record['order_time'],
							'amt' => $record['amt'],
							'fee_amt' => $record['fee_amt'],
							'tax_amt' => $record['tax_amt'],
							'currency' => $record['currency'],
							'payment_status' => $record['payment_status'],
							'note' => $record['note'],
						);
						$check_tran = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'"','*','',$this->config->OLDDB.".order_transaction");
						if($check_tran){
							$this->config->update($order_tran,'orderid = "'.$v['order_id'].'"',$this->config->OLDDB.".order_transaction");
							$update_num++;
							$success_num++;
						}else{
							$r = $this->config->insert($order_tran,$this->config->OLDDB.".order_transaction",false);
							if($r){
								$insert_num++;
								$success_num++;
							}else{
								$insert_num++;
								$failure_num++;
							}
						}	
					}
					break;
					
				case 'ueb_order_profit':
					$data_check = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order_profit');
					if($data_check[0]['count'] == 0 ){
						return false;
					}
					
					$data = $this->config_new->getCollectionBySimple("modify_time >='".$start_time."' and modify_time <='".$end_time."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order_profit');
					foreach ($data as $k => $profits_data){
						$profit_arr = array(
							'orderid' 			=> $profits_data['order_id'],
							'amt' 				=> $profits_data['amt'],
							'fee_amt' 			=> $profits_data['fee_amt'],
							'product_cost' 		=> $profits_data['product_cost'],
							'ship_cost' 		=> $profits_data['ship_cost'],
							'finalvaluefee' 	=> $profits_data['finalvaluefee'],
							'refund_amt' 		=> $profits_data['refund_amt'],
							'back_amt' 			=> $profits_data['back_amt'],
							'rate' 				=> $profits_data['rate'],
							'profit' 			=> $profits_data['profit'],
							'final_profit' 		=> $profits_data['final_profit'],
							'opration_date' 	=> $profits_data['opration_date'],
							'currency' 			=> $profits_data['currency'],
							'platform_code' 	=> $profits_data['platform_code'],
							'promotion_amount' 	=> $profits_data['promotion_amount'],
							'other_amount' 		=> $profits_data['other_amount'],
						);
						$profits_details_data = $this->config_new->getCollectionBySimple("order_id ='".$profits_data['order_id']."'","*","order_detail_id",'',$this->config_new->UORD.'.ueb_order_profit_detail');
						foreach ($profits_details_data as $detail_data){
							$profit_detail_arr = array(
								'order_detailid'	=> $detail_data['order_detail_id'],
								'orderid'			=> $detail_data['order_id'],
								'platform_code'		=> $detail_data['platform_code'],
								'product_code'		=> $detail_data['product_code'],
								'amt'				=> $detail_data['amt'],
								'fee_amt'			=> $detail_data['fee_amt'],
								'product_cost'		=> $detail_data['product_cost'],
								'ship_cost'			=> $detail_data['ship_cost'],
								'finalvaluefee'		=> $detail_data['finalvaluefee'],
								'refund_amt'		=> $detail_data['refund_amt'],
								'back_amt'			=> $detail_data['back_amt'],
								'rate'				=> $detail_data['rate'],
								'profit'			=> $detail_data['profit'],
								'final_profit'		=> $detail_data['final_profit'],
								'opration_date'		=> $detail_data['opration_date'],
								'currency'			=> $detail_data['currency'],
								'promotion_amount'	=> $detail_data['promotion_amount'],
								'other_amount'		=> $detail_data['other_amount'],
							);
							$check_profit_detail = $this->config->getRowBySimple('order_detailid = "'.$detail_data['order_detail_id'].'"','*','',$this->config->OLDDB.".order_profit_detail");
							if($check_profit_detail){
								$this->config->update($profit_detail_arr,'order_detailid = "'.$check_profit_detail['order_detail_id'].'"',$this->config->OLDDB.".order_profit_detail");
							}else{
								$this->config->insert($profit_detail_arr,$this->config->OLDDB.".order_profit_detail",false);
							}
						}
						$check_order = $this->config->getRowBySimple('orderid = "'.$profits_data['order_id'].'"','*','',$this->config->OLDDB.".order_profit");
						if($check_order){
							$this->config->update($profit_arr,'orderid = "'.$profits_data['order_id'].'"',$this->config->OLDDB.".order_profit");
							$update_num++;
							$success_num++;
						}else{
							$r = $this->config->insert($profit_arr,$this->config->OLDDB.".order_profit",false);
							if($r){
								$insert_num++;
								$success_num++;
							}else{
								$insert_num++;
								$failure_num++;
							}
						}
					}
					break;
			}	
			$syn_end_time = time();
			$this->config_new->insert(array('num'           => count($data),
											'key_val'		    => $key,
			    							'total_num'  	=> $data_check[0]['count'],
			    							'residue_num'	=> $data_check[0]['count']-$limit-count($data),
			    							'start_num'  	=> $limit,
			    							'start_order'	=> $data[0]['order_id'],
											'success_num'	=> $success_num,
											'failure_num'	=> $failure_num,
											'start_time' 	=> date('Y-m-d H:i:s',$syn_start_time),
											'end_time'   	=> date('Y-m-d H:i:s',$syn_end_time),
			    							'opration_date' => $data[$k]['modify_time'],
											'use_time'   	=> $syn_end_time - $syn_start_time,
			    							'insert_num' 	=> $insert_num,
			    							'update_num' 	=> $update_num),	
									  $this->config->UORD.".ueb_order_sync_log");					  
			$limit += $num;
			$this->synchron_order($start_time,$end_time,$key,$num,$limit);
	}
	
	function synchron_order2($key,$num =50,$limit=0,$orderId){
		$syn_start_time = time();
		$failure_num = $success_num = $insert_num = $update_num = 0;
		$data = array();
		$data_check = array();
		switch($key){
			case 'ueb_order':
	
				$data_check = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order');
				if($data_check[0]['count'] == 0 ){
					return false;
				}
				$data = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order');
				//数据对应关系
				foreach($data as $k => $v){
					$order = array(
							'orderid'   => $v['order_id'],
							'platform_code'  => $v['platform_code'],
							'platform_orderid'=> $v['platform_order_id'],
							'accountid'=> $v['account_id'],
							'logid' => $v['log_id'],
							'orderstatus' => $v['order_status'],
							'email'=> $v['email'],
							'buyerid'=> $v['buyer_id'],
							'timestamp'=> $v['timestamp'],
							'createdtime'=> $v['created_time'],
							'lastupdatetime'=> $v['last_update_time'],
							'paytime'=> $v['paytime'],
							'ship_name'=> $v['ship_name'],
							'ship_street1'=> $v['ship_street1'],
							'ship_street2'=> $v['ship_street2'],
							'ship_zip'=> $v['ship_zip'],
							'ship_cityname'=> $v['ship_city_name'],
							'ship_stateorprovince'=> $v['ship_stateorprovince'],
							'ship_country'=> $v['ship_country'],
							'ship_countryname'=> $v['ship_country_name'],
							'ship_phone'=> $v['ship_phone'],
							'print_remark'=> $v['print_remark'],
							'ship_cost'=> $v['ship_cost'],
							'subtotal_price'=> $v['subtotal_price'],
							'total_price'=> $v['total_price'],
							'currency'=> $v['currency'],
							'finalvaluefee'=> $v['final_value_fee'],
							'package_nums'=> $v['package_nums'],
							'repeat_nums'=> $v['repeat_nums'],
							'payment_status'=> $v['payment_status'],
							'ship_status'=> $v['ship_status'],
							'refund_status'=> $v['refund_status'],
							'ship_type'=> $v['ship_code'],
							'complete_status'=> $v['complete_status'],
							'opration_id'=> $v['opration_id'],
							'opration_date'=> $v['opration_date'],
							'service_remark'=> $v['service_remark'],
							'islock'=> $v['is_lock'],
							'abnormal'=> $v['abnormal'],
							//							'amazon_abroad_status'=> $v[''],
							'insurance_amount'=> $v['insurance_amount'],
					);
					//详情
					$order_details = $this->config_new->getCollectionBySimple("order_id ='".$v['order_id']."'","*","id",'',$this->config_new->UORD.'.ueb_order_detail');
					foreach($order_details as $detail){
						$order_detail = array(
								'order_detailid' => $detail['id'],
								'platform_code' => $detail['platform_code'],
								'transactionid' => $detail['transaction_id'],
								'orderid' => $detail['order_id'],
								'itemid' => $detail['item_id'],
								'title' => $detail['title'],
								'product_code_old' => $detail['sku_old'],
								'quantity_old' => $detail['quantity_old'],
								'product_code' => $detail['sku'],
								'site' => $detail['site'],
								'quantity' => $detail['quantity'],
								'qs' => $detail['qs'],
								'sale_price' => $detail['sale_price'],
								'ship_price' => $detail['ship_price'],
								'total_price' => $detail['total_price'],
								'currency' => $detail['currency'],
								'finalvaluefee' => $detail['final_value_fee'],
								'opration_id' => $detail['opration_id'],
								'opration_date' => $detail['opration_date'],
								'status' => $detail['status'],
								'pengding_status' => $detail['pending_status'],
								'islock' => $detail['is_lock'],
								'ischeck' => $detail['is_check'],
								'order_item_id' => $detail['order_item_id'],
						);
							
						$check_detail = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'" and product_code = "'.$v['sku'].'"','*','',$this->config->OLDDB.".order_detail");
						if($check_detail){
							$this->config->update($order_detail,'orderid = "'.$v['order_id'].'"',$this->config->OLDDB.".order_detail");
						}else{
							$this->config->insert($order_detail,$this->config->OLDDB.".order_detail",false);
						}
					}
					//判断update or insert
					$check_order = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'"','*','',$this->config->OLDDB.".order_main");
					if($check_order){
						$this->config->update($order,'orderid = "'.$v['order_id'].'"',$this->config->OLDDB.".order_main");
						$update_num++;
						$success_num++;
					}else{
						$r = $this->config->insert($order,$this->config->OLDDB.".order_main",false);
						if($r){
							$insert_num++;
							$success_num++;
						}else{
							$insert_num++;
							$failure_num++;
						}
					}
				}
				break;
			case 'ueb_order_note':
				$data_check = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order_note');
				if($data_check[0]['count'] == 0 ){
					return false;
				}
				$data = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order_note');
					
				foreach($data as $k => $note){
					$order_note = array(
							'orderid' => $note['order_id'],
							'note' => $note['note'],
							'create_date' => $note['create_time'],
							'status' => $note['status'],
							'opration_date' => $note['modify_time'],
							'update_id' => $note['create_user_id'],
							'opration_id' => $note['modify_user_id'],
					);
					$note_check = $this->config->getRowBySimple('orderid = "'.$note['order_id'].'"','*','',$this->config->OLDDB.".order_note");
					if($note_check){
						$this->config->update($order_note,'orderid = "'.$note['order_id'].'"',$this->config->OLDDB.".order_note");
						$update_num++;
						$success_num++;
					}else{
						$r = $this->config->insert($order_note,$this->config->OLDDB.".order_note",false);
						if($r){
							$insert_num++;
							$success_num++;
						}else{
							$insert_num++;
							$failure_num++;
						}
					}
				}
					
				break;
			case 'ueb_order_refund_log':
				$data_check = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order_refund_log');
				if($data_check[0]['count'] == 0 ){
					return false;
				}
				$data = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order_refund_log');
				//数据对应关系
				foreach($data as $k => $v){
					//退款数据
					$refundData=array(
							'orderid'		=> $va['order_id'],
							'currency'		=> $va['currency'],
							'amt'			=> $va['refund_amt'],
							'email'			=> $va['payer_email'],
							'buyerid'		=> $va['buyer_id'],
							'transactionid'	=> $va['return_transaction_id'],
							'cause'			=> $va['refund_reason'],
							'remark'		=> $va['refund_remark'],
							'type'			=> $va['refund_type'],
							'opration_id'	=> $va['opration_id'],
							'opration_date'	=> $va['opration_date']
					);
					//退款详情
					$order_details = $this->config_new->getCollectionBySimple("order_refundid ='".$v['id']."'","*","id",'',$this->config_new->UORD.'.ueb_order_refund_detail');
					foreach($order_details as $detail){
						$refundDetailData=array(
								'order_refundid'	=>$detail['id'],
								'platform_code'		=>$detail['platform_code'],
								'order_detailid'	=>$this->getOrderDetailId($detail['order_id'],$detail['sku']),
								'orderid'			=>$detail['order_id'],
								'product_code'		=>$detail['sku'],
								'quantity'			=>$detail['quantity'],
								'amt'				=>$detail['amt'],
								'amtfee'			=>$detail['amt_fee'],
								'upload_to_newfrog'	=>$detail['upload_to_newfrog'],
						);
						$check_order = $this->config->getRowBySimple('order_refundid = "'.$detail['id'].'" and product_code="'.$detail['sku'].'"','*','',$this->config->OLDDB.".order_refund_detail");
						if($check_order){
							$this->config->update($refundDetailData,'order_refundid = "'.$detail['id'].'" and product_code="'.$detail['sku'].'"',$this->config->OLDDB.".order_refund_detail");
							$update_num++;
							$success_num++;
						}else{
							$r = $this->config->insert($refundDetailData,$this->config->OLDDB.".order_refund_detail",false);
							if($r){
								$insert_num++;
								$success_num++;
							}else{
								$insert_num++;
								$failure_num++;
							}
						}
					}
					$check_order = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'" and transactionid="'.$v['return_transaction_id'].'"','*','',$this->config->OLDDB.".order_refund_log");
					if($check_order){
						$this->config->update($refundData,'orderid = "'.$v['order_id'].'" and transactionid="'.$v['return_transaction_id'].'"',$this->config->OLDDB.".order_refund_log");
						$update_num++;
						$success_num++;
					}else{
						$r = $this->config->insert($refundData,$this->config->OLDDB.".order_refund_log",false);
						if($r){
							$insert_num++;
							$success_num++;
						}else{
							$insert_num++;
							$failure_num++;
						}
					}
	
				}
	
				break;
			case 'ueb_order_repeat':
				$data_check = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order_repeat');
				if($data_check[0]['count'] == 0 ){
					return false;
				}
				$data = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order_repeat');
				//数据对应关系
				foreach($data as $k => $v){
					$repeatData=array(
							'orderid' 		=> $v['order_id'],
							'cause' 		=> $v['cause_id'],
							'remark' 		=> $v['remark'],
							'opration_id' 	=> $v['create_user_id'],
							'opration_date' => $v['carete_time'],
					);
					$check_order = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'"','*','',$this->config->OLDDB.".order_repeat_log");
					if($check_order){
						$this->config->update($repeatData,'orderid = "'.$v['order_id'].'"',$this->config->OLDDB.".order_repeat_log");
						$update_num++;
						$success_num++;
					}else{
						$r = $this->config->insert($repeatData,$this->config->OLDDB.".order_repeat_log",false);
						if($r){
							$insert_num++;
							$success_num++;
						}else{
							$insert_num++;
							$failure_num++;
						}
					}
				}
				break;
					
			case 'ueb_order_transaction':
				$data_check = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.order_transaction');
				if($data_check[0]['count'] == 0 ){
					return false;
				}
				$data = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.order_transaction');
				foreach($data as $k => $tran){
					$record = $this->config_new->getRowBySimple('transaction_id = "'.$tran['transaction_id'].'"','*','',$this->config_new->UORD.".ueb_paypal_transaction_record");
					$order_tran = array(
							'transactionid' => $record['transaction_id'],
							'orderid' => $record['order_id'],
							'accountid' => $tran['account_id'],
							'receive_type' => $record['receive_type'],
							'first' => $tran['is_first_transaction'],
							'receiver_business' => $record['receiver_business'],
							'receiver_email' => $record['receiver_email'],
							'receiver_id' => $record['receiver_id'],
							'payer_id' => $record['payer_id'],
							'payer_name' => $record['payer_name'],
							'payer_email' => $record['payer_email'],
							'payer_status' => $record['payer_status'],
							'parent_transactionid' => $record['parent_transaction_id'],
							'transaction_type' => $record['transaction_type'],
							'payment_type' => $record['payment_type'],
							'order_time' => $record['order_time'],
							'amt' => $record['amt'],
							'fee_amt' => $record['fee_amt'],
							'tax_amt' => $record['tax_amt'],
							'currency' => $record['currency'],
							'payment_status' => $record['payment_status'],
							'note' => $record['note'],
					);
					$check_tran = $this->config->getRowBySimple('orderid = "'.$v['order_id'].'"','*','',$this->config->OLDDB.".order_transaction");
					if($check_tran){
						$this->config->update($order_tran,'orderid = "'.$v['order_id'].'"',$this->config->OLDDB.".order_transaction");
						$update_num++;
						$success_num++;
					}else{
						$r = $this->config->insert($order_tran,$this->config->OLDDB.".order_transaction",false);
						if($r){
							$insert_num++;
							$success_num++;
						}else{
							$insert_num++;
							$failure_num++;
						}
					}
				}
				break;
					
			case 'ueb_order_profit':
				$data_check = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","count(*) as count","modify_time asc",'',$this->config_new->UORD.'.ueb_order_profit');
				if($data_check[0]['count'] == 0 ){
					return false;
				}
					
				$data = $this->config_new->getCollectionBySimple("order_id='".$orderId."'","*","modify_time asc",$limit.','.$num,$this->config_new->UORD.'.ueb_order_profit');
				foreach ($data as $k => $profits_data){
					$profit_arr = array(
							'orderid' 			=> $profits_data['order_id'],
							'amt' 				=> $profits_data['amt'],
							'fee_amt' 			=> $profits_data['fee_amt'],
							'product_cost' 		=> $profits_data['product_cost'],
							'ship_cost' 		=> $profits_data['ship_cost'],
							'finalvaluefee' 	=> $profits_data['finalvaluefee'],
							'refund_amt' 		=> $profits_data['refund_amt'],
							'back_amt' 			=> $profits_data['back_amt'],
							'rate' 				=> $profits_data['rate'],
							'profit' 			=> $profits_data['profit'],
							'final_profit' 		=> $profits_data['final_profit'],
							'opration_date' 	=> $profits_data['opration_date'],
							'currency' 			=> $profits_data['currency'],
							'platform_code' 	=> $profits_data['platform_code'],
							'promotion_amount' 	=> $profits_data['promotion_amount'],
							'other_amount' 		=> $profits_data['other_amount'],
					);
					$profits_details_data = $this->config_new->getCollectionBySimple("order_id ='".$profits_data['order_id']."'","*","order_detail_id",'',$this->config_new->UORD.'.ueb_order_profit_detail');
					foreach ($profits_details_data as $detail_data){
						$profit_detail_arr = array(
								'order_detailid'	=> $detail_data['order_detail_id'],
								'orderid'			=> $detail_data['order_id'],
								'platform_code'		=> $detail_data['platform_code'],
								'product_code'		=> $detail_data['product_code'],
								'amt'				=> $detail_data['amt'],
								'fee_amt'			=> $detail_data['fee_amt'],
								'product_cost'		=> $detail_data['product_cost'],
								'ship_cost'			=> $detail_data['ship_cost'],
								'finalvaluefee'		=> $detail_data['finalvaluefee'],
								'refund_amt'		=> $detail_data['refund_amt'],
								'back_amt'			=> $detail_data['back_amt'],
								'rate'				=> $detail_data['rate'],
								'profit'			=> $detail_data['profit'],
								'final_profit'		=> $detail_data['final_profit'],
								'opration_date'		=> $detail_data['opration_date'],
								'currency'			=> $detail_data['currency'],
								'promotion_amount'	=> $detail_data['promotion_amount'],
								'other_amount'		=> $detail_data['other_amount'],
						);
						$check_profit_detail = $this->config->getRowBySimple('order_detailid = "'.$detail_data['order_detail_id'].'"','*','',$this->config->OLDDB.".order_profit_detail");
						if($check_profit_detail){
							$this->config->update($profit_detail_arr,'order_detailid = "'.$check_profit_detail['order_detail_id'].'"',$this->config->OLDDB.".order_profit_detail");
						}else{
							$this->config->insert($profit_detail_arr,$this->config->OLDDB.".order_profit_detail",false);
						}
					}
					$check_order = $this->config->getRowBySimple('orderid = "'.$profits_data['order_id'].'"','*','',$this->config->OLDDB.".order_profit");
					if($check_order){
						$this->config->update($profit_arr,'orderid = "'.$profits_data['order_id'].'"',$this->config->OLDDB.".order_profit");
						$update_num++;
						$success_num++;
					}else{
						$r = $this->config->insert($profit_arr,$this->config->OLDDB.".order_profit",false);
						if($r){
							$insert_num++;
							$success_num++;
						}else{
							$insert_num++;
							$failure_num++;
						}
					}
				}
				break;
		}
	}
	
	public function getOrderDetailId($orderId,$sku){
		if(isset($orderId)){
			$row = $this->config->getRow(array('where'=>"orderid='".$orderId."' and product_code='".$sku."'"),$this->config->OLDDB.".order_detail");
			return 	isset($row)?$row['order_detailid_id']:'';
		}
	}
	public function runThread($url,$hostname='',$port=80) {
	
		if(!$hostname){
			$hostname=$_SERVER['HTTP_HOST'];
		} 
		$fp=fsockopen($hostname,$port,&$errno,&$errstr,600); 
		fputs($fp,"GET ".$url."\r\n");
		fclose($fp); 
	    }
	}

    

?>