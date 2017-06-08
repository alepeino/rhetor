<?php
namespace Alepeino\Rhetor;

class QueryBuilder
{
    private $resource;
    private $driver;

    public function __construct(\Alepeino\Rhetor\Resource $resource)
    {
        $driverClass = $resource->getDriverClass();
        $this->driver = new $driverClass($resource);
        $this->resource = $resource;
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

        return $this->resource->fill($attributes)->refresh();
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

    private function newResourceInstance($attributes)
    {
        $resourceClass = get_class($this->resource);

        return new $resourceClass($attributes);
    }
}
