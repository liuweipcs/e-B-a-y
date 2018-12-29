<?php
$baseUrl = Yii::app()->request->baseUrl;
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/default/style.css', 'screen');
Yii::app()->clientScript->registerCssFile($baseUrl.'/css/pda.css', 'screen');
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/default/style.css', 'screen');
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/css/core.css', 'screen');
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/css/mouseright.css', 'screen');
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/css/print.css', 'print');
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/css/pageform.css', 'screen');
Yii::app()->clientScript->registerCssFile($baseUrl.'/js/kindeditor-4.1.7/themes/default/default.css');
Yii::app()->clientScript->registerCssFile($baseUrl.'/js/colorbox/colorbox.css');
Yii::app()->clientScript->registerCssFile($baseUrl.'/js/pikachoose/base.css');
Yii::app()->clientScript->registerCssFile($baseUrl.'/js/pikachoose/left-without.css');
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/default/treev2.css', 'screen');
//Yii::app()->clientScript->registerCoreScript('jquery');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/jquery-1.7.2.js',CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/keywords.js?'.uniqid(),CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/jquery.cookie.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/jquery.bgiframe.js',CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/jquery.validate.js',CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/kindeditor-4.1.7/kindeditor.js", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/kindeditor-4.1.7/lang/zh_CN.js", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/colorbox/jquery.colorbox.js", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/pikachoose/jquery.jcarousel.min.js", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/pikachoose/jquery.pikachoose.min.js", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/pikachoose/jquery.pikachoose.min.js", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/echarts.min.js", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/highcharts/js/highcharts.js", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/jquery.freezeheader.js", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/grid.js", CClientScript::POS_HEAD);
//Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/highcharts/js/modules/exporting.js", CClientScript::POS_HEAD);//图表导出
//Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/highstock/js/highstock.js", CClientScript::POS_HEAD);
//Yii::app()->clientScript->registerScriptFile($baseUrl . "/js/highcharts/js/export-csv.js", CClientScript::POS_HEAD);


Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/jquery.yiilistview.js',CClientScript::POS_HEAD);

if ( Env::DEVELOPMENT == YII_ENV ) {
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.core.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.util.date.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.validate.method.js',CClientScript::POS_HEAD);

    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.barDrag.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.drag.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.tree.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.accordion.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.ui.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.theme.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.switchEnv.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.alertMsg.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.contextmenu.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.navTab.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.tab.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.resize.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.dialog.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.dialogDrag.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.sortDrag.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.msortDrag.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.cssTable.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.stable.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.taskBar.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.ajax.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.pagination.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.database.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.datepicker.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.effects.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.panel.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.checkbox.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.combox.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.history.js',CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.print.js',CClientScript::POS_HEAD);
} else {
    Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.min.js', CClientScript::POS_HEAD);
}
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.tree.cus.js',CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile($baseUrl.'css/jquery.classynotty.css', 'screen');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/notty/jquery.classynotty.js',CClientScript::POS_HEAD);

$lang = !empty(Yii::app()->language) ? Yii::app()->language : 'en';
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/lang/ueb.regional.'.$lang.'.js',CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/custom/ueb.system.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/custom/ueb.common.js', CClientScript::POS_HEAD);

Yii::app()->clientScript->registerCssFile($baseUrl.'/css/chosen.css', 'screen');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/custom/chosen.jquery.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.lazyload.js');
?>
    <link rel="stylesheet" type="text/css" href="/assets/3a8d4349/gridview/styles.css" />
    <style type="text/css">
        .bottompanelBar {
            display: block;
            margin-left: 1px;
        }
        ul.yiiPager {
            font-size: 11px;
            border: 0;
            margin: 0;
            padding: 0;
            line-height: 100%;
            display: inline;
            float:left
        }
        .pageHeader{
            min-height: 25px;
        }
    </style>

<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
		'id' => 'helpcategoryform-grid',
		'dataProvider' => $model->listSearch(null),
		'filter' => $model,
		'columns' => array(
				array(
						'name'=> 'id',
						'value'=>'$data->id',
						'htmlOptions' 	=> array('style' => 'width:30px',),
				),
				array(
						'name'=> 'title',
						'type'=>'raw',
						'value'=>'Chtml::link($data->title,"/systems/yibaihelper/view/id/$data->id",array("target"=>"dialog","max"=>true))',
						'htmlOptions' 	=> array('style' => 'width:250px;text-align:left',),
				),
				array(
						'name'=> 'created_at',
						'value'=>'$data->created_at',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),

				array(
						'name'=> '所属分组',
						'value'=>'UebModel::model("HelperCategory")->getCateName($data->category_id)',
						'htmlOptions' 	=> array('style' => 'width:100px','align' => 'center'),
				),

		),
		'tableOptions' => array(
				'layoutH' => 80,
				'style'=>'width:90%',
		),
		'pager' => array(),


));



?>

