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

    public function __construct(\Alepeino\Rhetor\Resource $resource, $options = [])
    {
        $this->resource = $resource;
        $this->options = array_merge_recursive($this->options, $options);
    }

    public function getResourceEndpoint()
    {
        // TODO query string params
        return $this->replaceUriPlaceholders(
            collect([
                $this->resource->getSite(),
                $this->resource->getElementName() ?: Str::lower(Str::plural(class_basename($this->resource))),
                $this->getResourceInstancePath(),
            ])
                ->filter()
                ->map(function ($segment) {
                    return trim($segment, '/');
                })
                ->implode('/'));
    }

    public function fetchOne()
    {
        $response = $this->doRequest($this->options['RETRIEVE_METHOD']);

        return $response;
    }

    public function fetchAll()
    {
        $response = $this->doRequest($this->options['RETRIEVE_METHOD']);

        return $response;
    }

    public function put()
    {
        $method = $this->options[$this->resource->exists() ? 'UPDATE_METHOD' : 'CREATE_METHOD'];
        $response = $this->doRequest($method);

        return $response;
    }

    public function doRequest($method)
    {
        return $this->resolveResponse(
            Zttp::bodyFormat($this->options['bodyFormat'])
                ->withHeaders($this->options['headers'])
                ->$method($this->resource->getEndpoint(), $this->resource->getAttributes()));
    }

    public function resolveResponse($response)
    {
        if ($response->status() == '404') {
            throw (new ResourceNotFoundException())
                ->setResource(
                    get_class($this->resource),
                    $this->resource->getKey()
                );
        } elseif ($response->status() >= 400) {
            throw new HttpException($response->status(), Response::$statusTexts[$response->status()]);
        }

        switch ($this->options['responseFormat']) {
            case 'json':
                return Arr::get($response->json(), $this->options['resolutionAccessor']);
        }
    }

    protected function getResourceInstancePath()
    {
        return $this->resource->exists()
            ? ($this->resource->getInstancePath() ?: "{{$this->resource->getKeyName()}}")
            : "";
    }

    protected function replaceUriPlaceholders($uri)
    {
        return preg_replace_callback('/{(.+?)}/', function ($match) {
            if (! ($replacement = $this->resource->getAttribute($match[1]))) {
                throw new LogicException("Attribute [{$match[1]}] not defined on resource.");
            }

            return $replacement;
        }, $uri);
    }
}
