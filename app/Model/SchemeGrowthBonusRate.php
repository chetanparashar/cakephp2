<?php

/**
 * Description of SchemeGrowthBonusRate
 *
 * @author vinay
 * 

  CREATE TABLE `scheme_service_bonus_slab` (
  `serno` int(11) NOT NULL AUTO_INCREMENT,
  `group_code` int NOT NULL DEFAULT '0',
  `rate_mode` char(1) NOT NULL DEFAULT '',
  `rate` double NOT NULL DEFAULT '0',
  `slab` double NOT NULL DEFAULT '0',
  `applicable_date` date NOT NULL DEFAULT '0000-00-00',
  `scheme` char(15) NOT NULL DEFAULT '',
  `entrydate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`serno`)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

 * 
 */
class SchemeGrowthBonusRate extends AppModel {

    public $useTable = "scheme_service_bonus_slab";

}
