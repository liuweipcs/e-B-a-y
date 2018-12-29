<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$config = array(
    'id' => 'dashboard-grid',
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
            'value' => '$row+1',
            'htmlOptions' => array( 'style' => 'width:40px;'),
        ),
        array(
            'name' => 'dashboard_title',
            'value' => '$data->dashboard_title',
        ),
        array(
            'name' => 'dashboard_url',
            'value' => '$data->dashboard_url',
        ),
        array(
            'name' => 'is_global',
            'value' => '$data->getMyconfig("is_global",$data->is_global)',
        ),
        array(
            'name' => 'status',
            'value' => 'VHelper::getStatusLable($data->status)',
        )
    ),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
    'pager' => array(),
);

$config['toolBar'] = array(
    array(
        'text' => Yii::t('dashboard', 'Add a DashBoard'),
        'url' => '/systems/dashboard/create',
        'htmlOptions' => array(
            'class' => 'add',
            'target' => 'dialog',
            'rel' => 'dashboard-grid',
            'width' => '800',
            'height' => '600',
        )
    ),
    array(
        'text' => Yii::t('dashboard', 'Batch delete the DashBoards'),
        'url' => '/systems/dashboard/delete',
        'htmlOptions' => array(
            'class' => 'delete',
            'title' => Yii::t('system', 'Really want to delete these records?'),
            'target' => 'selectedTodo',
            'rel' => 'dashboard-grid',
            'postType' => 'string',
            'callback' => 'navTabAjaxDone',
        )
    ),
);

$config['columns'][] = array(
    'header' => Yii::t('system', 'Operation'),
    'class' => 'CButtonColumn',
    'headerHtmlOptions' => array('width' => '100', 'align' => 'center'),
    'template' => '{edit}',
    'buttons' => array(
        'edit' => array(
            'url'       => 'Yii::app()->createUrl("/systems/dashboard/update", array("id" => $data->id))',
            'label'     => Yii::t('dashboard', 'Edit the DashBoard'),
            'options'   => array(
                'target'    => 'dialog',
                'class'     =>'btnEdit',
                'rel' => 'cargocompany-grid',
                'width'     => '800',
                'height'    => '500',
            ),
        ),

    ),
);
$this->widget('UGridView', $config);
?>


