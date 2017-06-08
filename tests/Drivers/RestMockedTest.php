<?php
namespace Alepeino\Rhetor\Drivers;

use Alepeino\Rhetor\AbstractTestCase;
use Alepeino\Rhetor\QueryBuilder;
use Alepeino\Rhetor\Resources\Article;

class RestMockedTest extends AbstractTestCase
{
    public function testSimple()
    {
        $article = new class () extends Article {
            protected $driverClass = FakeRestQueryDriver::class;
            protected $elementName = 'articles';
        };

        $builder = new QueryBuilder($article);
        $article->setBuilder($builder);
        $builder->all();

        $article->getDriver()->assertMadeRequest('GET', 'https://example.com/articles');
    }
}
