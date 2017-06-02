<?php

/**
 * Description of SchemeGrowthMaster
 *
 * @author vinay
 * 

  CREATE TABLE `scheme_service_growth_slab` (
  `serno` int(11) NOT NULL AUTO_INCREMENT,
  `growth_mode` char(1) NOT NULL DEFAULT '',
  `growth_type` char(1) NOT NULL DEFAULT '0',
  `growth` double NOT NULL DEFAULT '0',
  `applicable_from` date NOT NULL DEFAULT '0000-00-00',
  `scheme` char(15) NOT NULL DEFAULT '',
  `entrydate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`serno`)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

 * 
 */
class SchemeGrowthMaster extends AppModel {

    public $useTable = "scheme_service_growth_slab";

}
