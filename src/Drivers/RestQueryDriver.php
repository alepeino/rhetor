<?php
namespace Alepeino\Rhetor\Drivers;

use Alepeino\Rhetor\ResourceNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Zttp\Zttp;

class RestQueryDriver implements QueryDriver
{
    private $resource;

    private $options = [
        'CREATE_METHOD' => 'POST',
        'RETRIEVE_METHOD' => 'GET',
        'UPDATE_METHOD' => 'PUT',
        'DELETE_METHOD' => 'DELETE',
        'bodyFormat' => 'json',
        'responseFormat' => 'json',
        'resolutionAccessor' => null,
        'headers' => [],
    ];

    public function __construct(\Alepeino\Rhetor\Resource $resource, $extraOptions = [])
    {
        $this->resource = $resource;
        $this->options = Arr::merge($this->options, $resource->getDriverOptions(), $extraOptions);
    }

    public function getResourceEndpoint()
    {
        $trimSlash = function ($s) { return trim($s, '/'); };

        return $trimSlash(
            $trimSlash($this->resource->getSite())
            . '/'
            . $trimSlash($this->resource->getElementName() ?? Str::lower(Str::plural(class_basename($this->resource))))
            . $this->getResourceIdentifier()
        );
    }

    public function fetchOne()
    {
        $response = $this->doRequest($this->options['RETRIEVE_METHOD'], $this->resource->getEndpoint());

        return $response;
    }

    public function fetchAll()
    {
        $response = $this->doRequest($this->options['RETRIEVE_METHOD'], $this->resource->getEndpoint());

        return $response;
    }

    public function put()
    {
        $method = $this->options[$this->resource->exists() ? 'UPDATE_METHOD' : 'CREATE_METHOD'];
        $response = $this->doRequest($method, $this->resource->getEndpoint(), $this->resource->getAttributes());

        return $response;
    }

    public function doRequest($method, $url, $params = [])
    {
        return $this->resolveResponse(
            Zttp::bodyFormat($this->options['bodyFormat'])
                ->withHeaders($this->options['headers'])
                ->$method($url, $params)
        );
    }

    public function resolveResponse($response)
    {
        if ($response->status() == '404') {
            throw new ResourceNotFoundException(get_class($this->resource), $this->resource->getKey() ?: []);
        } elseif ($response->status() >= 400) {
            throw new HttpException($response->status(), Response::$statusTexts[$response->status()]);
        }

        switch ($this->options['responseFormat']) {
            case 'json':
                return Arr::get($response->json(), $this->options['resolutionAccessor']);
        }
    }

    private function getResourceIdentifier()
    {
        return $this->resource->exists()
            ? $this->replaceUriPlaceholders(
                ($this->resource->getIdentifier() ?? "/{{$this->resource->getKeyName()}}"))
            : '';
    }

    private function replaceUriPlaceholders($identifier)
    {
        switch ($identifier[0]) {
            case '/':
                return preg_replace_callback('/{(.+?)}/', [$this, 'getResourceMatchedAttribute'], $identifier);
            case '?':
                preg_match_all('/(?:[\?&]?(.+?))={(.+?)}/', $identifier, $matches, PREG_SET_ORDER);
                $query = array_reduce($matches, function ($q, $match) {
                    array_shift($match);
                    $q[$match[0]] = $this->getResourceMatchedAttribute($match);
                    return $q;
                }, []);
                return '?'.http_build_query($query);
            default:
                return $identifier;
        }
    }

    private function getResourceMatchedAttribute($match)
    {
        if (! ($setAttribute = $this->resource->getAttribute($match[1]))) {
            throw new LogicException("Attribute [{$match[1]}] not defined on resource.");
        }

        return $setAttribute;
    }
}
