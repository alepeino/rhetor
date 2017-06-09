<?php
namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Drivers\QueryDriver;
use Illuminate\Support\Arr;

class QueryBuilder
{
    private $resource;
    private $driver;
    private $constraints = [];

    public function __construct(\Alepeino\Rhetor\Resource $resource, QueryDriver $driver)
    {
        $this->resource = $resource;
        $this->driver = $driver;
    }

    public function create($attributes)
    {
        return $this->resource->update($attributes);
    }

    public function all()
    {
        return array_map(function ($attributes) {
            return $this->newResourceInstance($attributes);
        }, $this->driver->fetchAll());
    }

    public function find($id)
    {
        try {
            return $this->findOrFail($id);
        } catch (ResourceNotFoundException $e) {
            return null;
        }
    }

    public function findOrFail($id)
    {
        if (is_array($id)) {
            $attributes = $id;
        } else {
            $attributes = [
                $this->resource->getKeyName() => $id,
            ];
        }

        $data = $this->driver->fetchOne($this->resource->fill($attributes));

        return $this->resolveData('find', $data);
    }

    public function save()
    {
        return $this->driver->put();
    }

    public function fetch()
    {
        return $this->driver->fetchOne();
    }

    public function getEndpoint()
    {
        return $this->driver->getResourceEndpoint();
    }

    /**
     * @return \Alepeino\Rhetor\Drivers\QueryDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    private function resolveData($method, $data)
    {
        $resourceMethod = 'resolve'.ucfirst($method).'Data';

        if (method_exists($this->resource, $resourceMethod)) {
            return $resourceMethod($data);
        }

        return $this->newResourceInstance($data);
    }

    private function newResourceInstance($attributes)
    {
        $resourceClass = get_class($this->resource);

        return new $resourceClass($attributes);
    }
}
