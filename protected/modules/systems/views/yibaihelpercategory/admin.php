<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'shopeeproducttask-grid',
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'toolBar' => array(
        array(
            'text'          => '添加分类',
            'url'           => '/systems/yibaihelpercategory/create',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'postType' => 'string',
                'rel' 		=> 'shopeeproducttask-grid',
                'callback'  => 'navTabAjaxDone',
            )
        ),
    ),
    'columns' => array(
        array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 2,
            'value' =>'$data->id',
            'htmlOptions' 	=> array('style' => 'width:30px'),
            'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ),
        array(
            'name'=> '序号',
            'value'=>'$row+1',
            'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
        ),

        array(
            'name'=> '分类名称',
            'value'=>'$data->name',
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),

        array(
            'name'=> '简称，直接访问链接',
            'value'=>'$data->short_name',
            'htmlOptions' 	=> array('style' => 'width:200px',),
        ),

        array(
            'name'=> 'Add Time',
            'value'=>'$data->add_time',
            'htmlOptions' 	=> array('style' => 'width:200px',),
        ),

        array(
            'name'=> 'Add User',
            'value'=>'$data->add_user',
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),

        array(
            'name'=> '0,否，1是',
            'value'=>'$data->is_use',
            'htmlOptions' 	=> array('style' => 'width:100px',),
        ),

        array(
            'header' => Yii::t('system', 'Operation'),
            'class' => 'CButtonColumn',
            'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
            'htmlOptions' => array(
                'align' => 'center',
            ),
            'template' => '{viewNews}&nbsp;{changCode}&nbsp;{removeItem}',
            'buttons' => array(
                'viewNews' => array(
                    'label'=>'查看',
                    'title'=>'查看新闻',
                    'url'=>'Yii::app()->createUrl("/systems/yibaihelpercategory/view",array("id"=>$data->id))',
                    'options'=>array(
                        'class'=>'btnInfo',
                        'target'=>'dialog',
                        'width'=>800,
                        'height'=>500,
                        'mask'=>true
                    )
                ),


                'changCode' => array(
                    'label' => Yii::t('system', 'Edit'),
                    'url' => 'Yii::app()->createUrl("/systems/yibaihelpercategory/update", array("id" => $data->id))',
                    'title' => Yii::t('system', 'Edit'),
                    'options' => array(
                        'target' => 'dialog',
                        'class'=>'btnEdit',
                        'width'=>1000,
                        'height'=>700,
                        'mark'=>true,
                        'rel'=>'lazadaproducttask-grid',
                    ),
                ),

                'removeItem'=>array(
                    'label'=>'删除',
                    'url'=>'Yii::app()->createUrl("/systems/yibaihelpercategory/delete",array("id"=>$data->id))',

                    'options'=>array(
                        'target'=>'ajaxTodo',
                        'rel'=>'lazadaproducttask-grid',
                        'class'=>'btnDel',
                    )
                )
            ),
        ),

    ),
    'tableOptions' => array(
        'layoutH' => 75,
    ),
    'pager' => array(),
));
?>
