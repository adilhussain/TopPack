<?php

namespace Models;

use \Models\Modelpdo;
use \PDO;

class Repo extends ModelPDO {
    public function __construct($pdo, $data = false) {
        $schema = array(
            'description' => PDO::PARAM_STR,
            'name' => PDO::PARAM_STR,
            'owner' => PDO::PARAM_STR,
            'url' => PDO::PARAM_STR,
            'stargazers_count' => PDO::PARAM_INT,
            'watchers_count' => PDO::PARAM_INT,
            'forks_count' => PDO::PARAM_INT
        );
        parent::__construct($schema, $pdo, $data);
    }
    
    public static function getBy($field, $value){
      return parent::getBy($field, $value);
    }

}
?>
