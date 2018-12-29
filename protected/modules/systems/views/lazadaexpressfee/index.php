<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'lazadaexpressfeeform-grid',
		'dataProvider' => $model->search(null),
		'filter' => $model,
		'toolBar' => array(
				array(
						'text'          => Yii::t('system', 'Add'),
						'url'           => '/systems/lazadaexpressfee/create',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target'    => 'dialog',
								'rel'       => 'lazadaform-grid',
								'postType'  => '',
								'callback'  => '',
								'width'		=>800,
								'height'	=>600,
						)
				),
				array(
						'text'          => Yii::t('system', 'Delete'),
						'url'           => '/systems/lazadaexpressfee/delete',
						'htmlOptions'   => array(
								'class'     => 'delete',
								'title' => Yii::t('system', 'Really want to delete these records?'),
								'target' => 'selectedTodo',
								'rel'=>'lazadaexpressfeeform-grid',
								'postType' => 'string',
								'callback'=>"navTabAjaxDone",
						)
				)
		),
		'columns' => array(
				array(
						'class' => 'CCheckBoxColumn',
						'selectableRows' => 2,
						'value' =>'$data->id',
						'htmlOptions' 	=> array('style' => 'width:60px'),
						'headerHtmlOptions' 	=> array('style' => 'width:60px'),
				),
				array(
						'name'=> '序号',
						'value'=>'$row+1',
						'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
				),
				array(
						'name'=> 'weight',
						'value'=>'$data->weight',
						'htmlOptions' 	=> array('style' => 'width:60px',),
				),
				array(
						'name'=> 'my_price',
						'value'=>'$data->my_price',
						'htmlOptions' 	=> array('style' => 'width:60px',),
				),
				array(
						'name'=> 'ph_price',
						'value'=>'$data->ph_price',
						'htmlOptions' 	=> array('style' => 'width:60px',),
				),
				array(
						'name'=>'th_price',
						'value'=>'$data->th_price',
						'htmlOptions'=>array(
								'style'=>'width:60px'
						)
				),
				array(
						'name'=>'id_price',
						'value'=>'$data->id_price',
						'htmlOptions'=>array(
								'style'=>'width:60px'
						)
				),
				array(
						'name'=>'sg_price',
						'value'=>'$data->sg_price',
						'htmlOptions'=>array(
								'style'=>'width:60px'
						)
				),
				array(
						'name'=>'vn_price',
						'value'=>'$data->vn_price',
						'htmlOptions'=>array(
								'style'=>'width:60px'
						)
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
										'url' => 'Yii::app()->createUrl("/systems/lazadaexpressfee/update", array("id" => $data->id))',
										'title' => Yii::t('system', 'Edit'),
										'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
								),
						),
				)
		),
		'tableOptions' => array(
				'layoutH' => 135,
				'style' => 'width:80%',
		),
		'pager' => array(),
));