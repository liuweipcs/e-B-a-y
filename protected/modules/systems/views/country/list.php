<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'country-grid',
    //'template' => THelper::getListTpl(),
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'toolBar' => array(
        array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/country/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'country-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
    	array(
            'text'          => Yii::t('system', 'Add'),
            'url'           => '/systems/country/create',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'country-grid',
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
		   	'headerHtmlOptions' => array('style' => 'width:27px'),
        ),
		array(
			'name' => 'count',
			'value' => '$row+1',
			'htmlOptions' => array('style' => 'width:27px;text-align:center;'),
		),
		array(
			'name'	=>	'en_name',
			'value'	=>	'$data->en_name',
			'htmlOptions'  => array('style' => 'width:120px;')
		),
		array(
			'name'	=>	'en_abbr',
			'value'	=>	'$data->en_abbr',
			'htmlOptions'  => array('style' => 'width:120px;')
		),
		array(
			'name'	=>	'cn_name',
			'value'	=>	'$data->cn_name',
			'htmlOptions'  => array('style' => 'width:120px;')
		),
		array(
			'name'	=>	'continent',
			'value'	=>	'$data->continent',
			'htmlOptions'  => array('style' => 'width:120px;')
		),
		array(
			'name'	=>	'ebay_code',
			'value'	=>	'$data->ebay_code',
			'htmlOptions'  => array('style' => 'width:120px;')
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
		'headerHtmlOptions' => array('width' => '50', 'align' => 'center'),
		'template' => '{changType} {editebay}',
		'buttons' => array(
			'changType' => array(
				'label' => Yii::t('system', 'Edit'),
				'url' => 'Yii::app()->createUrl("/systems/country/update", array("id" => $data->id))',
				'title' => Yii::t('system', 'Edit'),
				'options' => array('target' => 'dialog','class'=>'btnEdit'),
			),
			'editebay' => array(
				'label' => Yii::t('system', '修改eBay码'),
				'url' => 'Yii::app()->createUrl("/systems/country/editebay", array("id" => $data->id))',
				'title' => Yii::t('system', 'Edit'),
				'options' => array('target' => 'dialog','class'=>'btnEdit'),
			),

		),
);
$this->widget('UGridView', $config);
?>
