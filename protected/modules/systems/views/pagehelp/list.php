<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'pagehelp-grid',
    //'template' => THelper::getListTpl(),
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'toolBar' => array(
        array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/pagehelp/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'pagehelp-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
    	array(
            'text'          => Yii::t('system', 'Add'),
            'url'           => '/systems/pagehelp/create',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'pagehelp-grid',
                'postType'  => '',
                'callback'  => '',
            	'height'    => '450',
            	'width'	    => '750',
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
	   'page_tag',
       'page_note',
	   array(
	   		'name'	=> 'create_user_id',
	   		'value' =>'MHelper::getUsername($data->create_user_id)',
       ),
	   'create_time',
       array(
    		'name'	=> 'modify_user_id',
    		'value' =>'MHelper::getUsername($data->modify_user_id)',
       ),
       'modify_time',
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
						'label' => Yii::t('system', 'Edit'),
						'url' => 'Yii::app()->createUrl("/systems/pagehelp/update", array("id" => $data->id))',
						'title' => Yii::t('system', 'Edit'),
						'options' => array('target' => 'dialog','class'=>'btnEdit','height'=>'450','width'=> '750'),
				),
		),
);
$this->widget('UGridView', $config);
?>
