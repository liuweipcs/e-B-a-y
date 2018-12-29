<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'wishform-grid', 
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/wish/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'wishform-grid',
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
            'value' => $model->wish_id,
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ), 
    	array(
            'name'=> 'wish_id',
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
			'name'=> 'access_token',
			'value'=>'$data->access_token',
			'htmlOptions' 	=> array('style' => 'width:280px',),
		),
		array(
			'name'=> 'client_id',
			'value'=>'$data->client_id',
			'htmlOptions' 	=> array('style' => 'width:180px',),
		),
		array(
			'name'=> 'client_secret',
			'value'=>'$data->client_secret',
			'htmlOptions' 	=> array('style' => 'width:180px',),
		),
        array(
            'name'=> 'group_id',
            'value'=>'UebModel::model("WishStoreGroup")->getQueryOne($data->group_id)',
            'htmlOptions' 	=> array('style' => 'width:60px'),
        ),
    	array(
    		'name'=> 'status',
    		'value'=>'UebModel::model("Wish")->getWishAccountStatus($data->status)',
    		'htmlOptions' 	=> array('style' => 'width:50px',),
    	),
        array(
            'name'=>'账号绑定erp账号',
            'value'=>'$data->user_id>0?UebModel::model("User")->getUserNameAndFullNameById($data->user_id)[$data->user_id]:""',
            'htmlOptions'=>array(
                'style'=>'width:80px'
            )
        ),
    	array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}&nbsp;{refreshWishToken}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/wish/update", array("id" => $data->wish_id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
    			),
				'refreshWishToken' => array(
    				'label' => Yii::t('system', 'Refresh'),
    				'url' => 'Yii::app()->createUrl("/systems/wish/refreshWishToken", array("id" => $data->wish_id))',
    				'title' => Yii::t('system', 'Refresh'),
    				'options' => array('target' => 'dialog','class'=>'btnRefresh','width'=>400,'height'=>180),
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

