<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$app = new Laravel\Lumen\Application(realpath(__DIR__.'/../'));

$app->get('/', function () { return response('OK'); });

$app->get('/posts', function () {
    return \Alepeino\Rhetor\Resources\Post::seedData();
});

$app->get('/posts/{id}', function ($id) {
    return collect(\Alepeino\Rhetor\Resources\Post::seedData())
        ->first(function ($post) use ($id) {
            return $post['id'] == $id;
    }) ?: response('', 404);
});

$app->run();
