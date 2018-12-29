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
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">  
		<div class="row">
            <?php echo $form->labelEx($model, 'sort'); ?>
            <?php echo $form->textField($model, 'sort', array('style'=>'width:60px;height:20px;')); ?>
            <?php echo $form->error($model, 'sort'); ?>
			<div style="color:#ACACAC;padding-left:130px;height:28px;">注：排序必须是使用1-9999之间的任意数字，数字越大排在越前面</div>
        </div>
        <div class="row">
        	<?php echo $form->labelEx($model, 'account_name'); ?>                 
            <?php echo $form->textField($model, 'account_name', array('style'=>'width:400px;height:20px;')); ?>
            <?php echo $form->error($model, 'account_name'); ?>    
        </div>  
        <div class="row">
            <?php echo $form->labelEx($model, 'short_name'); ?>
            <?php echo $form->textField($model, 'short_name', array('style'=>'width:400px;height:20px;')); ?>
            <?php echo $form->error($model, 'short_name'); ?>
        </div>
		<div class="row" style="color:#ACACAC;padding-left:130px;height:28px;">注：店铺别称为展示在前端的名称，不直接显示真实账号，保护账号隐私！</div>
		<div class="row">
            <?php echo $form->labelEx($model, 'site'); ?>
            <?php echo $form->dropDownList($model, 'site', UebModel::model('Amazon')->getAmazonSiteLable(),array('empty'=>Yii::t('system','Please Select'),'onchange'=>'getAmazonSiteInfo(this.value)')); ?>
            <?php echo $form->error($model, 'site'); ?>
        </div>		
        <div class="row">
            <?php echo $form->labelEx($model, 'merchant_id'); ?>
            <?php echo $form->textField($model, 'merchant_id', array('style'=>'width:400px;height:20px;')); ?>
            <?php echo $form->error($model, 'merchant_id'); ?>
        </div>    
        <div class="row">
            <?php echo $form->labelEx($model, 'market_place_id'); ?>
            <?php echo $form->textField($model, 'market_place_id', array('style'=>'width:400px;height:20px;')); ?>
            <?php echo $form->error($model, 'market_place_id'); ?>
        </div>    
        <div class="row">
            <?php echo $form->labelEx($model, 'aws_access_key_id'); ?>
            <?php echo $form->textField($model, 'aws_access_key_id', array('style'=>'width:400px;height:20px;')); ?>
            <?php echo $form->error($model, 'aws_access_key_id'); ?>
        </div>     
        <div class="row">
            <?php echo $form->labelEx($model, 'secret_key'); ?>
            <?php echo $form->textField($model, 'secret_key', array('style'=>'width:400px;height:20px;')); ?>
            <?php echo $form->error($model, 'secret_key'); ?>
        </div>     
        <div class="row">
            <?php echo $form->labelEx($model, 'service_url'); ?>
            <?php echo $form->textField($model, 'service_url', array('style'=>'width:400px;height:20px;')); ?>
            <?php echo $form->error($model, 'service_url'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'amzsite_email'); ?>
            <?php echo $form->textField($model, 'amzsite_email', array('style'=>'width:400px;height:20px;')); ?>
            <?php echo $form->error($model, 'amzsite_email'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('Amazon')->getAmazonAccountStatus()); ?>
            <?php echo $form->error($model, 'status'); ?>
        </div>
		<div class="row">
            <?php echo $form->labelEx($model, 'group_id'); ?>
            <?php echo $form->dropDownList($model, 'group_id', UebModel::model('Amazon')->getAmazonAccountGroup()); ?>
            <?php echo $form->error($model, 'group_id'); ?>
        </div>
        <!--
        <div class="row">
            <?php echo CHtml::label('授权'); ?>
            <div style="border: 1px solid #9AA4BA;float:left;margin-top: 2px;-webkit-box-shadow: inset 0 0 5px #9AA4BA;width:200px;overflow: auto;max-height: 295px">
                <button class="batchamzaccount" role="all" type="button">全选</button>
                <button class="batchamzaccount" role="opposite" type="button">反选</button>
                <button class="batchamzaccount" role="none" type="button">全不选</button>
                    <br/>

            <?php echo CHtml::checkBoxList('access', $access, UebModel::model('Amazon')->getGroupUser(array('amazon_user','autoleader','automember')), array(
                'container' => 'div',
                'template'  => '{input}{label}',
                'separator' => '<br/>')); ?>
            </div>
        </div>
        -->
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
function getAmazonSiteInfo(val){
	if(val == 'us'){
		$("#Amazon_market_place_id").val("ATVPDKIKX0DER");
		$("#Amazon_service_url").val("https://mws.amazonservices.com");
	}else if(val == 'uk'){
		$("#Amazon_market_place_id").val("A1F83G8C2ARO7P");
		$("#Amazon_service_url").val("https://mws.amazonservices.co.uk");
	}else if(val == 'de'){
		$("#Amazon_market_place_id").val("A1PA6795UKMFR9");
		$("#Amazon_service_url").val("https://mws.amazonservices.de");
	}else if(val == 'ca'){
		$("#Amazon_market_place_id").val("A2EUQ1WTGCTBG2");
		$("#Amazon_service_url").val("https://mws.amazonservices.ca");
	}else if(val == 'mx'){
		$("#Amazon_market_place_id").val("A1AM78C64UM0Y8");
		$("#Amazon_service_url").val("https://mws.amazonservices.com.mx");
	}else if(val == 'sp'){
		$("#Amazon_market_place_id").val("A1RKKUPIHCS9HS");
		$("#Amazon_service_url").val("https://mws.amazonservices.es");
	}else if(val == 'fr'){
		$("#Amazon_market_place_id").val("A13V1IB3VIYZZH");
		$("#Amazon_service_url").val("https://mws.amazonservices.fr");
	}else if(val == 'it'){
		$("#Amazon_market_place_id").val("APJ6JRA9NG5V4");
		$("#Amazon_service_url").val("https://mws.amazonservices.it");
	}else if(val == 'jp'){
		$("#Amazon_market_place_id").val("A1VC38T7YXB528");
		$("#Amazon_service_url").val("https://mws.amazonservices.jp");
	}else if(val == 'au'){
        $('#Amazon_market_place_id').val("A39IBJ37TRP1C6");
        $("#Amazon_service_url").val("https://mws.amazonservices.com.au");
    }else{
		$("#Amazon_market_place_id").val("");
		$("#Amazon_service_url").val("");
	}
}

$('.batchamzaccount').click(function(){
   var $this = $(this);
   switch($this.attr('role'))
   {
       case 'all':
           $('#access').find(':checkbox').attr('checked','true');
           break
       case 'opposite':
           $('#access').find(':checkbox').click();
           break;
       case 'none':
           $('#access').find(':checkbox').removeAttr('checked');
   }
});
</script>

