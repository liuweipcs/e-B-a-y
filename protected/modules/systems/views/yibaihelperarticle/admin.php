<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
	'id' => 'alibaba-grid',
	'dataProvider' => $model->search(null),
	'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', '添加新闻'),
			'url'           => '/systems/Yibaihelperarticle/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'alibaba-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>1000,
				'height'	=>800,
			)
		),
	),
	'columns' => array(
		array(
			'name'=> 'id',
			'value'=>'$row+1',
			'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
		),

		array(
			'name'=> '所属分类',
			'value'=>'$data->category->name',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'name'=> 'title',
			'value'=>'$data->title',
			'htmlOptions' 	=> array('style' => 'width:150px',),
		),
		array(
			'name'=> 'content',
			'value'=>'$data->content',
			'type' => 'html',
			'htmlOptions' 	=> array('style' => 'width:600px',),
		),
		array(
			'name'=> 'add_time',
			'value'=>'$data->add_time',
			'htmlOptions' 	=> array('style' => 'width:150px',),
		),

		array(
			'name'=> 'add_user',
			'value'=>'$data->user->user_full_name',
			'htmlOptions' 	=> array('style' => 'width:100px',),
		),
		array(
			'header' => Yii::t('system', 'Operation'),
			'class' => 'CButtonColumn',
			'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
			'htmlOptions' => array(
				'align' => 'center',
			),
			'template' => '{viewNews}&nbsp;{updateNews}&nbsp;{deleteNews}',
			'buttons' => array(
				'viewNews' => array(
					'label'=>'查看',
					'title'=>'查看新闻',
					'url'=>'Yii::app()->createUrl("/systems/Yibaihelperarticle/view",array("id"=>$data->id))',
					'options'=>array(
						'class'=>'btnInfo',
						'target'=>'dialog',
						'width'=>800,
						'height'=>500,
						'mask'=>true
					)
				),
				'updateNews' => array(
					'label' => '更新',
					'title' => '更新新闻',
					'url'=>'Yii::app()->createUrl("/systems/Yibaihelperarticle/update",array("id"=>$data->id))',
					'options'=>array(
						'class'=>'btnEdit',
						'target'=>'dialog',
						'width'=>800,
						'height'=>750,
						'mask'=>true
					)
				),
				'deleteNews' => array(
					'label' => '删除',
					'title' => '删除新闻',
					'url'=>'Yii::app()->createUrl("/systems/Yibaihelperarticle/delete",array("id"=>$data->id))',
					'options' => array('class'=>'btnDel','target'=>'ajaxTodo'),
				),

			),
		),
	),
	'tableOptions' => array(
		'layoutH' => 75,
	),
	'pager' => array(),
));


?>




<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'yibaihelper-article-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(

		array(
			'name'=>'id',
			'value'=>'$row+1',
			//'value'=>'$data->id'
		),
		'category_id',
		'content',
		'title',
		'add_time',
		'add_user',
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
