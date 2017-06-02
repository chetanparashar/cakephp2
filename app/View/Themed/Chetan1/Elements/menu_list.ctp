<?php

$LayoutData = AuthComponent::user("layoutData");
$menu = isset($LayoutData["menu"]) ? $LayoutData["menu"] : array();
$menuList = isset($LayoutData["menuData"]) ? $LayoutData["menuData"] : array();

echo '<ul class="sidebar-menu">';
foreach ($menu as $key => $val) {
    if (is_array($val)) {
        echo '<li class="treeview"><a href="#" onclick="return false;"><i class="' . $menuList[$key]['class'] . '"></i> <span>' . $menuList[$key]['name'] . '</span><i class="fa fa-angle-left pull-right"></i></a>';
        listItems($val, $menuList, $this->Form);
        echo "</li>";
    } else if (trim($val) != '') {
        $con_act = explode("/", $val);
        echo '<li>';
        echo $this->Form->create($con_act[1], array('method' => 'post', 'target' => '_self', 'url' => array("controller" => $con_act[0], "action" => $con_act[1]), 'name' => str_replace('/', '', $val)));
        echo $this->Form->hidden('AuthVar', array('value' => AuthComponent::user("AuthToken")));
        echo $this->Form->end();
        echo '<a href="#" onclick="$(\'#loaderDiv\').show();document.' . str_replace('/', '', $val) . '.submit();"><i class="' . $menuList[$key]['class'] . '"></i> <span>' . $menuList[$key]['name'] . '</span></a></li>';
    }
}
echo "</ul>";

function listItems($val, $menuList, $form) {
    if (is_array($val)) {
        echo '<ul class="treeview-menu">';
        foreach ($val AS $k => $v) {
            if (is_array($v)) {
                echo '<li><a href="#" onclick="return false;"><i class="' . $menuList[$k]['class'] . '"></i>' . $menuList[$k]['name'] . '<i class="fa fa-angle-left pull-right"></i></a>';
                listItems($v, $menuList, $form);
                echo "</li>";
            } else {
                $con_act = explode("/", $v);
                echo '<li>';
                echo $form->create($con_act[1], array('method' => 'post', 'target' => '_self', 'url' => array("controller" => $con_act[0], "action" => $con_act[1]), 'name' => str_replace('/', '', $v)));
                echo $form->hidden('AuthVar', array('value' => AuthComponent::user("AuthToken")));
                echo $form->end();
                echo '<a href="#" onclick="$(\'#loaderDiv\').show();document.' . str_replace('/', '', $v) . '.submit();"><i class="' . $menuList[$k]['class'] . '"></i> <span>' . $menuList[$k]['name'] . '</span></a></li>';
            }
        }
        echo "</ul>";
    }
}
