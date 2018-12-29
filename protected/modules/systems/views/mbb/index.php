<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'press-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/mbb/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'press-grid',
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
            'value' => $data->id,
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ), 
    	array(
            'name'=> 'id',
            'value'=>'$data->id',
    		'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
        ),

		array(
			'name'=> 'email',
			'value'=>'$data->email',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'name'=> 'password',
			'value'=>'$data->password',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),

		array(
			'name'=> 'access_token',
			'value'=>'$data->access_token',
			'htmlOptions' 	=> array('style' => 'width:300px',),
		),
		array(
			'name'=> 'companyid',
			'value'=>'$data->companyid',
			'htmlOptions' 	=> array('style' => 'width:300px',),
		),
        array(
            'name'=> 'status',
            'value'=>'UebModel::model("AliexpressAccount")->getAliexpressAccountStatus($data->status)',
            'htmlOptions' 	=> array('style' => 'width:50px',),
        ),
        array(
            'name'=> 'last_update_time',
            'value'=> function($data){
                return date('Y-m-d H:i:s',$data->last_update_time);
            },
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),

        array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}&nbsp;{refresh} &nbsp;{companies}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/mbb/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
    			),
				'refresh' => array(
					'label' => Yii::t('system', '刷新'),
					'url' => 'Yii::app()->createUrl("/systems/mbb/refreshToken", array("id" => $data->id))',
					'title' => Yii::t('system', '刷新'),
					'options' => array('target' => 'dialog','class'=>'btnRefresh','width'=>400,'height'=>180),
				),
				'companies' => array(
					'label' => Yii::t('system', '获取companyid'),
					'url' => 'Yii::app()->createUrl("/systems/mbb/companies", array("id" => $data->id))',
					'title' => Yii::t('system', '获取companyid'),
					'options' => array('target' => 'dialog','class'=>'btnSelect','width'=>400,'height'=>180),
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

