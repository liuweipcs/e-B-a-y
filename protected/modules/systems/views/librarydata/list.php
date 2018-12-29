
<?php

Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'librarydata-grid', 
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(),
    'columns' => array(
    	array(
            'name'=> 'id',
            'value'=>'$row+1',
    		'htmlOptions' => array( 'style' => 'width:50px;'),
        ), 
    	array(
    		'name'=> 'sku',
    		'value'=>'$data->sku',
    		'htmlOptions' => array( 'style' => 'width:80px;'),
    	),
    	array(
    		'name'=> 'platform_code',
    		'value'=>'$data->platform_code',
    		'htmlOptions' => array( 'style' => 'width:100px;font-size:14px;'),
    	),
    	array(
    		'name'=> 'quantity',
    		'value'=>'$data->quantity',
    		'htmlOptions' => array( 'style' => 'width:100px;color:red;font-size:15px;'),
    	),
    	array(
    		'name'=> 'ship_date',
    		'value'=>'$data->ship_date',
    		'htmlOptions' => array( 'style' => 'width:120px;'),
    	),
    ),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
    'pager' => array(),
));

?>
