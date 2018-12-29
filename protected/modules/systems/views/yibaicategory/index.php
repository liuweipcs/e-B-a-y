<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'helpcategoryform-grid',
		'dataProvider' => $model->search(null),
		'filter' => $model,
		'toolBar' => array(
				array(
						'text'          => Yii::t('system', 'add'),
						'url'           => '/systems/yibaicategory/add',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target' => 'dialog',
								'rel'=>'helpcategoryform-grid',
								'width'=>600,
								'height'=>600,
						)
				)
		),
		'columns' => array(
				array(
						'class' => 'CCheckBoxColumn',
						'selectableRows' => 2,
						'value' =>'$data->id',
						'htmlOptions' 	=> array('style' => 'width:30px'),
						'headerHtmlOptions' 	=> array('style' => 'width:30px'),
				),
				array(
						'name'=> '序号',
						'value'=>'$row+1',
						'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
				),
				array(
						'name'=> '分类名称',
						'value'=>'$data->name',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),
				array(
						'name'=> '分类简称',
						'value'=>'$data->short_name',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),
				array(
						'name'=> '绑定部门',
						'value'=>'UebModel::model("Department")->getDepartment($data->department_id)',
						'htmlOptions' 	=> array('style' => 'width:280px',),
				),
				array(
						'name'=> '父分类',
						'value'=>'UebModel::model("HelperCategory")->getCateName($data->parent_id)',
						'htmlOptions' 	=> array('style' => 'width:280px',),
				),
				array(
						'header' => Yii::t('system', 'Operation'),
						'class' => 'CButtonColumn',
						'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
						'htmlOptions' => array(
								'align' => 'center',
						),
						'template' => '{updateCode}',
						'buttons' => array(
								'updateCode' => array(
										'label' => Yii::t('system', 'Edit'),
										'url' => 'Yii::app()->createUrl("/systems/yibaicategory/update", array("id" => $data->id))',
										'title' => Yii::t('system', 'Edit'),
										'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
								),
						),
				)
		),
		'tableOptions' => array(
				'layoutH' => 75,
				'style'=>'width:90%',
		),
		'pager' => array(),
));