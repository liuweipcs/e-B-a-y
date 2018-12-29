<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$config = array(
    'id' => 'Orderaccesscontrol-grid',
    'dataProvider' => $model->search(null),
    'filter' => $model,
	'htmlOptions' => array('style' => 'width:800px;height:28px;'),
	'columns' => array(
       array(
            'class'              => 'CCheckBoxColumn',  
            'name'               => 'orgId',
            'selectableRows'     => 2,
            'value'              => '$data->id',
			'htmlOptions' => array('style' => 'width:80px;height:28px;'),
	   ),
       array(
            'name' => 'id',
            'value' => '$row+1',
			'htmlOptions' => array('style' => 'width:80px;height:28px;'),
	   ),

        'user_full_name',
    	array(
            'name'  => 'department_id',
            'value' => '$data->department_id>0 ? UebModel::model("Department")->getDepartment($data->department_id):"--"',
			'htmlOptions' => array('style' => 'width:180px;height:28px;'),
	   ),

    ),
    'tableOptions' => array(
        'layoutH' => 58,
    ),
    'pager' => array(),
);
    $config['columns'][] = array(
        'header' => Yii::t('system', 'Operation'),
        'class' => 'CButtonColumn',
        'headerHtmlOptions' => array('width' => '180', 'align' => 'center'),
		'htmlOptions' => array('style' => 'width:120px;height:28px;'),
		'template' => '{changType} {add}',
        'buttons' => array(
                    'changType' => array(
                        'label' => Yii::t('system', '编辑'),
                        'url' => 'Yii::app()->createUrl("/users/orderaccesscontrol/update", array("id" => $data->id,"department_id" => $data->department_id))',
                        'options' => array('target' => 'dialog','class'=>'','width'=>'1100','height'=>'600'),
                    ),
                    'add' => array(
                        'label' => Yii::t('system', '查看'),
                        'url' => 'Yii::app()->createUrl("/users/orderaccesscontrol/check", array("ids" => $data->id,"department" => $data->department_id))',
                        'options' => array('target' => 'dialog','class'=>'','width'=>'1100','height'=>'600'),
                        )
		),
	);

$this->widget('UGridView', $config);
?>
<script type="text/javascript">
</script>
