<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'currencyrate-grid', 
	'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
		array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/currencyrate/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'currencyrate-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/currencyrate/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'currencyrate-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>600,
				'height'	=>400,
			)
		),
		array(
				'text'          => Yii::t('system', '批量生成'),
				'url'           => '/systems/currencyrate/createmonth',
				'htmlOptions'   => array(
						'class'     => 'edit',
						'target'    => 'dialog',
						'rel'       => 'currencyrate-grid',
						'width'		=>600,
						'height'	=>400,
				)
		),
// 		array(
// 				'text'          => Yii::t('system', '在线更新'),
// 				'url' 			=> 'javascript:void(0);',
// 				'htmlOptions'   => array(
// 						'class'     => 'edit',
// 						'title'     => Yii::t('warehouses', 'Really want to refresh order?'),
// 						//'target'    => 'selectedTodo',
// 						'rel'       => 'currencyrate-grid',
// 						'onclick' 	=> 'refreshRate();',
// 						'postType'  => 'string',
// 						'callback'  => 'navTabAjaxDone',
// 				)
// 		),
	),
    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',          
            'selectableRows' => 2,
            'value' => $model->id,
        ), 
    	array(
            'name'=> 'id',
            'value'=>'$row+1',     
        ),
    	array(
    		'name'=> 'from_currency_code',
    		'value'=>'UebModel::model("Currency")->getCurrencyList($data->from_currency_code)',
    	),
    	array(
    		'name'=> 'to_currency_code',
    		'value'=>'UebModel::model("Currency")->getCurrencyList($data->to_currency_code)',
    	),
        'rate',
    	array(
    		'name'=> 'type',
    		'value'=>'UebModel::model("CurrencyRate")->rateTypeList($data->type)',
    	),	
    	'note',
    	'modify_time',
    	'rate_month',
    	array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '200', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit Code Type'),
    				'url' => 'Yii::app()->createUrl("/systems/currencyrate/update", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit Code Type'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit'),
    			),
    		),
    	)
    ),
    'tableOptions' => array(
        'layoutH' => 75,
    ),
    'pager' => array(),
));

?>
<script>
var refreshRate = function(){
	var url='/systems/currencyrate/updaterate/autorun/1';
	$.ajax({
        type: "post",
        url: url,
        dataType:'json',
        success: function(json) {
        	DWZ.ajaxDone(json);
        },
        error: DWZ.ajaxError
    });
	navTabPageBreak();
	
}
</script>
