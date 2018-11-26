<?php
namespace Controllers;

use Psr\Container\ContainerInterface;

class ImportController{
  protected $ci;

  public function __construct(ContainerInterface $ci){
      $this->ci = $ci;
  }

  public function __invoke($request, $response, $args) {
      $post_data = $request->getParsedBody();
      $data = $this->ci->ExternalAPIHandler->getPackageDotJSON($post_data['url'], $post_data['owner'], $post_data['description'], $post_data['name']);
      if (array_key_exists("error", $data)){
        $newResponse = $response->withJson($data);
      }else{
        $storageHandlerRespose = $this->ci->StorageHandler->storeOnImport($data, $this->ci->pdo);
        $newResponse = $response->withJson($storageHandlerRespose);
      }
      return $newResponse;
  }
}
 ?>
