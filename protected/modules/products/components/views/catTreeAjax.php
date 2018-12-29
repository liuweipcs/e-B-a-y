<?php
/**
 * Category Setting
 * @author Gordon
 * @since 2014-07-26
 */
$treeList = UebModel::model('productCategory')->getTreeList();
$Id = 1;
if($Id){
	echo '<ul> ';
	echo tree($treeList,$Id);
	echo '</ul>';
}else{
	echo '<ul> ';
	echo tree($treeList);
	echo '</ul>';
}
function tree($data,$Id=0) {
    $str = '';
    foreach ($data as $key => $val) {
        $str .= "<li>";
        $htmlOptions = array('id' => 'catTreeItem_' . $val['id']);
        $str .= CHtml::link($val['category_cn_name'] ? $val['category_cn_name'] : $val['category_en_name'], 'javascript:void(0);', $htmlOptions);
        if (! empty($val['subcat']) && $Id != $val['id']) {
            $str .= "<ul>";
            $str .= tree($val['subcat'],$Id);
            $str .= "</ul>";
        }
        $str .= '</li>';
    }
    return $str;
}
?>

