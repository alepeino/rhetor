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
    private $options = [
        'CREATE_METHOD' => 'POST',
        'RETRIEVE_METHOD' => 'GET',
        'UPDATE_METHOD' => 'PUT',
        'DELETE_METHOD' => 'DELETE',
        'bodyFormat' => 'json',
        'headers' => [],
    ];

    public function __construct($extraOptions = [])
    {
        $this->options = Arr::merge($this->options, $extraOptions);
    }

    public function getResourceEndpoint(\Alepeino\Rhetor\Resource $resource)
    {
        $trimSlash = function ($s) { return trim($s, '/'); };

        return $trimSlash(
            $trimSlash($resource->getSite())
            . '/'
            . $trimSlash($resource->getElementName() ?? Str::lower(Str::plural(class_basename($resource))))
            . $this->getResourceIdentifier($resource)
        );
    }

    public function fetchOne(\Alepeino\Rhetor\Resource $resource)
    {
        $response = $this->doRequest($this->options['RETRIEVE_METHOD'], $resource->getEndpoint());

        return $response;
    }

    public function fetchAll()
    {
        $response = $this->doRequest($this->options['RETRIEVE_METHOD'], $resource->getEndpoint());

        return $response;
    }

    public function put()
    {
        $method = $this->options[$resource->exists() ? 'UPDATE_METHOD' : 'CREATE_METHOD'];
        $response = $this->doRequest($method, $resource->getEndpoint(), $resource->getAttributes());

        return $response;
    }

    public function doRequest($method, $url, $params = [])
    {
        $response = Zttp::bodyFormat($this->options['bodyFormat'])
                ->withHeaders($this->options['headers'])
                ->$method($url, $params);

        if ($response->status() == '404') {
            throw new ResourceNotFoundException(get_class($resource), $resource->getKey() ?: []);
        } elseif ($response->status() >= 400) {
            throw new HttpException($response->status(), Response::$statusTexts[$response->status()]);
        } else {
            return $this->handleResponse($response);
        }
    }

    public function handleResponse($response)
    {
        switch (Arr::get($this->options, 'responseContentType', explode(';', $response->header('Content-Type'))[0])) {
            case 'json':
            case 'application/json':
                return $this->resolveResponse($response->json());
            case 'text/xml':
                $xml = simplexml_load_string($response->body());
                $data = json_decode(json_encode($xml), true);
                return $this->resolveResponse($data);
            default:
                return $this->resolveResponse($response);
        }
    }

    public function resolveResponse($responseData)
    {
        return Arr::get($responseData, Arr::get($this->options, 'resolutionAccessor'));
    }

    private function getResourceIdentifier(\Alepeino\Rhetor\Resource $resource)
    {
        return $resource->exists()
            ? $this->replaceUriPlaceholders($resource)
            : '';
    }

    private function replaceUriPlaceholders(\Alepeino\Rhetor\Resource $resource)
    {
        $identifier = $resource->getIdentifier() ?? "/{{$resource->getKeyName()}}";

        switch ($identifier[0]) {
            case '/':
                return preg_replace_callback('/{(.+?)}/', function ($match) use ($resource) {
                    return $this->getResourceAttributeOrFail($resource, $match[1]);
                }, $identifier);
            case '?':
                preg_match_all('/(?:[\?&]?(.+?))={(.+?)}/', $identifier, $matches, PREG_SET_ORDER);
                $query = array_reduce($matches, function ($q, $match) use ($resource) {
                    $q[$match[1]] = $this->getResourceAttributeOrFail($resource, $match[1]);
                    return $q;
                }, []);
                return '?'.http_build_query($query);
            default:
                return $identifier;
        }
    }

    private function getResourceAttributeOrFail(\Alepeino\Rhetor\Resource $resource, $key)
    {
        if (! ($found = $resource->getAttribute($key))) {
            throw new LogicException("Attribute [{$key}] not defined on resource.");
        }

        return $found;
    }
}
