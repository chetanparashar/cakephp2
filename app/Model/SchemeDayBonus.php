<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SchemeDayBonus
 *
 * @author chetan
 */
class SchemeDayBonus extends AppModel{
    public $useTable = 'scheme_day_bonus';
    public $table = 'scheme_day_bonus';
    var $primaryKey = 'serno';

      public function InsertRecord($request) {
        $i=0;
        $Record=array();
        foreach ($request['Service'] as $service) {
            if ($service['serviceno'] != 0) {
                $date=DateMethod::ChangeDateFormat($service['date'],'Y-m-d');                
                if($service['bonus']=="" || $service['bonus']==0){
                    throw new Exception('Invalid bonus % !!!');
                }
                if((trim($service['bonus'])>100 || trim($service['bonus']))<=0 ){
                     throw new Exception('Invalid bonus % !!!');
                }
                $date=DateMethod::ChangeDateFormat(trim($service['date'], 'Y-m-d'));;
                 $servicecode=$service['serviceno'] - 1;
                $olddata=$this->find('list',array('fields'=>array("group_code",'bonus_percent'),'conditions'=>array('applicable_date'=>$date,'group_code'=>$servicecode)));
                if(count($olddata)>0){
                    $condition=array('applicable_date'=>$date,'group_code'=>$servicecode);
                    $fields=array('bonus_percent'=>"'".trim($service['bonus'])."'",'entrydate'=>"'".date('Y-m-d H:i:s')."'");
                    $this->updateAll($fields,$condition);
                }else{
                    $Record[$i]['group_code'] = $servicecode;             
                    $Record[$i]['bonus_percent'] = trim($service['bonus']);              
                    $Record[$i]['applicable_date'] =$date;       
                    $Record[$i]['entrydate'] = date('Y-m-d H:i:s');
                    $i++;
                }
                
            }
        }
        try {
             if (count($Record) > 0) {
                 $this->saveAll($Record);
                 return true;
             }
         } catch (Exception $ex) {
             throw new Exception($ex->getMessage());
         }
         return "SUCCESS";
    }
    public function getData(){
        return $this->find('all',array('fields'=>'*','conditions'=>array('applicable_date >='=>date('Y-m-d'))));
        
    }
}
