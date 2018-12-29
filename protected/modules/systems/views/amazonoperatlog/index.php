<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$config = array(
    'id' => 'amazonoperatlog-grid',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'tableOptions' => array(
        'layoutH' => 90,
    ),
    'pager' => array(),
);

$config['columns'] = array(

    array(
        'name' => '编号',
        'value' => '$row+1',
        'htmlOptions' => array('style' => 'width:25px;text-align:center;'),
    ),

     array(
        'name' => '日志类型',
        'value' => '$data->type',
        'htmlOptions' => array('style' => 'width: 130px'),
    ),

    array(
        'name' => '操作人',
        'value' => '$data->username',
        'htmlOptions' => array('style' => 'width:100px'),
    ),

    array(
        'name' => '操作IP',
        'value' => '$data->ip',
        'htmlOptions' => array('style' => 'width:130px'),
    ),

    array(
        'name' => '操作内容',
        'value' => '$data->content',
        'htmlOptions' => array('style' => 'width:500px'),
    ),

    array(
        'name' => '操作时间',
        'value' => '$data->create_date',
        'htmlOptions' => array('style' => 'width:130px'),
    ),

);

$this->widget('UGridView', $config);
?>