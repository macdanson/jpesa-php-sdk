<?php

namespace JPesa\SDK\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use JPesa\SDK\JPesaClient;
use JPesa\SDK\Exceptions\JPesaException;
use JPesa\SDK\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

class JPesaClientTest extends TestCase
{
    private function makeClientWithMock(array $responses): JPesaClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $http = new HttpClient(['handler' => $handlerStack, 'base_uri' => 'https://my.jpesa.com/api/']);

        // Partially mock JPesaClient to inject our $http
        return new class('https://my.jpesa.com/api/', 'TEST_KEY') extends JPesaClient {
            public function __construct(string $baseUrl, string $apiKey)
            {
                parent::__construct($baseUrl, $apiKey);
            }
            public function setHttpClient(HttpClient $client): void
            {
                $r = new \ReflectionProperty(JPesaClient::class, 'http');
                $r->setAccessible(true);
                $r->setValue($this, $client);
            }
        };
    }

    public function testCreditSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['api_status'=>'success','tid'=>'T1','msg'=>'ok']))
        ]);
        $handler = HandlerStack::create($mock);
        $http = new HttpClient(['handler'=>$handler,'base_uri'=>'https://my.jpesa.com/api/']);

        $client = new JPesaClient('https://my.jpesa.com/api/', 'K1');
        // inject http
        $ref = new \ReflectionProperty(JPesaClient::class, 'http');
        $ref->setAccessible(true);
        $ref->setValue($client, $http);

        $res = $client->credit(['mobile'=>'256700000001','amount'=>1000]);
        $this->assertEquals('success', $res['api_status']);
        $this->assertEquals('T1', $res['tid']);
    }

    public function testCreditApiErrorThrows(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['api_status'=>'error','msg'=>'fail']))
        ]);
        $handler = HandlerStack::create($mock);
        $http = new HttpClient(['handler'=>$handler,'base_uri'=>'https://my.jpesa.com/api/']);

        $client = new JPesaClient('https://my.jpesa.com/api/', 'K1');
        $ref = new \ReflectionProperty(JPesaClient::class, 'http');
        $ref->setAccessible(true);
        $ref->setValue($client, $http);

        $this->expectException(JPesaException::class);
        $client->credit(['mobile'=>'256700000001','amount'=>1000]);
    }

    public function testHttpNon2xxThrows(): void
    {
        $mock = new MockHandler([ new Response(500, [], 'oops') ]);
        $handler = HandlerStack::create($mock);
        $http = new HttpClient(['handler'=>$handler,'base_uri'=>'https://my.jpesa.com/api/']);

        $client = new JPesaClient('https://my.jpesa.com/api/', 'K1');
        $ref = new \ReflectionProperty(JPesaClient::class, 'http');
        $ref->setAccessible(true);
        $ref->setValue($client, $http);

        $this->expectException(HttpException::class);
        $client->credit(['mobile'=>'256700000001','amount'=>1000]);
    }
}
