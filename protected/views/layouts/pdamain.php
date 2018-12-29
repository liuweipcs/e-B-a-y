<?php 
$baseUrl = Yii::app()->request->baseUrl;
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/default/style.css', 'screen');
Yii::app()->clientScript->registerCssFile($baseUrl.'/css/pda.css', 'screen');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="overflow-x:hidden; margin-right:-15px;margin-bottom:-15px; overflow-y:hidden;width:200px;height:210px; ">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo Yii::t('app', Yii::app()->name);?></title>

</head>
<body style='width:200px;overflow-x:hidden; margin-right:-15px;margin-bottom:-15px; margin-top:0px;overflow-y:hidden;' scroll="no">
	 <?php //echo '<div class="page-header" style="text-align:center;font-size:30px;width:190px;">'?>
    	<?php //echo Yii::t('app', Yii::app()->name);?>	
   	<?php //echo '</div>'; ?>
   	<div>
   		<?php echo Yii::t('system','User').':'.Yii::app()->user->name;?>
   	</div>
   	<div class="page_nav">
   		<?php echo PdaClientModel::createPdaTopNavBar(); ?>
   	</div>
	<?php echo $this->renderPartial('//pdaclient/pdaheader'); ?>
	<?php echo $content; ?>
	<?php //echo $this->renderPartial('//pdaclient/pdafooetr'); ?>
	
</body>
</html>