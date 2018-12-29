<style type="text/css">
    .grid .gridTbody td div{
        height:auto;
        overflow: hidden;
    }

    .btnview{
        color:red;
    }
</style>
<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'UserMapEbayAccount-grid',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'columns' => array(
        array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 2,
            'value' => $model->id,
            'headerHtmlOptions' => array('style' => 'width:27px'),
        ),
        array(
            'name' => 'count',
            'value' => '$row+1',
            'htmlOptions' => array('style' => 'width:27px;text-align:center;'),
        ),
        array(
            'name'	=>	'user_id',
            'value'	=>	'UebModel::model("User")->getUserNameAndFullNameById($data->user_id) ["$data->user_id"]',
            'htmlOptions'  => array('style' => 'width:100px;')
        ),

        array(
            'name' => 'ebay_account_names',
            'value' => '$data->ebay_account_names',
            'htmlOptions' => array( 'style' => 'width:400px;'),
        ),
        array(
            'name'	=> 'opration_id',
            'value' => 'UebModel::model("User")->getUserNameAndFullNameById($data->opration_id) ["$data->opration_id"]',
            'htmlOptions'  => array('style' => 'width:80px;'),
        ),

        array(
            'name'	=> 'opration_date',
            'value' => '$data->opration_date',
            'htmlOptions'  => array('style' => 'width:150px;'),
        ),

        array(
            'header' => Yii::t('system', 'Operation'),
            'class' => 'CButtonColumn',
            'headerHtmlOptions' => array('width' => '100', 'align' => 'center'),
            'template' => '{edit} {delete}',
            'buttons' => array(
                'edit' => array(
                    'url'       => 'Yii::app()->createUrl("/systems/usermapebayaccount/edit", array("id" => $data->user_id))',
                    'label'     => '编辑',
                    'options'   => array(
                        'target'    => 'dialog',
                        'class'     => 'btnEdit',
                        'width'     => '900',
                        'height'    => '450',
                    ),
                ),
                'delete' => array(
                    'url'       => 'Yii::app()->createUrl("/systems/usermapebayaccount/delete", array("id" => $data->user_id))',
                    'label'     => '删除',
                    'options'   => array(
                        'target'    => 'dialog',
                        'class'     => 'btnDel',
                        'width'     => '400',
                        'height'    => '180',
                    ),
                ),
            ),
        ),

    ),
    'toolBar'       => array(
        array(
            'text'          => Yii::t('system', 'Add'),
            'url'           => '/systems/usermapebayaccount/add',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'UserMapEbayAccount-grid',
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
