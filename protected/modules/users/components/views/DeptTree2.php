<?php
/**
 * department tree component
 * 
 * @author ethanhu
 */
$list = Dep::getTreeList();
$menuId = isset($menuId) ? $menuId : 0;
echo '<ul class="'. $class .'" id="'. $id .'"><li>';
echo CHtml::link(Yii::t('users', 'Organization'), '#');
echo '<ul>';
echo subMenu($list,$menuId);
echo '</ul></li></ul>';
function subMenu($data,$menuId=0) {
	$str = '';      
    foreach ($data as $key => $val) {
        $str .= "<li>";      
        $str .= CHtml::link($val['name'],'#', array('onclick'=>'setValue('.$val['id'].')'));       
        if (! empty($val['subdept']) && $menuId!=$val['id']) {
            $str .= "<ul>";
            $str .= subMenu($val['subdept'],$menuId);
            $str .= "</ul>";
        }       
        $str .= '</li>';
    }
    return $str;
}
?>
