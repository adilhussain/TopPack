<?php

use Slim\Http\Request;
use Slim\Http\Response;


$app->get('/', function (Request $request, Response $response, array $args) {

    $this->logger->info("Slim-Skeleton '/' route");
    return $this->renderer->render($response, 'index.html', $args);
});

$app->get('/packages/top', '\Controllers\TopController:topPackages');
$app->get('/packages/all', '\Controllers\TopController:allPackages');

$app->get('/packages/{language}', SearchController::class);

$app->post('/package/import/', ImportController::class);
