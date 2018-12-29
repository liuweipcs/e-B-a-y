<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'autocode-grid',
	'template' => THelper::getListTpl(),
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/autocode/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'autocode-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
		array(
			'text'          => Yii::t('system', 'Add Code Type'),
			'url'           => '/systems/autocode/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'autocode-grid',
				'postType'  => '',
				'callback'  => '',
			)
		),
	),
	
    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',          
            'selectableRows' => 2,
            'value' => $model->id,
        ), 
    	array(
            'name'=> 'id',
            'value'=>'$data->id',     
        ),
		array(
            'name'=> 'code_type',
            'value'=>'$data->code_type',     
        ),  
		array(
            'name'=> 'code_prefix',
            'value'=>'$data->code_prefix',     
        ),   
		array(
            'name'=> 'code_suffix',
            'value'=>'$data->code_suffix',     
        ),    
		array(
            'name'=> 'code_format',
            'value'=>'$data->code_format',     
        ),    
		array(
            'name'=> 'code_min_num',
            'value'=>'$data->code_min_num',     
        ),     
		array(
            'name'=> 'code_max_num',
            'value'=>'$data->code_max_num',     
        ),     
		array(
            'name'=> 'code_fix_length',
            'value'=>'$data->code_fix_length',     
        ),  
        array(
            'header' => Yii::t('system', 'Increate Type'),
			'value'=> 'VHelper::getIncreateTypeLabel($data->code_increate_type)',
		),
        array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '200', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit Code Type'),
    				'url' => 'Yii::app()->createUrl("/systems/autocode/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit Code Type'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit'),
    			),
    		),
    	)
    ),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
    'pager' => array(),
);

$this->widget('UGridView', $config);
?>
