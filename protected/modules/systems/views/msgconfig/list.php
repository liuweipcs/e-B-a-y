<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'msgtype-grid',
    'template' => THelper::getListTpl(),
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'toolBar' => array(
        array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/msgconfig/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'msgtype-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
    	array(
            'text'          => Yii::t('system', 'Add Message Type'),
            'url'           => '/systems/msgconfig/create',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'msgtype-grid',
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
            'name' => 'id',
            'value' => '$row+1',     
        ),       
       array(
           'name'   => 'name',
           'value'  => $model->name,
       ), 
       array(
           'name'   => 'code',
           'value'  => $model->code,
       ),
       array(
           'name'   => 'status',
           'value'  => 'VHelper::getStatusLable($data->status)',
       ),
		
    ),
    'pager' => array(),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
);
$config['columns'][] = array(
		'header' => Yii::t('system', 'Operation'),
		'class' => 'CButtonColumn',
		'headerHtmlOptions' => array('width' => '200', 'align' => 'center'),
		'template' => '{changType}',
		'buttons' => array(
				'changType' => array(
						'label' => Yii::t('system', 'Edit Message Type'),
						'url' => 'Yii::app()->createUrl("/systems/msgconfig/update", array("id" => $data->id))',
						'title' => Yii::t('system', 'Edit Message Type'),
						'options' => array('target' => 'dialog','class'=>'btnEdit'),
				),
		),
);

$this->widget('UGridView', $config);
?>
