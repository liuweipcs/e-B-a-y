<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$config = array(
    'id' => 'rolelist-grid',
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
        'name' => '用户',
        'value' =>'Amazonrole::getUser($data->user_id)',
        'htmlOptions' => array('style' => 'width: 100px'),
    ),

    array(
        'name' => '所属上级',
        'value' => 'Amazonrole::getUser($data->parent_id)',
        'htmlOptions' => array('style' => 'width:100px'),
    ),
     array(
        'name' => '所属部门',
        'value' => 'Amazonrole::getDept($data->department_id)',
        'htmlOptions' => array('style' => 'width:100px'),
    ),
     array(
        'name' => '更新时间',
        'value' => 'date("Y-m-d H:i:s",$data->update_time)',
        'htmlOptions' => array('style' => 'width:100px'),
    ),

    array(
        'header'            => Yii::t('system', 'Operation'),
        'class'             => 'CButtonColumn',
        'headerHtmlOptions' => array('width' => '20px', 'align' => 'center'),
        'htmlOptions'       => array(
            'align' => 'center',
        ),
        'template' => '{edit}{del}',
        'buttons'  => array(
            'edit' => array(
                'label'   => '编辑',
                'url'     => 'Yii::app()->createUrl("/systems/amazonrole/edit", array("id" => $data->id))',
                'title'   => '编辑('.$data->id . ')',
                'options' => array(
                    'target' => 'dialog',
                    'rel' => 'ratelist-grid',
                    'class'=>'btnEdit',
                    'width'=>400,
                    'height'=>600,
                    'mark'=>true),
            ),
            'del' => array(
                'url'       => 'Yii::app()->createUrl("/systems/amazonrole/delete", array("id" => $data->id))',
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
        'text'          => '添加角色控制',
        'url'           => '/systems/amazonrole/add',
        'htmlOptions'   => array(
            'class'     => 'add',
            'target'    => 'dialog',
            'rel'       => 'rolelist-grid',
            'postType'  => '',
            'callback'  => 'navTabAjaxDone',
            'height'    => '600',
            'width'     => '400',
        )
    ),
);

$this->widget('UGridView', $config);
?>
<script type="text/javascript">
    $(function(){
    $(".role").chosen({
       search_contains:true,
       inherit_select_classes:true,
    });
    $(".chosen-container-single").css('width','100px');

    $(".chosen-container .chosen-results").css('max-height','200px');
    });
</script>
