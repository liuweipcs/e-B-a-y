<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'mallform-grid', 
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/mall/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'mallform-grid',
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
            'value' => '$model->id',
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ), 
    	array(
            'name'=> 'id',
            'value'=>'$row+1',
    		'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
        ),
    	array(
    		'name'=> 'user_name',
    		'value'=>'$data->user_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
    	array(
    		'name'=> 'short_name',
    		'value'=>'$data->short_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
//         array(
//             'name'=> 'short_name',
//             'value'=>'$data->short_name',
//             'htmlOptions' 	=> array('style' => 'width:100px',),
//         ),
		array(
			'name'=> 'access_token',
			'value'=>'$data->access_token',
			'htmlOptions' 	=> array('style' => 'width:280px',),
		),
    	array(
    		'name'=> 'refresh_token',
    		'value'=>'$data->refresh_token',
    		'htmlOptions' 	=> array('style' => 'width:280px',),
    	),
		array(
			'name'=> 'client_id',
			'value'=>'$data->client_id',
			'htmlOptions' 	=> array('style' => 'width:180px',),
		),
		array(
			'name'=> 'client_secret',
			'value'=>'$data->client_secret',
			'htmlOptions' 	=> array('style' => 'width:180px',),
		),
//         array(
//             'name'=> 'group_id',
//             'value'=>'UebModel::model("WishStoreGroup")->getQueryOne($data->group_id)',
//             'htmlOptions' 	=> array('style' => 'width:100px'),
//         ),
    	array(
    		'name'=> 'status',
    		'value'=>'UebModel::model("MallAccount")->getMallAccountStatus($data->status)',
    		'htmlOptions' 	=> array('style' => 'width:50px',),
    	),	
    		
    	array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80px', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    			'width' =>'80'
    		),
    		'template' => '{changCode}&nbsp;{refreshMallToken}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/mall/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
    			),
				'refreshMallToken' => array(
    				'label' => Yii::t('system', 'Refresh'),
    				'url' => 'Yii::app()->createUrl("/systems/mall/refreshtoken", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Refresh'),
					'options' => array('target' => 'dialog','class'=>'btnRefresh','width'=>400,'height'=>180),
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

