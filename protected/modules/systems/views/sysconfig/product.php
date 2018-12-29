<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="panelBar">
    <ul class="toolBar">
        <li>
            <a class="add" href="javascript::void(0);" onclick="$.refreshConfigCache('product');" >
                <span><?php echo Yii::t('system', 'Refresh Cache')?></span>
            </a>
        </li>          
    </ul>
</div>
<h2 class="contentTitle"><?php echo Yii::t('system', 'Product display Settings');?></h2>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'menuForm',
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
            'class' => 'pageForm',         
         )
    ));
    ?>
    <div class="pageFormContent" layoutH="125">
    	<?php
        $i = 1;
        foreach($arributes as $key => $val) {
				$flag = (isset($model->config_value) && !empty($model->config_value) && in_array($key,$model->config_value)) ? true : false;
// 				$flag = false;
				echo '<div style="float:left;width:120px;margin-right:10px;">';
				echo CHtml::checkBox('SysConfig[attribute][]', $flag, array('value' =>$key,'id' =>'attribute_'.$key));
                echo $val;
                echo '&nbsp;&nbsp;';
                echo '</div>';
                if($i % 8 ==0) echo '<div style="clear:both;"></div>';
                $i++;
        }?>
        <?php echo $form->error($model, 'attribute'); ?>
    </div>
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">                                                                     
                        <button type="submit"><?php echo Yii::t('system', 'Save')?></button>                     
                    </div>
                </div>
            </li>
            <li>
                <div class="button"><div class="buttonContent"><button type="reset"><?php echo Yii::t('system', 'Reset')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>


