<?php $baseUrl = Yii::app()->request->baseUrl; ?>
<form method="post" action="/pdaClient/login" style="width: 500px;margin:0 0;">
	账号：<input type="text" name="LoginForm[user_name]" /><br>
	密码：<input type="password" name="LoginForm[user_password]" />
	<input type="submit" value="提交" /><br/>
	
</form>