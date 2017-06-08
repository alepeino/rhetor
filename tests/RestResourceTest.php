<?php
namespace Alepeino\Rhetor;

use Alepeino\Rhetor\Resources\Post;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RestResourceTest extends AbstractTestCase
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
        try {
            Post::findOrFail(48);
            $this->fail();
        } catch (ResourceNotFoundException $e) {
           $this->assertEquals([48], $e->getIds());
           $this->assertEquals(Post::class, $e->getResource());
        }
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

    public function testCreate()
    {
        $post = Post::create(['title' => 'New post title', 'body' => 'The body']);

        $this->assertNotNull($post->id);
        $this->assertEquals('New post title', $post->title);

        $this->assertCount(3, Post::all());
    }

    public function testUpdate()
    {
        $post = Post::find(1);

        $post->update(['title' => 'Updated title']);
        $this->assertNotNull($post->id);
        $this->assertEquals('Updated title', $post->title);

        $this->assertCount(2, Post::all());
    }

    public function testHttpException500()
    {
        try {
            $resource = new class () extends Post {
                protected $site = 'http://localhost:8999/status/500';
                protected $elementName = 'posts';
            };
            $resource->all();
            $this->fail('Should have got 500 code');
        } catch (HttpException $e) {
            $this->assertEquals(500, $e->getStatusCode());
        }
    }

    public function testHttpException405()
    {
        try {
            $resource = new class (['id' => 1]) extends Post {
                protected $elementName = 'posts';
                protected $driverOptions = ['UPDATE_METHOD' => 'POST'];
            };
            $resource->update(['title' => 'Should Fail']);
            $this->fail('Should have got 405 code');
        } catch (HttpException $e) {
            $this->assertEquals(405, $e->getStatusCode());
        }
    }
}
