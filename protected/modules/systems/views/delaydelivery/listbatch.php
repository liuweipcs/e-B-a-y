<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'AccountDelayDelivery-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/delaydelivery/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'press-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>900,
				'height'	=>600,
			)
		),
	),
    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',          
            'selectableRows' => 2,
            'value' => $data->logistics_type,
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ),
		array(
			'name'=> 'logistics_type',
			'value'=>'$data->type_name',
			'htmlOptions' 	=> array('style' => 'width:50px;height:100px;',),
		),
		array(
			'name'=> 'remain_day',
			'value'=>'$data->remain_day',
			'htmlOptions' 	=> array('style' => 'width:120px',),
		),
    	array(
            'name'=> 'remain_hours',
            'value'=>'$data->remain_hours',
    		'htmlOptions' 	=> array('style' => 'width:120px;height:32px;',),
        ),
        array(
            'name'=> 'extended_day',
            'value'=>'$data->extended_day',
            'htmlOptions' 	=> array('style' => 'width:80px',),
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
					'label' => Yii::t('system', '【编辑】'),
					'url' => 'Yii::app()->createUrl("/systems/delaydelivery/update", array("logistics_type" => $data->logistics_type))',
					'title' => '编辑',
					'options' => array('target' => 'dialog','width'=>900,'height'=>600),
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

