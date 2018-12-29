<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'paymentplatform-grid', 
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/paymentplatform/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'paymentplatform-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/paymentplatform/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'paymentplatform-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>600,
				'height'	=>400,
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
    		'htmlOptions' 	=> array('style' => 'width:50px',),
        ),
    	array(
    		'name'=> 'payment_platform_code',
    		'value'=>'$data->payment_platform_code',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
    	array(
    		'name'=> 'payment_platform_name',
    		'value'=>'$data->payment_platform_name',
    		'htmlOptions' 	=> array('style' => 'width:120px',),
    	),
    	array(
    		'name'=> 'sort',
    		'value'=>'$data->sort',
    		'htmlOptions' 	=> array('style' => 'width:40px',),
    	),	
    	array(
    		'name'=> 'platform_type',
    		'value'=>'PaymentPlatform::model()->getPlatformType($data->platform_type)',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),	
    	array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '50', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/paymentplatform/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit'),
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

