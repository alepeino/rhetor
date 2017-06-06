<?php
namespace Alepeino\Rhetor\Resources;

use Alepeino\Rhetor\Resource;

class User extends Resource
{
    protected $site = 'http://localhost:8999';

    public static function seedData()
    {
        return [
            [
                'id' => 1,
                'name' => 'A',
                'email' => 'Body Post 1',
            ],
        ];
    }
}

