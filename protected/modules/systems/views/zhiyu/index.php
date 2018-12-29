<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'zhiyuform-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/zhiyu/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'zhiyuform-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>800,
				'height'	=>450,
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
    		'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
        ),
    	array(
    		'name'=> 'account',
    		'value'=>'$data->account',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
    	array(
    		'name'=> 'account_name',
    		'value'=>'$data->account_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
        array(
            'name'=> 'short_name',
            'value'=>'$data->short_name',
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),


    	array(
    		'name'=> 'status',
    		'value'=>'UebModel::model("ZhiyuAccount")->getZhiyuAccountStatus($data->status)',
    		'htmlOptions' 	=> array('style' => 'width:50px',),
    	),	
    		
    	array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/zhiyu/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
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

