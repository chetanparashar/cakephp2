<?php

/**
 * Description of SchemeDailysaleController
 *
 * @author vinay
 */
class SchemeDailysaleController extends AppController {

    public $components = array("PwPlnarration", "PwAirCalPurAmt");
    public $uses = array("SchemeDailysale", "SchemeDashbordMaster", "SchemeWeaklySale");
    public $autoRender = false;

    public function index() {
        try {
            echo "\n Process Start Date :" . date("d/m/Y H:i:s") . "~!#!~";
            //echo("<br>Scheme Closed");
            //exit();
   
           // $this->ErrArr = array();
            //$this->SchemeDailysale->getSaleArr($this);
           // if (count($this->ErrArr) > 0) {
           //     CakeLog::write("SchemeDailysale1111", var_export($this->ErrArr, TRUE));
            //    echo("Done With Some Warnings111!");
          //  }
            $this->SchemeWeaklySale->CalculateWeaklysSale(DateMethod::AddDate(Date("Y-m-d"),-1));
            $this->SchemeDashbordMaster->CalculateRuns($this);
            $this->SchemeDashbordMaster->SetRanking($this);
 
	    // $this->SchemeDashbordMaster->CalculatePwGoRuns($this);
        } catch (Exception $e) {
            CakeLog::write("SchemeDailysale", sprintf("[%s] %s\n%s", get_class($e), $e->getMessage(), $e->getTraceAsString()));
            echo("Done With Some Exceptions222!!");
        }
        echo("SUCCESS");
        echo("<br>");
        echo "\n Process End Date :" . date("d/m/Y H:i:s") . "~!#!~";
        echo "\n PHP_PROCESS_COMPLETE";
    }

}
