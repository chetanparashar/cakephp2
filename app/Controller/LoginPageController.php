<?php

App::uses('AppController', 'Controller');
class  LoginPageController extends AppController {
  
  public $components=array('Auth','Security');
  public $helpers = array('Html', 'Form');
  public $layout='';
  public function beforeFilter(){
        parent::beforeFilter();
        //$this->Auth=$this->Components->Load('Auth');
	//$this->Auth->initialize($this);
	//$this->Security=$this->Components->Load('Security');
	//$this->Security->initialize($this);
	$this->Security->csrfUseOnce=true;
	$this->Security->blackHoleCallback="blackHole";
	$this->Security->allowedActions=array('login');
	$this->Security->unlockedActions=array('login');
	$this->Security->csrfExpires = "+1 minutes";
	$this->Security->csrfLimit = 1;
	$this->Auth->authenticate=array('Form'=>array('fields'=>array('username'=>'loginname','password'=>'password'),
                                                      'userModel'=>'User',
						      'scope'=>array('loginquery'=>1)));
	$this->Auth->loginAction=array('controller'=>'LoginPage','action'=>'login');
        $this->Auth->loginRedirect = array('controller' => 'LoginPage', 'action' => 'home');
        $this->Auth->logoutRedirect = array('controller' => 'LoginPage', 'action' => 'login');
        $this->Auth->unauthorizedRedirect = array('controller' => 'LoginPage', 'action' => 'login');
        $this->Auth->authError = "Invalid Login Credentials Used.";
	if (!($this->request->controller == 'LoginPage' && in_array($this->request->action, array('login', 'reset')))) {
            if (!$this->Auth->loggedIn()) {
                $this->logout("Session Expired, Please Login To Continue.");
            }
        }
        
  }
  public function login(){
	if ($this->request->is("post") && isset($this->request->data['loginname'])) {
            $this->Session->delete('Auth.User');
            $this->request->data['User']['loginname'] = base64_decode(trim($this->request->data['loginname']));
            $this->request->data['User']['password'] = base64_decode($this->request->data['password']);
	    $this->loadModel("User");
            if($this->Auth->login()){
            	$this->Session->write("Auth.User.loggedin", date('Y-m-d H:i:s'));
                $this->Session->write("Auth.User.finyeardate", "2017-04-01");
                $this->loadModel("MenuMaster");
                $this->Session->write("Auth.User.layoutData", $this->MenuMaster->getMenu($this->Auth->user()));
                //$this->ResponseArr["authMessage"] = "";
                $this->redirect($this->Auth->loginRedirect);
            }
        } 
	$this->render('login','login');
    }
public function home(){
      $n=5;
//        $postArray = array(
//                "reservationDate" => $timeData,
//                "emailLanguageCode" => "it_it",
//                "shipToCode" => $_POST['ship'],
//                "customer" => array(
//                    "firstName" => $_POST['firstName'],
//                    "lastName" => $_POST['lastName'],
//                    "emailId" => $_POST['emailId'],
//                    "phoneNumber" => $_POST['phoneNumber'],
//                    "address" => array(
//                        "addressLine1" => $_POST['addressLine1'],
//                        "city" => $_POST['city'],
//                        "state" => $_POST['state'],
//                        "country" => $_POST['country'],
//                        "postalCode" => $_POST['postalCode']
//                    )
//                ),
//                "product" => array(
//                    "productCode" => $_POST['productCode'],
//                    "issueReported" => $_POST['issueReported']
//                )
//            );
//$postArray=  array_merge(array("note" => array("text" => $note)),$postArray);
//$postArray["product"]=array_merge(array("serialNumber"=> $serial),$postArray["product"]);
//        $m = $n;
//        $k = $n - 1;
//        $kk = 1;
//        for ($i = $n * 2 - 1; $i > 0; $i--) {
//            if ($i >= $n) {
//                for ($j = $m; $j > 0; $j--) {
//                    print_r("&nbsp;");
//                    $kk++;
//                }for ($j = $n; $j >= $m; $j--) {
//                    print_r("&nbsp;*");
//                    $kk++;
//                }
//                $m--;
//            } else {
//                for ($j = $n; $j >= $k; $j--) {
//                    print_r("&nbsp;");
//                    $kk++;
//                }
//                for ($j = $k; $j > 0; $j--) {
//                    print_r("&nbsp;*");
//                    $kk++;
//                }
//                $k--;
//            }
//            echo "<br/>";
//        }
//        echo $kk;
//        $m = $n;
//        ;
//        $k = $n - 1;
//        for ($i = $n * 2 - 1; $i > 0; $i--) {
//            if ($i >= $n) {
//                for ($j = $n; $j >= $m; $j--) {
//                    print_r("&nbsp;");
//                }for ($j = $m; $j > 0; $j--) {
//                    print_r("&nbsp;*");
//                }
//                $m--;
//            } else {
//                for ($j = $k; $j > 0; $j--) {
//                    print_r("&nbsp;");
//                }
//                for ($j = $n; $j >= $k; $j--) {
//                    print_r("&nbsp;*");
//                }
//                $k--;
//            }
//            echo "<br/>";
//        }
//
//        $m = $n;
//        $k = $n - 1;
//
//        for ($i = $n * 2 - 1; $i > 0; $i--) {
//            if ($i >= $n) {
//                for ($j = $m; $j > 0; $j--) {
////                        if($j==$m)
//                    print_r("&nbsp;");
//                }
//                if ($i != $n * 2 - 1) {
//                    print_r("*");
//                    for ($j = $n; $j >= $m; $j--) {
//                        if ($j == $m)
//                            print_r("&nbsp;*");
//                        else
//                            print_r("&nbsp;&nbsp;");
//                    }
//                }else {
//                    print_r(" *");
//                }
//                $m--;
//            } else {
//                for ($j = $n; $j >= $k; $j--) {
//                    print_r("&nbsp;");
//                }
//                if ($i != 1) {
//                    print_r("*");
//                    for ($j = $k; $j > 0; $j--) {
//                        if ($j == 1)
//                            print_r("&nbsp;*");
//                        else
//                            print_r("&nbsp;&nbsp;");
//                    }
//                }else {
//                    print_r(" *");
//                }
//                $k--;
//            }
//            echo "<br/>";
//        }
    
   }
public function logout(){
$this->set('authMessage','successfuly logout');
	$this->redirect($this->Auth->logoutRedirect);
   }
public function blackHole(){
	$this->set('authMessage','session has been blackhole');
	$this->redirect($this->Auth->logoutRedirect);
   }
}
