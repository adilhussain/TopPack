<?php
namespace Handlers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ExternalAPIHandler
{
    public static $git_search_endpoint = 'https://api.github.com/search/repositories';

    //fetches repos based on language and keyword
    public function getGITRepos(string $keyword, $language) {
        $client = new Client();
        $params = array('q' => $keyword.' in:name language:'.$language,
                        'sort' => 'stars',
                        'order' => 'desc');
        $res = $client->get(self::$git_search_endpoint, ['query' => $params]);
        if ($res->getStatusCode() == 200){
          $response_json = json_decode($res->getBody());
          return $this->filterJSON($response_json);
        }
    }

    // sanotizes to only the attributes that we need
    private function filterJSON($response_json){
      $sanitized_results = [];
      $counter = 0;
      foreach($response_json->items as $item){
        $counter++;
        $sanitized_item = [
          'updated_at' => $item->updated_at,
          'description' => $item->description,
          'forks_count' => $item->forks_count,
          'html_url' => $item->html_url,
          'name' => $item->name,
          'stargazers_count' => $item->stargazers_count,
          'watchers_count' => $item->watchers_count,
          'owner' => $item->owner->login,
          'item_no' => $counter
        ];
        array_push($sanitized_results, $sanitized_item);
      }
      return $sanitized_results;
    }

    // check if there is a package.json and fetch it
    public function getPackageDotJSON(string $url, string $owner, string $description, string $name, string $stargazers_count, string $watchers_count, string $forks_count) {
        $content_url = 'https://api.github.com/repos/'.str_replace('https://github.com/', '', $url).'/contents/package.json';

        $client = new Client();

        // check for package.json
        try{
          $res = $client->get($content_url);
          $json_response = json_decode($res->getBody());
          $package_json_url = $json_response->download_url;
        }catch (ClientException $e){
          return ['error' => true, 'message' => 'There is no package.json file in the project.'];
        }

        // get package.json
        try{
          $res = $client->get($package_json_url);
        }catch (ClientException $e){
          return ['error' => true, 'message' => 'There is an error connecting to Github.'];
        }

        // parse package.json
        if ($res->getStatusCode() == 200){
          $response_json = json_decode($res->getBody());
          $repository = $this->getDependencies($response_json);
          $repository['name'] = $name;
          $repository['owner'] = $owner;
          $repository['description'] = $description;
          $repository['url'] = $url;
          $repository['forks_count'] = $forks_count;
          $repository['stargazers_count'] = $stargazers_count;
          $repository['watchers_count'] = $watchers_count;

          return $repository;
        }else{
          return ['error' => true, 'message' => 'There is an error connecting to Github.'];
        }
    }

    //Parse the JSON response to get dependencies and devDependencies
    private function getDependencies($response_json){
      if(!isset($response_json->devDependencies) && !isset($response_json->dependencies)){
        return array('error' => true, 'message' => 'The package.json has no dependencies.');
      }
      $dependencies = [];
      if(isset($response_json->devDependencies)){
        foreach($response_json->devDependencies as $dep => $versions){
          array_push($dependencies, $dep);
        }
      }
      if(isset($response_json->dependencies)) {
        foreach($response_json->dependencies as $dep => $versions){
          array_push($dependencies, $dep);
        }
      }
      $repository['dependencies'] = $dependencies;
      return $repository;
    }
}
 ?>
