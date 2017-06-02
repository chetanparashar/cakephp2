<?php
$menu= array(1 => 'LoginPage/home',2 => Array(3 => 'LoginPage/salesReport',4 => 'LoginPage/statewise'),5=>Array(6 =>'LoginPage/monthwise',7 =>'LoginPage/statewisemonthly',8=>'LoginPage/monthlywisestate'));
$menuList=array(
    1 => Array('class' => 'fa fa-home','name' => 'Home'),
    2 => Array('class' => 'fa fa-area-chart','name' => 'Live Sale' ),
    3 => Array('class' => 'fa fa-area-chart','name' => 'Grapical Sales/Cashout Reports'),
    4 => Array('class' => 'fa fa-area-chart','name' => 'Statewise Sale'),
    5 => Array('class' => 'fa fa-area-chart','name' => 'Yearly Sale') ,
    6 => Array('class' => 'fa fa-area-chart','name' => 'Monthwise Sale'),
    7 => Array('class' => 'fa fa-area-chart','name' => 'Statewise Monthly Sale'),
    8 => Array('class' => 'fa fa-area-chart','name' => 'Monthwise States Sale')
);
echo '<ul class="nav navbar-nav">';
foreach ($menu as $key => $val) {
    if (is_array($val)) {
        echo '<li class="dropdown"><a href="#" onclick="return false;" class="dropdown-toggle" data-toggle="dropdown"><i class="' . $menuList[$key]['class'] . '"></i> ' . $menuList[$key]['name'] . '<span class="caret" /></a>';
        listItems($val, $menuList, $this->Form);
        echo "</li>";
    } else if (trim($val) != '') {
        $con_act = explode("/", $val);
        echo '<li>';
        echo $this->Form->create($con_act[1], array('method' => 'post', 'target' => '_self', 'url' => array("controller" => $con_act[0], "action" => $con_act[1]), 'name' => str_replace('/', '', $val)));
        echo $this->Form->end();
        echo '<a href="#" onclick="$(\'#loaderDiv\').show();document.' . str_replace('/', '', $val) . '.submit();"><i class="' . $menuList[$key]['class'] . '"></i> <span>' . $menuList[$key]['name'] . '</span></a></li>';
    }
}
echo "</ul>";

function listItems($val, $menuList, $form) {
    if (is_array($val)) {
        echo '<ul class="dropdown-menu" role="menu">';
        foreach ($val AS $k => $v) {
            if (is_array($v)) {
                echo '<li><a href="#" onclick="return false;"><i class="' . $menuList[$k]['class'] . '"></i>' . $menuList[$k]['name'] . '<span class="caret" /></a>';
                listItems($v, $menuList, $form);
                echo "</li>";
            } else {
                $con_act = explode("/", $v);
                echo '<li>';
                echo $form->create($con_act[1], array('method' => 'post', 'target' => '_self', 'url' => array("controller" => $con_act[0], "action" => $con_act[1]), 'name' => str_replace('/', '', $v)));
                echo $form->end();
                echo '<a href="#" onclick="$(\'#loaderDiv\').show();document.' . str_replace('/', '', $v) . '.submit();"><i class="' . $menuList[$k]['class'] . '"></i> <span>' . $menuList[$k]['name'] . '</span></a></li>';
            }
        }
        echo "</ul>";
    }
}
