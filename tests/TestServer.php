<?php

namespace Alepeino\Rhetor;

use Symfony\Component\Process\Process;

trait TestServer
{
    static $process;

    public static function setUpBeforeClass()
    {
        static::startTestServer();
    }

    public static function tearDownAfterClass()
    {
        static::killTestServer();
    }

    public function setUp()
    {
        parent::setUp();

        static::seedLocalData();
    }

    public function tearDown()
    {
        parent::tearDown();

        static::deleteLocalStorage();
    }

    protected static function startTestServer()
    {
        $host_and_port = 'localhost:'.getenv('TEST_SERVER_PORT');
        $doc_root = __DIR__.'/server/public';

        static::$process = new Process("exec php -S {$host_and_port} -t {$doc_root}");
        static::$process->start();

        for ($tries = getenv('TEST_SERVER_TRIES'); $tries--; ) {
            if (@file_get_contents("http://$host_and_port") == 'OK') {
                return;
            } else {
                sleep(1);
            }
        }

        static::killTestServer();
        static::markTestSkipped('Could not run test server on '.static::class.'.');
    }

    protected static function killTestServer()
    {
        static::$process->stop();
    }
}
