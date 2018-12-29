<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'AliexpressAccount-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/aliexpress/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'press-grid',
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
            'name'=> 'account',
            'value'=>'$data->account',
            'htmlOptions' 	=> array('style' => 'width:50px',),
        ),
        array(
            'name'=> 'store_name',
            'value'=>'$data->store_name',
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),
    	array(
    		'name'=> 'short_name',
    		'value'=>'$data->short_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
		array(
			'name'=> 'email',
			'value'=>'$data->email',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'name'=> 'access_token',
			'value'=>'$data->access_token',
			'htmlOptions' 	=> array('style' => 'width:300px',),
		),
        array(
            'name'=> 'refresh_token',
            'value'=>'$data->refresh_token',
            'htmlOptions' 	=> array('style' => 'width:240px',),
        ),
        array(
            'name'=> 'app_key',
            'value'=>'$data->app_key',
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),
        array(
            'name'=> 'secret_key',
            'value'=>'$data->secret_key',
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),
		array(
			'name'=> 'group_id',
			'value'=>'UebModel::model("AliexpressStoreGroup")->getQueryOne($data->group_id)',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
        array(
            'name'=> 'status',
			'type' =>'raw',
            'value'=>function($data){
				$typeArr=array(
					1 => Yii::t('system', '启用'),
					2 => Yii::t('system', '停用'),
					3 => Yii::t('system', 'token更新失败，请重新授权'),
				);
				if($data->status == 3){
					$status = '<p style="background: yellow">'.$typeArr[$data->status].'</p>';
				}else{
					$status = $typeArr[$data->status];
				}
				return $status;
			},
            'htmlOptions' 	=> array('style' => 'width:50px',),
        ),
        array(
            'name'=> 'last_update_time',
            'value'=> function($data){
                return date('Y-m-d H:i:s',$data->last_update_time);
            },
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),




        array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}&nbsp;{configure}&nbsp;{authorization}&nbsp;{refresh}&nbsp;{delete}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/aliexpress/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
    			),
				'configure' => array(
					'label' => Yii::t('system', '【延迟收货配置】'),
					'url' => 'Yii::app()->createUrl("/systems/aliexpress/configure", array("id" => $data->id))',
					'title' => '延迟收货配置',
					'options' => array(
						'target' => 'dialog','width'=>1150,'height'=>650,
					),
				),
                'authorization' => array(
                    'label' => Yii::t('system', '授权'),
                    'url' => 'Yii::app()->createUrl("/systems/aliexpress/authorization", array("id" => $data->id))',
                    'title' => Yii::t('system', '授权'),
                    'options' => array('target' => 'dialog','class'=>'btnSelect','width'=>600,'height'=>400),
                ),
				'refresh' => array(
					'label' => Yii::t('system', '刷新'),
					'url' => 'Yii::app()->createUrl("/systems/aliexpress/refreshToken", array("id" => $data->id))',
					'title' => Yii::t('system', '刷新'),
					'options' => array('target' => 'dialog','class'=>'btnRefresh','width'=>400,'height'=>180),
				),
				'delete' => array(
					'url'       => 'Yii::app()->createUrl("/systems/aliexpress/delete", array("id" => $data->id))',
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

