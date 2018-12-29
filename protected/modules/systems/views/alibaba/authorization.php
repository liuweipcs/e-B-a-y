<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
		'id' => 'RefreshForm',
		'enableAjaxValidation' => false,
		'enableClientValidation' => true,
		'focus' => array($model, 'pay'),
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
<div class="pageContent">   
    <div class="tabs"> 
	 	<div class="tabsContent" style="height:300px;">
 			<div class="pageFormContent" layoutH="180" style="border:1px solid #B8D0D6">
				<div class="row" style="line-height: 20px;">

					用户授权成功后应用可以获得两个OAuth2令牌：RefreshToken和AccessToken，应用需要妥善保管RefreshToken，并在每次API调用时传递AccessToken。
				</div>
				<div class="row" >
					<font color="red" style="line-height: 20px;">1.第一次授权</font>
					<button type="button" onclick="getAccessToken()"><?php echo Yii::t('system', '一键授权') ?></button>
				</div>
				<div class="row">
					<font color="red" style="line-height: 20px;">2.如果已经有可用的refreshToken，那么可以直接用refreshToken换取accessToken </font>
					<input id="refreshToken" value="">
					<button type="button" onclick="getreAccessToken()"><?php echo Yii::t('system', '根据RefreshToken获取AccessToken') ?></button>
				</div>

	 	</div>
    </div>
</div>

    <div class="formBar">
        <ul>  

            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>

<script>
	function getAccessToken()
	{

		var app_key      = '<?=$model->app_key?>';
		var site   		 = 'alibaba';

		var redirect_uri = '<?=$model->redirect_uri?>';

		var signature	 = '<?=$model->Signature?>';
		var url 		 = 'http://gw.open.1688.com/auth/authorize.htm?';

		var url1 = url + "client_id=" + app_key + "&redirect_uri=" + redirect_uri+"&site=china&_aop_signature=" + signature;
		window.open(url1,'_blank');

	}
	function getreAccessToken()
	{
		var refresh = $("#refreshToken").val();
		var id = '<?=$model->id?>';
		if (refresh == '')
		{
			alert("<?php echo Yii::t('system', 'Token不能为空');?>");
			return false;
		} else {
			$.post("/systems/alibaba/getreaccesstoken",{ 'refresh': refresh, 'id' :id}, function(data) {
			 data = eval("("+data+")");
				if(data.statusCode == 300){
					alert("获取Access Token失败！请检查是否信息缺失！");
				}else{
					alert("重新获取Access Token成功！关闭弹出框即可使用。");
				}
		});
		}
	}
</script>