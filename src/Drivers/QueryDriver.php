<?php
namespace Alepeino\Rhetor\Drivers;

interface QueryDriver
{
    public function __construct($options = []);

    public function getResourceEndpoint(\Alepeino\Rhetor\Resource $resource);

    public function fetchOne(\Alepeino\Rhetor\Resource $resource);

    public function fetchAll();

    public function put();
}
