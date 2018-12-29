<?php 
$baseUrl = Yii::app()->request->baseUrl;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo Yii::t('app', Yii::app()->name);?></title>
</head>
<body style="background:#F5F5F5;">
<!-- main Start-->
<div class="login-main">
    <div class="layout-login-big">
    	<div class="login-header" style="margin-left:30px;font-size:17px;margin-top:100px;">
            <?php  echo Yii::t('app', Yii::app()->name);?>	
        </div>
        <div class="layout-login" style="margin-left:0px;font-size:17px;margin-top:20px;">                
            <?php echo $content;?>
        </div>
        <div class="login-footer" style="margin-left:15px;margin-top:20px;">
            <?php  echo Yii::app()->params['copyrightInfo'];?>	
        </div>
    </div>
</div>
<!-- main End-->

</body>
</html>