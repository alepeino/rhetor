<?php
namespace Alepeino\Rhetor\Examples;

use Alepeino\Rhetor\AbstractTestCase;

class BggTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        foreach (glob(__DIR__.'/../../examples/BoardGameGeek/*.php') as $file) {
            require_once $file;
        }
    }
    public function testFindGameById()
    {
        $agricola = \Boardgame::find(31260);

        $this->assertEquals('Agricola', $agricola->name);
        $this->assertEquals('Uwe Rosenberg', $agricola->boardgamedesigner);
    }
}
