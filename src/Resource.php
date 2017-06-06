<?php
namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Drivers\RestQueryDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class Resource
{
    protected $driver = 'REST';
    private $queryDriver;
    protected $driverOptions = [];

    protected $site;
    protected $elementName;

    protected $primaryKey = 'id';
    protected $instancePath;

    protected $attributes = [];
    protected $relations = [];

    public function __construct($attributes = [])
    {
        switch ($this->driver) {
            case 'REST':
                $this->queryDriver = new RestQueryDriver($this, $this->driverOptions);
            break;
        }

        $this->fill($attributes);
    }

    public function fill($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function refresh()
    {
        return $this->fill($this->queryDriver->fetchOne());
    }

    public function getEndpoint()
    {
        return $this->queryDriver->getResourceEndpoint();
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getElementName()
    {
        return $this->elementName;
    }

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function getInstancePath()
    {
        return $this->instancePath;
    }

    public function getAttributes()
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

    public function setAttribute($key, $value)
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

    public function relationLoaded($key)
    {
        return Arr::exists($this->relations, $key);
    }

    public function hasGetMutator($key)
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    public function hasSetMutator($key)
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

    public static function all()
    {
        return array_map(function ($attributes) {
            return new static($attributes);
        }, (new static())->queryDriver->fetchAll());
    }

    public static function find($id)
    {
        try {
            return static::findOrFail($id);
        } catch (ResourceNotFoundException $e) {
            return null;
        }
    }

    public static function findOrFail($id)
    {
        $instance = new static();

        if (is_array($id)) {
            $attributes = $id;
        } else {
            $attributes = [
                $instance->getKeyName() => $id,
            ];
        }

        return $instance->fill($attributes)->refresh();
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
}
