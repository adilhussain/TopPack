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

    public static function getAllPackages(){
      $packages = Packages::getAll();
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
