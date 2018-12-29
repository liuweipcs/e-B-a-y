<style type="text/css">
.grid .gridTbody td div{
	height:auto;
	overflow: hidden;
}
.chosen-single span{
padding-top:5px;
}
.pageHeader{
min-height:100px;overflow:visible;
}

</style>
<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'shopeeexpressfee-grid',
	'dataProvider' => $model->search(null),
    'filter' => $model,

    'columns' => array(
        array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 2,
            'value' => '$data->id',
        	'headerHtmlOptions' => array('style'=>'width:25px;'),
            'htmlOptions' => array('style' => 'width:25px;height:28px;'),
        ),

        array(
            'name' => 'id',
            'value' => '$row+1',
            'htmlOptions' => array('style' => 'width:25px;text-align:center;'),
        ),

    	array(
    		'name'  =>'weight',
    		'type'  =>'raw',
    		'value' =>'$data->weight',
    	    'htmlOptions'  => array('style' => 'width:100px;text-align:center',),
    	),
		array(
    		'name'	=>	'price',
    		'value'	=>	'$data->price',
    		'htmlOptions'  => array('style' => 'width:100px;'),
    	),

     	array(
     		'name'	=> 'country_code',
     		'value' => '$data->country_code',
     		'htmlOptions'  => array('style' => 'width:80px;',),
     	),

		array(
				'header' => Yii::t('system', 'Operation'),
				'class' => 'CButtonColumn',
				'headerHtmlOptions' => array('width' => '50px', 'align' => 'center'),
				'htmlOptions' => array(
						'align' => 'center',
				),
				'template' => '{changCode}',
				'buttons' => array(
						'changCode' => array(
								'label' => Yii::t('system', 'Edit'),
								'url' => 'Yii::app()->createUrl("/systems/shopeeexpressfee/update", array("id" => $data->id))',
								'title' => Yii::t('system', 'Edit'),
								'options' => array('target' => 'dialog','class'=>'btnEdit','mask'=>true,'height' => '400','width'=> '870',),
						),
				),
		),
	),

    'toolBar'       => array(
    ),
    'pager' 		=> array(),

)
);

?>
<script type="text/javascript">

</script>