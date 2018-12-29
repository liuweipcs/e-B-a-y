<style type="text/css">
    .grid .gridTbody td div{
        height:auto;
        overflow: hidden;
    }
</style>
<script type="text/javascript">
    $(function() {
        $("img").lazyload({
            effect : "fadeIn"
        });
    });
</script>
<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'EbayAccountGroup-grid',
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'columns' => array(
        array(
            'name' => 'count',
            'value' => '$row+1',
            'htmlOptions' => array('style' => 'width:27px;text-align:center;'),
        ),
        array(
            'name'	=>	'name',
            'value'	=>	'$data->name',
            'htmlOptions'  => array('style' => 'width:100px;')
        ),
        array(
            'name'	=>	'reference',
            'value'	=>	'$data->reference',
            'htmlOptions'  => array('style' => 'width:100px;')
        ),

        array(
            'name'	=> 'create_by',
            'value' => 'empty($data->create_by)?"":UebModel::model("User")->getUserNameAndFullNameById((int)$data->create_by)["$data->create_by"]',
            'htmlOptions'  => array('style' => 'width:80px;'),
        ),

        array(
            'name'	=> 'create_time',
            'value' => '$data->create_time',
            'htmlOptions'  => array('style' => 'width:150px;'),
        ),

        array(
            'name'	=> 'modify_by',
            'value' => 'empty($data->modify_by) ? "":UebModel::model("User")->getUserNameAndFullNameById((int)$data->modify_by)["$data->modify_by"]',
            'htmlOptions'  => array('style' => 'width:80px;'),
        ),

        array(
            'name'	=> 'modify_time',
            'value' => 'empty($data->modify_by) ? "" : $data->modify_time',
            'htmlOptions'  => array('style' => 'width:150px;'),
        ),

        array(
            'header' => Yii::t('system', 'Operation'),
            'class' => 'CButtonColumn',
            'headerHtmlOptions' => array('width' => '100', 'align' => 'center'),
            'template' => '{edit}',
            'buttons' => array(
                'edit' => array(
                    'url'       => 'Yii::app()->createUrl("/systems/ebayaccountgroup/edit", array("id" => $data->id))',
                    'label'     => '编辑',
                    'options'   => array(
                        'target'    => 'dialog',
                        'class'     => 'btnEdit',
                        'height'    => '450',
                        'width'     => '900',
                    ),
                ),
            ),
        ),

    ),
    'toolBar'       =>array(
        array(
            'text'          => Yii::t('system', 'Add'),
            'url'           => '/systems/ebayaccountgroup/add',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'EbayAccountGroup-grid',
                'postType'  => '',
                'callback'  => '',
                'height'    => '450',
                'width'	    => '900',
            )
        ),
    ),
    'tableOptions' 	=> array(
        'layoutH' 	=> 100,
    ),
    'pager' 		=> array()
));

?>
