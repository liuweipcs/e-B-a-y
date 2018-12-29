<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'rule-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', '创建规则'),
			'url'           => '/systems/attendance/createrule',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'rule-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>800,
				'height'	=>450,
			)
		),
	),
    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',          
            'selectableRows' => 2,
            'value' => $data->rule_id,
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ), 
    	array(
            'name'=> 'rule_id',
            'value'=>'$data->rule_id',
    		'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
        ),

        array(
            'name'=> 'type',
            'value'=>function($data)
			{
				return UebModel::model('AttendanceRule')->getAttendanceRule($data->type);
			},
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),
    	array(
    		'name'=> 'check_time',
    		'value'=>'$data->check_time',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
		array(
			'name'=> 'checkout_time',
			'value'=>'$data->checkout_time',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),

		array(
			'name'=> 'status',
			'value'=>function($data)
			{
				return UebModel::model('AttendanceRule')->getAttendanceRuleStatus($data->status);
			},
			'htmlOptions' 	=> array('style' => 'width:500px',),
		),
		array(
			'name'=> 'rule_content',
			'value'=>'$data->rule_content',
			'htmlOptions' 	=> array('style' => 'width:500px',),
		),




        array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/attendance/editrule", array("id" => $data->rule_id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
    			),


    		),
    	)
    ),
    'tableOptions' => array(
        'layoutH' => 75,
    ),
    'pager' => array(),
));

?>

