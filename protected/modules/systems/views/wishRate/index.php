<?php 
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'wishrate-grid',
		'dataProvider' => $model->search(null),
		'filter' => $model,
		'toolBar' => array(
				array(
						'text'          => Yii::t('system', 'Add'),
						'url'           => '/systems/wishRate/create',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target'    => 'dialog',
								'rel'       => 'wishrate-grid',
								'postType'  => '',
								'callback'  => '',
								'width'		=>800,
								'height'	=>450,
						)
				),
		),
		'columns' => array(
				array(
						'class' => 'CCheckBoxColumn',
						'selectableRows' => 2,
						'value' => $model->id,
						'htmlOptions' 	=> array('style' => 'width:30px'),
						'headerHtmlOptions' 	=> array('style' => 'width:30px'),
				),
				array(
						'name'=> '编号',
						'value'=>'$row+1',
						'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
				),
				array(
						'name'=>'所属账号',
						'value'=>'UebModel::model("WishAccount")->getAccountNameById($data->wish_id)',
						'htmlOptions'=>array(
								'style'=>'width:50px;'
						)
				),
				array(
						'name'=> '起始价格',
						'value'=>'$data->start_price',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),
				array(
						'name'=> 'top价格',
						'value'=>'$data->top_price',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),
				array(
						'name'=> '标准利润率',
						'value'=>'$data->basic_rate',
						'htmlOptions' 	=> array('style' => 'width:280px',),
				),
				array(
						'name'=> '最低利润率',
						'value'=>'$data->mini_rate',
						'htmlOptions' 	=> array('style' => 'width:180px',),
				),
				array(
						'name'=> '浮动利润率',
						'value'=>'$data->float_rate',
						'htmlOptions' 	=> array('style' => 'width:180px',),
				),
				array(
						'name'=> '运费',
						'value'=>'$data->ship_fee',
						'htmlOptions' 	=> array('style' => 'width:50px',),
				),
				array(
						'name'=> '状态',
						'value'=>'UebModel::model("WishRate")->getStatus($data->status)',
						'htmlOptions' 	=> array('style' => 'width:50px',),
				),
				array(
						'header' => Yii::t('system', 'Operation'),
						'class' => 'CButtonColumn',
						'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
						'htmlOptions' => array(
								'align' => 'center',
						),
						'template' => '{changCode}',
						'buttons' => array(
								'changCode' => array(
										'label' => Yii::t('system', 'Edit'),
										'url' => 'Yii::app()->createUrl("/systems/wishRate/update", array("id" => $data->id))',
										'title' => Yii::t('system', 'Edit'),
										'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>340),
								),
						),
				)
		),
		'tableOptions' => array(
				'layoutH' => 75,
		),
		'pager' => array(),
));
