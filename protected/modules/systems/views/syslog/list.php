<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'syslog-grid',  
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'columns' => array(
       array(
            'name'=> 'id',
            'value'=>'$row+1',     
        ),       
        'user_name', 
        'user_remote_ip',
		array(
			'name' =>'user_login_location',
			'value'=>'VHelper::getUserLoginLocationLable($data->user_login_location)',
		),
        'user_login_num',
        array(
            'name' =>'user_login_status',
			'value'=>'VHelper::getLogStatusLable($data->user_login_status)',
		),
        'user_login_time'
    ),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
    'pager' => array(),
));
?>
