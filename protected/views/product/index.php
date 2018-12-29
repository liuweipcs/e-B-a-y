<?php 
$baseUrl = Yii::app()->request->baseUrl;
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/core/dwz.min.js',CClientScript::POS_HEAD);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" " http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="renderer" content="webkit" />
<title><?php echo Yii::t('app', Yii::app()->name)?></title>
<link rel="stylesheet" type="text/css" href="/themes/default/style.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/themes/css/core.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/themes/css/pageform.css" media="screen" />
<script type="text/javascript" src="/js/core/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/js/core/dwz.min.js"></script>
</head>
<body>
<div class="pageContent" >
<?php
$tab2HtmlOptions = array( 'class' => 'j-ajax');
if ( empty($model->id) ) {
	$tab2HtmlOptions['style'] = "display:none;height:600px;";
}
$data = array(
		'currentIndex'  => '0',
		'eventType'     => 'click',
		'tabsHeader'    => array(
				array(
						'text' => Yii::t('products', 'Basic information'),
						'url'  => "/product/baseu/id/".$model->id,
						'htmlOptions' => array( 'class' => 'j-ajax')
				),
				 
				array(
						'text' => Yii::t('products', 'Product attribute'),
						'url'  => "/product/attrs/product_id/".$model->id,
						'htmlOptions' => $tab2HtmlOptions
				),
				array(
						'text' => Yii::t('products', '品检报告'),
						'url'  => "/product/qualityreport/id/".$model->id,
						'htmlOptions' => $tab2HtmlOptions
				),
		),
		'tabsContent' => array(),
);
$count=count($data['tabsHeader']);

for ($i=0;$i<$count;$i++){
	$data['tabsContent'][]=array( 'content' => '');
}
?>
<div class="tabs"  currentIndex=<?php echo $currentIndex;?> eventType=<?php echo $eventType;?>  >
    <div class="tabsHeader">
        <div class="tabsHeaderContent">
            <ul>
                <?php foreach ( $data['tabsHeader'] as $key => $val ):?>
                <?php $htmlOptions = isset( $val['htmlOptions']) ? $val['htmlOptions'] : array();?>
                <li>
                    <?php echo CHtml::link('<span>'.$val['text'].'</span>', $val['url'], $htmlOptions)?>
                </li>             
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="tabsContent" style="height:600px;" layoutH="40">
	
        <?php foreach ($data['tabsContent'] as $key => $val ):?>
		<?php //var_dump($val['content']); ?>
        <?php $htmlOptions = isset( $val['htmlOptions']) ? $val['htmlOptions'] : array();?>
        <?php echo CHtml::openTag('div', $htmlOptions);?>
            <?php echo $val['content'];?>
        <?php echo CHtml::closeTag('div');?>
		
        <?php endforeach;?>      
		
	<?php //var_dump($tabsContent);?>
    </div>
</div>
</div>
<script>
$('.tabs').tabs();
$('.selected a').click();
</script>
</body></html>
<?php 
exit();
?>