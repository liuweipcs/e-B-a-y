<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
//echo '<h1 style="font-size: 25px;color: red;text-align: center">如果一个任务包含几个问题的意思,请分别提交,感谢你们的配合</h1>';
$config = array(
    'id' => 'task-grid',
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
            'name' => 'id',
            'value' => '$data->id',
            'htmlOptions' => array( 'style' => 'width:40px;'),
        ),
        array(
            'name' => 'task_name',
            'value' => '$data->task_name',
            'htmlOptions' => array( 'style' => 'width:200px;'),
        ),
        array(
            'name' => 'schedule',
            'value' => function($data)
            {
                echo  "<progress value='{$data->schedule}' max='100'></progress>".$data->schedule.'%';
            },
            'htmlOptions' => array( 'style' => 'width:180px;'),
        ),
        array(
            'name' => 'task_type',
            'value' => function($data)
            {
                return VHelper::getTaskType($data->task_type);
            },
            'htmlOptions' => array( 'style' => 'width:50px;'),
        ),

        array(
            'name' => 'task_create_group',
            'value' => function($data)
            {

                return Tasks::getDeps($data->task_create_group);
            },
            'htmlOptions' => array( 'style' => 'width:60px;'),
        ),
        array(
            'name' => 'task_create_founder',
            'value' => function($data) {
                if ($data->task_create_founder == '1') {

                        return Yii::t('system', '管理员');
                } else {
                    return Tasks::getUsers($data->task_create_founder);
                }
            },
            'htmlOptions' => array( 'style' => 'width:80px;'),
        ),

        array(
            'name' => 'task_status',
            'value' => function($data)
            {
                return VHelper::getTaskStatusColor($data->task_status);
            },
            'htmlOptions' => array( 'style' => 'width:60px;'),
        ),
       /* array(
            'name' => 'task_resolution',
            'value' => function($data)
            {
                if (!empty(TasksAssign::getResolution($data->id)))
                {
                    return VHelper::getRelution(TasksAssign::getResolution($data->id));
                } else {
                    return Yii::t('system' ,'暂无');
                }

            },
            'htmlOptions' => array( 'style' => 'width:60px;'),
        ),*/
        array(
            'name' => 'task_assign',
            'value' => function($data)
            {
                return Tasks::getDeps($data->task_assign);
            },
            'htmlOptions' => array( 'style' => 'width:60px;'),
        ),
        array(
            'name' => 'task_create_time',
            'value' => function($data)
            {
                return !empty($data->task_create_time)?date('Y-m-d H:i:s',$data->task_create_time):"";
            },
            'htmlOptions' => array( 'style' => 'width:120px;'),
        ),
        array(
            'name' => 'task_start_time',
            'value' => function($data)
            {
                return  !empty($data->task_start_time)?date('Y-m-d H:i:s',$data->task_start_time):'';
            },
            'htmlOptions' => array( 'style' => 'width:120px;'),
        ),
        array(
            'name' => 'task_claim_time',
            'value' => function($data)
            {
                return !empty($data->task_claim_time)?date('Y-m-d H:i:s',$data->task_claim_time):'';
            },
            'htmlOptions' => array( 'style' => 'width:120px;'),
        ),
        array(
            'name' => 'task_promise_time',
            'value' => function($data)
            {
                return !empty($data->task_promise_time)?date('Y-m-d H:i:s',$data->task_promise_time):'';
            },
            'htmlOptions' => array( 'style' => 'width:120px;'),
        ),
        /*array(
            'name' => 'task_assign_id',
            'value' => function($data)
            {
                $assin= TasksAssign::getAssinID($data->id);
                if($assin == '1'){
                    return Yii::t('system', '管理员');
                } else {
                   $assin = $assin?$assin:'1';
                    $sb =Tasks::getUsers($assin);
                    return $sb;
                }
            },
            'htmlOptions' => array( 'style' => 'width:60px;'),
        ),
        array(
            'name' => 'task_complete_person',
            'value' => function($data)
            {
                if($data->task_complete_person == '1'){
                    return Yii::t('system', '管理员');
                } else {
                    return Tasks::getUsers($data->task_complete_person);
                }
            },
            'htmlOptions' => array( 'style' => 'width:60px;'),
        ),*/
    ),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
    'pager' => array(),
);

$config['toolBar'] = array(
    array(
        'text' => Yii::t('system', 'Add a task'),
        'url' => '/systems/tasks/create',
        'htmlOptions' => array(
            'class' => 'add',
            'target' => 'dialog',
            'rel' => 'task-grid',
            'width' => '1000',
            'height' => '800',
        )
    ),
    array(
        'text' => Yii::t('system', 'delete the task'),
        'url' => '/systems/tasks/delete',
        'htmlOptions' => array(
            'class' => 'delete',
            'title' => Yii::t('system', 'Really want to delete these records?'),
            'target' => 'selectedTodo',
            'rel' => 'task-grid',
            'postType' => 'string',
            'callback' => 'navTabAjaxDone',
        )
    ),
    array(
        'text' => Yii::t('system', '我的任务'),
        'url' => '/systems/tasks/mytasks',
        'htmlOptions' => array(
            'class' => 'add',
            'title' => Yii::t('system', '我的任务'),
            'target' => 'dialog',
            'rel' => 'task-grid',
            'width' => '1200',
            'height' => '600',

        )
    ),

);

$config['columns'][] = array(
    'header' => Yii::t('system', 'Operation'),
    'class' => 'CButtonColumn',
    'headerHtmlOptions' => array('width' => '200', 'align' => 'center'),
    'template' => '{edit}{info}{assign}{schedule}{acceptance}{finish}{extension}{cancel}',
    'buttons' => array(
        'edit' => array(
            'url'       => function($data){
                $arr  = ['1','2'];
                if(in_array($data->task_status,$arr))
                {
                    return Yii::app()->createUrl("/systems/tasks/update", array("id" => $data->id));
                } else{
                    return '对不起,此状态下不能再做任何编辑了！';
                }

            },
            'label'     => Yii::t('system', 'Edit the Task'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnEdit',
                'rel'       => 'task-grid',
                'width'     => '1000',
                'height'    => '800',


            ),
        ),
        'assign' => array(
            'url'       =>function($data) {
                $arr  = ['1'];
            if(in_array($data->task_status,$arr))
            {
                return Yii::app()->createUrl("/systems/tasks/assign", array("id" => $data->id, "gid" => $data->task_assign));
            }else{
                return '对不起,此状态下不能再做任何分配了！';
            }

            },
            'label'     => Yii::t('system', 'Assigned the task'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnAssign',
                'rel' => 'task-grid',
                'width'     => '1000',
                'height'    => '800',
            ),
        ),
        'info' => array(
            'url'       => 'Yii::app()->createUrl("/systems/tasks/info", array("id" => $data->id))',
            'label'     => Yii::t('system', '详情'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnInfo',
                'rel' => 'task-grid',
                'width'     => '1000',
                'height'    => '800',
            ),
        ),
        /*'start' => array(
            'url'       => 'Yii::app()->createUrl("/systems/tasks/start", array("id" => $data->id))',
            'label'     => Yii::t('system', '开始'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnStay',
                'rel' => 'task-grid',
                'width'     => '800',
                'height'    => '500',
            ),
        ),*/
        'finish' => array(
            'url'       => function($data) {
                $arr  = ['7'];
                if(in_array($data->task_status,$arr))
                {
                    return Yii::app()->createUrl("/systems/tasks/finish", array("id" => $data->id));
                }else{
                    return '对不起,此状态下不能再做任何完成了！';
                }

            },
            'label'     => Yii::t('system', '上线完成'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnSelect',
                'rel' => 'task-grid',
                'width'     => '800',
                'height'    => '800',
            ),
        ),
       /* 'close' => array(
            'url'       => 'Yii::app()->createUrl("/systems/tasks/close", array("id" => $data->id))',
            'label'     => Yii::t('system', '关闭'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnStop',
                'rel' => 'task-grid',
                'width'     => '800',
                'height'    => '500',
            ),
        ),*/
        'schedule' => array(
            'url'       => function($data) {
                $arr  = ['2'];
                if(in_array($data->task_status,$arr))
                {
                    return Yii::app()->createUrl("/systems/tasks/schedule", array("id" => $data->id));
                }else{
                    return '对不起,此状态下不能再做任何更新了！';
                }

            },
            'label'     => Yii::t('system', '更新进度'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnAdd',
                'rel' => 'task-grid',
                'width'     => '800',
                'height'    => '500',
            ),
        ),
        'acceptance' => array(
            'url'       =>  function($data) {
                $arr  = ['3'];
                if(in_array($data->task_status,$arr))
                {
                    return Yii::app()->createUrl("/systems/tasks/acceptance", array("id" => $data->id));
                }else{
                    return '对不起,此状态下不能再做任何验收了！';
                }

            },
            'label'     => Yii::t('system', '测试验收'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnStay',
                'rel' => 'task-grid',
                'width'     => '800',
                'height'    => '800',
            ),
        ),
        'extension' => array(
            'url'       => function($data) {
                $arr  = ['2'];
                if(in_array($data->task_status,$arr))
                {
                    return Yii::app()->createUrl("/systems/tasks/extension", array("id" => $data->id));
                }else{
                    return '对不起,此状态下不能再做任何延期了！';
                }

            },
            'label'     => Yii::t('system', '任务延期'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnView',
                'rel'       => 'task-grid',
                'width'     => '800',
                'height'    => '800',
            ),
        ),
        'cancel' => array(
            'url'       => function($data) {
                $arr  = ['1'];
                if(in_array($data->task_status,$arr))
                {
                    return Yii::app()->createUrl("/systems/tasks/cancel", array("id" => $data->id));
                }else{
                    return '对不起,此状态下不能再做任何取消了！';
                }

            },
            'label'     => Yii::t('system', '取消'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnDel',
                'rel' => 'task-grid',
                'width'     => '800',
                'height'    => '800',
            ),
        ),



    ),
);
$this->widget('UGridView', $config);
?>


