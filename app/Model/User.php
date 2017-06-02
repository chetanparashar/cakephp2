<?php

/**
 * Description of User
 *
 * @author vinay
 */
class User extends AppModel {

    public $useTable = "bo_usermaster";
    public $primaryKey = "usercode";
    public $hashPasswordFlag = false;
    public $virtualFields = array("brand" => "'PW'", "apptype" => "'Analytics'");

    public function beforeFind($queryData) {
        parent::beforeFind($queryData);
        if (!empty($queryData) && isset($queryData["conditions"]["loginquery"])) {
            unset($queryData["conditions"]["loginquery"]);
            $this->hashPasswordFlag = true;
        }
        return $queryData;
    }

    public function afterFind($results, $primary = false) {
        parent::afterFind($results);
        if ($this->hashPasswordFlag && !empty($results[0][$this->name]["password"])) {
            $this->hashPasswordFlag = false;
            App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
            $PasswordHasher = new SimplePasswordHasher();
            $results[0][$this->name]["password"] = $PasswordHasher->hash(strtoupper($results[0][$this->name]["password"]));
        }
        return $results;
    }

}
