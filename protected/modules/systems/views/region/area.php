<?php
/**
 * area list
 * 
 * @author ethanhu
 */
$list = $areas;
echo '<ul class="tree treeFolder" id="areaTreePanel" style="padding-top:5px;"><li>';

echo CHtml::link(Yii::t('users', 'County List'),  array('id'=>'area_0','target' => 'ajax', 'rel' => 'jbsxBox'));
echo '<ul>';
if($list){
	echo subMenu($list);
}else{
	echo Yii::t('users', 'There is no counties under the city');
}
echo '</ul></li></ul>';
function subMenu($data) {
	$str = '';      
    foreach ($data as $key => $val) {
        $str .= "<li>";      
        $str .= CHtml::link($val['region_name'], array('id'=>'area_'.$val['id'],'target' => 'ajax', 'rel' => 'areaBox'));       
        if (! empty($val['subdept'])) {
            $str .= "<ul>";
            $str .= subMenu($val['subdept']);
            $str .= "</ul>";
        }       
        $str .= '</li>';
    }
    return $str;
}
?>
