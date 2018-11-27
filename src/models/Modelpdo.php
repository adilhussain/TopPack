<?php

namespace Models;
use \PDO;
use \Models\Package;
use \Models\Repo;
use \Models\RepoPackages;

abstract class ModelPDO {
    private static $pdo;

public $host='localhost';
public $db = 'toppack';
public $username = 'root';
public $password = 'root';

    protected static function getPDO() {
        if (!isset(self::$pdo)) {
            $env = getenv('ENVIRONMENT');
            if($env != "DEV"){
              $db = parse_url(getenv("DATABASE_URL"));

              $pdo = new PDO("pgsql:" . sprintf(
                  "host=%s;port=%s;user=%s;password=%s;dbname=%s",
                  $db["host"],
                  $db["port"],
                  $db["user"],
                  $db["pass"],
                  ltrim($db["path"], "/")
              ));
              self::$pdo = $pdo;
            }else{
              $dsn = "mysql:host=localhost;dbname=toppack";
              self::$pdo = new PDO($dsn, 'root', 'root');
            }

            // $container = $app->getContainer();
             // $db = $container['settings']['db'];
             // self::$pdo = new PDO('mysql:host='.$db['host'].';dbname='.$db['dbname'], $db['user'], $db['pass']);
             self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // self::$pdo = new PDO(
            //     'mysql:dbname=' . Config::DB . ';host=' . Config::HOST,
            //     Config::USER,
            //     Config::PASS
            // );
            // self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        }
        return self::$pdo;
    }
    protected static function getModelName() {
        // echo "KK " . get_class();
        // echo "KKA " . get_called_class();
        $cla = explode('\\', get_called_class());
        // return strtolower($cla[count($cla) - 1]);
        // return "package";
        return $cla[count($cla) - 1];
    }

    protected static function getFullModelName(){
      return get_called_class();
    }

    protected static function getTableName() {
        return self::getModelName();
    }
    protected static function getFieldName($field) {
        // return self::getModelName() . '_' . $field;
        return $field;
    }
    protected static function getBindName($field) {
        return ":{$field}";
    }
    protected static function getPropertyName($prop) {
        // return substr($prop, strlen(self::getModelName()) + 1);
        return $prop;
    }
    public static function get($id) {
        return self::getBy('id', $id);
    }
    protected static function getBy($field, $value) {
        $tableName = self::getTableName();
        $fieldName = self::getFieldName($field);
        $bindName = self::getBindName($field);
        $q = "SELECT * FROM {$tableName} ";
        $q .= "WHERE {$fieldName} = {$bindName}";
        $sth = self::getPDO()->prepare($q);
        $sth->bindParam($bindName, $value);
        $sth->execute();
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $modelName = self::getFullModelName();
            return new $modelName(self::getPDO(), $data);
        }
        return null;
    }
    public static function getAll($pdo) {
        $tableName = self::getTableName();
        error_log("Table:: " . $tableName );
        $q = "SELECT * FROM {$tableName}";
        // $pdo = $pdo || self::getPDO();
        $sth = $pdo->prepare($q);
        try{
          $sth->execute();
          $data = $sth->fetchAll();
        }catch(\PDOException $e){
          error_log($e->getMessage(), 4);
        }
        if ($data) {
            $models = array();
            foreach ($data as $d) {
                $modelName = self::getFullModelName();
                                error_log($modelName, 4);
                $models[] = new $modelName($pdo, $d);

            }
            return $models;
        }
        return null;
    }
    protected static function getAllBy($field, $value) {
        $tableName = self::getTableName();
        $fieldName = self::getFieldName($field);
        $bindName = self::getBindName($field);
        $q = "SELECT * FROM {$tableName} ";
        $q .= "WHERE {$fieldName} = {$bindName}";
        $sth = self::getPDO()->prepare($q);
        $sth->bindValue($bindName, $value);
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            $models = array();
            foreach ($data as $d) {
                $modelName = self::getModelName();
                $models[] = new $modelName($d);
            }
            return $models;
        }
        return null;
    }
    private $fields = array();
    public function __construct($schema, $pdo, $data = false) {
      self::$pdo = $pdo;
        error_log("111");
        $this->fields[strtolower(self::getTableName()) . '_id'] = array('value' => null, 'type' => PDO::PARAM_INT);
        foreach ($schema as $name => $type) {
            error_log($name);
            error_log($type);
            $this->fields[$name] = array('value' => null, 'type' => $type);
        }
        if ($data) {
            foreach ($data as $column => $value) {
              error_log($column);
              error_log($value);
                $prop = self::getPropertyName($column);
                // var_dump($this->fields[$prop]);
                $this->fields[$prop]['value'] = $value;
            }
        }
    }
    public function save() {
        $tableName = self::getTableName();
        if ($this->fields['id']['value'] != null) {
            foreach ($this->fields as $field => $f) {
                if ($field != 'id' && $f['value'] != null) {
                    $fieldName = self::getFieldName($field);
                    $bindName = self::getBindName($field);
                    $fields[] = "{$fieldName} = {$bindName}";
                }
            }
            $fieldName = self::getFieldName('id');
            $bindName = self::getBindName('id');
            $set = implode(', ', $fields);
            $q = "UPDATE {$tableName} ";
            $q .= "SET {$set} ";
            $q .= "WHERE {$fieldName} = {$bindName}";
        } else {
            foreach ($this->fields as $field => $f) {
                if ($field != 'id' && $f['value'] != null) {
                    $cols[] = self::getFieldName($field);
                    $binds[] = self::getBindName($field);
                }
            }
            $columns = implode(', ', $cols);
            $bindings = implode(', ', $binds);
            $q = "INSERT INTO {$tableName} ";
            $q .= "({$columns}) VALUES ({$bindings})";
        }
        $sth = ModelPDO::getPDO()->prepare($q);
        // $sth = self::$pdo->prepare($q);
        foreach ($this->fields as $field => $f) {
            $value = $f['value'];
            if ($f['value'] != null) {
                $sth->bindValue(self::getBindName($field), $f['value'], $f['type']);
            }
        }
        return $sth->execute();
    }

    public function __set($name, $value) {
        if (array_key_exists($name, $this->fields)) {
            $this->fields[$name]['value'] = $value;
        }
    }
    public function __get($name) {
        if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name]['value'];
        }
    }
}
?>
