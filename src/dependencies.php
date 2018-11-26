<?php
// DIC configuration
require '../vendor/autoload.php';

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

//PDO
$container['pdo'] = function($c){
    $config = $c->get('settings')['pdo'];
    $dsn = "{$config['engine']}:host={$config['host']};dbname={$config['database']}";
    $username = $config['username'];
    $password = $config['password'];
    return new PDO($dsn, $username, $password, $config['options']);
};

// controllers
$container['SearchController'] = function($c){
  return new Controllers\SearchController($c);
};

$container['ImportController'] = function($c){
  return new Controllers\ImportController($c);
};

$container['TopController'] = function($c){
  return new Controllers\TopController($c);
};

// handlers
$container["ExternalAPIHandler"] = function($c){
  return new Handlers\ExternalAPIHandler();
};
$container["StorageHandler"] = function($c){
  return new Handlers\StorageHandler();
};
