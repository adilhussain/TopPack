<?php
namespace Handlers;

class StorageHandler{
    public function storeOnImport($data, $pdo){
        $insertRepoQuery = $pdo->prepare("INSERT INTO Repo (name, owner, description, url)
                                  values ('".$data["name"]."', '".$data["owner"]."', '".$data["description"]."', '".$data["url"]."')");
        try {
            $insertRepoQuery->execute();
            $repoId = $pdo->lastInsertId();
            foreach($data["dependencies"] as $dep){
            // $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND status=?');
            // $stmt->execute([$email, $status]);
            // $user = $stmt->fetch();
            // // or
            // $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND status=:status');
            // $stmt->execute(['email' => $email, 'status' => $status]);
            // $user = $stmt->fetch();              
              $fetchPackageQuery = $pdo->prepare("SELECT * FROM Package where name='".$dep."' LIMIT 1");
              $fetchPackageQuery->execute();

              $package = $fetchPackageQuery->fetch();
              if (!$package){
                $insertPackageQuery = $pdo->prepare("INSERT INTO Package (name) values ('".$dep."')");
                $insertPackageQuery->execute();
                $packageId = $pdo->lastInsertId();
              }
              else{
                $packageId = $package["package_id"];
              }
              $insertRepoPackageQuery = $pdo->prepare("INSERT INTO RepoPackages (repo_id, package_id)
                                                      values ('".$repoId."', '".$packageId."')");
              $insertRepoPackageQuery->execute();
            }
            return array("success" => true, "packages" => $data["dependencies"]);
        } catch (\PDOException $e) {
            return array("error" => true, "sql_error" => $e->getMessage(), "message" => "This Repo is already imported.");
        }
    }

    public function checkIfRepoImported($url, $pdo){
      $checkIfRepoExistsQuery = $pdo->prepare("SELECT * FROM Repo where url='".$url."' LIMIT 1");
      try{
        $checkIfRepoExistsQuery->execute();
        $repo = $checkIfRepoExistsQuery->fetch();
        if (!$repo){
          return false;
        }else{
          return true;
        }
      } catch (\PDOException $e){
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
