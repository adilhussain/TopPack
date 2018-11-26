<?php

namespace Models;

use \Models\Modelpdo;
use \PDO;

class Package extends ModelPDO {
    public function __construct($pdo, $data = false) {
        $schema = array(
            'name' => PDO::PARAM_STR
        );
        parent::__construct($schema, $pdo, $data);
    }

    public static function getBy($field, $value){
      return parent::getBy($field, $value);
    }

    public static function getAll(){
      return parent::getAll();
    }

}
?>
