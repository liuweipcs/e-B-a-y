<?php 
	set_time_limit(0);
	include_once("inc/base_mysql.php");
	include_once("inc/config.php");
	echo 111111;exit;
	
	$model =  new orderModel();
	//echo strtotime('2014-01-01 00:00:00');exit;
	
	
	if($_REQUEST['start_syn_time'] != ''){
		$start_time = $_REQUEST['start_syn_time'];
		$end_time = date('Y-m-d H:i:s',$_REQUEST['start_syn_time']+24*3600);

    	$start_time = $model->get_time($start_time);

		$model->synchron_main_order($start_time,$end_time);
	}else{
        $time_synchron = strtotime(date('2014-10-08 23:59:59'))-24*3600*15; //同步前3个月的订单

		for($i=0;$i<=16;$i++){
			//echo "/synchronousdata/order.php?start_syn_time=".$time_synchron.'<br/>';
			$model->runThread("/synchronousdata/order.php?start_syn_time=".$time_synchron);
			$time_synchron += 24*3600;
		}
		echo '以天为单位同步数据';exit;
	}
		
?>

