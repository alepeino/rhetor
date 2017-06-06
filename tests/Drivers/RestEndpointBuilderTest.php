<?php
namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Resources\User;

class RestEndpointBuilderTest extends AbstractTestCase
{
    public function testExplicitName()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'stuff';
        };

        $this->assertEquals('http://example.com/stuff', $resource->getEndpoint());
    }

    public function testExplicitNameImplicitKey()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'stuff';
        };

        $resource->id = 1;

        $this->assertEquals('http://example.com/stuff/1', $resource->getEndpoint());
    }

    public function testImplicitName()
    {
        $resource = new User();

        $this->assertEquals('http://localhost:8999/users', $resource->getEndpoint());
    }

    public function testImplicitNameImplicitKey()
    {
        $resource = new User();
        $resource->id = 1;

        $this->assertEquals('http://localhost:8999/users/1', $resource->getEndpoint());
    }

    public function testNonDefaultPrimaryKey()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'stuff';
            protected $primaryKey = 'somekey';
        };

        $resource->somekey = 'xx';

        $this->assertEquals('http://example.com/stuff/xx', $resource->getEndpoint());
    }

    public function testInstanceUriWithMultipleAttributes()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'article';
            protected $instancePath = '/{id}/{slug}';
        };

        $resource->id = 2;
        $resource->slug = 'article-title-slug';

        $this->assertEquals('http://example.com/article/2/article-title-slug', $resource->getEndpoint());
    }

    public function testInstanceUriWithMultipleAttributesAndNonDefaultPrimaryKey()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'article';
            protected $instancePath = '/{date}/{slug}';
            protected $primaryKey = 'slug';
        };

        $resource->slug = 'article-title-slug';
        $resource->date = '2017-05-04';

        $this->assertEquals('http://example.com/article/2017-05-04/article-title-slug', $resource->getEndpoint());
    }

    public function testInstanceWithUndefinedAttributeInPathThrowsException()
    {
        $this->expectException(\LogicException::class);

        $resource = new class () extends Resource {
            protected $host = 'example.com';
            protected $path = '/posts';
            protected $instancePath = '/{id}/{slug}';
        };

        $resource->id = 2;
        $resource->getEndpoint();
    }
}

