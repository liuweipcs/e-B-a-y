<?php 
	set_time_limit(0);
	include_once("inc/base_mysql.php");
	include_once("inc/config.php");
	include_once("inc/warning.class.php");
	
	$model =  new warningModel();
	$model->synchron_main_warning();
		
?>

