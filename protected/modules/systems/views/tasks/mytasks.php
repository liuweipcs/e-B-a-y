<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$config = array(
    'id' => 'tasks-grid',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'columns' => array(
        array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 2,
            'value' => '$data->id',
            'htmlOptions' => array('style' => 'width:30px;'),
        ),
        array(
            'name' => 'task_id',
            'value' => '$data->task_id',
            'htmlOptions' => array( 'style' => 'width:40px;'),
        ),
        array(
            'name' => 'task_name',
            'value' => function($data)
            {
                return Tasks::getTaskId($data->task_id)['task_name'];
            },
        ),
        array(
            'name' => 'task_content',
            'value' => function($data)
            {
                return Tasks::getTaskId($data->task_id)['task_content'];
            },
        ),
        array(
            'name' => 'gourp_id',
            'value' => function($data)
            {
                return Tasks::getDeps($data->gourp_id);
            },
        ),
        array(
            'name' => 'task_assign_id',
            'value' => function($data)
            {
                if($data->task_assign_id == '1'){
                    return Yii::t('system', '管理员');
                } else {
                    return Tasks::getUsers($data->task_assign_id);
                }
            },
        ),
        array(
            'name' => 'task_assign_status',
            'value' => function($data)
            {

                return VHelper::getTaskStatusColor(UebModel::model('tasks') ->queryPairs('id,task_status',"id = ".$data->task_id)[$data->task_id]);
            },
        ),

        array(
            'name' => 'task_assign_time',
            'value' => function($data)
            {
                return date('Y-m-d H:i:s', $data->task_assign_time);
            },
        ),
    ),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
    'pager' => array(),
);


$config['columns'][] = array(
    'header' => Yii::t('system', 'Operation'),
    'class' => 'CButtonColumn',
    'headerHtmlOptions' => array('width' => '100', 'align' => 'center'),
    'template' => '',
    'buttons' => array(

        'finish' => array(
            'url'       => 'Yii::app()->createUrl("/systems/tasks/finish", array("id" => $data->task_id))',
            'label'     => Yii::t('system', '完成'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnSelect',
                'rel' => 'tasks-grid',
                'width'     => '800',
                'height'    => '500',
            ),
        ),
        'close' => array(
            'url'       => 'Yii::app()->createUrl("/systems/tasks/close", array("id" => $data->task_id))',
            'label'     => Yii::t('system', '关闭'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnStop',
                'rel' => 'tasks-grid',
                'width'     => '800',
                'height'    => '500',
            ),
        ),


    ),
);
$this->widget('UGridView', $config);
?>


