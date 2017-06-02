<?php

/**
 * Description of SchemeDailysale
 *
 * @author vinay
 * 
 * Table Structure
 * 

CREATE TABLE `scheme_dailysale_servicewise` (
  `db_serno` int(11) NOT NULL DEFAULT '0',
  `countercode` char(20) NOT NULL DEFAULT '',
  `agentcode` char(20) NOT NULL DEFAULT '',
  `servicecode` char(20) NOT NULL DEFAULT '',
  `ttype` char(20) NOT NULL DEFAULT '',
  `saledate` date NOT NULL DEFAULT '0000-00-00',
  `mrp` double NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

 * 
 */
App::import("Lib", "DateMethod");
App::import("Lib", "PwSpecialFunc");
App::import("Vendor", "class_pw_zipcash_rescodes");
App::import("Vendor", "class_pw_flexierecharge_rescodes");
App::import("Vendor", "class_pw_tatatelflexierecharge_rescodes");
App::import("Vendor", "class_pw_olrerecharge_rescodes");
App::import("Vendor", "class_pw_dtherecharge_rescodes");
App::import("Vendor", "class_pw_docomoerecharge_rescodes");
App::import("Vendor", "class_pw_acl_bill_rescodes");
App::import("Vendor", "pw_airbooking_rescodes");
App::import("Vendor", "class_pw_busbooking_rescodes");
App::import("Vendor", "class_pw_busbooking_tv_rescodes");
App::import("Vendor", "class_pw_gameepin_rescodes");
App::import("Vendor", "class_pw_bsnlerecharge_pyro_api_rescode");

class SchemeDailysale extends AppModel {

    public $useTable = "scheme_dailysale_servicewise";

    public function getSaleArr($DataObj) {
        error_reporting(E_ALL ^ E_WARNING);
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
		//$DataObj->StartDate = "2017-02-13";
        //    $DataObj->EndDate = "2017-02-13";
        
        if (DateMethod::GetDiffInDate($DataObj->StartDate, $DataObj->EndDate, "D") > 0)
            throw new Exception("Start Date Is Greater Than End Date");
        if (DateMethod::GetDiffInDate("2014-04-01", $DataObj->StartDate, "D") > 0)
            throw new Exception("From Date Should Not Be Greater Than 01-Appr-2014");
        if (DateMethod::GetDiffInDate($DataObj->StartDate, date("Y-m-d"), "D") >= 0)
            throw new Exception("Start Date Is Greater Than Or Equal To Current Date");

        App::import('Model', "DbMaster");
        $DbMaster = new DbMaster();
        App::import('Model', "Account");
        $Account = new Account();
        $DBArr = $DbMaster->getStateNameDbserno('PW', '1');
        foreach ($DBArr As $val) {
            $state = $val['state'];
            $brand = 'PW';
            if (trim($val['serno']) == 24)
                continue;
            if (!$Account->chkAccounts($state, $brand, $DataObj->StartDate, $DataObj->EndDate, "", ''))
                throw new Exception("#ERROR:Some Problem Occured In Accounts In State:" . $state . " And Brand:" . $brand);
        }
        $StartDate = $DataObj->StartDate;
        $EndDate = $DataObj->EndDate;

        $this->BlackListTable = array("bill_collection", "bsnlerecharge_sales");
        $fromdate = $StartDate;
        echo("StartDate:" . $fromdate);
        echo("StartDate:" . $EndDate);
        $this->cancellationProcess($DataObj->ErrArr, $DataObj->PwPlnarration, $fromdate, $EndDate, $DataObj->PwAirCalPurAmt);
        while ($fromdate <= $EndDate) {
            foreach ($DataObj->PwPlnarration->arrservice_idxservicecode as $servicecode => $SvcRow) {
                echo("<" . $SvcRow['service'] . ">");
                $Crini_conditions = array();
                $CreditFields = array();
                $fields = array();
                $ini_conditions = array();
                if (trim($SvcRow["commencementdate"]) == "0000-00-00")
                    continue;
                if (trim($servicecode) == "0") {
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "sum(denomination) as mrp", "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,sum(denomination - distributorrate),sum(denomination - counterpurrate)) as ret_comm");
                } elseif (trim($servicecode) == "0-2") {
                    $class_pw_zipcash_rescodes = new class_pw_zipcash_rescodes();
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , " if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , " if(db_serno=0,'API',agentcounter) as agentcounter"
                        , " count(*) as txn"
                        , " date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " sum(denomination) as mrp"
                        , " db_serno"
                        , " '0' as agt_tds"
                        , " '0' as ret_tds"
                        , " if(db_serno=0,'0', SUM(denomination*(agentcommrate - countercommrate)/100)) as agt_comm "
                        , " if(db_serno=0,sum(denomination * distributorcommrate/100), SUM(denomination*countercommrate/100)) as ret_comm");
                    $ini_conditions = array("(status NOT IN ('FAILED'," . $class_pw_zipcash_rescodes->zipcash_get_statusrescodes_dbstr('FAILED') . ") OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(denomination) as mrp"
                        , "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*SUM(denomination*(agentcommrate - countercommrate)/100)) as agt_comm "
                        , "if(db_serno=0,(-1)*sum(denomination * distributorcommrate/100),(-1)*SUM(denomination*countercommrate/100)) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif (trim($servicecode) == "0-9") {
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "sum(denomination) as mrp", "db_serno", " '0' as agt_tds", " '0' as ret_tds"
                        , "sum(counterpurrate - agentpurrate) as agt_comm "
                        , "SUM(denomination - counterpurrate) as ret_comm");
                    $ini_conditions = array(" (status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d')))");

                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(denomination) as mrp"
                        , "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "(-1)*sum(counterpurrate - agentpurrate) as agt_comm "
                        , "(-1)*SUM(denomination - counterpurrate) as ret_comm");

                    $Crini_conditions = array("status" => "FAILED");
                } elseif (trim($servicecode) == "1") {
                    $class_pw_flexierecharge_rescodes = new class_pw_flexierecharge_rescodes();
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1", "sum(recharge) as mrp", "db_serno"
                        , "'0' as agt_tds", " '0' as ret_tds"
                        , "if(db_serno=0,'0',SUM(recharge*(agentcommrate-countercommrate)/100)) as agt_comm "
                        , "if(db_serno=0,sum(recharge *distributorcommrate/100),SUM(recharge*countercommrate/100)) as ret_comm");
                    $ini_conditions = array("(transrescode NOT IN ('WV','CE','TE','IE','RP','MU','NV','FAILED'," . $this->getsqlqueristring($class_pw_flexierecharge_rescodes->fer_getrescodes_dbstr('FAILED')) . ") OR (transrescode='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d'))) ");
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(recharge) as mrp", "db_serno", " '0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*SUM(recharge*(agentcommrate-countercommrate)/100)) as agt_comm "
                        , "if(db_serno=0,(-1)*sum(recharge *distributorcommrate/100),(-1)*SUM(recharge*countercommrate/100)) as ret_comm"
                    );
                    $Crini_conditions = array("transrescode" => "FAILED");
                } elseif (trim($servicecode) == "1-1") {
                    $class_pw_tatatelflexierecharge_rescodes = new class_pw_tatatelflexierecharge_rescodes();
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0, dstbtr_no, counter_code) as counter_code1"
                        , "if(db_serno=0, 'API', agentcounter) as agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "sum(recharge) as mrp", "db_serno", " '0' as agt_tds", " '0' as ret_tds"
                        , "if(db_serno=0,'0',SUM(recharge*(agentcommrate-countercommrate)/100)) as agt_comm "
                        , "if(db_serno=0,sum(recharge *distributorcommrate/100),SUM(recharge*countercommrate/100)) as ret_comm");
                    $ini_conditions = array("(transrescode NOT IN ('FAILED'," . $this->getsqlqueristring($class_pw_tatatelflexierecharge_rescodes->tatatel_fer_getrescodes_dbstr('FAILED')) . ") OR (transrescode='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d'))) ");
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "sum(recharge) as mrp", "db_serno"
                        , "'0' as agt_tds", " '0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*SUM(recharge*(agentcommrate-countercommrate)/100)) as agt_comm "
                        , "if(db_serno=0,(-1)*sum(recharge *distributorcommrate/100),(-1)*SUM(recharge*countercommrate/100)) as ret_comm"
                    );
                    $Crini_conditions = array("transrescode" => "FAILED");
                } elseif (trim($servicecode) == "1-3") {
                    $class_pw_olrerecharge_rescodes = new class_pw_olrerecharge_rescodes();
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')as saledate1"
                        , "sum(denomination) as mrp", "db_serno", " '0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,SUM(denomination - distributorrate),SUM(denomination-counterpurrate)) as ret_comm");
                    $ini_conditions = array(" (transrescode NOT IN ('FAILED'," . $this->getsqlqueristring($class_pw_olrerecharge_rescodes->olr_getrescodes('FAILED')) . ") OR (transrescode='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d')))");
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(denomination) as mrp", "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,(-1)*SUM(denomination - distributorrate),(-1)*SUM(denomination-counterpurrate)) as ret_comm"
                    );
                    $Crini_conditions = array("transrescode" => "FAILED");
                } elseif (trim($servicecode) == "1-5") {
                    ///To Be Continue
                    $class_pw_dtherecharge_rescodes = new class_pw_dtherecharge_rescodes();
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "if(db_serno=0, dstbtr_no, counter_code) as counter_code1"
                        , "if(db_serno=0, 'API', agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "sum(denomination) as mrp", "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,SUM(denomination - distributorrate),SUM(denomination-counterpurrate)) as ret_comm");
                    $ini_conditions = array(" (transrescode NOT IN ('FAILED'," . $this->getsqlqueristring($class_pw_dtherecharge_rescodes->dthflexi_get_rescodes('FAILED')) . ") OR (transrescode='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d'))) ");
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(denomination) as mrp"
                        , "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,(-1)*SUM(denomination - distributorrate),(-1)*SUM(denomination-counterpurrate)) as ret_comm"
                    );
                    $Crini_conditions = array("transrescode" => "FAILED");
                } elseif (trim($servicecode) == "1-6") {
                    $fields = array(
                        "'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "sum(recharge) as mrp"
                        , "db_serno", " '0' as agt_tds", " '0' as ret_tds"
                        , "if(db_serno=0,'0',SUM(recharge*(agentcommrate-countercommrate)/100)) as agt_comm "
                        , "if(db_serno=0,sum(recharge *distributorcommrate/100),SUM(recharge*countercommrate/100)) as ret_comm");
                    $ini_conditions = array(" (status<>'FAILED' OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d'))) ");

                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(recharge) as mrp"
                        , "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*SUM(recharge*(agentcommrate-countercommrate)/100)) as agt_comm "
                        , "if(db_serno=0,(-1)*sum(recharge *distributorcommrate/100),(-1)*SUM(recharge*countercommrate/100)) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif (trim($servicecode) == "1-7") {
                    $class_pw_docomoerecharge_rescodes = new class_pw_docomoerecharge_rescodes();
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1", "sum(denomination) as mrp", "db_serno"
                        , "'0' as agt_tds", " '0' as ret_tds"
                        , "if(db_serno=0,'0',sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,SUM(denomination - distributorrate),SUM(denomination-counterpurrate)) as ret_comm");
                    $ini_conditions = array(" (transrescode NOT IN ('FAILED'," . $this->getsqlqueristring($class_pw_docomoerecharge_rescodes->docomo_fer_getrescodes_dbstr('FAILED')) . ") OR (transrescode='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d'))) ");
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(denomination) as mrp", "db_serno"
                        , "'0' as agt_tds", " '0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,(-1)*SUM(denomination - distributorrate),(-1)*SUM(denomination-counterpurrate)) as ret_comm"
                    );
                    $Crini_conditions = array("transrescode" => "FAILED");
                } elseif (strstr("-" . $servicecode, "-1-")) {
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1", "sum(denomination) as mrp", "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,SUM(denomination - distributorrate), SUM(denomination-counterpurrate)) as ret_comm");
                    $ini_conditions = array(" (status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d'))) ");
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(denomination) as mrp"
                        , "db_serno"
                        , "'0' as agt_tds", " '0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,(-1)*SUM(denomination - distributorrate),(-1)*SUM(denomination-counterpurrate)) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif (trim($servicecode) == "3") {
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1", "agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "SUM(if(mincommamt_type='Included',amount,amount+charges)) as mrp"
                        , "db_serno"
                        , "SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*(agentcommrate-countercommrate)*(agttdsrate/100)) as agt_tds"
                        , "SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*countercommrate*(tdsrate/100))  as ret_tds"
                        , "SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*(agentcommrate-countercommrate)) as agt_comm "
                        , "SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*countercommrate) as ret_comm"
                    );
                    $ini_conditions = array(" (transstatus NOT IN ('FAILED') OR (transstatus='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))", "cocode" => $SvcRow["cocode"]);
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*SUM(if(mincommamt_type='Included',amount,amount+charges)) as mrp"
                        , "db_serno"
                        , "(-1)*SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*(agentcommrate-countercommrate)*(cragttdsrate/100)) as agt_tds"
                        , "(-1)*SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*countercommrate*(crtdsrate/100))  as ret_tds"
                        , "(-1)*SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*(agentcommrate-countercommrate)) as agt_comm "
                        , "(-1)*SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*countercommrate) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED", "cocode" => $SvcRow["cocode"]);
                } elseif (trim($servicecode) == "3-8") {
                    $class_pw_acl_bill_rescodes = new class_pw_acl_bill_rescodes();
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(bill_amt) as mrp", "db_serno"
                        , " SUM((totcommamt-(totcommamt*(servicetaxrate/100)))*((agentcommrate-countercommrate)/100)*(agttdsrate/100))  as agt_tds"
                        , " SUM((totcommamt-(totcommamt*(servicetaxrate/100)))*(countercommrate/100)*(tdsrate/100)) as ret_tds"
                        , " SUM((totcommamt-(totcommamt*(servicetaxrate/100)))*((agentcommrate-countercommrate)/100))as agt_comm "
                        , " SUM((totcommamt-(totcommamt*(servicetaxrate/100)))*(countercommrate/100))as ret_comm"
                    );
                    $ini_conditions = array("  (transrescode NOT IN ('FAILED'," . $this->getsqlqueristring($class_pw_acl_bill_rescodes->aclbill_getrescodes_dbstr('FAILED')) . ") OR (transrescode='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))");

                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1", "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(bill_amt) as mrp", "db_serno"
                        , " (-1)*SUM((totcommamt-(totcommamt*(servicetaxrate/100)))*((agentcommrate-countercommrate)/100)*(cragttdsrate/100))  as agt_tds"
                        , " (-1)*SUM((totcommamt-(totcommamt*(servicetaxrate/100)))*(countercommrate/100)*(crtdsrate/100)) as ret_tds"
                        , " (-1)*SUM((totcommamt-(totcommamt*(servicetaxrate/100)))*((agentcommrate-countercommrate)/100))as agt_comm "
                        , " (-1)*SUM((totcommamt-(totcommamt*(servicetaxrate/100)))*(countercommrate/100))as ret_comm"
                    );
                    $Crini_conditions = array("transrescode" => "FAILED");
                } else if (strstr("-" . $servicecode, "-3-")) {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(if(mincommamt_type='Included',amount,amount+charges)) as mrp", "db_serno"
                        , " SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*(agentcommrate-countercommrate)*(agttdsrate/100)) as agt_tds"
                        , " SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*countercommrate*(tdsrate/100)) as ret_tds"
                        , " SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*(agentcommrate-countercommrate)) as agt_comm "
                        , " SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*countercommrate) as ret_comm"
                    );
                    $ini_conditions = array(" (transstatus NOT IN ('FAILED') OR (transstatus='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d'))) ", "cocode" => $SvcRow["cocode"]);

                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter", "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(if(mincommamt_type='Included',amount,amount+charges)) as mrp", "db_serno"
                        , " (-1)*SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*(agentcommrate-countercommrate)*(cragttdsrate/100)) as agt_tds"
                        , " (-1)*SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*countercommrate*(crtdsrate/100)) as ret_tds"
                        , " (-1)*SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*(agentcommrate-countercommrate)) as agt_comm "
                        , " (-1)*SUM((charges-(charges*(servicetaxrate/(100+servicetaxrate))))*countercommrate) as ret_comm"
                    );
                    $Crini_conditions = array("transstatus" => "FAILED", "cocode" => $SvcRow["cocode"]);
                } else if (trim($servicecode) == "4" || strstr("-" . $servicecode, "-4-")) {
                    $this->AirCalc($DataObj, $servicecode, $fromdate, $fromdate, $SvcRow, $DataObj->PwAirCalPurAmt);
                    continue;
                } elseif ($servicecode == "5" || $servicecode == "5-34" || $servicecode == "5-40") {
                    $class_pw_busbooking_rescodes = new class_pw_busbooking_rescodes();
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(totalfare) as mrp"
                        , " db_serno"
                        , " SUM((totalfare*(agentcommrate-countercommrate)/100)*agttdsrate/100) as agt_tds"
                        , " SUM((totalfare*countercommrate/100)*tdsrate/100) as ret_tds"
                        , " SUM(totalfare*(agentcommrate-countercommrate)/100) as agt_comm "
                        , " SUM(totalfare*countercommrate/100) as ret_comm"
                    );
                    $ini_conditions = array(" (co_transstatus NOT IN ('FAILED'," . $class_pw_busbooking_rescodes->busbooking_getrescodes_dbstr('FAILED') . ") OR (co_transstatus='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))", "co_code" => $SvcRow["cocode"]);

                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(totalfare) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((totalfare*(agentcommrate-countercommrate)/100)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM((totalfare*countercommrate/100)*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(totalfare*(agentcommrate-countercommrate)/100) as agt_comm "
                        , " (-1)*SUM(totalfare*countercommrate/100) as ret_comm"
                    );
                    $Crini_conditions = array("co_transstatus" => "FAILED", "co_code" => $SvcRow["cocode"]);
                } elseif ($servicecode == "5-4") {
                    $class_pw_busbooking_tv_rescodes = new class_pw_busbooking_tv_rescodes();
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(totalfare) as mrp"
                        , " db_serno"
                        , " SUM((totalfare*(agentcommrate-countercommrate)/100)*agttdsrate/100) as agt_tds"
                        , " SUM((totalfare*countercommrate/100)*tdsrate/100) as ret_tds"
                        , " SUM(totalfare*(agentcommrate-countercommrate)/100) as agt_comm "
                        , " SUM(totalfare*countercommrate/100) as ret_comm"
                    );
                    $ini_conditions = array(" (co_transstatus NOT IN ('FAILED'," . $class_pw_busbooking_tv_rescodes->busbooking_tv_getrescodes_dbstr('FAILED') . ") OR (co_transstatus='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d'))) ");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(totalfare) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((totalfare*(agentcommrate-countercommrate)/100)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM((totalfare*countercommrate/100)*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(totalfare*(agentcommrate-countercommrate)/100) as agt_comm "
                        , " (-1)*SUM(totalfare*countercommrate/100) as ret_comm"
                    );
                    $Crini_conditions = array("co_transstatus" => "FAILED");
                } elseif ($servicecode == "6") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter", "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(ticketamt+servicecharges+co_commamt+commamt+servicetax) as mrp"
                        , " db_serno"
                        , " SUM((agentcommamt-countercommamt)*agttdsrate/100) as agt_tds"
                        , " SUM(countercommamt*tdsrate/100) as ret_tds"
                        , " SUM(agentcommamt-countercommamt) as agt_comm "
                        , " SUM(countercommamt) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(totalfare+co_commamt+commamt+servicetax-pgcharges) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((agentcommamt-countercommamt)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM(countercommamt*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(agentcommamt-countercommamt) as agt_comm "
                        , " (-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "7") {
                    continue;
                } elseif ($servicecode == "8") {
                    $class_pw_bsnlerecharge_pyro_api_rescode = new class_pw_bsnlerecharge_pyro_api_rescode();
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(denomination)  as mrp"
                        , " db_serno"
                        , " '0' as agt_tds"
                        , " '0' as ret_tds"
                        , " if(db_serno=0,'0',SUM(counterpurrate-agentpurrate)) as agt_comm "
                        , " if(db_serno=0,SUM(denomination - distributorrate),SUM(denomination-counterpurrate)) as ret_comm"
                    );
                    $ini_conditions = array(" (transrescode NOT IN (" . $this->getsqlqueristring($class_pw_bsnlerecharge_pyro_api_rescode->bsnlsouth_get_rescodes("FAILED")) . ") OR (transrescode='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*SUM(denomination)  as mrp"
                        , "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*SUM(counterpurrate-agentpurrate)) as agt_comm "
                        , "if(db_serno=0,(-1)*SUM(denomination - distributorrate),(-1)*SUM(denomination-counterpurrate)) as ret_comm"
                    );
                    $Crini_conditions = array("transrescode" => "FAILED");
                } elseif ($servicecode == "9") {
                    continue;
                } elseif ($servicecode == "11") {
                    $class_pw_gameepin_rescodes = new class_pw_gameepin_rescodes();
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " sum(denomination) as mrp"
                        , " db_serno"
                        , " '0' as agt_tds"
                        , " '0' as ret_tds"
                        , " SUM(denomination*(agentcommrate-countercommrate)/100) as agt_comm "
                        , " SUM(denomination*countercommrate/100) as ret_comm"
                    );
                    $ini_conditions = array("(transrescode NOT IN ('FAILED'" . $this->getsqlqueristring($class_pw_gameepin_rescodes->zapak_getrescodes('FAILED')) . ") OR (transrescode='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(denomination) as mrp"
                        , " db_serno"
                        , " '0' as agt_tds"
                        , " '0' as ret_tds"
                        , " (-1)*SUM(denomination*(agentcommrate-countercommrate)/100) as agt_comm "
                        , " (-1)*SUM(denomination*countercommrate/100) as ret_comm"
                    );
                    $Crini_conditions = array("transrescode" => "FAILED");
                } elseif ($servicecode == "12") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(mrpamt) as mrp"
                        , " db_serno"
                        , " SUM((agentcommamt-countercommamt)*agttdsrate/100) as agt_tds"
                        , " SUM(countercommamt*tdsrate/100) as ret_tds"
                        , " SUM(agentcommamt-countercommamt) as agt_comm "
                        , " SUM(countercommamt) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(mrpamt) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((agentcommamt-countercommamt)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM(countercommamt*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(agentcommamt-countercommamt) as agt_comm "
                        , " (-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "13") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(premium_amt) as mrp"
                        , " db_serno"
                        , " SUM((counterpurrate-agentpurrate)*agttdsrate/100) as agt_tds"
                        , " SUM((premium_amt-counterpurrate)*tdsrate/100) as ret_tds"
                        , " SUM(counterpurrate-agentpurrate) as agt_comm "
                        , " SUM(premium_amt-counterpurrate) as ret_comm"
                    );
                    $ini_conditions = array();
                } elseif ($servicecode == "13-15") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(insurance_amt) as mrp"
                        , " db_serno"
                        , " SUM((counterpurrate-agentpurrate)*agttdsrate/100) as agt_tds"
                        , " SUM((insurance_amt-counterpurrate)*tdsrate/100) as ret_tds"
                        , " SUM(counterpurrate-agentpurrate) as agt_comm "
                        , " SUM(insurance_amt-counterpurrate) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d'))) ");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(insurance_amt) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((counterpurrate-agentpurrate)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM((insurance_amt-counterpurrate)*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(counterpurrate-agentpurrate) as agt_comm "
                        , " (-1)*SUM(insurance_amt-counterpurrate) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ((trim($servicecode) == "13-56") || (trim($servicecode) == "13-57") || (trim($servicecode) == "13-66")) { //FGI && Lib.V.I.
                    $cocode = trim($SvcRow["cocode"]);
                    $SvcRow["transdate_fieldname"] = "dateval";
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1", "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')as saledate1"
                        , "SUM(collected_prem) as mrp"
                        , "db_serno"
                        , "SUM((counterpurrate-agentpurrate)*agttdsrate/100) as agt_tds"
                        , "SUM((collected_prem-counterpurrate)*tdsrate/100) as ret_tds"
                        , "SUM(counterpurrate-agentpurrate) as agt_comm "
                        , "SUM(collected_prem-counterpurrate) as ret_comm"
                    );

                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d'))) ", "co_code" => $cocode);
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*SUM(collected_prem) as mrp"
                        , "db_serno"
                        , "(-1)*SUM((counterpurrate-agentpurrate)*cragttdsrate/100) as agt_tds"
                        , "(-1)*SUM((collected_prem-counterpurrate)*crtdsrate/100) as ret_tds"
                        , "(-1)*SUM(counterpurrate-agentpurrate) as agt_comm "
                        , "(-1)*SUM(collected_prem-counterpurrate) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED", "co_code" => $cocode);
                } elseif ($servicecode == "13-25") {
                    $SvcRow["transdate_fieldname"] = "dateval";
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1", "agentcounter", "count(*) as txn"
                        , " date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')as saledate1"
                        , " SUM(collected_prem) as mrp"
                        , " db_serno"
                        , " SUM((counterpurrate-agentpurrate)*agttdsrate/100) as agt_tds"
                        , " SUM((collected_prem-counterpurrate)*tdsrate/100) as ret_tds"
                        , " SUM(counterpurrate-agentpurrate)    as agt_comm "
                        , " SUM(collected_prem-counterpurrate)  as ret_comm"
                    );
                    $ini_conditions = array();
                } elseif ($servicecode == "14") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter", "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(totalfare) as mrp"
                        , " db_serno"
                        , " SUM((agentcommamt-countercommamt)*agttdsrate/100) as agt_tds"
                        , " SUM(countercommamt*tdsrate/100) as ret_tds"
                        , " SUM(agentcommamt-countercommamt)   as agt_comm "
                        , " SUM(countercommamt) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*SUM(totalfare) as mrp"
                        , "db_serno"
                        , "(-1)*SUM((agentcommamt-countercommamt)*cragttdsrate/100) as agt_tds"
                        , "(-1)*SUM(countercommamt*crtdsrate/100) as ret_tds"
                        , "(-1)*SUM(agentcommamt-countercommamt)   as agt_comm "
                        , "(-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "15") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter", "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "  SUM(netpayable) as mrp"
                        , " db_serno"
                        , " SUM((counterpurrate-agentpurrate)*agttdsrate/100) as agt_tds"
                        , " SUM((netpayable-counterpurrate)*tdsrate/100) as ret_tds"
                        , " SUM(counterpurrate-agentpurrate)   as agt_comm "
                        , " SUM(netpayable-counterpurrate) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(netpayable) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((counterpurrate-agentpurrate)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM((netpayable-counterpurrate)*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(counterpurrate-agentpurrate)   as agt_comm "
                        , " (-1)*SUM(netpayable-counterpurrate) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "16") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter", "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , "  SUM(amount) as mrp"
                        , " db_serno"
                        , " SUM((counterpurrate-agentpurrate)*agttdsrate/100) as agt_tds"
                        , " SUM((amount-counterpurrate)*tdsrate/100) as ret_tds"
                        , " SUM(counterpurrate-agentpurrate)   as agt_comm "
                        , " SUM(amount-counterpurrate) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1", "agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(amount) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((counterpurrate-agentpurrate)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM((amount-counterpurrate)*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(counterpurrate-agentpurrate)   as agt_comm "
                        , " (-1)*SUM(amount-counterpurrate) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "17") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')  as saledate1"
                        , " SUM(amount) as mrp"
                        , " db_serno"
                        , " SUM((agentcommamt-countercommamt)*agttdsrate/100) as agt_tds"
                        , " SUM(countercommamt*tdsrate/100) as ret_tds"
                        , " SUM(agentcommamt-countercommamt)   as agt_comm "
                        , " SUM(countercommamt) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(amount) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((agentcommamt-countercommamt)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM(countercommamt*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(agentcommamt-countercommamt)   as agt_comm "
                        , " (-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "18" || strstr("-" . $servicecode, "-18-")) {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(amount) as mrp"
                        , " db_serno"
                        , " SUM((agentcommamt-countercommamt)*agttdsrate/100) as agt_tds"
                        , " SUM(countercommamt*tdsrate/100) as ret_tds"
                        , " SUM(agentcommamt-countercommamt)   as agt_comm "
                        , " SUM(countercommamt) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))", "co_code" => $SvcRow["cocode"]);
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d')  as saledate1"
                        , " (-1)*SUM(amount) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((agentcommamt-countercommamt)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM(countercommamt*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(agentcommamt-countercommamt)   as agt_comm "
                        , " (-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED", "co_code" => $SvcRow["cocode"]);
                } elseif ($servicecode == "19") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(amount) as mrp"
                        , " db_serno"
                        , " SUM((counterpurrate-agentpurrate)*agttdsrate/100) as agt_tds"
                        , " SUM((amount-counterpurrate)*tdsrate/100) as ret_tds"
                        , " SUM(counterpurrate-agentpurrate)   as agt_comm "
                        , " SUM(amount-counterpurrate) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d')  as saledate1"
                        , " (-1)*SUM(amount) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((counterpurrate-agentpurrate)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM((amount-counterpurrate)*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(counterpurrate-agentpurrate)   as agt_comm "
                        , " (-1)*SUM(amount-counterpurrate) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "20") {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')as saledate1"
                        , " SUM(handling_charge) as mrp"
                        , " db_serno"
                        , " SUM((counterpurrate-agentpurrate)*agttdsrate/100) as agt_tds"
                        , " SUM((handling_charge-counterpurrate)*tdsrate/100) as ret_tds"
                        , " SUM(counterpurrate-agentpurrate)  as agt_comm "
                        , " SUM(handling_charge-counterpurrate)  as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(saledate,'%Y-%m-%d')))");
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter", "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(handling_charge) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((counterpurrate-agentpurrate)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM((handling_charge-counterpurrate)*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(counterpurrate-agentpurrate)  as agt_comm "
                        , " (-1)*SUM(handling_charge-counterpurrate)  as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "21") {
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1", "sum(denomination) as mrp", "db_serno"
                        , "'0' as agt_tds"
                        , "'0' as ret_tds"
                        , "if(db_serno=0,'0',sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,SUM(denomination - distributorrate), SUM(denomination-counterpurrate)) as ret_comm");
                    $ini_conditions = array(" (status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d'))) ");
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(denomination) as mrp"
                        , "db_serno"
                        , "'0' as agt_tds", " '0' as ret_tds"
                        , "if(db_serno=0,'0',(-1)*sum(counterpurrate - agentpurrate)) as agt_comm "
                        , "if(db_serno=0,(-1)*SUM(denomination - distributorrate),(-1)*SUM(denomination-counterpurrate)) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED");
                } elseif ($servicecode == "22") {
                    $fields = array("'To Cashout' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')  as saledate1"
                        , " SUM(amount) as cashout"
                        , " SUM(mdr_charges) as mdramt"
                        , " db_serno"
                        , " SUM((agentcommamt-countercommamt)*agttdsrate/100) as agt_tds"
                        , " SUM(countercommamt*tdsrate/100) as ret_tds"
                        , " SUM(agentcommamt-countercommamt)   as agt_comm "
                        , " SUM(countercommamt) as ret_comm"
                    );
                    $ini_conditions = array("status" => 'SUCCESS');
                    $CreditFields = array();
                    $Crini_conditions = array();
                }elseif ($servicecode == "23" || strstr("-" . $servicecode, "-23-")) {
                    $fields = array("'To Sales' as ttype", "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn", "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1", "sum(amount) as mrp", "db_serno"
                        , "SUM((agentcommrate - countercommrate)*agttdsrate/100) as agt_tds"
                        , "SUM(countercommrate*tdsrate/100) as ret_tds"
                        , "if(db_serno=0,'0',sum(agentcommrate - countercommrate)) as agt_comm "
                        , "if(db_serno=0,SUM(distributorcommrate), SUM(countercommrate)) as ret_comm");
                    $ini_conditions = array(" (status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d'))) ", "co_code" => $SvcRow["cocode"]);
                    $CreditFields = array("'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "if(db_serno=0,dstbtr_no,counter_code) as counter_code1"
                        , "if(db_serno=0,'API',agentcounter) as agentcounter"
                        , "count(*) as txn"
                        , "date_format(crdate,'%Y-%m-%d') as saledate1"
                        , "(-1)*sum(amount) as mrp"
                        , "db_serno"
                        , "SUM((agentcommrate - countercommrate)*cragttdsrate/100) as agt_tds"
                        , "SUM(countercommrate*crtdsrate/100) as ret_tds"
                        , "if(db_serno=0,'0',(-1)*sum(agentcommrate - countercommrate)) as agt_comm "
                        , "if(db_serno=0,(-1)*SUM(distributorcommrate),(-1)*SUM(countercommrate)) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED", "co_code" => $SvcRow["cocode"]);
                }elseif ($servicecode == "24" || strstr("-" . $servicecode, "-24-")) {
                    $fields = array("'To Sales' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter", "count(*) as txn"
                        , "date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d') as saledate1"
                        , " SUM(amount) as mrp"
                        , " db_serno"
                        , " SUM((agentcommamt-countercommamt)*agttdsrate/100) as agt_tds"
                        , " SUM(countercommamt*tdsrate/100) as ret_tds"
                        , " SUM(agentcommamt-countercommamt) as agt_comm "
                        , " SUM(countercommamt) as ret_comm"
                    );
                    $ini_conditions = array("(status NOT IN ('FAILED') OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(" . $SvcRow["transdate_fieldname"] . ",'%Y-%m-%d')))", "co_code" => $SvcRow["cocode"]);
                    $CreditFields = array(
                        "'To Credit' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "counter_code as counter_code1"
                        , "agentcounter"
                        , "count(*) as txn"
                        , " date_format(crdate,'%Y-%m-%d') as saledate1"
                        , " (-1)*SUM(amount) as mrp"
                        , " db_serno"
                        , " (-1)*SUM((agentcommamt-countercommamt)*cragttdsrate/100) as agt_tds"
                        , " (-1)*SUM(countercommamt*crtdsrate/100) as ret_tds"
                        , " (-1)*SUM(agentcommamt-countercommamt) as agt_comm "
                        , " (-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Crini_conditions = array("status" => "FAILED", "co_code" => $SvcRow["cocode"]);
                }else {
                    continue;
                }
                if (count($fields) <= 0) {
                    continue;
                }

                echo("<" . $SvcRow['service'] . ">");
                $result = $this->getaData($fromdate, $fromdate, $SvcRow, $ini_conditions, $fields, $Crini_conditions, $CreditFields);
                if (count($result[0]) > 0)
                    $this->ArrangeAndInsertData($result[0]);
                if (count($result[1]) > 0)
                    $this->ArrangeAndInsertData($result[1]);
            }
            $fromdate = DateMethod::AddDate(Date("Y-m-d", strtotime($fromdate)), 1);
        }
    }

    private function getaData($StartDate, $EndDate, $SvcRow, $ini_conditions, $fields, $Crini_conditions, $CreditFields) {
        $result = array();
        $CrResult = array();
        $TotalResult = array();
        $Saledate = $SvcRow["transdate_fieldname"];
        $tableArr = explode(" ", $SvcRow["transtable"]);

        foreach ($tableArr as $tablename) {
            if (in_array($tablename, $this->BlackListTable))
                continue;
            $TableArr1 = $this->getTablesArray($tablename, $StartDate, $EndDate);
            $conditions_1 = array($Saledate . " >= " => $StartDate, $Saledate . " < " => DateMethod::AddDate(Date("Y-m-d", strtotime($EndDate)), 1));
            $conditions = array_merge($ini_conditions, $conditions_1);
            $creditcondition = array(" date_format(crdate,'%Y-%m-%d')>date_format(" . $Saledate . ",'%Y-%m-%d')", "crdate >= " => $StartDate, "crdate < " => DateMethod::AddDate(Date("Y-m-d", strtotime($EndDate)), 1));
            $conditions = array_merge($conditions_1, $ini_conditions);
            $CrConditions = array_merge($creditcondition, $Crini_conditions);
            echo("<Getting Data>");
            foreach ($TableArr1 as $sourceTable) {
                $this->setSource($sourceTable);
                $rs = $this->find("all", array("conditions" => $conditions, "fields" => $fields, "group" => array("saledate1", "counter_code1")));
                $result = PwSpecialFunc::getDirectResArray($rs);
            }
            $this->setSource($tablename . "_rep");
            if (count($CreditFields) <= 0)
                continue;
            $rs1 = $this->find("all", array("conditions" => $CrConditions, "fields" => $CreditFields, "group" => array("saledate1", "counter_code1")));
            $CrResult = PwSpecialFunc::getDirectResArray($rs1);
        }
        $TotalResult[0] = $result;
        $TotalResult[1] = $CrResult;
		$this->setSource("scheme_dailysale_servicewise");
        return $TotalResult;
    }

    private function getMinStartdate() {
        $this->setSource("scheme_dailysale_servicewise");
        $res = $this->find("all", array('fields' => array('Max(saledate) as dt')));
        if (count($res) > 0) {
            $res = PwSpecialFunc::getDirectResArray($res);
            if (trim($res[0]['dt']) != '')
                return DateMethod::AddDate($res[0]['dt'], 1);
        }
        List($startdate, $startdate1) = PwSpecialFunc::getFinYearRange();
        return $startdate;
    }

    private function ArrangeAndInsertData($result) {
        $this->setSource("scheme_dailysale_servicewise");
        foreach ($result as $row) {
            $res = $this->find("all", array("conditions" => array("saledate" => $row["saledate1"], "servicecode" => $row["service"], "countercode" => trim($row["counter_code1"]), "ttype" => $row["ttype"]), 'limit' => 1));
            if (count($res) <= 0)
                $this->insertData($row);
            else
                $this->updateData($row);
        }
    }

    public function insertData($row) {
        $Data = array();
        $Data["db_serno"] = $row["db_serno"];
        $Data["countercode"] = $row["counter_code1"];
        $Data["agentcode"] = $row["agentcounter"];
        $Data["servicecode"] = $row["service"];
        $Data["ttype"] = $row["ttype"];
        $Data["saledate"] = $row["saledate1"];
        $Data["mrp"] = (isset($row["mrp"]) ? $row["mrp"] : 0) + (isset($row["cashout"]) ? $row["cashout"] : 0);
        $this->setSource("scheme_dailysale_servicewise");
        $this->create();
        $this->save($Data);
    }

    public function updateData($row) {
        $this->setSource("scheme_dailysale_servicewise");
        $Data["db_serno"] = $row["db_serno"];
        $Data["agentcode"] = "'" . $row["agentcounter"] . "'";
        $Data["mrp"] = (isset($row["mrp"]) ? $row["mrp"] : 0) + (isset($row["cashout"]) ? $row["cashout"] : 0);

        $conditions = array("saledate" => $row["saledate1"], "servicecode" => $row["service"], "countercode" => $row["counter_code1"], "ttype" => $row["ttype"]);
        $this->updateAll($Data, $conditions);
    }

    private function getsqlqueristring($str) {
        if (empty($str) || $str == "")
            return "";
        $arr = explode(",", $str);
        $str = '';
        foreach ($arr as $row)
            $str.= ",'" . $row . "'";
        return substr($str, 1);
    }

    private function AirCalc($DataObj, $servicecode, $StartDate, $EndDate, $SvcRow, $PwAirCalPurAmt) {
        $pw_airbooking_rescodes = new pw_airbooking_rescodes();
        $Saledate = $SvcRow["transdate_fieldname"];
        $tableArr = explode(" ", $SvcRow["transtable"]);
        foreach ($tableArr as $tablename) {
            if (in_array($tablename, $this->BlackListTable))
                continue;
            $result = array();
            $CrResult = array();
            $TableArr1 = $this->getTablesArray($tablename, $StartDate, $EndDate);
            $fields = array(
                "'" . $servicecode . "'  as service", "counter_code", "agentcounter", "firstflight_carrierid", " totbfare", " totfsur", "db_serno", "date_format(" . $Saledate . ",'%Y-%m-%d') as saledate"
                , " tottax", " bfarecommamt", " bfarecommcalc", " bfarecommrate", " counterbfarecommrate", " counterfsurcommrate"
                , " agentbfarecommrate", " agentfsurcommrate", " sf_bfarecommrate", " sf_fsurcommrate", " sf_counterbfarecommrate", " sf_counterfsurcommrate", " sf_agentbfarecommrate"
                , " sf_agentfsurcommrate", " if(co_apiversion=1, servicetax, (totbfare*servicetaxrate/100))servicetaxrate"
                , " (adults+children+infants) as noofpassengers", " fsurcommamt", " fsurcommcalc", " fsurcommrate", " sf_bfarecommamt"
                , " sf_bfarecommcalc", " sf_fsurcommamt", " sf_fsurcommcalc", " tdsrate"
                , " agttdsrate", " airlinetransfee", " othercharges", " counter_markupamt", " snd_markupamt"
            );
            $CrFields = array(
                "'" . $servicecode . "'  as service", "counter_code", "agentcounter", "firstflight_carrierid", " totbfare", " totfsur", "db_serno", "date_format(crdate,'%Y-%m-%d') as saledate"
                , " tottax", " bfarecommamt", " bfarecommcalc", " bfarecommrate", " counterbfarecommrate", " counterfsurcommrate"
                , " agentbfarecommrate", " agentfsurcommrate", " sf_bfarecommrate", " sf_fsurcommrate", " sf_counterbfarecommrate", " sf_counterfsurcommrate", " sf_agentbfarecommrate"
                , " sf_agentfsurcommrate", " if(co_apiversion=1, servicetax, (totbfare*servicetaxrate/100))servicetaxrate"
                , " (adults+children+infants) as noofpassengers", " fsurcommamt", " fsurcommcalc", " fsurcommrate", " sf_bfarecommamt"
                , " sf_bfarecommcalc", " sf_fsurcommamt", " sf_fsurcommcalc", " crtdsrate as tdsrate"
                , " cragttdsrate as agttdsrate", " airlinetransfee", " othercharges", " counter_markupamt", " snd_markupamt"
            );
            $conditions = array(" (status NOT IN ('FAILED'," . $pw_airbooking_rescodes->airbooking_getrescodes_dbstr('FAILED') . ") OR (status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d'))) "
                , "co_code" => $SvcRow["cocode"]
                , $Saledate . " >= " => $StartDate, $Saledate . " < " => DateMethod::AddDate(Date("Y-m-d", strtotime($EndDate)), 1));
            $CrConditions = array("status='FAILED' AND date_format(crdate,'%Y-%m-%d')>date_format(dateval,'%Y-%m-%d')"
                , "co_code" => $SvcRow["cocode"]
                , "crdate  >= " => $StartDate, "crdate < " => DateMethod::AddDate(Date("Y-m-d", strtotime($EndDate)), 1));

            foreach ($TableArr1 as $sourceTable) {
                $this->setSource($sourceTable);
                $rs = $this->indexedArray($this->find("all", array("conditions" => $conditions, "fields" => $fields, "order" => array("counter_code"))));
                $rs1 = $this->indexedArray($this->find("all", array("conditions" => $CrConditions, "fields" => $CrFields, "order" => array("counter_code"))));
                if (is_array($rs) && count($rs) > 0) {
                    $resultArr = $this->calculateAir($rs, $SvcRow, $PwAirCalPurAmt, "SALES");
                    if (is_array($rs) && count($resultArr) > 0) {
                        foreach ($resultArr as $key => $val1)
                            $result[] = $val1;
                    }
                }
            }
            $this->setSource($tablename . "_rep");
            $resultArr1 = $this->calculateAir($rs1, $SvcRow, $PwAirCalPurAmt, "CREDIT");
            if (is_array($rs1) && count($resultArr1) > 0) {
                foreach ($resultArr1 as $key => $val1)
                    $CrResult[] = $val1;
            }
        }
        if (count($result) <= 0) {
            //$DataObj->ErrArr[] = " Err In Air Calculaton Kindly Check It Again !!";
            return;
        }
        $this->ArrangeAndInsertData($result);
        if (count($CrResult) > 0)
            $this->ArrangeAndInsertData($CrResult);
    }

    private function calculateAir($rs, $SvcRow, $PwAirCalPurAmt, $transaction = "") {
        $resultArr = array();
        $CreditresultArr = array();
        foreach ($rs as $row) {
            $rowset = $row[$this->name];
            $counter_mrpamt = $PwAirCalPurAmt->calmrpamt($rowset["totbfare"], $rowset["totfsur"], $rowset["tottax"], $rowset["sf_bfarecommrate"]
                    , $rowset["sf_fsurcommrate"], $rowset["servicetaxrate"], $rowset["sf_bfarecommamt"]
                    , $rowset["sf_bfarecommcalc"], $rowset["sf_fsurcommamt"]
                    , $rowset["sf_fsurcommcalc"], $rowset["noofpassengers"]
                    , $rowset["airlinetransfee"], $rowset["othercharges"]
                    , $rowset["counter_markupamt"], $rowset["snd_markupamt"]);

            list($agent_tdsamt, $agent_commamt) = explode(',', $PwAirCalPurAmt->caltdscommamt($rowset["totbfare"]
                            , $rowset["totfsur"], $rowset["tottax"]
                            , $rowset["bfarecommamt"], $rowset["bfarecommcalc"]
                            , $rowset["bfarecommrate"], $rowset["agentbfarecommrate"]
                            , $rowset["agentfsurcommrate"], $rowset["sf_bfarecommrate"]
                            , $rowset["sf_fsurcommrate"], $rowset["sf_agentbfarecommrate"]
                            , $rowset["sf_agentfsurcommrate"], $rowset["servicetaxrate"], $rowset["noofpassengers"]
                            , $rowset["fsurcommamt"], $rowset["fsurcommcalc"], $rowset["fsurcommrate"]
                            , $rowset["sf_bfarecommamt"], $rowset["sf_bfarecommcalc"], $rowset["sf_fsurcommamt"], $rowset["sf_fsurcommcalc"]
                            , $rowset["agttdsrate"], $rowset["counter_markupamt"], $rowset["snd_markupamt"]));
            list($counter_tdsamt, $counter_commamt) = explode(',', $PwAirCalPurAmt->caltdscommamt($rowset["totbfare"]
                            , $rowset["totfsur"], $rowset["tottax"], $rowset["bfarecommamt"]
                            , $rowset["bfarecommcalc"], $rowset["bfarecommrate"], $rowset["counterbfarecommrate"]
                            , $rowset["counterfsurcommrate"], $rowset["sf_bfarecommrate"], $rowset["sf_fsurcommrate"], $rowset["sf_counterbfarecommrate"]
                            , $rowset["sf_counterfsurcommrate"], $rowset["servicetaxrate"], $rowset["noofpassengers"], $rowset["fsurcommamt"]
                            , $rowset["fsurcommcalc"], $rowset["fsurcommrate"], $rowset["sf_bfarecommamt"], $rowset["sf_bfarecommcalc"]
                            , $rowset["sf_fsurcommamt"], $rowset["sf_fsurcommcalc"], $rowset["tdsrate"]
                            , $rowset["counter_markupamt"], $rowset["snd_markupamt"]
            ));
            $agent_commamt = $agent_commamt - $counter_commamt;
            $agent_tdsamt = ($agent_commamt * $rowset["agttdsrate"] / 100);
            $key1 = trim($rowset["counter_code"]) . trim($rowset["saledate"]);
            if (strtoupper(trim($transaction)) == "SALES") {
                if (isset($resultArr[$key1])) {
                    $resultArr[$key1]["ttype"] = "To Sales";
                    $resultArr[$key1]["service"] = $rowset["service"];
                    $resultArr[$key1]["counter_code1"] = $rowset["counter_code"];
                    $resultArr[$key1]["agentcounter"] = $rowset["agentcounter"];
                    $resultArr[$key1]["txn"] = $resultArr[$key1]["txn"] + 1;
                    $resultArr[$key1]["saledate1"] = $rowset["saledate"];
                    $resultArr[$key1]["mrp"] = $resultArr[$key1]["mrp"] + $counter_mrpamt;
                    $resultArr[$key1]["db_serno"] = $rowset["db_serno"];
                    $resultArr[$key1]["agt_tds"] = $resultArr[$key1]["agt_tds"] + $agent_tdsamt;
                    $resultArr[$key1]["ret_tds"] = $resultArr[$key1]["ret_tds"] + $counter_tdsamt;
                    $resultArr[$key1]["agt_comm"] = $resultArr[$key1]["agt_comm"] + $agent_commamt;
                    $resultArr[$key1]["ret_comm"] = $resultArr[$key1]["ret_comm"] + $counter_commamt;
                } else {
                    $resultArr[$key1]["ttype"] = "To Sales";
                    $resultArr[$key1]["service"] = $rowset["service"];
                    $resultArr[$key1]["counter_code1"] = $rowset["counter_code"];
                    $resultArr[$key1]["agentcounter"] = $rowset["agentcounter"];
                    $resultArr[$key1]["txn"] = 1;
                    $resultArr[$key1]["saledate1"] = $rowset["saledate"];
                    $resultArr[$key1]["mrp"] = $counter_mrpamt;
                    $resultArr[$key1]["db_serno"] = $rowset["db_serno"];
                    $resultArr[$key1]["agt_tds"] = $agent_tdsamt;
                    $resultArr[$key1]["ret_tds"] = $counter_tdsamt;
                    $resultArr[$key1]["agt_comm"] = $agent_commamt;
                    $resultArr[$key1]["ret_comm"] = $counter_commamt;
                }
            } elseif (strtoupper(trim($transaction)) == "CREDIT") {
                if (isset($CreditresultArr[$key1])) {
                    $CreditresultArr[$key1]["ttype"] = "To Credit";
                    $CreditresultArr[$key1]["service"] = $rowset["service"];
                    $CreditresultArr[$key1]["counter_code1"] = $rowset["counter_code"];
                    $CreditresultArr[$key1]["agentcounter"] = $rowset["agentcounter"];
                    $CreditresultArr[$key1]["txn"] = $CreditresultArr[$key1]["txn"] + 1;
                    $CreditresultArr[$key1]["saledate1"] = $rowset["saledate"];
                    $CreditresultArr[$key1]["mrp"] = ($CreditresultArr[$key1]["mrp"]) + ((-1) * $counter_mrpamt);
                    $CreditresultArr[$key1]["db_serno"] = $rowset["db_serno"];
                    $CreditresultArr[$key1]["agt_tds"] = ($CreditresultArr[$key1]["agt_tds"]) + ((-1) * $agent_tdsamt);
                    $CreditresultArr[$key1]["ret_tds"] = ($CreditresultArr[$key1]["ret_tds"]) + ((-1) * $counter_tdsamt);
                    $CreditresultArr[$key1]["agt_comm"] = ($CreditresultArr[$key1]["agt_comm"]) + ((-1) * $agent_commamt);
                    $CreditresultArr[$key1]["ret_comm"] = ($CreditresultArr[$key1]["ret_comm"]) + ((-1) * $counter_commamt);
                } else {
                    $CreditresultArr[$key1]["ttype"] = "To Credit";
                    $CreditresultArr[$key1]["service"] = $rowset["service"];
                    $CreditresultArr[$key1]["counter_code1"] = $rowset["counter_code"];
                    $CreditresultArr[$key1]["agentcounter"] = $rowset["agentcounter"];
                    $CreditresultArr[$key1]["txn"] = 1;
                    $CreditresultArr[$key1]["saledate1"] = $rowset["saledate"];
                    $CreditresultArr[$key1]["mrp"] = (-1) * $counter_mrpamt;
                    $CreditresultArr[$key1]["db_serno"] = $rowset["db_serno"];
                    $CreditresultArr[$key1]["agt_tds"] = (-1) * $agent_tdsamt;
                    $CreditresultArr[$key1]["ret_tds"] = (-1) * $counter_tdsamt;
                    $CreditresultArr[$key1]["agt_comm"] = (-1) * $agent_commamt;
                    $CreditresultArr[$key1]["ret_comm"] = (-1) * $counter_commamt;
                }
            }
        }
        if (strtoupper(trim($transaction)) == "SALES")
            return $resultArr;
        elseif (strtoupper(trim($transaction)) == "CREDIT")
            return $CreditresultArr;
        else
            return array();
    }

    public function cancellationProcess(&$ErrArr, $PwPlnarration, $Fromdate, $EndDate, $PwAirCalPurAmt) {
        $Field = array();
        $Conditions = array();

        //while($Fromdate<=$EndDate)
        {
            foreach ($PwPlnarration->arrservice_idxservicecode as $servicecode => $SvcRow) {
                $sourcetable = "";
                if (trim($servicecode) == "5-4") {
                    $sourcetable = "bustravel_tv_canceldetail";
                    $class_pw_busbooking_tv_rescodes = new class_pw_busbooking_tv_rescodes();
                    $Field = array(
                        " 'To Cancel' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , " date_format(pldate,'%Y-%m-%d')as saledate1"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , " count(*) as txn"
                        , "db_serno"
                        , "(-1)*SUM(refundamt) As mrp"
                        , "(-1)*SUM((totalfare*(agentcommrate-countercommrate)/100)*agttdsrate/100) As agt_tds"
                        , "(-1)*SUM((totalfare*countercommrate/100)*tdsrate/100) AS ret_tds"
                        , "(-1)*SUM(totalfare*(agentcommrate-countercommrate)/100) As agt_comm"
                        , "(-1)*SUM(totalfare*countercommrate/100) as ret_comm"
                    );
                    $Conditions = array("status in ('SUCCESS'," . $class_pw_busbooking_tv_rescodes->busbooking_tv_getrescodes_dbstr('SUCCESS') . ")"
                        , "pldate >= " => $Fromdate
                        , "pldate < " => DateMethod::AddDate($EndDate, 1)
                    );
                } elseif (trim($servicecode) == "5" || trim($servicecode) == "5-34" || trim($servicecode) == "5-40") {
                    $sourcetable = "bustravel_canceldetail";
                    $class_pw_busbooking_rescodes = new class_pw_busbooking_rescodes();
                    $Field = array(
                        " 'To Cancel' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "date_format(pldate,'%Y-%m-%d')as saledate1"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , "count(*) as txn"
                        , "db_serno"
                        , "(-1)*SUM(refundamt) As mrp"
                        , "(-1)*SUM((totalfare*(agentcommrate-countercommrate)/100)*agttdsrate/100) As agt_tds"
                        , "(-1)*SUM((totalfare*countercommrate/100)*tdsrate/100) AS ret_tds"
                        , "(-1)*SUM(totalfare*(agentcommrate-countercommrate)/100) As agt_comm"
                        , "(-1)*SUM(totalfare*countercommrate/100) as ret_comm"
                    );
                    $arr = array_merge(array('SUCCESS'), explode(',', $class_pw_busbooking_rescodes->busbooking_getrescodes_dbstr('SUCCESS')));
                    $Conditions = array('status' => $arr
                        , "pldate >= " => $Fromdate
                        , "pldate < " => DateMethod::AddDate($EndDate, 1)
                        , "co_code" => $SvcRow["cocode"]
                    );
                } elseif (trim($servicecode) == "6") {
                    $this->IrctcCancelCal($servicecode, $Fromdate, $EndDate);
                    continue;
                } elseif (trim($servicecode) == "4" || strstr("-" . trim($servicecode), "-4-")) {
                    $this->airCancelCal($ErrArr, $servicecode, $Fromdate, $EndDate, $PwAirCalPurAmt, $SvcRow);
                    continue;
                } elseif (trim($servicecode) == "18" || strstr("-" . $servicecode, "-18-")) {
                    $sourcetable = "eshopping_collection_cncdetail";
                    $Field = array(
                        " 'To Cancel' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "date_format(dateval,'%Y-%m-%d')as saledate1"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , "count(*) as txn"
                        , "db_serno"
                        , "(-1)*SUM(amount-cnc_charge) As mrp"
                        , "(-1)*SUM((agentcommamt-countercommamt)*agttdsrate/100) As agt_tds"
                        , "(-1)*SUM(countercommamt*tdsrate/100) AS ret_tds"
                        , "(-1)*SUM(agentcommamt-countercommamt) As agt_comm"
                        , "(-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Conditions = array("status" => "SUCCESS"
                        , "dateval >= " => $Fromdate
                        , "dateval < " => DateMethod::AddDate($EndDate, 1)
                        , "co_code" => $SvcRow["cocode"]
                    );
                }elseif (trim($servicecode) == "24" || strstr("-" . $servicecode, "-24-")){
                    $sourcetable = "st_travelling_cncdetail";
                    $Field = array(
                        " 'To Cancel' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "date_format(dateval,'%Y-%m-%d')as saledate1"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , "count(*) as txn"
                        , "db_serno"
                        , "(-1)*SUM(amount-cnc_charge) As mrp"
                        , "(-1)*SUM((agentcommamt-countercommamt)*agttdsrate/100) As agt_tds"
                        , "(-1)*SUM(countercommamt*tdsrate/100) AS ret_tds"
                        , "(-1)*SUM(agentcommamt-countercommamt) As agt_comm"
                        , "(-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Conditions = array("status" => "SUCCESS"
                        , "dateval >= " => $Fromdate
                        , "dateval < " => DateMethod::AddDate($EndDate, 1)
                        , "co_code" => $SvcRow["cocode"]
                    );
                }
				elseif (trim($servicecode) == "14") {
                    $sourcetable = "irctctour_canceldetail";
                    $Field = array(
                        " 'To Cancel' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "IF(crdate<>'0000-00-00 00:00:00',date_format(crdate,'%Y-%m-%d'), date_format(dateval,'%Y-%m-%d'))as saledate1"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , "count(*) as txn"
                        , "db_serno"
                        , "(-1)*SUM(refundamt) As mrp"
                        , "'0' As agt_tds"
                        , "'0' AS ret_tds"
                        , "'0' As agt_comm"
                        , "'0' as ret_comm"
                    );
                    $Conditions = array("status" => "SUCCESS"
                        , "IF(crdate<>'0000-00-00 00:00:00',crdate>='" . $Fromdate . "' AND crdate<'" . DateMethod::AddDate($EndDate, 1) . "',dateval>='" . $Fromdate . "' AND dateval<'" . DateMethod::AddDate($EndDate, 1) . "')"
                    );
                } elseif (trim($servicecode) == "19") {
                    $sourcetable = "hotelavenue_canceldetail";
                    $Field = array(
                        " 'To Cancel' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "date_format(dateval,'%Y-%m-%d')as saledate1"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , "count(*) as txn"
                        , "db_serno"
                        , "(-1)*SUM(refundamt) As mrp"
                        , "((counterpurrate-agentpurrate)*agttdsrate/100) As agt_tds"
                        , "(-1)*SUM((amount-counterpurrate)*tdsrate/100) AS ret_tds"
                        , "(-1)*SUM(counterpurrate-agentpurrate) As agt_comm"
                        , "(-1)*SUM(amount-counterpurrate) as ret_comm"
                    );
                    $Conditions = array("status" => "SUCCESS"
                        , "dateval >= " => $Fromdate
                        , "dateval < " => DateMethod::AddDate($EndDate, 1)
                    );
                } elseif (trim($servicecode) == "17") {
                    $sourcetable = "yesbank_refund_details";
                    $Field = array(
                        " 'To Cancel' as ttype"
                        , "'" . $servicecode . "'  as service"
                        , "date_format(dateval,'%Y-%m-%d')as saledate1"
                        , "counter_code as counter_code1"
                        , "agentcounter as agentcounter"
                        , "count(*) as txn"
                        , "db_serno"
                        , "(-1)*SUM(refundamt+refundfee) As mrp"
                        , "(-1)*SUM((agentcommamt-countercommamt)*agttdsrate/100) As agt_tds"
                        , "(-1)*SUM(countercommamt*tdsrate/100) AS ret_tds"
                        , "(-1)*SUM(agentcommamt-countercommamt) As agt_comm"
                        , "(-1)*SUM(countercommamt) as ret_comm"
                    );
                    $Conditions = array("status" => "SUCCESS"
                        , "dateval >= " => $Fromdate
                        , "dateval < " => DateMethod::AddDate($EndDate, 1)
                    );
                } else
                    continue;
                if ($sourcetable == "")
                    continue;

                $this->setSource($sourcetable);
                $rs = $this->find("all", array("conditions" => $Conditions, "fields" => $Field, "group" => array("saledate1", "counter_code1")));
                if (count($rs) <= 0) {
                    //$ErrArr[] = "No Record Found  [ Date Range: ".$Fromdate." - ".$EndDate." ] Cancelation Service ".$SvcRow["service"]."[".$servicecode."]";
                    continue;
                }
                $result = PwSpecialFunc::getDirectResArray($rs);
                $this->ArrangeAndInsertData($result);
            }
            //$Fromdate	= DateMethod::AddDate(Date("Y-m-d",strtotime($Fromdate)), 1);
        }
    }

    private function IrctcCancelCal($servicecode, $Fromdate, $EndDate) {
        $field = array(
            " 'To Cancel' as ttype"
            , "'" . $servicecode . "'  as service"
            , "date_format(crdate,'%Y-%m-%d')as saledate1"
            , "counter_code as counter_code1"
            , "agentcounter as agentcounter"
            , "count(*) as txn"
            , "db_serno"
            , "(-1)*SUM(refundamt) As mrp "
            , "'0' As agt_tds"
            , "'0' AS ret_tds"
            , "'0' As agt_comm"
            , "'0' AS ret_comm"
        );
        $conditions = array("status" => '1'
            , "crdate >= " => $Fromdate
            , "crdate < " => DateMethod::AddDate($EndDate, 1)
        );
        $this->setSource("irctc_auto_canceldetail");
        $rs = $this->find("all", array("conditions" => $conditions, "fields" => $field
            , "group" => array("saledate1", "counter_code1")));

        $finalArr = array();
        if (count($rs) > 0) {
            $res = $this->indexedArray($rs);
            foreach ($res as $row) {
                $ky = trim($row[$this->name]["saledate1"]) . trim($row[$this->name]["counter_code1"]);
                $finalArr[$ky] = $row[$this->name];
            }
        }
        $field = array(
            " 'To Cancel' as ttype"
            , "'" . $servicecode . "'  as service"
            , "IF(crdate<>'0000-00-00 00:00:00',date_format(crdate,'%Y-%m-%d'),date_format(dateval,'%Y-%m-%d'))as saledate1"
            , "counter_code as counter_code1"
            , "agentcounter as agentcounter"
            , "count(*) as txn"
            , "db_serno"
            , "(-1)*SUM(refundamt) As mrp"
            , "'0' As agt_tds"
            , "'0' AS ret_tds"
            , "'0' As agt_comm"
            , "'0' AS ret_comm"
        );
        $conditions = array(
            "status" => 'SUCCESS'
            , "IF(crdate<>'0000-00-00 00:00:00',crdate >= '" . $Fromdate . "' AND crdate < '" . DateMethod::AddDate($EndDate, 1) . "',dateval >= '" . $Fromdate . "' AND dateval < '" . DateMethod::AddDate($EndDate, 1) . "')"
        );
        $this->setSource("irctc_canceldetail");
        $rs = $this->find("all", array("conditions" => $conditions
            , "fields" => $field
            , "group" => array("saledate1", "counter_code1")
                )
        );
        if (count($rs) > 0) {
            $res = $this->indexedArray($rs);
            foreach ($res as $row) {
                $ky = trim($row[$this->name]["saledate1"]) . trim($row[$this->name]["counter_code1"]);
                if (isset($finalArr[$ky])) {
                    $finalArr[$ky]["txn"] = $finalArr[$ky]["txn"] + $row[$this->name]["txn"];
                    $finalArr[$ky]["mrp"] = $finalArr[$ky]["mrp"] + $row[$this->name]["mrp"];
                } else {
                    $finalArr[$ky]["txn"] = $row[$this->name]["txn"];
                    $finalArr[$ky]["mrp"] = $row[$this->name]["mrp"];
                }
                $finalArr[$ky]["ttype"] = $row[$this->name]["ttype"];
                $finalArr[$ky]["service"] = $row[$this->name]["service"];
                $finalArr[$ky]["saledate1"] = $row[$this->name]["saledate1"];
                $finalArr[$ky]["counter_code1"] = $row[$this->name]["counter_code1"];
                $finalArr[$ky]["agentcounter"] = $row[$this->name]["agentcounter"];
                $finalArr[$ky]["db_serno"] = $row[$this->name]["db_serno"];
                $finalArr[$ky]["agt_tds"] = $row[$this->name]["agt_tds"];
                $finalArr[$ky]["ret_tds"] = $row[$this->name]["ret_tds"];
                $finalArr[$ky]["agt_comm"] = $row[$this->name]["agt_comm"];
                $finalArr[$ky]["ret_comm"] = $row[$this->name]["ret_comm"];
            }
        }
        if (count($finalArr) > 0)
            $this->ArrangeAndInsertData($finalArr);
    }

    private function airCancelCal(&$ErrArr, $servicecode, $Fromdate, $EndDate, $PwAirCalPurAmt, $SvcRow) {
        $Field = array("*");
        $conditions = array("status" => pw_airbooking_rescodes::$airbooking_processed_transstatus
            , "pldate >= " => $Fromdate
            , "pldate < " => DateMethod::AddDate(Date("Y-m-d", strtotime($EndDate)), 1)
            , "co_code" => $SvcRow["cocode"]
        );
        $this->setSource("airbooking_changecanceldetail");
        $rs = $this->indexedArray($this->find("all", array("conditions" => $conditions
                    , "fields" => $Field
                    , "ordeer" => "transno")
        ));
        $transno = "";
        $DataArr = array();
        foreach ($rs as $val) {
            $row = $val[$this->name];
            if ($transno != $row["transno"]) {
                $this->setSource("airbooking_sales");
                $res = $this->indexedArray($this->find("all", array("fields" => array("*")
                            , "conditions" => array("db_serno" => trim($row["db_serno"])
                                , "transno" => trim($row["transno"])
                            ))
                ));
                if (count($res) <= 0) {
                    $this->setSource("airbooking_sales_rep");
                    $res = $this->indexedArray($this->find("all", array("fields" => array("*")
                                , "conditions" => array("db_serno" => trim($row["db_serno"])
                                    , "transno" => trim($row["transno"])
                                ))
                    ));
                    if (count($res) <= 0) {
                        $ErrArr[] = "#ERROR:Flight Sales Details Not Found During Cancellation Calculation." . $row["transno"] . " = " . $row["db_serno"];
                        return;
                    }
                }
                $rowset = $res[0][$this->name];
            }
            if ($row["co_code"] == "3")
                $rowset["bfarecommamt"] = $row["bfarecommamt"];

            $tmp_sale_agt_mrp_cancel = $PwAirCalPurAmt->calmrpamt($row["fare"], $row["fsur"], $row["tax"], 0, 0, 0, 0, 0, 0, 0, 1, $row["airlinetransfee"], $row["othercharges"], 0);
            $tmp_sale_agt_mrp_cancel = $tmp_sale_agt_mrp_cancel - $row["cancellationcharge"];
            $tmp_sale_ret_mrp_cancel = $tmp_sale_agt_mrp_cancel;

            list($tmp_sale_agt_tds_cancel, $tmp_sale_agt_comm_cancel) = explode(',', $PwAirCalPurAmt->caltdscommamt($row["fare"], $row["fsur"], $row["tax"], $rowset["bfarecommamt"], $rowset["bfarecommcalc"], $rowset["bfarecommrate"], $rowset["agentbfarecommrate"], $rowset["agentfsurcommrate"], 0, 0, 0, 0, 0, 1, $rowset["fsurcommamt"], $rowset["fsurcommcalc"], $rowset["fsurcommrate"], 0, 0, 0, 0, $row["agttdsrate"], 0));

            list($tmp_sale_ret_tds_cancel, $tmp_sale_ret_comm_cancel) = explode(',', $PwAirCalPurAmt->caltdscommamt($row["fare"], $row["fsur"], $row["tax"], $rowset["bfarecommamt"], $rowset["bfarecommcalc"], $rowset["bfarecommrate"], $rowset["counterbfarecommrate"], $rowset["counterfsurcommrate"], 0, 0, 0, 0, 0, 1, $rowset["fsurcommamt"], $rowset["fsurcommcalc"], $rowset["fsurcommrate"], 0, 0, 0, 0, $row["tdsrate"], 0));

            $pldate = Date("Y-m-d", strtotime($row["pldate"]));
            $tmpsale_agttds = (($tmp_sale_agt_comm_cancel - $tmp_sale_ret_comm_cancel) * $row["agttdsrate"] / 100);
            $agtcomm = ($tmp_sale_agt_comm_cancel - $tmp_sale_ret_comm_cancel);

            $ky = $pldate . $row["counter_code"];

            $DataArr[$ky]["ttype"] = "To Cancel";
            $DataArr[$ky]["service"] = $servicecode;
            $DataArr[$ky]["saledate1"] = $pldate;
            $DataArr[$ky]["counter_code1"] = $row["counter_code"];
            $DataArr[$ky]["agentcounter"] = $row["agentcounter"];
            $DataArr[$ky]["db_serno"] = $row["db_serno"];
            if (isset($DataArr[$ky]["txn"])) {
                $DataArr[$ky]["txn"] = $DataArr[$ky]["txn"] + 1;
                $DataArr[$ky]["mrp"] = ($DataArr[$ky]["mrp"] - $tmp_sale_ret_mrp_cancel);
                $DataArr[$ky]["agt_tds"] = ($DataArr[$ky]["agt_tds"] - $tmpsale_agttds);
                $DataArr[$ky]["ret_tds"] = ($DataArr[$ky]["ret_tds"] - $tmp_sale_ret_tds_cancel);
                $DataArr[$ky]["agt_comm"] = ($DataArr[$ky]["agt_comm"] - $agtcomm);
                $DataArr[$ky]["ret_comm"] = ($DataArr[$ky]["ret_comm"] - $tmp_sale_ret_comm_cancel);
            } else {
                $DataArr[$ky]["txn"] = 1;
                $DataArr[$ky]["mrp"] = (-1) * ($tmp_sale_ret_mrp_cancel);
                $DataArr[$ky]["agt_tds"] = (-1) * ($tmpsale_agttds);
                $DataArr[$ky]["ret_tds"] = (-1) * ($tmp_sale_ret_tds_cancel);
                $DataArr[$ky]["agt_comm"] = (-1) * ($agtcomm);
                $DataArr[$ky]["ret_comm"] = (-1) * ($tmp_sale_ret_comm_cancel);
            }
        }
        if (count($DataArr) > 0)
            $this->ArrangeAndInsertData($DataArr);
    }

}
