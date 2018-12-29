<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'bank-grid', 
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/bank/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'bank-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/bank/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'bank-grid',
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
    		'name'=> 'bank_name',
    		'value'=>'$data->bank_name',
    		'htmlOptions' 	=> array('style' => 'width:200px',),
    	),
    	array(
    		'name'=> 'payment_platform_id',
    		'value'=>'UebModel::model("PaymentPlatform")->getPaymentPlatformList($data->payment_platform_id)',
    		'htmlOptions' 	=> array('style' => 'width:120px',),
    	),
    	array(
    		'name'=> 'bank_province_id',
    		'value'=>'UebModel::model("Region")->getRegionNameById($data->bank_province_id)',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
    	array(
    		'name'=> 'bank_city_id',
    		'value'=>'UebModel::model("Region")->getRegionNameById($data->bank_city_id)',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),	
    	array(
    		'name'=> 'bank_area_id',
    		'value'=>'UebModel::model("Region")->getRegionNameById($data->bank_area_id)',
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
    				'url' => 'Yii::app()->createUrl("/systems/bank/update", array("id" => $data->id))',
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

