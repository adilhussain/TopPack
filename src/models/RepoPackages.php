<?php

namespace Models;

use \Models\Modelpdo;
use \PDO;

class RepoPackages extends ModelPDO {
    public function __construct($pdo, $data = false) {
        $schema = array(
            'repo_id' => PDO::PARAM_INT,
            'package_id' => PDO::PARAM_INT
        );
        parent::__construct($schema, $pdo, $data);
    }

    public static function getBy($field, $value){
      return parent::getBy($field, $value);
    }    
}
?>
