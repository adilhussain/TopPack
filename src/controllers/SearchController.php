<?php
namespace Controllers;

use Psr\Container\ContainerInterface;

class SearchController{
  protected $ci;

  public function __construct(ContainerInterface $ci){
      $this->ci = $ci;
  }

  public function __invoke($request, $response, $args) {
      $this->ci->logger->info("Slim-Skeleton '/packages/javascript/' route");
      $searchKeyword = $request->getQueryParam('keyword', $default = null);
      if ($searchKeyword == null){
        $newResponse = $response->withJson(["error" => true, "message" => "Please provide a search keyword."]);
        return $newResponse;
      }
      $language = $args['language'];
      $data = $this->ci->ExternalAPIHandler->getGITRepos($searchKeyword, $language);
      $counter = 0;
      foreach($data as $repo){
        if ($this->ci->StorageHandler->checkIfRepoImported($repo["html_url"], $this->ci->pdo)){
          $data[$counter]["exists"] = true;
        }
        $counter++;
      }
      $newResponse = $response->withJson($data);
      return $newResponse;
  }
}
 ?>
