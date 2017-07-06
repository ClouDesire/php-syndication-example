<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

/**
 * @covers CloudesireAPIClient
 */
final class CloudesireAPIClientTest extends TestCase
{
    use HttpMockTrait;

    public static function setUpBeforeClass()
    {
        static::setUpHttpMockBeforeClass('8082', 'localhost');
    }

    public static function tearDownAfterClass()
    {
        static::tearDownHttpMockAfterClass();
    }

    public function setUp()
    {
        $this->setUpHttpMock();
    }

    public function tearDown()
    {
        $this->tearDownHttpMock();
    }

    public function testCanConstructApiClientObject()
    {
        $this->http->mock
            ->when()
                ->methodIs('GET')
                ->pathIs('/login?expire=false')
            ->then()
                ->header('Content-Type', 'application/json')
                ->body('"a-valid-token"')
            ->end();
        $this->http->setUp();

        $client = new \Cloudesire\APIClient('http://localhost:8082', 'test-admin', 'test-password');
        $this->assertNotNull($client);
    }
}
