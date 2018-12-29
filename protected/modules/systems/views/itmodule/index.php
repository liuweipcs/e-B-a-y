<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$config = array(
    'id'           => 'itmodule-grid',
    'dataProvider' => $model->search(),
    'filter'       => $model,
    'tableOptions' => array(
        'layoutH' => 90,
    ),
    'pager' => array(),
    'rowHtmlOptionsExpression' => "array('style' => 'float:left;')",
);

$config['columns'] = array(
    array(
        'class'          => 'CCheckBoxColumn',
        'selectableRows' => 2,
        'value'          => '$data->id',
        'htmlOptions'    => array('style' => 'width:5px'),
    ),

    array(
        'name'        => 'id',
        'value'       => '$data->id',
        'htmlOptions' => array('style' => 'width:25px;text-align:center;'),
    ),

    array(
        'name'        => 'module_name',
        'value'       => ' MHelper::utf8_cut($data->module_name, 8)',
        'htmlOptions' => array('style' => 'width:60px'),
    ),

    array(
        'name'        => 'source_code',
        'value'       => '$data->source_code',
        'htmlOptions' => array('style' => 'width:30px'),
    ),

    array(
        'name'        => 'author',
        'value'       => '$data->user->user_name',
        'htmlOptions' => array('style' => 'width:80px;'),
    ),

    array(
        'name'        => 'create_date',
        'value'       => '$data->create_date',
        'htmlOptions' => array('style' => 'width:80px;'),
    ),

    array(
        'name'        => 'last_modify_date',
        'value'       => '$data->last_modify_date',
        'htmlOptions' => array('style' => 'width:80px','class'=>''),
    ),
    
    array(
        'name'        => 'snap_shot',
        'value'       => '$data->snap_shot',
        'htmlOptions' => array('style' => 'width:80px','class'=>''),
    ),

    array(
        'name'        => 'description',
        'value'       => '$data->description',
        'htmlOptions' => array('style' => 'width:120px;'),
    ),
);

$config['toolBar'] = array(
    array(
        'text'          => '添加',
        'url'           => '/systems/itmodule/add',
        'htmlOptions'   => array(
            'class'     => 'add',
            'target'    => 'dialog',
            'rel'       => 'itmodule-grid',
            'postType'  => '',
            'callback'  => 'navTabAjaxDone',
            'width'     => '920',
            'height'    => '639',
        )
    ),
);

$config['lattice'] = <<<EOF
<div style="width:175px;height:175px;border:1px dashed #ccc;margin:5px;padding:2px;position:relative;">

    <a href="/systems/itmodule/view/id/{id}" target="dialog" width="600" height="750"><img src="{snap_shot}" width="100%" height="80%" style="cursor:pointer;" /></a>

    <div style="position:absolute;bottom: 0px;left: 0px;">
        <p style="padding:2px;">功能：<font color="blue">{module_name}</font> </p>
        <p style="padding:2px;">作者：{author}&nbsp;
        <a href="/systems/itmodule/edit/id/{id}" target="dialog" width="920" height="639" rel="itmodule-grid">编辑<a/>&nbsp;
        <a href="/systems/itmodule/del/id/{id}" mask="1" target="ajaxTodo" title="确定要删除?">删除</a></p>
    </div>

</div>
EOF;

$this->widget('UebListGridView', $config);
?>