<?php

App::uses('AppModel', 'Model');

/**
 * CakePHP MenuMaster
 * @author vinay
 * 
  CREATE TABLE `analytics_menu` (
  `menucode` int(11) NOT NULL AUTO_INCREMENT,
  `menuname` varchar(50) NOT NULL DEFAULT '',
  `menuicon` varchar(30) NOT NULL DEFAULT 'fa fa-circle-o',
  `menuaction` varchar(100) NOT NULL DEFAULT '',
  `menuindex` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `parent` int(11) NOT NULL DEFAULT '0',
  `finyearaccess` enum('BOTH','CURRENT') NOT NULL DEFAULT 'CURRENT',
  `status` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`menucode`)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 * 
 */
class MenuMaster extends AppModel {

    public $useTable = "analytics_menu";
    public $primaryKey = "menucode";

    public function getMenu($auth) {
        $conditions = array("status" => true);
        if ($auth["finyeardate"] == ((date('m') > 3) ? date('Y') : date('Y') - 1) . '-04-01') {
            $conditions["finyearaccess"] = array('BOTH', 'CURRENT');
        }
        $menuarr = $this->find("all", array("conditions" => $conditions, "order" => array("parent", "menuindex")));
        $menudata = array();
        $Data = array();
        foreach ($menuarr as $row) {
            $row = $row[$this->name];
            $menudata[$row['menucode']]['class'] = $row['menuicon'];
            $menudata[$row['menucode']]['name'] = $row['menuname'];
            $Data[$row['level']][$row['parent']][$row['menucode']] = $row['menuaction'];
        }
        $menuList = array();
        if (isset($Data[0][0])) {
            foreach ($Data[0][0] as $mcode => $maction) {
                if (trim($maction) != "") {
                    $menuList[$mcode] = $maction;
                } else {
                    $tmp = $this->getSubMenu($mcode, 1, $Data);
                    $menuList[$mcode] = $tmp[$mcode];
                }
            }
        }
        return array("menu" => $menuList, "menuData" => $menudata);
    }

    function getSubMenu($parent, $level, $Data) {
        $menu = isset($Data[$level][$parent]) ? $Data[$level][$parent] : array();
        foreach ($menu AS $key => $val) {
            if (trim($val) != '') {
                $arr[$parent][$key] = $val;
            } else {
                $level++;
                $tmp = $this->getSubMenu($key, $level, $Data);
                if (is_array($tmp[$key])) {
                    $arr[$parent][$key] = $tmp[$key];
                }
                $level --;
            }
        }
        return isset($arr) ? $arr : array($parent => array());
    }

}
