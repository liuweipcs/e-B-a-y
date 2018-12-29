<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;


$this->widget('UGridView', array(
    'id' => 'count-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,

    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',          
            'selectableRows' => 2,
            'value' => $data->record_id,
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ), 
    	array(
            'name'=> 'record_id',
            'value'=>'$data->record_id',
    		'htmlOptions' 	=> array('style' => 'width:80px;height:32px;',),
        ),

        array(
            'name'=> 'personnel_id',
            'value'=>function($data)
			{
				return UebModel::model('Attendance')->getname($data->personnel_id);
			},
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),
    	array(
    		'name'=> 'working_time',
    		'value'=>'$data->working_time',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
		array(
			'name'=> 'offwork_time',
			'value'=>'$data->offwork_time',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'name'=> 'years',
			'value'=>function($data)
			{
				return $data->years;
			},
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'name'=> 'month',
			'value'=>function($data)
			{
				return UebModel::model('AttendanceRecord')->setStr_pad($data->month);
			},
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),

		array(
			'name'=> 'day',
			'value'=>function($data)
			{
				return UebModel::model('AttendanceRecord')->setStr_pad($data->day);
			},
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'name'=> 'week',
			'value'=>function($data)
			{

				return UebModel::model('AttendanceRecord')->getweek($data->years,$data->month,$data->day);
			},
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),






    ),
    'tableOptions' => array(
        'layoutH' => 75,
    ),
    'pager' => array(),
));

?>

