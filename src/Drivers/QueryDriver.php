<?php
namespace Alepeino\Rhetor\Drivers;

interface QueryDriver
{
    public function __construct(\Alepeino\Rhetor\Resource $resource, $options = []);

    public function getResourceEndpoint();

    public function fetchOne();

    public function fetchAll();
}
