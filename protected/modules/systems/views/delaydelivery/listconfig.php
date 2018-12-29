<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'AliexpressAccount-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
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
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{configure}',
    		'buttons' => array(
				'configure' => array(
					'label' => Yii::t('system', '【编辑配置】'),
					'url' => 'Yii::app()->createUrl("/systems/delaydelivery/configure", array("id" => $data->id))',
					'title' => '编辑配置',
					'options' => array('target' => 'dialog','width'=>1150,'height'=>650),
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

