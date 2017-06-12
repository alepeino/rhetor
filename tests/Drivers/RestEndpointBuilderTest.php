<?php
namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Resources\Article;
use Alepeino\Rhetor\Resources\User;

class RestEndpointBuilderTest extends AbstractTestCase
{
    public function testStaticCall()
    {
        $this->assertEquals('https://example.com/articles', Article::getEndpoint());
    }

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

    public function testIdentifierWithMultipleAttributes()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'articles';
            protected $identifier = '/{id}/{slug}';
        };

        $resource->id = 2;
        $resource->slug = 'article-title-slug';

        $this->assertEquals('http://example.com/articles/2/article-title-slug', $resource->getEndpoint());
    }

    public function testIdentifierWithMultipleAttributesAndNonDefaultPrimaryKey()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'articles';
            protected $identifier = '/{date}/{slug}';
            protected $primaryKey = 'slug';
        };

        $resource->slug = 'article-title-slug';
        $resource->date = '2017-05-04';

        $this->assertEquals('http://example.com/articles/2017-05-04/article-title-slug', $resource->getEndpoint());
    }

    public function testInstanceWithUndefinedAttributeInPathThrowsException()
    {
        $this->expectException(\LogicException::class);

        $resource = new class () extends Resource {
            protected $host = 'example.com';
            protected $path = '/posts';
            protected $identifier = '/{id}/{slug}';
        };

        $resource->id = 2;
        $resource->getEndpoint();
    }

    public function testQueryStringIdentifier()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'articles';
            protected $identifier = '?id={id}';
        };

        $resource->id = 2;

        $this->assertEquals('http://example.com/articles?id=2', $resource->getEndpoint());
    }

    public function testIdentifierWithMultipleAttributesInQueryString()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'articles';
            protected $identifier = '?id={id}&slug={slug}';
        };

        $resource->id = 2;
        $resource->slug = 'article-title-slug';

        $this->assertEquals('http://example.com/articles?id=2&slug=article-title-slug', $resource->getEndpoint());
    }

    public function testMixedIdentifier()
    {
        $resource = new class () extends Resource {
            protected $site = 'http://example.com';
            protected $elementName = 'articles';
            protected $identifier = '/{id}?new={isNew}';
        };

        $resource->id = 2;
        $resource->isNew = 1;

        $this->assertEquals('http://example.com/articles/2?new=1', $resource->getEndpoint());
    }
}

