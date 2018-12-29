<?php

Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
		'id' => 'dashBoardForm',
		'enableAjaxValidation' => false,
		'enableClientValidation' => true,
		'focus' => array($model, 'task_name'),
		'clientOptions' => array(
				'validateOnSubmit' => true,
				'validateOnChange' => true,
				'validateOnType' => false,
				'afterValidate'=>'js:afterValidate',
		),
		'action' => Yii::app()->createUrl($this->route.'/id/'.$model->id),
		'htmlOptions' => array(
				'class' => 'pageForm',
		)
));
$lang_code = 'quality';
?>
<style>
<!--
label.alt{width: 128px; opacity: 1;height:20px;margin:0;line-height:22px;}
-->
</style>
<div class="pageContent">   
    <div class="tabs"> 
	    <div class="tabsHeader"> 
	 		<div class="tabsHeaderContent"> 
	 			<ul> 
	 				<li class="selected"><a href="#"><span><?php echo Yii::t('logistics', 'Basic Information')?></span></a></li>
	 			</ul> 
	 		</div> 
	 	</div>

		<div   class="pageFormContent" layouth="56" style="height:100%; overflow: auto;">
			<style>
				table,table tr th, table tr td { border:1px solid #aaa;line-height: 25px;}
				table {border-collapse: collapse;border-collapse: collapse;line-height: 25px;  }
				font{line-height: 25px;}
				.h1{
					color: red;font-size: 12px;border: 1px solid #ccc;margin: 10px 0px;padding: 10px 0px;

				}
			</style>

			<table width="100%" >
				<tr>
					<td>
						任务名称:
						<?php echo $model->task_name; ?>

					</td>
					<td>
						任务创建人:
						<?php echo Tasks::getUsers($model->task_create_founder); ?>

					</td>
				</tr>
				<tr>
					<td>
						任务内容:
						<?php echo $model->task_content; ?>

					</td>
					<td>
						任务创建时间:
						<?php echo date('Y-m-d H:i:s',$model->task_create_time); ?>
						<?php echo $form->error($model, 'task_create_time'); ?>
					</td>

				</tr>
				<tr>
					<td>

					</td>
					<td>
						要求完成时间:
						<?php echo date('Y-m-d H:i:s',$model->task_claim_time); ?>
						<?php echo $form->error($model, 'task_claim_time'); ?>
					</td>

				</tr>
				<tr>
					<td>
						附件下载:<a href="<?=$model->annex?>" style="color: red" target="_blank">点击下载</a>
					</td>
					<td>
						开始时间:
						<?php echo date('Y-m-d H:i:s',$model->task_start_time); ?>
						<?php echo $form->error($model, 'task_start_time'); ?>
					</td>

				</tr>
				<tr>
					<td>

					</td>
					<td>
						承诺完成时间:
						<?php echo date('Y-m-d H:i:s',$model->task_promise_time); ?>
						<?php echo $form->error($model, 'task_promise_time'); ?>
					</td>

				</tr>

			</table>

			<h1 class="h1">任务指派给</h1>
			<table  width="100%"  >
				<tr>
					<th>责任人</th>
					<th>积分</th>
					<th>备注</th>
				</tr>
				<?php
				$as  =UebModel::model('TasksAssign') ->queryPairs('*',"task_id = ".$_GET['id']);

				//$b=0;
				foreach($as as $k=>$v){?>
					<tr>
						<td>
							<?=Tasks::getUsers($v['task_assign_id'])?>
						</td>
						<td>
							<?=$v['integral']?>

						</td>
						<td>
							<?=$v['task_remarks']?>
						</td>

					</tr>
					<?php
					//$b++;
				}?>
				<tr>
					<td>
						<?php echo $form->labelEx($model, 'schedule'); ?>
						<?php echo "<progress value='{$model->schedule}' max='100'></progress>".$model->schedule.'%'; ?>
						<?php echo $form->error($model, 'schedule'); ?>
					</td>
					<td colspan="2" >
						<?php echo $form->labelEx($model, 'task_status'); ?>
						<?php echo VHelper::getTaskStatusColor($model->task_status); ?>
						<?php echo $form->error($model, 'task_status'); ?>
					</td>

				</tr>
				</table>
				<h1 class="h1">操作日志</h1>
			<table  width="100%"  >
				<?php if($models){
					foreach($models as $v) {
						?>
				<tr>
					<td><?php echo Tasks::getUsers($v['user_id']) ?></td>
					<td><?=$v['content']?></td>
					<td><?=date('Y-m-d H:i:s',$v['log_time'])?></td>
				</tr>
				<?php }}?>
			</table>
			<h1 class="h1">验收结果</h1>
			<table  width="100%"  >
				<?php if($modela){
					foreach($modela as $v) {
						?>
						<tr>
							<td><?php echo Tasks::getUsers($v['acceptance_id']) ?></td>
							<td><?=$v['acceptance_content']?></td>
							<td><?=date('Y-m-d H:i:s',$v['acceptance_time'])?></td>
						</tr>
					<?php }}?>
			</table>

		</div>

    </div>
   <!-- <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php /*echo Yii::t('system', 'Save')*/?></button>
                    </div>
                </div>
            </li>
            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php /*echo Yii::t('system', 'Cancel')*/?></button></div></div>
            </li>
        </ul>
    </div>-->
    <?php $this->endWidget(); ?>
</div>

<script type="text/javascript">
	var keditor = null;
	var keditor1 = null;
	$(function (){
		var id_desc = "<?php echo $lang_code.'_desc';?>";
		var id_inc = "<?php echo $lang_code.'_inc';?>";
		keditor = kedit(id_desc);
		keditor1 = kedit(id_inc);
		//checkLens('<?php echo $lang_code.'_title';?>','<?php echo $lang_code.'_count';?>',95);
	});
	function kedit(keid){
		var keditor =  KindEditor.create('#' + keid,{
			allowFileManager: true,
			width: '80%',
			afterCreate : function(){
				this.sync();
			},
			afterBlur:function(){
				this.sync();
			}
		});
		return keditor;
	}
</script>