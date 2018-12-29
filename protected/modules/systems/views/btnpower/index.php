<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/14
 * Time: 10:54
 */

Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'Trackinglogistics-grid',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'columns' => array(
        array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 2,
            'value' => '$data->id',
            'htmlOptions' => array('style' => 'width:12px;'),
        ),

//        array(
//            'name'=>'id',
//            'value'=>'$data->id',
//            'htmlOptions'=>array(
//                'width'=>'15'
//            )
//        ),

        array(
            'name'=>'name',
            'value' => '$data->name',
            'htmlOptions'=>array(
                'width'=>'15'
            )
        ),

        array(
            'name'=>'type',
            'value'=>'$data->type==1?订单重检:($data->type==2?设置正常:其它)',
            'htmlOptions'=>array(
                'width'=>'15'
            )
        ),

        array(
            'name'=>'detail',
            'value'=>'$data->detail',
            'htmlOptions'=>array(
                'width'=>'50'
            )
        ),

        array(
            'header' => Yii::t('system', 'Operation'),
            'class' => 'CButtonColumn',
            'headerHtmlOptions' => array('width' => '5', 'align' => 'center'),
            'template' => '{edit}',
            'buttons' => array(
                'edit' => array(
                    'url'       => 'Yii::app()->createUrl("/systems/Btnpower/edit", array("id" => $data->id))',
                    'label'     => '编辑',
                    'options'   => array(
                        'target'    => 'dialog',
                        'class'     => 'btnEdit',
                        'width'     => '600',
                        'height'    => '400',
                        'rel'       => 'Trackinglogistics-grid',
                    ),
                ),
//                'delete' => array(
//                    'url'       => 'Yii::app()->createUrl("/systems/Btnpower/del", array("id" => $data->id))',
//                    'label'     => '删除',
//                    'options'   => array(
//                        'target'    => 'dialog',
//                        'class'     => 'btnDelete',
//                        'width'     => '400',
//                        'height'    => '180',
//                    ),
//                ),

            ),
        ),
    ),
    'tableOptions' => array(
        'layoutH' => 135,
        'style' => 'width:100%',
    ),
    'pager' => array(),
);

    $config['toolBar'] = array(
        array(
            'text'=>'添加',
            'url' => '/systems/Btnpower/add',
            'htmlOptions' => array(
                'class' => 'add',
                'target' => 'dialog',
                'rel' => 'Trackinglogistics-grid',
                'width'=>'600',
                'height'=>'400'
            )
        ),

        array(
            'text' => '批量删除',
            'url' => '/systems/Btnpower/delete',
            'htmlOptions' => array(
                'class' => 'delete',
                'title' => Yii::t('system', 'Really want to delete these records?'),
                'target' => 'selectedTodo',
                'rel' => 'Trackinglogistics-grid',
                'postType' => 'string',
                'callback' => 'navTabAjaxDone',
            )
        ),

    );


$this->widget('UGridView', $config);
?>

