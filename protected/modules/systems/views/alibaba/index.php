<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'alibaba-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/alibaba/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'alibaba-grid',
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
			'name'=> 'member_id',
			'value'=>'$data->member_id',
			'htmlOptions' 	=> array('style' => 'width:150px',),
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
            'name'=> 'status',
            'value'=>'UebModel::model("AlibabaAccount")->getAlibabaAccountStatus($data->status)',
            'htmlOptions' 	=> array('style' => 'width:50px',),
        ),
        array(
            'name'=> 'last_update_time',
            'value'=> function($data){
                return date('Y-m-d H:i:s',$data->last_update_time);
            },
            'htmlOptions' 	=> array('style' => 'width:150px',),
        ),




        array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}&nbsp;{authorization}&nbsp;{refresh}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/alibaba/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
    			),
                'authorization' => array(
                    'label' => Yii::t('system', '授权'),
                    'url' => 'Yii::app()->createUrl("/systems/alibaba/authorization", array("id" => $data->id))',
                    'title' => Yii::t('system', '授权'),
                    'options' => array('target' => 'dialog','class'=>'btnSelect','width'=>600,'height'=>400),
                ),
				'refresh' => array(
					'label' => Yii::t('system', '刷新'),
					'url' => 'Yii::app()->createUrl("/systems/alibaba/refreshToken", array("id" => $data->id))',
					'title' => Yii::t('system', '刷新'),
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

