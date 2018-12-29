<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$config = array(
    'id' => 'userquerylist-grid',
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
        'name' => '部门名称',
        'value' => '$data->department_name',
        'htmlOptions' => array('style' => 'width: 130px'),
    ),

    array(
        'name' => '部门分组ID',
        'value' => '$data->department_id',
        'htmlOptions' => array('style' => 'width:200px'),
    ),

    array(
        'header'            => Yii::t('system', 'Operation'),
        'class'             => 'CButtonColumn',
        'headerHtmlOptions' => array('width' => '50px', 'align' => 'center'),
        'htmlOptions'       => array(
            'align' => 'center',
        ),
        'template' => '{edit}{del}',
        'buttons'  => array(
            'edit' => array(
                'label'   => '编辑',
                'url'     => 'Yii::app()->createUrl("/users/userqueryconf/edit", array("id" => $data->id))',
                'title'   => '编辑('.$data->id . ')',
                'options' => array(
                    'target' => 'dialog',
                    'rel' => 'userquerylist-grid',
                    'class'=>'btnEdit',
                    'width'=>500,
                    'height'=>500,
                    'mark'=>true
                ),
            ),
            'del' => array(
                'url'       => 'Yii::app()->createUrl("/users/userqueryconf/delete", array("id" => $data->id))',
                'label'     => '删除?',
                'options'   => array(
                    'mask'   => 1,
                    'target' => 'ajaxTodo',
                    'class'  => 'btnDel',
                ),

            ),
        ),
    ),
);

$config['toolBar'] = array(
    array(
        'text'          => '添加',
        'url'           => '/users/userqueryconf/add',
        'htmlOptions'   => array(
            'class'     => 'add',
            'target'    => 'dialog',
            'rel'       => 'userquerylist-grid',
            'postType'  => '',
            'callback'  => 'navTabAjaxDone',
            'height'    => '500',
            'width'     => '500',
        )
    ),

    array(
        'text'          => Yii::t('system', 'Batch delete messages'),
        'url'           => '/users/userqueryconf/delete',
        'htmlOptions'   => array(
            'class'     => 'delete',
            'title'     => Yii::t('system', 'Really want to delete these records?'),
            'target'    => 'selectedTodo',
            'rel'       => 'userquerylist-grid',
            'postType'  => 'string',
            'warn'      => Yii::t('system', 'Please Select'),
            'callback'  => 'navTabAjaxDone',
        )
    ),
);

$this->widget('UGridView', $config);
?>
