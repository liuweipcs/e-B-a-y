<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'deliveryset-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/deliveryset/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'press-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>800,
				'height'	=>300,
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
            'name'=> 'id',
            'value'=>'$data->id',
    		'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
        ),
		array(
			'name'=> 'platform_name',
			'value'=>'$data->platform_name',
			'htmlOptions' 	=> array('style' => 'width:50px',),
		),
        array(
            'name'=> 'platform_code',
            'value'=>'$data->platform_code',
            'htmlOptions' 	=> array('style' => 'width:50px',),
        ),
		array(
			'name'=> 'site',
			'value'=>'$data->site',
			'htmlOptions' 	=> array('style' => 'width:200px',),
		),
    	array(
    		'name'=> 'delivery_time',
    		'value'=>function($data){
				return isset($data->delivery_time)?$data->delivery_time.'天':'';
			},
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
		array(
			'name'=> 'return_time',
			'value'=>function($data){
				return isset($data->return_time)?$data->return_time.'天':'';
			},
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'name'=> 'execution_type',
			'value'=>function($data){
				$execution_type = array(1=>'在设置时间内可同步',2=>'到设置时间时才可同步');
				return $execution_type[$data->execution_type];
			},
			'htmlOptions' 	=> array('style' => 'width:150px',),
		),
		array(
			'name'=> 'upload_user_id',
			'value'=>function($data){
				 $upload_user_id = UebModel::model('User')->getUserNameAndFullNameById($data->upload_user_id);
				return $upload_user_id[$data->upload_user_id];
			},
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'name'=> 'upload_time',
			'value'=>'$data->upload_time',
			'htmlOptions' 	=> array('style' => 'width:200px',),
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
    				'url' => 'Yii::app()->createUrl("/systems/deliveryset/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>300),
    			),
				'delete' => array(
					'url'       => 'Yii::app()->createUrl("/systems/deliveryset/delete", array("id" => $data->id))',
					'label'     => '删除',
					'options'   => array(
						'target'    => 'dialog',
						'class'     => 'btnDel',
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

