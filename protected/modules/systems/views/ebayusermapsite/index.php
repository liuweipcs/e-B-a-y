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
    'id' => 'EbayUserMapSite-grid',
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'columns' => array(
        array(
            'name' => 'count',
            'value' => '$row+1',
            'htmlOptions' => array('style' => 'width:27px;text-align:center;'),
        ),
        array(
            'name'	=>	'user_id',
            'value'	=>	'$data->user_name',
            'htmlOptions'  => array('style' => 'width:100px;')
        ),
        array(
            'name'	=>	'siteid',
            'value'	=>	'$data->siteid',
            'htmlOptions'  => array('style' => 'width:300px;')
        ),
        array(
            'name'	=>	'is_valid',
            'value'	=>	'$data->is_valid',
            'htmlOptions'  => array('style' => 'width:100px;')
        ),
        array(
            'name'	=> 'operation_date',
            'value' => '$data->operation_date',
            'htmlOptions'  => array('style' => 'width:80px;'),
        ),
        array(
            'name'	=> 'operation_id',
            'value' => '$data->operation_id',
            'htmlOptions'  => array('style' => 'width:150px;'),
        ),

        array(
            'header' => Yii::t('system', 'Operation'),
            'class' => 'CButtonColumn',
            'headerHtmlOptions' => array('width' => '100', 'align' => 'center'),
            'template' => '{edit}',
            'buttons' => array(
                'edit' => array(
                    'url'       => 'Yii::app()->createUrl("/systems/ebayusermapsite/edit", array("id" => $data->user_id))',
                    'label'     => '编辑',
                    'options'   => array(
                        'target'    => 'dialog',
                        'class'     => 'btnEdit',
                        'width'     => '750',
                        'height'    => '450',
                    ),
                ),
            ),
        ),
    ),
    'toolBar'       => array(
        array(
            'text'          => Yii::t('system', 'Add'),
            'url'           => '/systems/ebayusermapsite/add',
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'EbayUserMapSite-grid',
                'postType'  => '',
                'callback'  => '',
                'height'    => '450',
                'width'	    => '750',
            )
        ),
    ),
    'tableOptions' 	=> array(
        'layoutH' 	=> 100,
    ),
    'pager' 		=> array()
));

?>
