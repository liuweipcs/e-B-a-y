<style type="text/css">
.grid .gridTbody td div{
	height:auto;
	padding-top:2px;
}
</style>
<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'paypalaccount-grid',
		'dataProvider' => $model->search(null),
		'filter' => $model,
		
		'toolBar' => array(
				array(
						'text'          => Yii::t('system', 'Lock Account'),
						'url'           => '/systems/paypalaccount/lock',
						'htmlOptions'   => array(
								'class'     => 'delete',
								'title'     => Yii::t('system', 'Really want to lock these account?'),
								'target'    => 'selectedTodo',
								'rel'       => 'paypalaccount-grid',
								'postType'  => 'string',
								'warn'      => Yii::t('system', 'Please Select'),
								'callback'  => 'navTabAjaxDone',
						)
				),
				array(
						'text'          => Yii::t('system', 'Open Account'),
						'url'           => '/systems/paypalaccount/open',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target'    => 'selectedTodo',
								'rel'       => 'paypalaccount-grid',
								'postType'  => 'string',
								'callback'  => 'navTabAjaxDone',
						)
				),
				array(
						'text'          => Yii::t('system', 'Add'),
						'url'           => '/systems/paypalaccount/create',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target'    => 'dialog',
								'rel'       => 'paypalaccount-grid',
								'postType'  => '',
								'callback'  => '',
								'height'    => '450',
								'width'	    => '750',
						)
				),
 		),

		'columns' => array(
				array(
						'class' => 'CCheckBoxColumn',
						'selectableRows' =>2,
						'value'=> '$data->id',
						'htmlOptions' => array('style' => 'width:30px;'),
				),
				array(
						'name'=> 'count',
						'value'=>'$row+1',
						'htmlOptions' 	=> array('style' => 'width:50px',),
				),
				array(
						'name' => 'email',
						'value'=> '$data->email',
						'htmlOptions' => array('style' => 'width:100px;'),
				),
				array(
					'name' => 'api_user_name',
					'value'=> '$data->api_user_name',
					'htmlOptions' => array('style' => 'width:100px;'),
				),
				array(
					'name' => 'api_signature',
					'value'=> '$data->api_signature',
					'htmlOptions' => array('style' => 'width:150px;'),
				),
				array(
						'name' => 'amount_status',
						'value' => 'VHelper::getStatusLable($data->status)',
						'htmlOptions' => array('style' => 'width:70px;'),
				),
				array(
						'name' => 'platform_code',
						'value' => 'uebModel::model("Platform")->getPlatformList($data->platform_code)',
						'htmlOptions' => array('style' => 'width:70px;'),
				),
				array(
						'name' => 'opration_id',
						'value' => 'UebModel::model("User")->getUserNameAndFullNameById($data->opration_id) ["$data->opration_id"]',
						'htmlOptions' => array('style' => 'width:100px;'),
				),
				array(
						'name' => 'opration_date',
						'value' => '$data->opration_date',
						'htmlOptions' => array('style' => 'width:115px;'),
				),
				array(
						'name' => 'amount_start',
						'value' => '$data->amount_start',
						'htmlOptions' => array('style' => 'width:75px;'),
				),
				array(
						'name' => 'amount_end',
						'value' => '$data->amount_end',
						'htmlOptions' => array('style' => 'width:75px;'),
				),
// 				array(
// 						'name' => 'group_name',
// 						'value' => '$data->group_name',
// 						'htmlOptions' => array('style' => 'width:70px;'),
// 				),
				array(
						'name' => 'group_id',
						'value' => '$data->group_id',
						'htmlOptions' => array('style' => 'width:70px;'),
				),
				array(
						'header' => Yii::t('system', 'Operation'),
						'class' => 'CButtonColumn',
						'headerHtmlOptions' => array('width' => '100', 'align' => 'center'),
						'template' => '{edit}',
						'buttons' => array(
								'edit' => array(
										'url'       => 'Yii::app()->createUrl("/systems/paypalaccount/update", array("id" => $data->id))',
										'label'     => Yii::t('system', 'Edit Paypal Account'),
										'options'   => array(
												'target'    => 'dialog',
												'class'     =>'btnEdit',
												'width'     => '500',
												'height'    => '300',
										),
								),
				
						),
				),
			),
// 				array(
// 						'header' => Yii::t('order', 'propellingmovementlog'),
// 						'class' => 'CButtonColumn',
// 						'headerHtmlOptions' => array('style' => 'width:60px;', 'align' => 'center'),
// 						'template' => '{edit}',
// 						'buttons' => array(
								 
// 								'edit' => array(
// 										'url'       => 'Yii::app()->createUrl("/orders/orderpackage/Wmscheck", array("ids" => $data->key_word))',
// 										'label'     => "$data->key_word",
// 										'options'   => array(
// 												'target'    => 'dialog',
// 												'class'     =>'btnEdit',
// 												'mask'		=>true,
// 												'rel' => 'orderpackage-grid',
// 												'width'     => '600',
// 												'height'    => '400',
// 										),
// 								),

// 						),
// 				),
// 		),

		'tableOptions' 	=> array(
				'layoutH' 	=> 90,
		),
		'pager' 		=> array(
        ),
));

?>

<script language="javascript">
</script>




