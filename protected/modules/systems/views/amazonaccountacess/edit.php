<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'amazonForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl('/systems/amazonaccountacess/edit', array('id' => $id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
            'onsubmit' => 'return dosomethingAcess(this)',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="66">
    	<div class="row">
    		<?php echo CHtml::label('账号'); ?>
    		<div><?= $account->account_name ?> <?= CHtml::hiddenField('account_id', $id) ?></div>
    	</div>
        <div class="row">
            <?php echo CHtml::label('已授权用户') ?>
            <div><?= CHtml::checkBoxList('', $access, $accessuser, array('container'=>'span', 'separator' => '&nbsp;&nbsp;', 'baseID'=>'assocId')) ?></div>
        </div>
        <div class="row">
            <?php echo CHtml::label('用户'); ?>
            <div><?= CHtml::textField('', '', array('style' => '','dataid'=>$id, 'onchange' => 'dosearch(this)'))?></div>
        </div>
        <div class="row">
            <?php echo CHtml::label('授权'); ?>
            <div style="border: 1px solid #9AA4BA;float:left;margin-top: 2px;-webkit-box-shadow: inset 0 0 5px #9AA4BA;width: 80%;">

            <div style="margin-bottom: 10px;">
                <button class="batchamzacnt" role="all" type="button">全选</button>
                <button class="batchamzacnt" role="opposite" type="button">反选</button>
                <button class="batchamzacnt" role="none" type="button">全不选</button>
            </div>

            <div id="accessItem">

            <?php foreach ($deplist as $val): ?>
                
                <fieldset style="margin: 5px 10px;">
                    <legend><?php echo $val['department_name'] ?></legend>

                    <?php echo CHtml::checkBoxList('access', $access, $data[$val['id']], array(
                        'container' => 'div',
                        'template'  => '<div style="width:80px;float:left;">{input}{label}</div>',
                        'separator' => ''
                    )) ?>

                </fieldset>

            <?php endforeach; ?>
            </div>

            <?php
             //    echo CHtml::checkBoxList('access', $access, UebModel::model('Amazon')->getGroupUser(array('amazon_user','autoleader','automember')), array(
            	// 'baseID' => 'accessItem',
             //    'container' => 'div',
             //    'template'  => '<div style="width:80px;float:left;">{input}{label}</div>',
             //    'separator' => ''));
            ?>
            </div>
        </div>
    </div>
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">
                        <button type="submit"><?php echo Yii::t('system', 'Save') ?></button>                     
                    </div>
                </div>
            </li>
            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel') ?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>

<script type="text/javascript">
    function dosomethingAcess(form) {
        var $form = $(form);

        $.ajax({
            type: form.method || 'POST',
            url: $form.attr("action"),
            data: $form.serializeArray(),
            dataType: "json",
            cache: false,
            success: function(json) {
                navTabAjaxDone(json);
            },
            error: DWZ.ajaxError
        });
        return false;
    }

    $('#assocId').on('click', 'input', function(event) {
        var v = $(this).val();
        var c = $(this).prop('checked');
        $('#accessItem').find('input[value="'+v+'"]').prop("checked",c);
    });

	$('.batchamzacnt').click(function(){
	   var $this = $(this);
	   switch($this.attr('role'))
	   {
	       case 'all':
	           $('#accessItem').find(':checkbox').attr('checked','true');
	           break
	       case 'opposite':
	           $('#accessItem').find(':checkbox').click();
	           break;
	       case 'none':
	           $('#accessItem').find(':checkbox').removeAttr('checked');
	   }
	});

    function dosearch(obj) {
        var name = $(obj).val();
        if (1) {
            $.ajax({
                type: 'POST',
                url: '/systems/amazonaccountacess/search',
                data: {name:name, id:$(obj).attr('dataid')},
                success: function (data) {
                    if (data)
                        $('#accessItem').html(data);
                }
            });
        }
    }
</script>