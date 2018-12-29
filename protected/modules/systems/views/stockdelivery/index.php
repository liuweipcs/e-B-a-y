<?php 
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
    'id' => 'stockdelivery',
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'validateOnChange' => true,
        'validateOnType' => false,
        'afterValidate'=>'js:afterValidate',
    ),
    'action' => Yii::app()->createUrl($this->route),
    'htmlOptions' => array(
        'class' => 'pageForm ',
    )
));
?>
<style type="text/css">
.pageFormContent label{width:220px;}
.row label.labelTag {width:110px;line-height:24px; text-align:center;}
.con {float:left;width:800px;line-height:24px;line-height:24px;}
.con_tips {float:left;padding-left:6px;width:120px;line-height:22px;padding-top:2px;border-left:1px solid #70b3fa;}
.con_tag{text-align:center;line-height:24px;}
.btn_help  {width:20px;height:20px;}
.errorMessage  {width:auto;}
.row{border-bottom:1px solid #70b3fa;}
.row_1{background:#f5f5f5;}
.con{border-left:1px solid #70b3fa;}
.pageForm label{margin:0;}
.con label{padding-top:6px;}
</style>
<div class="pageFormContent" layoutH="90">
	<div class="bg14 pdtb2 dot">
            <strong>发货设置</strong>           
    </div>
    <div class="dot7 pd5" style="height:550px;">
        	<div class="row">
            <label class="labelTag">平台名称</label>
            <div class="con con_tag">
                                            分配策略
            </div>
           <div class="con_tips"> 发货期(天)</div>
           <div style="clear:both;"></div>
        </div>

        <?php for($i=0; $i<count($model); $i++) { ?>
    	<div class="row row_<?php echo $i % 2?>">
    	    <label  class="labelTag" for="Stockdelivery_stock_delivery_platform_code"><?php echo $model[$i]->stock_delivery_platform_code; ?></label>   
            <div class="con">
            <?php echo $form->radioButtonList($model[$i], 'stock_delivery_flag', $wayList, array('separator'=>'&nbsp','template'=>'{input}{label}', 'name'=>'flag' . '_' . $model[$i]->stock_delivery_platform_code,'style'=>'float:left;height:24px;line-height:24px;'));?>
            </div>
            <?php echo $form->error($model[$i], 'stock_delivery_platform_code'); ?>
            <div class="con_tips"><?php echo $form->textField($model[$i], 'stock_delivery_day', array('size' => 6,'inc_sub_size' => 1,'style'=>'text-align:center;', 'name'=>'day_' .$model[$i]->stock_delivery_platform_code)); ?></div>
        <div style="clear:both;"></div>
        </div>
        
	   <?php } ?>
		
	

		
		

    </div>
    
    
</div>
<div class="formBar">
    <ul>              
        <li>
            <div class="buttonActive">
                <div class="buttonContent">
                    <input type="hidden" value="1" name="stock_flag" />                        
                    <button type="submit"><?php echo Yii::t('system', 'Save')?></button>                     
                </div>
            </div>
        </li>
        <li>
            <div class="button"><div class="buttonContent"><button type="button" class="close" ><?php echo Yii::t('system', 'Cancel')?></button></div></div>
        </li>
    </ul>
</div>
<?php $this->endWidget(); ?>
<script type="text/javascript">
$(document).ready(function(){
	var spanMouseDown = function(obj){
	    return false;
	}

	var spanMouseUp = function(obj){
	    return false;
	}	
	
    $('a.t11').click(function(){ 
        var val = $(this).parent().parent().find('input').val();
        val++;
        $(this).parent().parent().find('input').val(val);
    });

    $('a.t12').click(function(){ 
        var val = $(this).parent().parent().find('input').val();
        val--;
        $(this).parent().parent().find('input').val(val);
    });
        
})
</script>



