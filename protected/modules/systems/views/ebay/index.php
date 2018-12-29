<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'ebayform-grid', 
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/ebay/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'ebayform-grid',
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
    		'name'=> 'user_name',
    		'value'=>'$data->user_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
    	array(
    		'name'=> 'store_name',
    		'value'=>'$data->store_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
    	array(
    		'name'=> 'short_name',
    		'value'=>'$data->short_name',
    		'htmlOptions' 	=> array('style' => 'width:80px',),
    	),
		array(
			'name'=> 'user_token_endtime',
			'value'=>'$data->user_token_endtime',
			'htmlOptions' 	=> array('style' => 'width:120px',),
		),
		array(
			'name'=> 'email',
			'value'=>'$data->email',
			'htmlOptions' 	=> array('style' => 'width:80px',),
		),
		array(
			'name'=> 'email_host',
			'value'=>'$data->email_host',
			'htmlOptions' 	=> array('style' => 'width:80px',),
		),	
		array(
			'name'=> 'email_port',
			'value'=>'$data->email_port',
			'htmlOptions' 	=> array('style' => 'width:40px',),
		),		
    	array(
    		'name'=> 'status',
    		'value'=>'UebModel::model("Ebay")->getEbayAccountStatus($data->status)',
    		'htmlOptions' 	=> array('style' => 'width:50px',),
    	),	
    	array(
    		'name'=> 'is_lock',
    		'value'=>'UebModel::model("Ebay")->getEbayAccountLock($data->is_lock)',
    		'htmlOptions' 	=> array('style' => 'width:50px',),
    	),		
    	/*array(
    		'name'=> 'group_id',
    		'value'=>'UebModel::model("Ebay")->getEbayAccountGroup($data->group_id)',
    		'htmlOptions' 	=> array('style' => 'width:60px',),
    	),*/
		array(
			'name'=> 'platform',
			'value'=>'strtr($data->platform,array("|"=>",","ebay"=>"ebay国内仓","ebayout"=>"ebay海外仓"))',
			'htmlOptions' 	=> array('style' => 'width:165px',),
		),
		array(
			'name'=> 'image_host',
			'value'=>'$data->image_host',
			'htmlOptions' 	=> array('style' => 'width:160px',),
		),
		array(
			'name'=> '是否设置通知',
			'value'=>'$data->isSetNotification()?"是":"否"',
			'htmlOptions' 	=> array('style' => 'width:80px',),
		),
		array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '60', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/ebay/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>600,'height'=>400),
    			),
    		),
    	)
    ),
    'tableOptions' => array(
        'layoutH' => 100,
    ),
    'pager' => array(),
));

?>

