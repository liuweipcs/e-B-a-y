<?php
/**
 * role setting tree component
 * 
 * @author Bob <Foxzeng>
 */
if ( User::isAdmin()) {
    $list = Role::getTreeList();
} else {
    $roles = User::getLoginUserRoles();
    $list = Role::getTreeList($roles);
}
//findClass($list,1);
$menuId = isset($menuId) ?  $menuId : '';
$pairs = Role::getPairs();
echo '<ul class="'. $class .'" id="'. $id .'">';
if(strpos($menuId,' ')){//传来的menuId值单词间空格改成'_'，
	$menuId = str_replace(' ', '_', $menuId);
}

$disableOption = User::isAdmin() ? false : true;//默认不能修改同级角色

if ( isset( $checkedArr) ) {    
    echo subMenu($list, $pairs, $checkedArr, $menuId, $disableOption);
} else {   
    echo subMenu($list, $pairs,'',$menuId, $disableOption);
}
echo '</ul>';
function subMenu($data, $pairs = null, $checkedArr = null,$menuId=null, $disableOption=false) {  
    $str = '';
    foreach ($data as $key => $val) {
    	if($val==null){
    		continue;
    	}
        $str .= "<li>";  
        if($disableOption){//不能操作权限的角色
        	$htmlOptions = array();
        }else{
        	$htmlOptions = array( "id" => 'roleAccessId_'.$val['child']);
        }
        
        if ( $checkedArr && in_array($val['child'], $checkedArr)) {
            $htmlOptions['checked'] = true;
        }
        
        $str .= CHtml::link($pairs[$val['child']], 'javascript:void(0);', $htmlOptions); 
        if ( isset($val['children']) && $val['child']!=$menuId) {
            $str .= "<ul>";
            $str .= subMenu($val['children'], $pairs, $checkedArr,$menuId);
            $str .= "</ul>";
        }       
        $str .= '</li>';
    }
    return $str;
}
?>

