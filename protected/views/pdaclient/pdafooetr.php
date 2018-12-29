<div style="text-align:left;width:300px;">
		<?php echo CHtml::link('Return Home Page', Yii::app()->baseUrl.PdaClientModel::createPdaClientLink('/pdaclient/index'));?>
	</div>
	<div style="text-align:left;width:300px;">
		<?php echo CHtml::link(Yii::t('system','Logout'), Yii::app()->baseUrl.PdaClientModel::createPdaClientLink('/pdaclient/login'));?>
	</div>
	<div class="page-footer" style="text-align:center;width:300px;">
    	<?php echo Yii::app()->params['copyrightInfo'];?>
   	</div>