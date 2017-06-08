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
 * @method \Alepeino\Rhetor\Drivers\QueryDriver getDriver()
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

    protected $site;
    protected $elementName;

    protected $primaryKey = 'id';
    protected $identifier;

    protected $attributes = [];
    protected $relations = [];

    public function __construct($attributes = [])
    {
        $this->fill($attributes);
    }

    public function getDriverClass(): string
    {
        return $this->driverClass;
    }

    public function getDriverOptions(): array
    {
        return $this->driverOptions;
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
        if (! $key) {
            return;
        }

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

    public function getAttributeValue($key)
    {
        return $this->getAttributeFromArray($key);
    }

    public function getRelationValue($key)
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }

    public function relationLoaded($key): bool
    {
        return Arr::exists($this->relations, $key);
    }

    public function hasGetMutator($key): bool
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    public function hasSetMutator($key): bool
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    protected function getAttributeFromArray($key)
    {
        return Arr::get($this->attributes, $key);
    }

    protected function getRelationshipFromMethod($method)
    {
        return [];
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

    protected function getBuilder(): QueryBuilder
    {
        return $this->queryBuilder ?: $this->queryBuilder = new QueryBuilder($this);
    }

    public function setBuilder($builder)
    {
        return $this->queryBuilder = $builder;
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
        $builder = count($parameters) && $parameters[0] instanceof QueryBuilder
            ? array_shift($parameters)
            : $this->getBuilder();

        return $builder->$method(...$parameters);
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
        $builder = count($parameters) && $parameters[0] instanceof QueryBuilder
            ? array_shift($parameters)
            : (new static())->getBuilder();

        return $builder->$method(...$parameters);
    }
}
