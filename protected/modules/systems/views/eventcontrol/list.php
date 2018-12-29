<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'eventcontrol-grid',
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'toolBar' => array(),
    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 2,
            'value' => $model->id,
        ), 
       array(
            'name' => 'id',
            'value' => '$row+1',
        ),
	   'event_name',
       'note',
       'start_time',
       'respond_time',
	   array(
	   		'name'	=> 'event_status',
	   		'value' =>	'UebModel::model("EventControl")->getEventControlStatus($data->event_status)',
       ),

    ),
    'pager' => array(),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
);
	$config['columns'][] = array(
			'header' => Yii::t('system', 'Operation'),
			'headerHtmlOptions' => array('width' => '200', 'align' => 'center'),
			'type'=>'raw',
			'value' => '$data->event_status == 2 || $data->event_status == -1 ?  CHtml::link(Yii::t("system", "Cannot operate"),"", array("title"=> Yii::t("system", "Cannot operate"), "class" => "btnStay")) : CHtml::link(Yii::t("system", "Confirm stop"),"systems/eventcontrol/stop/id/".$data->id, array("title"=> Yii::t("system", "Confirm stop"), "target"=>"ajaxTodo", "class" => "btnStop"))',
// 			'template' => '{changType}',
// 			'buttons' => array(
// 					'changType' => array(
// 							'label' => Yii::t('system', 'Confirm stop'),
// 							'url' => 'Yii::app()->createUrl("/systems/eventcontrol/stop", array("id" => $data->id))',
// 							'title' => Yii::t('system', 'Stop'),							
// 							'options' => array('target' => 'ajaxTodo', 'class'=>'btnStop'),
// 					),
// 			),
	);

$this->widget('UGridView', $config);
?>
