<?php
namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Resources\Post;

class SimpleTest extends AbstractTestCase
{
    use TestServer;

    public function testFindOk()
    {
        $this->assertEquals(1, Post::find(1)->id);
        $this->assertEquals('Post 1', Post::find(1)->title);
    }

    public function testFindFails()
    {
        $this->assertNull(Post::find(48));
    }

    public function testFindOrFailFails()
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->assertNull(Post::findOrFail(48));
    }

    public function testAll()
    {
        $response = Post::all();

        $this->assertCount(2, $response);
        $this->assertEquals('Post 1', $response[0]->title);
        $this->assertEquals('Post 2', $response[1]->title);
    }

    public function testAllFails()
    {
        $this->expectException(ResourceNotFoundException::class);

        $resource = new class () extends Post {
            protected $elementName = 'not-exists';
        };

        $resource::all();
    }
}
