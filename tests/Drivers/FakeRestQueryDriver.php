<?php
namespace Alepeino\Rhetor\Drivers;

use PHPUnit\Framework\Assert as PHPUnit;

class FakeRestQueryDriver extends RestQueryDriver
{
    private $lastRequest;

    public function doRequest($method, $url, $params = [])
    {
        $this->lastRequest = func_get_args();

        return [];
    }

    public function assertMadeRequest()
    {
        collect(func_get_args())->each(function ($expected, $i) {
            if (! is_null($expected) && isset($this->lastRequest[$i])) {
                PHPUnit::assertEquals($expected, $this->lastRequest[$i]);
            }
        });
    }
}
