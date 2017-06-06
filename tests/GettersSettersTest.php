<?php
namespace Alepeino\Rhetor;

class GettersSettersTest extends AbstractTestCase
{
    public function testEmpty()
    {
        $resource = new class () extends Resource {};

        $this->assertEmpty($resource->getAttributes());
    }

    public function testSetter()
    {
        $resource = new class () extends Resource {};

        $resource->id = 1;
        $resource->foo = 'bar';

        $this->assertArraySubset(['id' => 1], $resource->getAttributes());
        $this->assertEquals(1, $resource->id);
        $this->assertEquals(1, $resource->getAttribute('id'));

        $this->assertArraySubset(['foo' => 'bar'], $resource->getAttributes());
        $this->assertEquals('bar', $resource->foo);
        $this->assertEquals('bar', $resource->getAttribute('foo'));
    }

    public function testConstructor()
    {
        $resource = new class (['id' => 1, 'foo' => 'bar']) extends Resource {};

        $this->assertArraySubset(['id' => 1], $resource->getAttributes());
        $this->assertEquals(1, $resource->id);
        $this->assertEquals(1, $resource->getAttribute('id'));

        $this->assertArraySubset(['foo' => 'bar'], $resource->getAttributes());
        $this->assertEquals('bar', $resource->foo);
        $this->assertEquals('bar', $resource->getAttribute('foo'));
    }
}
