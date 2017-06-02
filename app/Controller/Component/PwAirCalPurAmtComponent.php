<?
        App::uses('Component', 'Controller');
	//class pw_class_checkdb extends Object
        class PwAirCalPurAmtComponent extends Component
	{
            Public function calpuramt($totbfare, $totfsur, $tottax, $bfarecommamt, $bfarecommcalc, $bfarecommrate, $other_bfarecommrate, $other_fsurcommrate, $sf_bfarecommrate, $sf_fsurcommrate, $sf_other_bfarecommrate, $sf_other_fsurcommrate, $servicetaxrate, $noofpassengers, $fsurcommamt, $fsurcommcalc, $fsurcommrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $tds, $totairlinetransfee, $othercharges, $markupamt, $snd_markupamt=0){
                    $totalsaleamt=self::calmrpamt($totbfare, $totfsur, $tottax, $sf_bfarecommrate, $sf_fsurcommrate, $servicetaxrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $noofpassengers, $totairlinetransfee, $othercharges, $markupamt, $snd_markupamt);

                    list($tottds,$totcomm)=split(',',self::caltdscommamt($totbfare, $totfsur, $tottax, $bfarecommamt, $bfarecommcalc, $bfarecommrate, $other_bfarecommrate, $other_fsurcommrate, $sf_bfarecommrate, $sf_fsurcommrate, $sf_other_bfarecommrate, $sf_other_fsurcommrate, $servicetaxrate, $noofpassengers, $fsurcommamt, $fsurcommcalc, $fsurcommrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $tds, $markupamt, $snd_markupamt));
                    return $totalsaleamt-$totcomm+$tottds;
            }
            Public function caltdscommamt($totbfare, $totfsur, $tottax, $bfarecommamt, $bfarecommcalc, $bfarecommrate, $other_bfarecommrate, $other_fsurcommrate, $sf_bfarecommrate, $sf_fsurcommrate, $sf_other_bfarecommrate, $sf_other_fsurcommrate, $servicetaxrate, $noofpassengers, $fsurcommamt, $fsurcommcalc, $fsurcommrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $tds, $markupamt, $snd_markupamt=0){
                    //commission
                    $tmpamt=self::calsfcommamt($totbfare,$bfarecommcalc,$bfarecommamt,$bfarecommrate,$noofpassengers);
                    if($bfarecommrate!=0)
                            $tmpamt=($other_bfarecommrate/$bfarecommrate)*$tmpamt;
                    else
                            $tmpamt=0;
                    $tmp_fsuramt=self::calsfcommamt($totfsur,$fsurcommcalc,$fsurcommamt,$fsurcommrate,$noofpassengers);
                    if($fsurcommrate!=0)
                            $tmp_fsuramt=($other_fsurcommrate/$fsurcommrate)*$tmp_fsuramt;
                    else
                            $tmp_fsuramt=0;
                    $comm=$tmpamt+$tmp_fsuramt;

                    //service fee
                    $tmp_sf_bfareamt=self::calsfcommamt($totbfare,$sf_bfarecommcalc,$sf_bfarecommamt,$sf_bfarecommrate,$noofpassengers);
                    if($sf_bfarecommrate!=0)
                            $tmp_sf_bfareamt=($sf_other_bfarecommrate/$sf_bfarecommrate)*$tmp_sf_bfareamt;
                    else
                            $tmp_sf_bfareamt=0;

                    $tmp_sf_fsuramt=self::calsfcommamt($totfsur,$sf_fsurcommcalc,$sf_fsurcommamt,$sf_fsurcommrate,$noofpassengers);
                    if($sf_fsurcommrate!=0)
                            $tmp_sf_fsuramt=($sf_other_fsurcommrate/$sf_fsurcommrate)*$tmp_sf_fsuramt;
                    else
                            $tmp_sf_fsuramt=0;

                    //total comm
                    $sf_comm=$tmp_sf_bfareamt+$tmp_sf_fsuramt;

                    $tmpmarkupamt=$markupamt*$noofpassengers;

                    $tottds=($tmpmarkupamt+$comm+$sf_comm)*$tds/100;
                    return $tottds.",".($tmpmarkupamt+$comm+$sf_comm);
            }
            Public function calmrpamt($totbfare, $totfsur, $tottax, $sf_bfarecommrate, $sf_fsurcommrate, $servicetaxrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $noofpassengers, $totairlinetransfee, $othercharges, $markupamt, $snd_markupamt=0){
                    //$servicetax=$totbfare*$servicetaxrate/100;
                    $servicetax=$servicetaxrate;
                    $tmp_sf_bfareamt=self::calsfcommamt($totbfare,$sf_bfarecommcalc,$sf_bfarecommamt,$sf_bfarecommrate,$noofpassengers);
                    $tmp_sf_fsuramt=self::calsfcommamt($totfsur,$sf_fsurcommcalc,$sf_fsurcommamt,$sf_fsurcommrate,$noofpassengers);
                    $totalsaleamt=($totbfare+$tmp_sf_bfareamt)+($totfsur+$tmp_sf_fsuramt)+$tottax;
                    $tmpmarkupamt=$markupamt*$noofpassengers + $snd_markupamt*$noofpassengers;
                    $totairlinetransfee=$totairlinetransfee*$noofpassengers;
                    $othercharges=$othercharges*$noofpassengers;
                    return ($totalsaleamt+$servicetax+$totairlinetransfee+$othercharges+$tmpmarkupamt);
            }
            Public function calsfcommamt($fare,$commcalc,$commamt,$commrate,$noofpassengers){
                    if(strtoupper(trim($commcalc))=="F")
                            $calcommamt=$commamt*$noofpassengers;
                    else
                    {
                            $calcommamt=$fare*$commrate/100;
                            if(strtoupper(trim($commcalc))=="H"&&(($commamt*$noofpassengers)>$calcommamt))
                                    $calcommamt=$commamt*$noofpassengers;
                            elseif(strtoupper(trim($commcalc))=="L"&&(($commamt*$noofpassengers)<$calcommamt))
                                    $calcommamt=$commamt*$noofpassengers;
                    }
                    return $calcommamt;
            }
            //five extra parameters from calpuramt function at last in list
            Public function agtcalpuramt($totbfare, $totfsur, $tottax, $bfarecommamt, $bfarecommcalc, $bfarecommrate, $other_bfarecommrate, $other_fsurcommrate, $sf_bfarecommrate, $sf_fsurcommrate, $sf_other_bfarecommrate, $sf_other_fsurcommrate, $servicetaxrate, $noofpassengers, $fsurcommamt, $fsurcommcalc, $fsurcommrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $agttds, $counterbfarecommrate, $counterfsurcommrate, $sf_counterbfarecommrate, $sf_counterfsurcommrate, $tds, $totairlinetransfee, $othercharges, $markupamt, $snd_markupamt=0){
                    $totalsaleamt=self::calmrpamt($totbfare, $totfsur, $tottax, $sf_bfarecommrate, $sf_fsurcommrate, $servicetaxrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $noofpassengers, $totairlinetransfee, $othercharges, $markupamt, $snd_markupamt);

                    //agent
                    list($totagttds,$totagtcomm)=split(',',self::caltdscommamt($totbfare, $totfsur, $tottax, $bfarecommamt, $bfarecommcalc, $bfarecommrate, $other_bfarecommrate, $other_fsurcommrate, $sf_bfarecommrate, $sf_fsurcommrate, $sf_other_bfarecommrate, $sf_other_fsurcommrate, $servicetaxrate, $noofpassengers, $fsurcommamt, $fsurcommcalc, $fsurcommrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $agttds, $markupamt, $snd_markupamt));

                    //retailer
                    list($tottds,$totcomm)=split(',',self::caltdscommamt($totbfare, $totfsur, $tottax, $bfarecommamt, $bfarecommcalc, $bfarecommrate, $counterbfarecommrate, $counterfsurcommrate, $sf_bfarecommrate, $sf_fsurcommrate, $sf_counterbfarecommrate, $sf_counterfsurcommrate, $servicetaxrate, $noofpassengers, $fsurcommamt, $fsurcommcalc, $fsurcommrate, $sf_bfarecommamt, $sf_bfarecommcalc, $sf_fsurcommamt, $sf_fsurcommcalc, $tds, $markupamt, $snd_markupamt));

                    $selfagttds=($totagtcomm-$totcomm)*$agttds/100;

                    return $totalsaleamt-$totagtcomm+$selfagttds+$tottds;
            }
	}

?>
