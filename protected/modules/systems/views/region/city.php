<?php
/**
 * city component
 * 
 * @author ethanhu 
 */
$list = $cities;

echo '<ul class="tree treeFolder" id="cityTreePanel"><li>';
echo CHtml::link(Yii::t('users', 'City List'), array('id'=>'city_0','target' => 'ajax', 'rel' => 'areaBox'));
echo '<ul>';
echo subMenu($list);
echo '</ul></li></ul>';
function subMenu($data) {
	$str = '';      
    foreach ($data as $key => $val) {
        $str .= "<li>";      
        $str .= CHtml::link($val['region_name'],'/systems/region/area/city_id/'.$val['id'], array('onclick'=>'showarea("city");','id'=>'city_'.$val['id'],'target' => 'ajax', 'rel' => 'areaBox'));       
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

