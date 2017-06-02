<?php

/**
 * Description of SchemeServiceRunMaster
 *
 * @author vinay
 * 

  CREATE TABLE `scheme_service_points` (
  `serno` int(11) NOT NULL AUTO_INCREMENT,
  `group_code` int NOT NULL DEFAULT '0',
  `points` char(2) NOT NULL DEFAULT '0',
  `scheme_type` char(1) NOT NULL DEFAULT '0',
  `applicable_from` date NOT NULL DEFAULT '0000-00-00',
  `applicable_to` date NOT NULL DEFAULT '0000-00-00',
  `min_sale_amount` double NOT NULL DEFAULT '0',
  `scheme` char(15) NOT NULL DEFAULT '',
  `entrydate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`serno`)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;


 * 
 */
class SchemeServiceRunMaster extends AppModel {

    public $useTable = "scheme_service_points";

    public function GetGroupBaseRun($Schemedate) {
        return $this->find('all', array(
                    'fields' => array(
                        'group_code',
                        'points',
                        'min_sale_amount',
                        'scheme_type'
                    ),
                    'conditions' => array(
                        'applicable_from <=' => $Schemedate,
                        'applicable_to >=' => $Schemedate),
                    'order' => 'entrydate desc'
        ));
    }

}
