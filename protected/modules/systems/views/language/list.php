<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'language-grid',
    //'template' => THelper::getListTpl(),
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'toolBar' => array(
        array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/language/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'language-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
    	array(
            'text'          => Yii::t('system', 'Add'),
            'url'           => '/systems/language/create',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'language-grid',
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
	   'language_code',
       'google_code',
	   'cn_code',
    	array(
    		'header' => Yii::t('system', 'Attributed'),
    		'value' => 'UebModel::model("Language")->getLanguageOptions($data->attributed)',
    	),
	   'sort',
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
						'url' => 'Yii::app()->createUrl("/systems/language/update", array("id" => $data->id))',
						'title' => Yii::t('system', 'Edit'),
						'options' => array('target' => 'dialog','class'=>'btnEdit'),
				),
		),
);
$this->widget('UGridView', $config);
?>
