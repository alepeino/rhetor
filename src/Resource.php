<?php
namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Drivers\QueryDriver;
use Alepeino\Rhetor\Drivers\RestQueryDriver;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Delegated "magic" methods:
 *
 * @method static string getEndpoint()
 * @method static \Alepeino\Rhetor\Resource create($attributes)
 * @method static \Alepeino\Rhetor\Resource find($id)
 * @method static \Alepeino\Rhetor\Resource findOrFail($id)
 * @method static \Alepeino\Rhetor\Resource[] all()
 */
abstract class Resource implements Jsonable
{
    protected $queryBuilder;

    protected $driverClass = RestQueryDriver::class;
    protected $driverOptions = [];
    protected $driver;

    protected $site;
    protected $elementName;

    protected $primaryKey = 'id';
    protected $identifier;

    protected $attributes = [];
    protected $relations = [];

    public function __construct($attributes = [])
    {
        $this->fill($attributes);
        $this->config();
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function getElementName(): ?string
    {
        return $this->elementName;
    }

    public function getKeyName(): ?string
    {
        return $this->primaryKey;
    }

    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getDriver(): QueryDriver
    {
        return $this->driver;
    }

    public function setDriver($driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function exists(): bool
    {
        return Arr::exists($this->attributes, $this->getKeyName());
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($key)
    {
        if (Arr::exists($this->attributes, $key) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return;
        }

        return $this->getRelationValue($key);
    }

    public function setAttribute($key, $value): self
    {
        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';

            return $this->{$method}($value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    public function update($attributes): self
    {
        return $this->fill($attributes)->save();
    }

    public function fill($attributes = []): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function save(): self
    {
        $updated = $this->getBuilder()->save();

        return $this->fill($updated);
    }

    public function refresh(): self
    {
        $updated = $this->getBuilder()->fetch();

        return $this->fill($updated);
    }

    protected function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    protected function getRelationValue($key)
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }

    protected function relationLoaded($key): bool
    {
        return Arr::exists($this->relations, $key);
    }

    protected function hasGetMutator($key): bool
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    protected function hasSetMutator($key): bool
    {
        return method_exists($this, 'set'.Str::studly($key).'Attribute');
    }

    protected function mutateAttribute($key, $value)
    {
        return $this->{'get'.Str::studly($key).'Attribute'}($value);
    }

    protected function getAttributeFromArray($key)
    {
        return Arr::get($this->attributes, $key);
    }

    protected function getRelationshipFromMethod($method)
    {
        return [];
    }

    protected function config()
    {
        $this->driver = new $this->driverClass($this->getDriverOptions());
    }

    protected function createBuilder()
    {
        return new QueryBuilder($this, $this->driver);
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->attributes, $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Handle dynamic method calls into the corresponding builder method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->createBuilder()->$method(...$parameters);
    }

    /**
     * Handle dynamic static method calls into the corresponding builder method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static();

        if (count($parameters) && $parameters[0] instanceof QueryDriver) {
            $instance->setDriver(array_shift($parameters));
        }

        return $instance->createBuilder()->$method(...$parameters);
    }
}
