<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'walmartform-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/walmart/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'walmartform-grid',
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
            'value' => $model->id,
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ), 
    	array(
            'name'=> 'id',
            'value'=>'$row+1',
    		'htmlOptions' 	=> array('style' => 'width:30px;height:32px;',),
        ),
    	array(
    		'name'=> 'account_name',
    		'value'=>'$data->account_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
    	array(
    		'name'=> 'short_name',
    		'value'=>'$data->short_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
		array(
			'name'=> 'consumer_id',
			'value'=>'$data->consumer_id',
			'htmlOptions' 	=> array('style' => 'width:240px',),
		),
		array(
			'name'=> 'private_key',
			'value'=>'$data->private_key',
			'htmlOptions' 	=> array('style' => 'width:300px',),
		),
		array(
			'name'=> 'channel_type',
			'value'=>'$data->channel_type',
			'htmlOptions' 	=> array('style' => 'width:240px',),
		),
    	array(
    		'name'=> 'ship_node',
    		'value'=>'$data->ship_node',
    		'htmlOptions' 	=> array('style' => 'width:120px',),
    	),
		array(
			'name'=> 'group_id',
			'value'=>'UebModel::model("WalmartStoreGroup")->getQueryOne($data->group_id)',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
    	array(
    		'name'=> 'status',
    		'value'=>'UebModel::model("Walmart")->getWalmartAccountStatus($data->status)',
    		'htmlOptions' 	=> array('style' => 'width:50px',),
    	),	
    		
    	array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}&nbsp;{delete}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/walmart/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
    			),
				'delete' => array(
						'url'       => 'Yii::app()->createUrl("/systems/walmart/delete", array("id" => $data->id))',
						'label'     => '删除',
						'options'   => array(
								'target'    => 'dialog',
								'class'     => 'btnDelete',
								'width'     => '400',
								'height'    => '180',
						),
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

