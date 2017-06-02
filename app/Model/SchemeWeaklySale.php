<?php

/**
 * Description of SchemeWeaklySale
 *
 * @author vinay
 * 

  CREATE TABLE `scheme_weaklysale_groupwise` (
  `db_serno` int(11) NOT NULL DEFAULT '0',
  `countercode` char(20) NOT NULL DEFAULT '',
  `agentcode` char(20) NOT NULL DEFAULT '',
  `groupcode` SMALLINT UNSIGNED DEFAULT NULL,
  `yearofweak` char(7) NOT NULL DEFAULT '0000-00',
  `basemrp` double NOT NULL DEFAULT '0',
  `curmrp` double NOT NULL DEFAULT '0',
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

 * find("list",array('fields'=>array(),'conditions'=>array()));
 */
App::import("Lib", "DateMethod");

class SchemeWeaklySale extends AppModel {

    public $useTable = "scheme_weaklysale_groupwise";

    public function CalculateWeaklysSale($ofday) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $prevday = DateMethod::AddDate(Date("Y-m-d", strtotime($ofday)), 1);
        if (date("W", strtotime($prevday)) == date("W", strtotime($ofday))) {
            return;
        }
        $curweak = date("W", strtotime($ofday));
        $curyearofweak = date("Y-W", strtotime($ofday));
        if ($this->hasAny(array('yearofweak' => $curyearofweak))) {
            return;
        }
        $baseyearweak = date("Y-W", strtotime(DateMethod::AddDate($ofday, -7)));
        $this->virtualFields=array('groupwise_countercode'=>"concat(countercode,',',groupcode)");
        $baseData = $this->find("list", array('fields' => array('groupwise_countercode','curmrp'), 'conditions' => array('yearofweak' => $baseyearweak)));
        //cakeLog::write("DDDDD1111",var_export($baseData,true));
        $ServiceGroup = ClassRegistry::init('ServiceGroup');
        $ServicewiseGroup = $ServiceGroup->find('list', array('fields' => array('servicecode', 'group_code'),"conditions"=>array("active"=>'Y')));
        $Dailysale = ClassRegistry::init('SchemeDailysale');
        $Dailysale->virtualFields = array('totmrp' => 'sum(mrp)');
        $CurrWeakRawData = $Dailysale->find('all', array('fields'=>array('*'), 'conditions' => array('DATE_FORMAT(saledate,"%v")' => $curweak, 'ttype' => 'To Sales', 'db_serno <>' => '0'), 'group' => array('servicecode', 'countercode')));
        $ServiceGroupData = array();
        foreach ($CurrWeakRawData as $row) {
            $row = $row['SchemeDailysale'];
            $group = isset($ServicewiseGroup[$row['servicecode']]) ? $ServicewiseGroup[$row['servicecode']] : '';
	    if(trim($group)==""){
		continue;
	    }
            $ServiceGroupData[$row['countercode']][$group] = (isset($ServiceGroupData[$row['countercode']][$group]) ? $ServiceGroupData[$row['countercode']][$group] : 0) + $row['totmrp'];
            $ServiceGroupData[$row['countercode']]['agentcode'] = $row['agentcode'];
            $ServiceGroupData[$row['countercode']]['db_serno'] = $row['db_serno'];
        }
        foreach ($ServiceGroupData as $countercode => $row1) {
            $Data = array('db_serno' => $row1['db_serno'], 'countercode' => $countercode, 'agentcode' => $row1['agentcode'], 'yearofweak' => $curyearofweak, 'groupcode' => 0, 'basemrp' => 0, 'curmrp' => 0);
            unset($row1['db_serno']);
            unset($row1['agentcode']);
           
//            if (isset($baseData[$countercode])) {
//                $Data['basemrp'] = $baseData[$countercode];
//            }
            foreach ($row1 as $groupcode => $totalsale) {                
                $Data['groupcode'] = $groupcode;
                $Data['basemrp']=  isset($baseData[$countercode.",".$groupcode])?$baseData[$countercode.",".$groupcode]:0;
                $Data['curmrp'] = $totalsale;
          //      cakeLog::write("DDDDD",var_export($Data,true));                
            	$this->save($Data);
            }
        }
    }

    public function CalculateWeaklyRun($ofday) {
        $BonusRun = array();
        $prevday = DateMethod::AddDate(Date("Y-m-d", strtotime($ofday)), 1);
        if (date("W", strtotime($prevday)) == date("W", strtotime($ofday))) {
            return $BonusRun;
        }
        $curyearofweak = date("Y-W", strtotime($ofday));
        $Data = $this->find("all", array('conditions' => array('yearofweak' => $curyearofweak)));
        $SchemeGrowthMaster = ClassRegistry::init('SchemeGrowthMaster');
        $SchemeGrowthBonusRate = ClassRegistry::init('SchemeGrowthBonusRate');
        $SchemeServiceRunMaster = ClassRegistry::init('SchemeServiceRunMaster');
        foreach ($Data as $row) {
            $row = $row[$this->name];
            $freehit = 0;
            if ($row['basemrp'] < $row['curmrp']) {
                $Conditions = array('applicable_from <=' => $ofday);
                if ($row['basemrp'] > 0) {
                    $growthAmt = ($row['curmrp'] - $row['basemrp']);
                    $Conditions['growth <='] = ($growthAmt * 100) / $row['basemrp'];
                }
                $slab = $SchemeGrowthMaster->find("all", array('fields' => array('max(growth) AS slab'), 'conditions' => $Conditions, 'order' => 'entrydate desc', 'limit' => 1));
                if (!(is_null($slab[0][0]['slab']) || trim($slab[0][0]['slab']) == "")) {
                    $rundata = $SchemeServiceRunMaster->find("all", array('conditions' => array('group_code' => $row['groupcode'], 'applicable_from <=' => $ofday, 'applicable_to >=' => $ofday), 'order' => 'entrydate desc', 'limit' => 1));
                    $run = isset($rundata[0]['SchemeServiceRunMaster']['points']) ? ($rundata[0]['SchemeServiceRunMaster']['points'] * $row['curmrp'] / $rundata[0]['SchemeServiceRunMaster']['min_sale_amount']) : 0;
                    $bonusrate = $SchemeGrowthBonusRate->find("all", array('fields' => array('rate'), 'conditions' => array('group_code' => $row['groupcode'], 'applicable_date <=' => $ofday, 'slab' => $slab[0][0]['slab']), 'order' => 'entrydate desc', 'limit' => 1));
                    $freehit = isset($bonusrate[0]['SchemeGrowthBonusRate']['rate']) ? ($bonusrate[0]['SchemeGrowthBonusRate']['rate'] * $run / 100) : 0;
                }
            }
            $BonusRun[$row['countercode']] = (isset($BonusRun[$row['countercode']]) ? $BonusRun[$row['countercode']] : 0) + $freehit;
        }
        return $BonusRun;
    }

    public function CalculateSale() {
        $StartDate = '2016-03-21';
        $EndDate = '2016-04-07'; //$DataObj->EndDate;
        $fromdate = $StartDate;
        echo("StartDate:" . $fromdate);
        echo("EndDate:" . $EndDate);
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        while ($fromdate <= $EndDate) {
            $this->CalculateWeaklysSale($fromdate);
            $fromdate = DateMethod::AddDate(Date("Y-m-d", strtotime($fromdate)), 1);
        }
    }

}
