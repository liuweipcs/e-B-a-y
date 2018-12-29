<?php 
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'catForm',
        'enableAjaxValidation' => false,  
        'enableClientValidation' => true,
    	'focus' => array($model, ''),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => array(Yii::app()->createUrl($this->route)), 
        'htmlOptions' => array(        
            'class' => 'pageForm',   
			'onSubmit' => "return divSubmitRefresh(this)"      
         )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56"> 
    	<div class="row">
    		<?php echo CHtml::label(Yii::t('system', 'Reciever'), 'to_user')?>
    		<?php echo CHtml::hiddenField('to_user_id', '', array(
    				'readonly' => true,
    				'rel' => "{type:'input', callback:'toUserIdCallback'}"
    		));?>
	        <div class="chosen-container chosen-container-multi" style="width:350px;float:left;" title="" id="to_user_id_chosen">
				<ul class="chosen-choices" id="toUsers">
					<li style="margin: 3px 0 3px 5px;padding: 3px 20px 3px 5px;"><span>&nbsp;</span><a data-option-array-index="0"></a></li>
				</ul>
			</div>
	        <?php 
	        	echo CHtml::link(Yii::t('system', 'Select'), '/users/users/list/target/dialog/on/userId/', array( 
						'class' 		=> 'btnLook', 
						'lookupGroup' 	=> 'users', 
						'style' 		=> 'float:left;',
						'mask'			=> 1,
						'rel'			=> 'user_sel',
				));
			?>
    	</div><br/>  
    	<div class="row">
    		<?php echo CHtml::label(Yii::t('system', 'Message Content'), 'msg_content')?>
    		<?php echo CHtml::textArea('msg_content','',array('cols' => '55','rows'=> '8'));?>
    	</div>         
    </div>
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">                         
                        <button type="submit"><?php echo Yii::t('system', 'Send')?></button> 
                        <input type="hidden" value="<?php echo Yii::t('system', 'Reply').':'.$info->msg_title;?>" id="send_title" />
                    </div>
                </div>
            </li>
            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>
<script type="text/javascript">
function toUserIdCallback(data) {      
    var data = eval(data);
	var name = '';
	var id = '';
	var str = [];
	var string = '';
	$.each(data,function(i,item){
		name += item.name + ',';
		id += item.id +',';
		str.push(item.name);
		string += '<li class="search-choice"><span>'+item.name+'</span>';
    	string += '<a onclick=delCode(this); class="search-choice-close" data-option-array-index="'+item.name+'" userId="'+item.id+'"></a></li>';
	});
	id = id.substring(0,id.length-1);

    $('#to_user_id').val(id);
	string += '<li style="margin: 3px 0 3px 5px;padding: 3px 20px 3px 5px;"><span>&nbsp;</span><a data-option-array-index="0"></a></li>';
	$('#toUsers').html(string);
}
function delCode(obj){
	var curcode = $(obj).attr('userId');
	var allcode = $('#to_user_id').val();
	var newcode = '';
	if(allcode.indexOf(','+curcode) >0){
		newcode = allcode.replace(","+curcode,"");
	}else{
		if(curcode!=allcode){
			newcode = allcode.replace(curcode+",","");
		}
	}
	$('#to_user_id').val(newcode);
	$(obj).parent().remove();
}
function divSubmitRefresh(form) {
    var $form = $(form);
    var resources = [];
    $.ajax({
        type: form.method || 'POST',
        url: $form.attr("action"),
        data: {
        	msg_content:$('#msg_content').val(),
        	reciever:$('#to_user_id').val(),
        	title:$('#send_title').val()
        },
        dataType: "json",
        cache: false,
        success: function(json) {
            dialogAjaxDone(json);
        },
        error: DWZ.ajaxError
    });
    return false;
}
</script>
<?php 
if( isset($info->create_user_id) ){
	if( $info->create_user_id > 1 ){
		$recieverData[0]['id'] = $info->create_user_id;
		$userInfo = User::model()->getUserNameById($recieverData[0]['id']);
		$recieverData[0]['name'] = $userInfo['user_full_name'];
		echo '<script>toUserIdCallback('.json_encode($recieverData).')</script>';
	}
}
?>