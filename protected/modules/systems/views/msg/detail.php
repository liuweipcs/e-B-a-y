<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
	<div class="pageFormContent" layoutH="56">
		<div class="msg_head" style="font-size:14px;font-weight:bold;">
			<?php echo $info->create_user_id==0 ? Yii::t('system', 'System Message') : MHelper::getUsername($info->create_user_id);?>
			<?php echo ':'.$info->msg_title;?>
		</div>
		<div class="msg_body" style="word-wrap:break-word;margin-top:10px;">
			<?php echo $info->msg_content; ?>
		</div>
		<div class="msg_option" style="margin-top:20px;float:right;">
			<?php echo CHtml::link(Yii::t('system', 'Mark Message'), '/systems/msg/flag/ids/'.$model->id);?>&nbsp;&nbsp;&nbsp;
			<?php echo CHtml::link(Yii::t('system', 'Delete Message'), '/systems/msg/delete/ids/'.$model->id);?>&nbsp;&nbsp;&nbsp;
			<?php echo CHtml::link(Yii::t('system', 'Reply Message'), '/systems/msg/reply/id/'.$model->id);?>
		</div>
		<div style="clear:both"></div>
	</div>
</div>