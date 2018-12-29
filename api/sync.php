<?php 
	set_time_limit(0);
	ini_set('display_errors',true);
	include_once("inc/base_mysql.php");
	include_once("inc/config.php");
	include_once("inc/sync.class.php");
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'user';	

	$starttime 	= isset($_REQUEST['starttime']) ? $_REQUEST['starttime'] : '2008-01-01 00:00:00';
	$lastStr 	= isset($_REQUEST['lastStr']) ? $_REQUEST['lastStr'] : '';
	$num		= isset($_REQUEST['num']) ? $_REQUEST['num'] : '';	
	$model 		= new syncModel();
	if($action == 'purchase_new_to_old'){
		//删除新系统已删除的采购单
		$model->purchase_order_new_to_old_check();

		$model->synchron_purchase_order_new_to_old();		
	}
	//首先同步用户 2供应商 3商品 4库存 5询价 6绑定 
	if( isset($_REQUEST['lastStr']) ){
    	$starttime = $model->get_time_thread($action,$lastStr);
		switch($action){
			case 'mainproduct':
				$model->synchron_main_product($lastStr,$starttime);
				break;
			case 'ebayproduct':
				$model->synchron_ebay_product($lastStr,$starttime);
				break;
			case 'ebayproductHWC':
				$model->synchron_ebay_productHWC($lastStr,$starttime);
				break;
			case 'ebayproductfee':
				$model->synchron_ebay_product_fee($lastStr,$starttime);
				break;
			case 'amazonproduct':
				$model->synchron_amazon_product($lastStr,$starttime);
				break;
			case 'productProvider':
				$model->productProvider($lastStr,$starttime);
				break;
			case 'supply':
				$model->synchron_supply($starttime);
				break;
			case 'user':
				$model->synchron_user($starttime);
				break;
			case 'skumap':
				$model->synchron_skumap($lastStr,$starttime);
				break;
			case 'skucombine':
				$model->syncSkuCombine($lastStr,$starttime);
				break;
            case 'inquire':
                $model->synchron_inquire($lastStr,$starttime);
                break;   
            case 'synchronAttribute':           
               $model->synchron_product_attribute($lastStr);
               break;
            case 'synchronSonAttribute':
              $model->synchron_son_product_attribute($lastStr);            
              break;
            case 'synchronAttributeBySonSku':             
              $model->synchron_sonAttribute($lastStr);
              break; 
             case 'synchronMultiToOld'://老到老 多属性整合              	
              	$model->synchron_multi_toold($lastStr);
              	break;
             case 'synchronDevelopers'://新角色  新的开发人
              	$model->synchron_developers($lastStr);
              	break;
             case 'updateProcutCost'://个别sku成本
             	$cost=$_REQUEST['cost'];            	
              	$model->synchron_product_cost($lastStr,$cost);
              	break;
             case 'synchronPlatfromProductUnline';
             	$model->synchron_platfrom_product_unline($lastStr,$starttime);   
             	break;
             case 'checkOrderTransaction';   
             	$model->check_ordertransaction($lastStr);
             	break;
			default:
		}
	}else{		
        $hostname=$_SERVER['HTTP_HOST'];
		$starttime = $model->get_time($action);
        switch($action){
        	case 'mainproduct':
        		$threadArr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');        		
				foreach($threadArr as $str){
					$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
				}
        		break;
        	case 'ebayproduct':
        		$threadArr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        		foreach($threadArr as $str){
        			$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
        		}
        		break;
        	case 'ebayproductHWC':
        		$threadArr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        		foreach($threadArr as $str){
        			$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
        		}
        		break;
        	case 'ebayproductfee';
	        	$threadArr = array('0','1','2','3','4','5','6','7','8','9');
	        	foreach($threadArr as $str){
	        		$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
	        	}
        		break;
        	case 'amazonproduct':
        		$threadArr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        		foreach($threadArr as $str){
        			$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
        		}
        		break;
        	case 'updateStockSync':
        		//从老系统同步库存到新系统
        		//$starttime = date('Y-m-d H:i:s',strtotime('-2 month')-24*60*60);//同步2个月前数据
        		$starttime = '2014-11-01 00:00:00';
        		$sku = $_REQUEST['sku'];
        		$model->updateStockSync($starttime,0,500,$sku);
        		break;
        	case 'checkamount':
        		//根据明细检测采购单实物金额
        		$starttime = '2014-06-01 00:00:00';
        		$pur_code = $_REQUEST['pur_code'];
        		if(empty($pur_code)){
        			//因有时跑一次不能跑完，故跑3次
        			for($i=0;$i<3;$i++){
        				$model->checkamount($starttime,0,50,$pur_code);
        			}
        		}else{
        			$model->checkamount($starttime,0,50,$pur_code);
        		}        		
        		break;
        	case 'updateStockinQty':
        		//从新系统入库表数据获取到货数、未到货、不良品信息，同步到采购明细
        		//$starttime = date('Y-m-d H:i:s',strtotime('-2 month')-24*60*60);//同步2个月前数据
        		$starttime = '2014-11-01 00:00:00';
        		$pur_code = $_REQUEST['pur_code'];
        		$model->updateStockinQty($starttime,0,500,$pur_code);
        		
        		break;
        	
        	case 'purchaseOrderSync':
        		$starttime = date('Y-m-d H:i:s',strtotime('-2 month')-24*60*60);//同步2个月前数据
//         		$starttime = date('Y-m-d H:i:s',strtotime('-10 days'));//同步一个月前数据
        		//$starttime = date('Y-m-d H:i:s',strtotime('-10 days'));//同步一个月前数据
        		$pur_code = $_REQUEST[''];
        		$model->purchaseOrder($starttime,0,500,$pur_code);
        		$model->purchaseOrderDetail($starttime,0,500,$pur_code);
        		//$model->getReceiptStatus($starttime,0,500,$pur_code);
        		break;
        	case 'purchaseOrderStockInStatus':
        		//更改采购单入库与到货状态
        		$starttime = date('Y-m-d H:i:s',strtotime('-2 month')-24*60*60);//同步一个月前数据
        		$pur_code = $_REQUEST['pur_code'];
        		$model->purchaseOrderStockInStatus($starttime,0,500,$pur_code);
        		break;
        	case 'purchaseOrder':
        		$starttime = date('Y-m-d H:i:s',strtotime('-2 month')-24*60*60);//同步一个月前数据
//         		$starttime = '2013-02-01 00:00:00';
        		$pur_code = $_REQUEST['pur_code'];
        		$model->purchaseOrder($starttime,0,500,$pur_code);
        		break;
        	case 'purchaseOrderStockIn':
        		$starttime = date('Y-m-d H:i:s',strtotime('-2 month')-24*60*60);//同步一个月前数据
        		$pur_code = $_REQUEST['pur_code'];
        		$model->purchaseOrderStockIn($starttime,0,500,$pur_code);
        		break;
        	case 'getReceiptStatus':
        		$starttime = date('Y-m-d H:i:s',strtotime('-10 month')-24*60*60);//同步一个月前数据
        		$pur_code = $_REQUEST['pur_code'];
        		$model->getReceiptStatus($pur_code);
        		break;
			case 'purchaseOrderPayInfo':
        		$starttime = date('Y-m-d H:i:s',strtotime('-6 month')-24*60*60);//同步一个月前数据
        		$pur_code = $_REQUEST['pur_code'];
        		$model->purchaseOrderPayInfo($starttime,0,500,$pur_code);
        		break;
        	case 'purchaseOrderDetail':
        		$starttime = date('Y-m-d H:i:s',strtotime('-10 month')-24*60*60);//同步一个月前数据
        		$starttime = '2013-02-01 00:00:00';
        		$pur_code = $_REQUEST['pur_code'];
        		$model->purchaseOrderDetail($starttime,0,500,$pur_code);
        		break;
        	case 'productProvider':
        		$threadArr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
				foreach($threadArr as $str){//"http://".$_SERVER['HTTP_HOST'].
					$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
				}
				//echo '<br>以sku末位数开线程';
        		break;
        	case 'skucombine':
        		$threadArr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
				foreach($threadArr as $str){//"http://".$_SERVER['HTTP_HOST'].
					$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
				}
				//echo '<br>以sku末位数开线程';
        		break;
        	case 'supply':
        		$sup_abbr = $_REQUEST['sup_abbr'];
        		$model->synchron_supply($starttime,500,0,$sup_abbr);
        		break;
        	case 'synchronAttribute':       //同步公共属性值 与sku对应关系
        		$threadArr = array('1','2','3','4','5','6','7','8','9','10','11','12','13');        		
        		foreach($threadArr as $str){  
        			$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
        		}     		  		
        		break;
        	case 'synchronSonAttribute':   // 非公共属性 与 非公共属性值对应的关系
        		$threadArr = array('color','size','style');
        		foreach($threadArr as $str){
        			$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);       	
        		}     
        		break;
        	case 'synchronAttributeBySonSku':   //同步非公共属性
        		$threadArr = array('color','size','style');
        		foreach($threadArr as $str){
        			$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
        		}
        		break;
			case 'purchasependingorder':
				//$starttime = date("Y-m-d 00:00:00",time());
				$starttime = date("2014-10-01 00:00:00");
				$model->purchasependingorder($starttime);
        		break;
        	case 'purchasepending':
				//同步数据前把原来数据状态都置为3
				$model->purchasependingStatus();
//         		$starttime = date("Y-m-d 00:00:00");//只取当天的采购需求
        		$starttime = date("2014-10-01 00:00:00");
        		//$starttime = '2014-06-20 00:00:00';//只取当天的采购需求  
        		$model->purchasepending($starttime);
        		break;
        	case 'pendingCron':
        		 $starttime = date("Y-m-d H:i:s",time()-2*60*60);//只取当天更改过的采购需求
        		//$starttime = '2014-06-20 00:00:00';//只取当天的采购需求
        		$model->pendingCron($starttime);
        		break;
        	
            case 'inquire':
				$threadArr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
				foreach($threadArr as $str){//"http://".$_SERVER['HTTP_HOST'].
					$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
				}
				//echo '<br>以sku末位数开线程';
                //$model->synchron_inquire($starttime);
                break;
        	case 'user':
        		$model->synchron_user($starttime);
        		break;
        	case 'package'://包装
        		$model->synchron_package();
        		break;
        	case 'packingmaterial'://包材
        		$model->synchron_packingmaterial();
        		break;        	
			case 'ebayonlinecatid':
        		$model->ebay_online_categoryid();
        		break;
        	case 'skumap':
        		$threadArr = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
				foreach($threadArr as $str){//"http://".$_SERVER['HTTP_HOST'].
					$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
				}
				//echo '<br>以stock_id首位数开线程';

        		break; 
        	case 'synchronPackage':
        			ini_set('memory_limit','2000M');
					set_time_limit('3600');
        			$model->synchron_package_pmaterial();
        		break;
        	
        	case 'synchronRecievePackUrse'://包装人员
        		$model->synchron_recieve_pack_urse('PACK');
        		break;
        	case 'synchronEbayUser': // 老到新 eaby 市场专员
        		$model->synchron_ebay_user('ebay_user');
        		break;
        	case 'oldCategoryAndSku': // 老系统 sku 与 分类ID
        		$model->synchron_category_sku();
        		break; 
        	case 'synchronMultiToOld'://老到老   删除属性重复
        		$threadArr = array('color','size','style');
        		foreach($threadArr as $str){
        			$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
        		}
        		break;
        	case 'synchronPlatfromProductUnline':
        		$threadArr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        		foreach($threadArr as $str){
        			$model->runThread("http://".$_SERVER['HTTP_HOST']."/synchronousdata/sync.php?action=".$action."&lastStr=".$str);
        		}
        		break;
        	default:
        		//$model->synchron_supply($starttime);
        }
		
	}

	
		
?>