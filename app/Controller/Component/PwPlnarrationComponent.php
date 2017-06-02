<?App::uses('Component', 'Controller');
	class PwPlnarrationComponent extends Component
	{
            Public $narration_failed="To Failed";
            Public $narration_mrp="To Sales";
            Public $narration_comm="To Commission";
            Public $narration_ret_comm="To Commission [Retailer]";
            Public $narration_tds="To TDS";
            Public $narration_ret_tds="To TDS [Retailer]";
            Public $narration_cancel="To Cancel";
            Public $narration_ret_cancel="To Cancel [Retailer]";
            Public $narration_credit="To Credit";
            Public $narration_ret_credit="To Credit [Retailer]";
            Public $narr_service_pinr="pin-r";
            Public $narr_service_fer="flexi-er";
            Public $narr_service_airline="airline";
            Public $narr_service_redbus="bus reservation";
            Public $narr_service_mauj="mobile gallery";
            Public $narr_service_billdesk="bill collection";
            Public $narr_service_irctc="train reservation";
            Public $narr_service_bsnler="bsnl-er";
            Public $narr_service_gameepin="game-epin";
            Public $narr_service_tatateler="tatatel-er";
            Public $narr_service_zipcashpin="zipcashpin";
            Public $narr_service_olrer="olr-er";
            Public $narr_service_ticketvala="bus reservation";
            Public $narr_service_dthflexi="dth-er";
            Public $narr_service_astrology="astrology";
            Public $narr_service_airtel="airtel-er";
            Public $narr_service_docomo="docomo";
            Public $narr_service_acl="bill collection";
            Public $narr_service_paycash="paycash";
            Public $narr_service_fgi="fgi";
            Public $narr_service_mgl="mgl";
			Public $narr_service_ticketengine="Payworld-Travelling-Air";
            Public $narr_service_ticketengine_bus = "Payworld-Travelling-Bus";

            Public $service_pinr="pin-r";
            Public $service_fer="f-er";
            Public $service_airline="airline";
            Public $service_redbus="redbus";
            Public $service_mauj="mauj";
            Public $service_billdesk="billdesk";
            Public $service_irctc="irctc";
            Public $service_bsnler="bsnl-er";
            Public $service_apnaloan="apnaloan";
            Public $service_gameepin="game-epin";
            Public $service_tatateler="tatatel-er";
            Public $service_zipcashpin="zipcashpin";
            Public $service_olrer="olr-er";
            Public $service_ticketvala="ticketvala";
            Public $service_dthflexi="dth-er";
            Public $service_astrology="astrology";
            Public $service_airtel="airtel-er";
            Public $service_docomo="docomo";
            Public $service_acl="acl";
            Public $service_paycash="paycash";
            Public $service_fgi="fgi";
            Public $service_mgl="mgl";
			Public $service_ticketengine="ticketengine";
			Public $service_ticketengine_bus = "ticketengine-bus";

            public $arrservice_idxservicecode, $arrservice_idxcodetrans, $productlist, $nontdsservices;

            public function  __construct() {
		App::import('model','Service');
		$this->Service	= new Service();
                //Controller::loadModel('Service');
                $plnarr_result_supplement=$this->Service->query("(SELECT serno as 'service_code', '' as 'supplement_code', service, transtable, transtable_dstbtr, transdate_fieldname, '' as entity_code, code_trans, code_plnarr, sequence, provider, version, versiondate, commencementdate, flgtdsapplicable, active, cocode, modelname FROM services WHERE commencementdate<>'0000-00-00' AND commencementdate <='".Date("Y-m-d")."') UNION ALL (SELECT sno_mas as 'service_code',  serno as 'supplement_code', service, transtable, transtable_dstbtr, transdate_fieldname, entity_code, code_trans, code_plnarr, '' as sequence, provider, version, versiondate, commencementdate, flgtdsapplicable, active, cocode, modelname FROM services_supplement WHERE commencementdate<>'0000-00-00' AND commencementdate <='".Date("Y-m-d")."') ORDER BY 0+service_code, 0+supplement_code");
                foreach ($plnarr_result_supplement as $key=> $val)
                {
                    foreach ($val as $ky=> $vl)
                    {
                        $plnarr_servicecode=$vl["service_code"];
                        if(trim($vl["supplement_code"])!="")
                                $plnarr_servicecode=$plnarr_servicecode."-".$vl["supplement_code"];
                        $this->arrservice_idxservicecode[$plnarr_servicecode]=$vl;
                        $this->arrservice_idxcodetrans[$vl["code_trans"]]=$vl;
                        $this->productlist  = $this->productlist.", '".$vl["code_trans"]."'";
                        if(strtoupper(trim($vl["flgtdsapplicable"]))=='N')
                                $this->nontdsservices=$this->nontdsservices.", '".$vl["code_trans"]."'";
                    }
                }
                $this->productlist=substr($this->productlist,1);
                $this->nontdsservices=substr($this->nontdsservices,1);
            }
	}
?>
