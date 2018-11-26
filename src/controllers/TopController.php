<?php
namespace Controllers;

use Psr\Container\ContainerInterface;

class TopController{
  protected $ci;

  public function __construct(ContainerInterface $ci){
      $this->ci = $ci;
  }

  public function __invoke($request, $response, $args) {
      $this->ci->logger->info("Slim-Skeleton '/packages/top' route");

      $data = $this->ci->StorageHandler->getTopPackages($this->ci->pdo);

      $newResponse = $response->withJson($data);
      return $newResponse;
  }

  public function allPackages($request, $response, $args){
    $this->ci->logger->info("Slim-Skeleton '/packages/allPackages' route");
    $data = $this->ci->StorageHandler->getAllPackages($this->ci->pdo);

    $newResponse = $response->withJson($data);
    return $newResponse;
  }

}
 ?>
