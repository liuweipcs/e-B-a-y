<?php $baseUrl = Yii::app()->request->baseUrl; ?>
<div class="loginForm form">   
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'login-form',
        'enableAjaxValidation' => true,
    ));
    ?>
    <span class="a1"></span>  
    <?php echo $form->textField($model, 'user_name', array( 'class' => 'input_login')); ?>
    <?php echo $form->error($model, 'user_name',array( 'class' => 'login-ts b1')); ?>  
    <span class="a2"></span>
    <?php echo $form->passwordField($model, 'user_password', array('class' => 'input_password')); ?>
    <?php echo $form->error($model, 'user_password'); ?>
    
    <?php if($model->useCaptcha && CCaptcha::checkRequirements()):?>
    <span class="a3"></span>
    <?php  echo $form->textField($model,'verifyCode', array('class' => 'input_yz')); ?>
    <?php
        //echo '<p class="hint">'.Yii::t('app','Please enter the letters in the image above.').'</p>';
        echo $form->error($model, 'verifyCode');  
    ?>  
    <span class="a4"></span>
    <div class="login-img-yz row">
        <div class="row" style="margin-top:5px;">
            <?php   
            echo '<div>';
            $this->widget('CCaptcha',array(
                'clickableImage'=>true,
                'showRefreshButton'=>false,
                'imageOptions'=>array(
                    'style'=>'display:block;cursor:pointer;',
                    'title'=>Yii::t('app','Click to get a new image')
                )
            )); echo '</div>';                            
            ?>
        </div>
    </div>
    <?php endif;?>
    <span class="a5"></span>
   <input class="btn_login" type="submit" name="yt0" value=""  id="submit11">
    <?php $this->endWidget(); ?>
    <span  id="privateLogin" style="width:200px;height:60px;line-height:60px;text-align:center;display:inline-block;cursor:pointer;">内网登录</span>
</div>
<script>
$(function(){
	$('#submit11').click(function(){
		var user_name = $('#LoginForm_user_name').val();
			  $.post("/site/check",{'user_name':user_name},function(result){
				  var obj = eval('('+result+')');
				 if(obj.status == 1){
						/* alertMsg.info(obj.msg); */
						alert(obj.msg);
						return false;
					 }else{
						$('#login-form').submit();
					}
			  });
	});
/*	
	//$('#privateLogin').click(function(){
	$(window).load(function(){
		  var url_list = new Array; //var redirect_url = '';
		  url_list[0] = 'http://192.168.10.17/index.php'; //深圳
		  url_list[1] = 'http://192.168.1.15:8089/index.php'; //东莞
		  url_list[2] = 'http://192.168.5.212/index.php'; //武汉
		  url_list[3] = 'http://192.168.0.80/index.php'; //成都

		    for(i=0;i<url_list.length;i++){
		      if(url_list[i] != ''){
		        $.ajax(  {
		            type:'get',
		            url : url_list[i]+'?check=check&key='+i,
		            dataType : 'jsonp',
		            jsonp:"jsoncallback",
		            success  : function(data) {
		              if(data.check=='check'){
		                window.location.href = url_list[data.key];
		              }
		            },
		            error : function() { }
		          }
		        );
		      }
		    }
	});
}); */
</script>
