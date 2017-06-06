<?php
namespace Alepeino\Rhetor\Resources;

use Alepeino\Rhetor\Resource;

class Post extends Resource
{
    protected $site = 'http://localhost:8999';

    public static function seedData()
    {
        return [
            [
                'id' => 1,
                'title' => 'Post 1',
                'body' => 'Body Post 1',
            ],
            [
                'id' => 2,
                'title' => 'Post 2',
                'body' => 'Body Post 2',
            ],
        ];
    }
}

