<?php
namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Drivers\QueryDriver;

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

    public function create($attributes): \Alepeino\Rhetor\Resource
    {
        $this->resource = $this->newResourceInstance($attributes);
        $this->getDriver()->setResource($this->resource);

        return $this->save();
    }

    public function all(): array
    {
        return array_map(function ($attributes) {
            return $this->newResourceInstance($attributes);
        }, $this->getDriver()->fetchMany());
    }

    public function find($id): ?\Alepeino\Rhetor\Resource
    {
        try {
            return $this->findOrFail($id);
        } catch (ResourceNotFoundException $e) {
            return null;
        }
    }

    public function findOrFail($id): \Alepeino\Rhetor\Resource
    {
        $this->resource->fill(is_array($id) ? $id : [$this->resource->getKeyName() => $id]);
        $new_data = $this->resource->resolveFind($this->getDriver()->fetchOne());

        return $this->resource->fill($new_data);
    }

    public function save(): \Alepeino\Rhetor\Resource
    {
        $response = $this->getDriver()->put();

        $new_data = $this->resource->exists()
            ? $this->resource->resolveSave($response)
            : $this->resource->resolveCreate($response);

        return $this->resource->fill($new_data);
    }

    public function getEndpoint(): string
    {
        return $this->getDriver()->getResourceEndpoint();
    }

    public function getDriver(): QueryDriver
    {
        return $this->driver;
    }

    private function newResourceInstance($attributes = []): \Alepeino\Rhetor\Resource
    {
        $resourceClass = get_class($this->resource);

        return new $resourceClass($attributes);
    }
}
