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

    public static function getAll($pdo){
      $packages =  parent::getAll($pdo);
      $all = [];
      foreach ($packages as $package) {
        $all[$package->name] = $package->name;
      }
      return $all;
    }

    public static function getTopPackages($pdo){
      $fetchMostUsedPackageNames = $pdo->prepare("SELECT p.name, p.package_id, count(p.name) FROM Package p RIGHT JOIN RepoPackages rp on p.package_id = rp.package_id RIGHT JOIN Repo r on rp.repo_id = r.repo_id GROUP BY p.name, p.package_id ORDER BY COUNT(p.name) DESC LIMIT 10;");
      try{
        $data = [];
        $fetchMostUsedPackageNames->execute();
        $packages = $fetchMostUsedPackageNames->fetchAll();
        if ($packages){
          foreach($packages as $p){
            $name = $p['name'];
            $package_id = $p['package_id'];
            $count = $p['count(p.name)'];
            $fetchRepoData = $pdo->prepare("SELECT r.name, r.owner, r.url FROM Repo r RIGHT JOIN RepoPackages rp on r.repo_id = rp.repo_id RIGHT JOIN Package p ON rp.package_id = p.package_id WHERE p.package_id='".$package_id."'");
            $data[$name] = ["count" => $count, "repos" => []];
            try{
              $fetchRepoData->execute();
              $repos = $fetchRepoData->fetchAll();
              foreach($repos as $repo){
                $repo_array = ["name" => $repo["name"], "owner" => $repo["owner"], "url" => $repo["url"]];
                array_push($data[$name]["repos"], $repo_array);
              }
            }catch(\PDOException $e){
              return array("error" => true, "sql_error" => $e->getMessage(), "message" => "There was an error connecting to the database.");
            }
          }
          return $data;
        }else{
          return array("error" => true, "message" => "No packages imported yet.");
        }
      } catch (\PDOException $e){
        return array("error" => true, "sql_error" => $e->getMessage(), "message" => "There was an error connecting to the database.");      }
    }

}
?>
