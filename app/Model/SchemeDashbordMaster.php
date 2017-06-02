<?php

/**
 * Description of SchemeDashbordMaster
 *
 * @author vinay
 *
 * Table Structure
 * 

  CREATE TABLE `scheme_dashbord_master` (
  `db_serno` int(11) NOT NULL DEFAULT '0',
  `agentcode` char(20) DEFAULT NULL,
  `agentcounter` char(20) DEFAULT NULL,
  `entrydate` date NOT NULL DEFAULT '0000-00-00',
  `openingrun` double NOT NULL DEFAULT '0',
  `currentrun` double NOT NULL DEFAULT '0',
  `weaklybonusrun` double NOT NULL DEFAULT '0',
  `staterank` SMALLINT UNSIGNED DEFAULT NULL,
  `allrank` MEDIUMINT UNSIGNED DEFAULT NULL,
  KEY `codeidx` (`agentcode`),
  KEY `dbserno` (`db_serno`)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

 * 
 */
App::import("Lib", "DateMethod");
App::import("Lib", "PwSpecialFunc");

class SchemeDashbordMaster extends AppModel {

    public $useTable = "scheme_dashbord_master";

    public function CalculateRuns($DataObj) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        if (isset($DataObj->array_merge["startdate"]) && isset($DataObj->array_merge["enddate"])) {
            if (!DateMethod::IsValidDate($DataObj->array_merge["startdate"], "Y-m-d"))
                throw new Exception("Invalid Parameter Received [Start Date]");
            if (!DateMethod::IsValidDate($DataObj->array_merge["enddate"], "Y-m-d"))
                throw new Exception("Invalid Parameter Received [Start Date]");
            $DataObj->StartDate = Date("Y-m-d", strtotime($DataObj->array_merge["startdate"]));
            $DataObj->EndDate = Date("Y-m-d", strtotime($DataObj->array_merge["enddate"]));
        }
        else {

            $DataObj->StartDate = $this->getMinStartdate();
            $DataObj->EndDate = DateMethod::AddDate(Date("Y-m-d"), -1);
        }

        if (DateMethod::GetDiffInDate($DataObj->StartDate, $DataObj->EndDate, "D") > 0)
            throw new Exception("Start Date Is Greater Than End Date");
        if (DateMethod::GetDiffInDate("2014-04-01", $DataObj->StartDate, "D") > 0)
            throw new Exception("From Date Should Not Be Greater Than 01-Appr-2014");
        if (DateMethod::GetDiffInDate($DataObj->StartDate, date("Y-m-d"), "D") >= 0)
            throw new Exception("Start Date Is Greater Than Or Equal To Current Date");
        $Dailysale = ClassRegistry::init('SchemeDailysale');
        $ServiceGroup = ClassRegistry::init('ServiceGroup');
        $ServiceRun = ClassRegistry::init('SchemeServiceRunMaster');
        $SchemeWeaklySale = ClassRegistry::init('SchemeWeaklySale');
        $SchemeDayBonus=  ClassRegistry::init('SchemeDayBonus');
        $ServicewiseGroup = $ServiceGroup->find('list', array('fields' => array('servicecode', 'group_code')));
        $StartDate = $DataObj->StartDate;
        $EndDate = $DataObj->EndDate;
        $fromdate = $StartDate;
        echo("StartDate:" . $fromdate);
        echo("EndDate:" . $EndDate);
        while ($fromdate <= $EndDate) {
            if ($this->hasAny(array('entrydate' => $fromdate))) {
                return;
            }
            //googli
              $DayBonus=$SchemeDayBonus->find('list',array("fields"=>array('group_code','bonus_percent'),'conditions'=>array('applicable_date'=>$fromdate)));
              $gogliflag = TRUE;
            if(count($DayBonus)<=0){
                $dayBonusServicewise=0;
                 $gogliflag = FALSE;
            }
            $Dailysale->virtualFields = array('totmrp' => 'sum(mrp)');
            $CurrDateRawData = $Dailysale->find('all', array('fields' => array('totmrp', 'servicecode', 'countercode', 'db_serno', 'agentcode'), 'conditions' => array('saledate' => $fromdate, 'ttype' => 'To Sales', 'db_serno <>' => 0), 'group' => array('servicecode', 'countercode')));
            $ServiceGroupData = array();
            foreach ($CurrDateRawData as $row) {
                $row = $row['SchemeDailysale'];
                $group = isset($ServicewiseGroup[$row['servicecode']]) ? $ServicewiseGroup[$row['servicecode']] : '';
                $ServiceGroupData[$row['countercode']][$group] = (isset($ServiceGroupData[$row['countercode']][$group]) ? $ServiceGroupData[$row['countercode']][$group] : 0) + $row['totmrp'];
                $ServiceGroupData[$row['countercode']]['agentcounter'] = $row['agentcode'];
                $ServiceGroupData[$row['countercode']]['db_serno'] = $row['db_serno'];
            }
            $ServicewiseBaseRun = $ServiceRun->GetGroupBaseRun($fromdate);
            $prevDate = DateMethod::AddDate(Date("Y-m-d", strtotime($fromdate)), -1);
            $this->setSource("scheme_dashbord_master");
            $prevDayData = $this->find('all', array('fields'=>array('*'),'conditions' => array('entrydate' => $prevDate)));
            $prevDayData = Set::combine($prevDayData, '{n}.' . $this->name . '.agentcode', '{n}.' . $this->name);
            $WeaklyBonusrun = $SchemeWeaklySale->CalculateWeaklyRun($fromdate);
            foreach ($ServiceGroupData as $agentcode => $row1) {
                $Data = array('db_serno' => $row1['db_serno'], 'agentcode' => $agentcode, 'agentcounter' => $row1['agentcounter'], 'entrydate' => $fromdate, 'openingrun' => 0, 'weaklybonusrun' => 0, 'currentrun' => 0,'googli'=>0 );
                unset($row1['db_serno']);
                unset($row1['agentcounter']);
                if (isset($prevDayData[$agentcode])) {
                    $Data['openingrun'] = $prevDayData[$agentcode]['openingrun'] + $prevDayData[$agentcode]['currentrun']+$prevDayData[$agentcode]['googli'];
                    $Data['weaklybonusrun'] = $prevDayData[$agentcode]['weaklybonusrun'];
                    if (isset($WeaklyBonusrun[$agentcode])) {
                        $Data['openingrun'] += $prevDayData[$agentcode]['weaklybonusrun'];
                        $Data['weaklybonusrun'] = $WeaklyBonusrun[$agentcode];
                    }
                    unset($prevDayData[$agentcode]);
                }
                //Calculate Current Run
                foreach ($ServicewiseBaseRun as $service) {
                    $service = $service['SchemeServiceRunMaster'];
                    if (!isset($row1[$service['group_code']])) {
                        continue;
                    }
                    //googli
                   if($gogliflag){
                       $dayBonusServicewise=isset($DayBonus[$service['group_code']])?$DayBonus[$service['group_code']]:0;
                       
                    }
                    $Data['currentrun'] += ($row1[$service['group_code']] * $service['points']) / $service['min_sale_amount'];
		    $Data['googli']+=((($row1[$service['group_code']] * $service['points']) / $service['min_sale_amount'])*$dayBonusServicewise)/100;
                }
                $this->save($Data);
            }
            foreach ($prevDayData as $agentcode => $row1) {
                $Data = array('db_serno' => $row1['db_serno'], 'agentcode' => $agentcode, 'agentcounter' => $row1['agentcounter'], 'entrydate' => $fromdate, 'openingrun' => ($row1['openingrun'] + $row1['currentrun']+$row1['googli']), 'weaklybonusrun' => $row1['weaklybonusrun'], 'currentrun' => 0,'googli'=>0);
                if (isset($WeaklyBonusrun[$agentcode])) {
                    $Data['openingrun'] += $row1['weaklybonusrun'];
                    $Data['weaklybonusrun'] = $WeaklyBonusrun[$agentcode];
                }
                $this->save($Data);
            }
            $AgtList = array($agentcode);
            $fromdate = DateMethod::AddDate(Date("Y-m-d", strtotime($fromdate)), 1);
        }
    }

    private function getMinStartdate($cond = array()) {
        $this->setSource("scheme_dashbord_master");
        $res = $this->find("all", array('fields' => array('Max(entrydate) as dt'), 'conditions' => $cond));
        if (count($res) > 0) {
            $res = PwSpecialFunc::getDirectResArray($res);
            if (trim($res[0]['dt']) != '')
                return DateMethod::AddDate($res[0]['dt'], 1);
        }
        //List($startdate, $startdate1) = PwSpecialFunc::getFinYearRange();
        //return $startdate;
	return '2016-08-01';
    }

    public function SetRanking($DataObj) {
        if (isset($DataObj->array_merge["startdate"]) && isset($DataObj->array_merge["enddate"])) {
            if (!DateMethod::IsValidDate($DataObj->array_merge["startdate"], "Y-m-d"))
                throw new Exception("Invalid Parameter Received [Start Date]");
            if (!DateMethod::IsValidDate($DataObj->array_merge["enddate"], "Y-m-d"))
                throw new Exception("Invalid Parameter Received [Start Date]");
            $DataObj->StartDate = Date("Y-m-d", strtotime($DataObj->array_merge["startdate"]));
            $DataObj->EndDate = Date("Y-m-d", strtotime($DataObj->array_merge["enddate"]));
        }
        else {

            $DataObj->StartDate = $this->getMinStartdate(array('staterank <>' => NULL));
            $DataObj->EndDate = DateMethod::AddDate(Date("Y-m-d"), -1);
        }

        if (DateMethod::GetDiffInDate($DataObj->StartDate, $DataObj->EndDate, "D") > 0)
            throw new Exception("Start Date Is Greater Than End Date");
        if (DateMethod::GetDiffInDate("2014-04-01", $DataObj->StartDate, "D") > 0)
            throw new Exception("From Date Should Not Be Greater Than 01-Appr-2014");
        if (DateMethod::GetDiffInDate($DataObj->StartDate, date("Y-m-d"), "D") >= 0)
            throw new Exception("Start Date Is Greater Than Or Equal To Current Date");
        $StartDate = $DataObj->StartDate;
        $EndDate = $DataObj->EndDate;
        $fromdate = $StartDate;
        echo("StartDate:" . $fromdate);
        echo("EndDate:" . $EndDate);
        while ($fromdate <= $EndDate) {
            if ($this->hasAny(array('entrydate' => $fromdate, 'staterank <>' => NULL))) {
                return;
            }
            $this->virtualFields = array('totalpoint' => 'openingrun + currentrun + weaklybonusrun+googli');
            $Data = $this->find('all', array('fields' => array('agentcode', 'totalpoint', 'db_serno'), 'conditions' => array('entrydate' => $fromdate, 'db_serno <>' => 0), 'order' => 'totalpoint desc'));
            $dbarr = array();
            foreach ($Data as $key => $row) {
                $row = $row[$this->name];
                $cond = array('agentcode' => $row['agentcode'], 'db_serno' => $row['db_serno'], 'entrydate' => $fromdate);
                $this->updateAll(array('allrank' => ($key + 1)), $cond);
                $dbarr[$row['db_serno']] = $row['db_serno'];
            }
            foreach ($dbarr as $dbserno) {
                $Data = $this->find('all', array('fields' => array('agentcode', 'totalpoint', 'db_serno'), 'conditions' => array('entrydate' => $fromdate, 'db_serno' => $dbserno), 'order' => 'totalpoint desc'));
                $dbarr = array();
                foreach ($Data as $key => $row) {
                    $row = $row[$this->name];
                    $cond = array('agentcode' => $row['agentcode'], 'db_serno' => $row['db_serno'], 'entrydate' => $fromdate);
                    $this->updateAll(array('staterank' => ($key + 1)), $cond);
                }
            }
            $fromdate = DateMethod::AddDate(Date("Y-m-d", strtotime($fromdate)), 1);
        }
    }

    public function CalculatePwGoRuns($DataObj) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        if (isset($DataObj->array_merge["startdate"]) && isset($DataObj->array_merge["enddate"])) {
            if (!DateMethod::IsValidDate($DataObj->array_merge["startdate"], "Y-m-d"))
                throw new Exception("Invalid Parameter Received [Start Date]");
            if (!DateMethod::IsValidDate($DataObj->array_merge["enddate"], "Y-m-d"))
                throw new Exception("Invalid Parameter Received [Start Date]");
            $DataObj->StartDate = Date("Y-m-d", strtotime($DataObj->array_merge["startdate"]));
            $DataObj->EndDate = Date("Y-m-d", strtotime($DataObj->array_merge["enddate"]));
        }
        else {
            $DataObj->StartDate = $this->getMinStartdate(array('entrydate >='=>'2016-08-01'));
            $DataObj->EndDate = DateMethod::AddDate(Date("Y-m-d"), -1);
        }
        if (DateMethod::GetDiffInDate($DataObj->StartDate, $DataObj->EndDate, "D") > 0)
            throw new Exception("Start Date Is Greater Than End Date");
        if (DateMethod::GetDiffInDate("2014-04-01", $DataObj->StartDate, "D") > 0)
            throw new Exception("From Date Should Not Be Greater Than 01-Appr-2014");
        if (DateMethod::GetDiffInDate($DataObj->StartDate, date("Y-m-d"), "D") >= 0)
            throw new Exception("Start Date Is Greater Than Or Equal To Current Date");
        $Dailysale = ClassRegistry::init('SchemeDailysale');
        $ServiceGroup = ClassRegistry::init('ServiceGroup');
        $ServiceRun = ClassRegistry::init('SchemeServiceRunMaster');
        //$SchemeWeaklySale = ClassRegistry::init('SchemeWeaklySale');
        $SchemeDayBonus = ClassRegistry::init('SchemeDayBonus');
        $ServicewiseGroup = $ServiceGroup->find('list', array('fields' => array('servicecode', 'group_code')));
        $StartDate = $DataObj->StartDate;
        $EndDate = $DataObj->EndDate;
        $fromdate = $StartDate;
        echo("StartDate:" . $fromdate);
        echo("EndDate:" . $EndDate);
        while ($fromdate <= $EndDate) {
            if ($this->hasAny(array('entrydate' => $fromdate))) {
                return;
            }
            //googli
            $DayBonus = $SchemeDayBonus->find('list', array("fields" => array('group_code', 'bonus_percent'), 'conditions' => array('applicable_date' => $fromdate)));
            $gogliflag = TRUE;
            if (count($DayBonus) <= 0) {
                $dayBonusServicewise = 0;
                $gogliflag = FALSE;
            }
            $Dailysale->virtualFields = array('totmrp' => 'sum(mrp)');
            $CurrDateRawData = $Dailysale->find('all', array('fields' => array('totmrp', 'servicecode', 'countercode', 'db_serno', 'agentcode'), 'conditions' => array('saledate' => $fromdate, 'ttype' => 'To Sales', 'db_serno <>' => 0), 'group' => array('servicecode', 'countercode')));
            $ServiceGroupData = array();
            foreach ($CurrDateRawData as $row) {
                $row = $row['SchemeDailysale'];
                $group = isset($ServicewiseGroup[$row['servicecode']]) ? $ServicewiseGroup[$row['servicecode']] : '';
                $ServiceGroupData[$row['countercode']][$group] = (isset($ServiceGroupData[$row['countercode']][$group]) ? $ServiceGroupData[$row['countercode']][$group] : 0) + $row['totmrp'];
                $ServiceGroupData[$row['countercode']]['agentcounter'] = $row['agentcode'];
                $ServiceGroupData[$row['countercode']]['db_serno'] = $row['db_serno'];
            }
            $ServicewiseBaseRun = $ServiceRun->GetGroupBaseRun($fromdate);
            $prevDate = DateMethod::AddDate(Date("Y-m-d", strtotime($fromdate)), -1);
            $this->setSource("scheme_dashbord_master");
            $prevDayData = $this->find('all', array('fields' => array('*'), 'conditions' => array('entrydate' => $prevDate)));
            $prevDayData = Set::combine($prevDayData, '{n}.' . $this->name . '.agentcode', '{n}.' . $this->name);
            $WeaklyBonusrun = array(); //$SchemeWeaklySale->CalculateWeaklyRun($fromdate);
            foreach ($ServiceGroupData as $agentcode => $row1) {
                $Data = array('db_serno' => $row1['db_serno'], 'agentcode' => $agentcode, 'agentcounter' => $row1['agentcounter'], 'entrydate' => $fromdate, 'openingrun' => 0, 'currentrun' => 0, 'googli' => 0, 'basejackpoint' => 0, 'transpoint' => 0, 'jackpotpoint' => 0, 'baseluckypoint' => 0, 'luckypoint' => 0);//'openingrun' => ($row1['openingrun'] + $row1['currentrun']), 'basejackpoint' => ($row1['basejackpoint'] + $row1['transpoint'] + $row1['jackpotpoint'] + $row1['googli']), 'baseluckypoint' => ($row1['baseluckypoint'] + $row1['luckypoint']), 'currentrun' => 0, 'googli' => 0);
                //$Data = array('db_serno' => $row1['db_serno'], 'agentcode' => $agentcode, 'agentcounter' => $row1['agentcounter'], 'entrydate' => $fromdate, 'openingrun' => 0, 'weaklybonusrun' => 0, 'currentrun' => 0, 'googli' => 0);
                //unset($row1['db_serno']);
                //unset($row1['agentcounter']);
                if (isset($prevDayData[$agentcode])) {
                    $Data['openingrun'] = $prevDayData[$agentcode]['openingrun'] + $prevDayData[$agentcode]['currentrun'];
                    $Data['basejackpoint'] = $prevDayData[$agentcode]['basejackpoint'] + $prevDayData[$agentcode]['jackpotpoint']+$prevDayData[$agentcode]['googli'] + $prevDayData[$agentcode]['transpoint'];
                    $Data['baseluckypoint'] = $prevDayData[$agentcode]['baseluckypoint'] + $prevDayData[$agentcode]['luckypoint'];
                    //+ $prevDayData[$agentcode]['googli'];
                    //$Data['weaklybonusrun'] = $prevDayData[$agentcode]['weaklybonusrun'];
                    //if (isset($WeaklyBonusrun[$agentcode])) {
                    //    $Data['openingrun'] += $prevDayData[$agentcode]['weaklybonusrun'];
                    //    $Data['weaklybonusrun'] = $WeaklyBonusrun[$agentcode];
                    //}
                    unset($prevDayData[$agentcode]);
                }
                //Calculate Current Run
                foreach ($ServicewiseBaseRun as $service) {
                    $service = $service['SchemeServiceRunMaster'];
                    if (!isset($row1[$service['group_code']])) {
                        continue;
                    }
                    //googli
                    if ($gogliflag) {
                        $dayBonusServicewise = isset($DayBonus[$service['group_code']]) ? $DayBonus[$service['group_code']] : 0;
                    }
                    $Data['currentrun'] += ($row1[$service['group_code']] * $service['points']) / $service['min_sale_amount'];
                    $Data['googli']+=((($row1[$service['group_code']] * $service['points']) / $service['min_sale_amount']) * $dayBonusServicewise) / 100;
                }
                $this->save($Data);
            }
            foreach ($prevDayData as $agentcode => $row1) {
                $Data = array('db_serno' => $row1['db_serno'], 'agentcode' => $agentcode, 'agentcounter' => $row1['agentcounter'], 'entrydate' => $fromdate, 'openingrun' => ($row1['openingrun'] + $row1['currentrun']), 'basejackpoint' => ($row1['basejackpoint'] + $row1['transpoint'] + $row1['jackpotpoint'] + $row1['googli']), 'baseluckypoint' => ($row1['baseluckypoint'] + $row1['luckypoint']), 'currentrun' => 0, 'googli' => 0);
//                if (isset($WeaklyBonusrun[$agentcode])) {
//                    $Data['openingrun'] += $row1['weaklybonusrun'];
//                    $Data['weaklybonusrun'] = $WeaklyBonusrun[$agentcode];
//                }
                $this->save($Data);
            }
            //$AgtList = array($agentcode);
            $fromdate = DateMethod::AddDate(Date("Y-m-d", strtotime($fromdate)), 1);
        }
    }

}
