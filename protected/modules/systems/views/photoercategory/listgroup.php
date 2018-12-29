<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'Accountgroup-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/lazada/groupcreate',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'press-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>500,
				'height'	=>200,
			)
		),
	),
    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 2,
            'value' => $data->id,
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ),
		array(
			'name'=> 'group_name',
			'value'=>'$data->group_name',
			'htmlOptions' 	=> array('style' => 'width:50px;',),
		),

    	array(
            'name'=> 'creater_name',
			'type'  =>'raw',
            'value'=>function($data){
				if($data->creater_name){
					$userName = UebModel::model('User')->getUserNameAndFullNameById($data->creater_name);
					return $userName[$data->creater_name];
				}
				return '';
			},
    		'htmlOptions'  	=> array('style' => 'width:120px;',),
        ),
        array(
            'name'=> 'creater_time',
            'value'=>'$data->creater_time',
            'htmlOptions' 	=> array('style' => 'width:80px',),
        ),
		array(
			'name'=> 'operator',
			'value'=>function($data){
				if($data->operator){
					$userName = UebModel::model('User')->getUserNameAndFullNameById($data->operator);
					return $userName[$data->operator];
				}
				return '';
			},
			'htmlOptions' 	=> array('style' => 'width:80px',),
		),
		array(
			'name'=> 'operator_time',
			'value'=>'$data->operator_time',
			'htmlOptions' 	=> array('style' => 'width:80px',),
		),
		array(
			'name'=> 'sort',
			'value'=>'$data->sort',
			'htmlOptions' 	=> array('style' => 'width:120px',),
		),
        array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{groupupdate} {delete}',
    		'buttons' => array(
				'groupupdate' => array(
					'label' => Yii::t('system', '【编辑】'),
					'url' => 'Yii::app()->createUrl("/systems/lazada/groupupdate", array("id" => $data->id))',
					'title' => '编辑',
					'options' => array('target' => 'dialog','width'=>500,'height'=>200,'class'=>'btnEdit'),
				),
				'delete' => array(
					'url'       => 'Yii::app()->createUrl("/systems/lazada/groupdelete", array("id" => $data->id))',
					'label'     => '删除',
					'options'   => array(
						'target'    => 'dialog',
						'class'     => 'btnDel',
						'width'     => '400',
						'height'    => '180',
					),
				),
    		),
    	),
    ),
    'tableOptions' => array(
        'layoutH' => 150,
    ),
    'pager' => array(),
));

?>

