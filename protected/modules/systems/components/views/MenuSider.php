<?php

/**
 * menu sider tree component
 * 
 * @author Bob <Foxzeng>
 */
$menuList = Menu::getTreeList(1, 1);

/******************************/
//让测试账号全部通过...测试完成后删除
$flag = false;
$userArr = array('testA','testB','testC','testD','testE');
if( in_array(Yii::app()->user->name,$userArr) ){
	$flag = true;
}
$flag = true;
/******************************/
//$data = Menu::model()->findByPk($parentId);

echo '<ul class="tree treeFolder" id="tree-menu" > ';
//var_dump($menuList[$parentId]['submenu']);
//echo $menuList[138]['submenu'];exit;
echo subMenu($menuList[$parentId]['submenu'], $flag);
//$data = $menuList[$parentId]['submenu'];
function subMenu($data, $flag) {
    $str = '';      
    foreach ($data as $key => $val) {
        if (! Menu::checkAccess('menu_'.$val['id']) && !$flag ) {
            continue;
        }
        $str .= "<li>";
        if (! empty($val['menu_url']) ) {
            $str .=  '<a href="'.Yii::app()->baseUrl. $val['menu_url'].'" target="navTab" id="page_'.$val['id'].'" rel="page'.$val['id'].'">'.$val['name'].'</a>';
        } else {
            $str .=  '<a href="javascript::void(0);" >'.$val['name'].'</a>';
        }
        
        if (! empty($val['submenu'])) {
            $str .= "<ul>";
            //$str .= subMenu($val['submenu'], $flag);
            $str .= "</ul>";
        }       
        $str .= '</li>';
    }
    return $str;
}
echo '</ul>';
?>
