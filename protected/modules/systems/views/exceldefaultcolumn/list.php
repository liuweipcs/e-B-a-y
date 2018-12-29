<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'exceldefaultcolumn-grid',
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'toolBar' => array(
        array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/exceldefaultcolumn/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'exceldefaultcolumn-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
    	array(
            'text'          => Yii::t('system', 'Add'),
            'url'           => '/systems/exceldefaultcolumn/create',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'exceldefaultcolumn-grid',
                'postType'  => '',
                'callback'  => '',
            	'height'    => '500',
            	'width'	    => '850',
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
// 	   'column_type',
       array(
    		'name'	=> 'column_type',
    		'value' => '$data->column_type_name',
       ),
       'column_title',
       'table_name',
       'column_field',
	   array(
	   		'name'	=> 'create_user_id',
	   		'value' => 'MHelper::getUsername($data->create_user_id)',
       ),
	   'create_time',
       array(
    		'name'	=> 'modify_user_id',
    		'value' => '!empty($data->modify_user_id) ? MHelper::getUsername($data->modify_user_id) : $data->modify_user_id',
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
					//	'label' => Yii::t('system', 'Edit'),
						'url' => 'Yii::app()->createUrl("/systems/exceldefaultcolumn/update", array("id" => $data->id))',
						'title' => Yii::t('system', 'Edit'),
						'options' => array('target' => 'dialog','class'=>'btnEdit','height'=>'500','width'=> '850'),
				),
		),
);
$this->widget('UGridView', $config);
?>
