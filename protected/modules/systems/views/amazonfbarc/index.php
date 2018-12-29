<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$config = array(
    'id' => 'amazonfbarc-grid',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'tableOptions' => array(
        'layoutH' => 90,
    ),
    'pager' => array(),
);

$config['columns'] = array(
    array(
        'class' => 'CCheckBoxColumn',
        'selectableRows' => 2,
        'value' => '$data->id',
        'htmlOptions' => array('style' => 'width:5px'),
    ),

    array(
        'name' => '编号',
        'value' => '$row+1',
        'htmlOptions' => array('style' => 'width:25px;text-align:center;'),
    ),

    array(
        'header'            => Yii::t('system', 'Operation'),
        'class'             => 'CButtonColumn',
        'headerHtmlOptions' => array('width' => '20px', 'align' => 'center'),
        'htmlOptions'       => array(
            'align' => 'center',
        ),
        'template' => '{edit}',//{del}
        'buttons'  => array(
            'edit' => array(
                'label'   => '编辑',
                'url'     => 'Yii::app()->createUrl("/systems/amazonfbarc/edit", array("id" => $data->id))',
                'title'   => '编辑('.$data->id . ')',
                'options' => array(
                    'target' => 'dialog',
                    'rel' => 'amazonfbarc-grid',
                    'class'=>'btnEdit',
                    'width'=>500,
                    'height'=>500,
                    'mark'=>true
                ),
            ),
            'del' => array(
                'url'       => 'Yii::app()->createUrl("/systems/amazonfbarc/delete", array("id" => $data->id))',
                'label'     => '删除?',
                'options'   => array(
                    'mask'   => 1,
                    'target' => 'ajaxTodo',
                    'class'  => 'btnDel',
                ),

            ),
        ),
    ),

    array(
        'name' => '小组',
        'value' => function($model){
            if($model->group_id){
                return "第 $model->group_id 组";
            }
            return '';
        },
        'htmlOptions' => array('style' => 'width: 50px'),
    ),

    array(
        'name' => '补货日期',
        'value' => function($model){
            $weeks='';
            if($model->weeks){
                foreach (explode(',',$model->weeks) as $v){
                    $weeks.=AmazonFbarc::getWeeks()[$v].", ";
                }
            }

            return rtrim($weeks,', ');
        },
        'htmlOptions' => array('style' => 'width:330px;'),
    ),

    /*array(
        'name' => '状态',
        'type' => 'raw',
        'value' => '$data->state == 1 ? "<font color=\"blue\">√</font>" : "×"',
        'htmlOptions' => array('style' => 'width:30px;','class'=>'state'),
    ),*/

    array(
        'name' => 'creator',
        'value' => function($model){
            if($model->creator){
                return current(User::model()->getUserNameAndFullNameById($model->creator,"uname"));
            }else{
                return '';
            }
        },
        'htmlOptions' => array('style' => 'width:60px'),
    ),

    array(
        'name' => 'create_date',
        'value' => 'date("Y-m-d",$data->create_date)',
        'htmlOptions' => array('style' => 'width:66px'),
    ),

    array(
        'name' => 'editor',
        'value' => function($model){
            if($model->editor){
                return current(User::model()->getUserNameAndFullNameById($model->editor,"uname"));
            }else{
                return '';
            }
        },
        'htmlOptions' => array('style' => 'width:60px'),
    ),

    array(
        'name' => 'edit_date',
        'value' => '$data->edit_date ? date("Y-m-d",$data->edit_date) : ""',
        'htmlOptions' => array('style' => 'width:66px'),
    ),
);

$config['toolBar'] = array(
    array(
        'text'          => '添加',
        'url'           => '/systems/amazonfbarc/add',
        'htmlOptions'   => array(
            'class'     => 'add',
            'target'    => 'dialog',
            'rel'       => 'amazonfbarc-grid',
            'postType'  => '',
            'callback'  => 'navTabAjaxDone',
            'height'    => '500',
            'width'     => '500',
        )
    ),

    array(
        'text'          => Yii::t('system', 'Batch delete messages'),
        'url'           => '/systems/amazonfbarc/delete',
        'htmlOptions'   => array(
            'class'     => 'delete',
            'title'     => Yii::t('system', 'Really want to delete these records?'),
            'target'    => 'selectedTodo',
            'rel'       => 'amazonfbarc-grid',
            'postType'  => 'string',
            'warn'      => Yii::t('system', 'Please Select'),
            'callback'  => 'navTabAjaxDone',
        )
    ),
);

$this->widget('UGridView', $config);
?>
