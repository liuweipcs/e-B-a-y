<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/8
 * Time: 18:14
 */
?>

<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false;?>
<div class="pageContent">
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'shopeeexpressfee_grid',
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
        <?php if (isset($model->id) ):?>
            <div class="row">
                <?php echo $form->labelEx($model, 'weight'); ?>
                <?php echo $form->textfield($model, 'weight'); ?>
                <?php echo $form->error($model, 'weight'); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'price'); ?>
                <?php echo $form->textfield($model, 'price'); ?>
                <?php echo $form->error($model, 'price'); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'country_code'); ?>
                <?php echo $form->textfield($model, 'country_code'); ?>
                <?php echo $form->error($model, 'country_code'); ?>
            </div>
        <?php endif?>

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
