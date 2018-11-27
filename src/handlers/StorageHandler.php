<?php
namespace Handlers;

use \Models\Package;
use \Models\Repo;
use \Models\RepoPackages;

class StorageHandler{

    protected static function saveRepo($data, $pdo){
      $repo = new Repo($pdo);
      $repo->name = $data["name"];
      $repo->owner = $data["owner"];
      $repo->description = $data["description"];
      $repo->url = $data["url"];
      $repo->stargazers_count = $data["stargazers_count"];
      $repo->watchers_count = $data["watchers_count"];
      $repo->forks_count = $data["forks_count"];
      $repo->save();
    }

    protected static function savePackage($dep, $pdo){
      $package = new Package($pdo);
      $package->name = $dep;
      $package->save();
    }

    protected static function saveRepoPackage($package_id, $repo_id, $pdo){
      $rp = new RepoPackages($pdo);
      $rp->repo_id = $repo_id;
      $rp->package_id = $package_id;
      $rp->save();
    }

    public static function getAllPackages($pdo){
      $packages = Package::getAll($pdo);
      return $packages;
    }

    public function storeOnImport($data, $pdo){
      try{
        $pdo->beginTransaction();
          $repo = Repo::getBy("url", $data["url"]);
          if(!$repo){
            self::saveRepo($data, $pdo);
            $repoId = $pdo->lastInsertId();
          }else{
            $repoId = $repo->id;
            // if repo is already present but join table is empty then show message directly.
            $repopackage = RepoPackages::getBy("repo_id", $repoId);
            if($repopackage){
              return array("error" => true, "message" => "This Repo is already imported.");
            }
          }
          foreach($data["dependencies"] as $dep){
            $package = Package::getBy("name", $dep);
            if(!$package){
              self::savePackage($dep, $pdo);
              $packageId = $pdo->lastInsertId();
              self::saveRepoPackage($packageId, $repoId, $pdo);
            }
          }
          $pdo->commit();
          return array("success" => true, "packages" => $data["dependencies"]);
        } catch (\PDOException $e) {
            $pdo->rollback();
            return array("error" => true, "sql_error" => $e->getMessage(), "message" => "Something is Wrong.");
        } catch (Exception $e) {
          $pdo->rollback();
          throw $e;
        }
    }

    public function checkIfRepoImported($url, $pdo){
      try{
        $repo = Repo::getBy("url", $url);
        if (!$repo){
          return false;
        }else{
          return true;
        }
      } catch (\PDOException $e){
        throw $e;
        return false;
      }
    }

    public function getTopPackages($pdo){
      return Package::getTopPackages($pdo);
    }
}
