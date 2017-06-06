<?php

namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Resources\Post;

trait SeedData
{
    public static function seedLocalData()
    {
        static::storeData(Post::seedData(), 'posts');
    }

    public static function getStoredData($element)
    {
        return json_decode(@file_get_contents(__DIR__."/server/storage/{$element}.json") ?: '[]', JSON_OBJECT_AS_ARRAY);
    }

    public static function storeData($data, $element)
    {
        file_put_contents(__DIR__."/server/storage/{$element}.json", json_encode($data));
    }

    public static function deleteLocalStorage()
    {
        @unlink(__DIR__.'/server/storage/posts.json');
    }
}
