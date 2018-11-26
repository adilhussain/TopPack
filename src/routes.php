<?php

use Slim\Http\Request;
use Slim\Http\Response;


$app->get('/', function (Request $request, Response $response, array $args) {

    $this->logger->info("Slim-Skeleton '/' route");
    return $this->renderer->render($response, 'index.html', $args);
});

$app->get('/packages/top', TopController::class);
$app->get('/packages/all', TopController::class, ':allPackages');

$app->get('/packages/{language}', SearchController::class);

$app->post('/package/import/', ImportController::class);
