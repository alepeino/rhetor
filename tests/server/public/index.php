<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$app = new Laravel\Lumen\Application(realpath(__DIR__.'/../'));

$app->get('/', function () { return response('OK'); });

$app->get('/posts', function () {
    return \Alepeino\Rhetor\AbstractTestCase::getStoredData('posts');
});

$app->get('/posts/{id}', function ($id) {
    return collect(\Alepeino\Rhetor\AbstractTestCase::getStoredData('posts'))
        ->first(function ($post) use ($id) {
            return $post['id'] == $id;
        }) ?: response('', 404);
});

$app->post('/posts', function (\Illuminate\Http\Request $request) {
    $posts = \Alepeino\Rhetor\AbstractTestCase::getStoredData('posts');
    $request->merge(['id' => count($posts)]);
    $attributes = $request->only('id', 'title', 'body');
    $post = new \Alepeino\Rhetor\Resources\Post($attributes);
    $posts[] = $post->getAttributes();
    \Alepeino\Rhetor\AbstractTestCase::storeData($posts, 'posts');
    return response($post, 201);
});

$app->put('/posts/{id}', function (\Illuminate\Http\Request $request) {
    $posts = collect(\Alepeino\Rhetor\AbstractTestCase::getStoredData('posts'));
    $post = $posts->first(function ($post) use ($request) {
        return $post['id'] == $request['id'];
    });

    if (! $post) {
        return response('', 404);
    }

    $updated = $request->only('id', 'title', 'body');

    \Alepeino\Rhetor\AbstractTestCase::storeData(
        $posts->map(function ($post) use ($updated) {
            if ($post['id'] == $updated['id']) {
                return $updated;
            } else {
                return $post;
            }
        }), 'posts');

    return response($updated, 201);
});

$app->run();
