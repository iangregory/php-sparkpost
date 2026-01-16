<?php

namespace SparkPost\Test;

use Nyholm\NSA;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SparkPost\SparkPost;

class SparkPostTest extends TestCase
{
    private $clientMock;
    /** @var SparkPost */
    private $resource;

    private $exceptionMock;
    private $exceptionBody;

    private $responseMock;
    private $responseBody;

    private $promiseMock;

    private $postTransmissionPayload = [
        'content' => [
            'from' => ['name' => 'Sparkpost Team', 'email' => 'postmaster@sendmailfor.me'],
            'subject' => 'First Mailing From PHP',
            'text' => 'Congratulations, {{name}}!! You just sent your very first mailing!',
        ],
        'substitution_data' => ['name' => 'Avi'],
        'recipients' => [
            ['address' => 'avi.goldman@sparkpost.com'],
        ],
    ];

    private $getTransmissionPayload = [
        'campaign_id' => 'thanksgiving',
    ];

    public function setUp(): void
    {
        // response mock up
        $responseBodyMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');
        $this->responseBody = ['results' => 'yay'];
        $this->responseMock = \Mockery::mock('Psr\\Http\\Message\\ResponseInterface');
        $this->responseMock->shouldReceive('getStatusCode')->andReturn(200);
        $this->responseMock->shouldReceive('getBody')->andReturn($responseBodyMock);
        $responseBodyMock->shouldReceive('__toString')->andReturn(json_encode($this->responseBody));

        $errorBodyMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');
        $this->badResponseBody = ['errors' => []];
        $this->badResponseMock = \Mockery::mock('Psr\Http\Message\ResponseInterface');
        $this->badResponseMock->shouldReceive('getStatusCode')->andReturn(503);
        $this->badResponseMock->shouldReceive('getBody')->andReturn($errorBodyMock);
        $errorBodyMock->shouldReceive('__toString')->andReturn(json_encode($this->badResponseBody));

        // exception mock up - use standard Exception and add getResponse method
        $exceptionResponseMock = \Mockery::mock('Psr\\Http\\Message\\ResponseInterface');
        $exceptionBodyMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');
        $this->exceptionBody = ['results' => 'failed'];
        $this->exceptionMock = \Mockery::mock('Exception');
        $this->exceptionMock->shouldReceive('getMessage')->andReturn('Test error');
        $this->exceptionMock->shouldReceive('getCode')->andReturn(0);
        $this->exceptionMock->shouldReceive('getPrevious')->andReturn(null);
        $this->exceptionMock->shouldReceive('getResponse')->andReturn($exceptionResponseMock);
        $exceptionResponseMock->shouldReceive('getStatusCode')->andReturn(500);
        $exceptionResponseMock->shouldReceive('getBody')->andReturn($exceptionBodyMock);
        $exceptionBodyMock->shouldReceive('__toString')->andReturn(json_encode($this->exceptionBody));

        // setup mocks for PSR-18 client and PSR-17 factories
        $this->clientMock = \Mockery::mock(ClientInterface::class);
        $this->requestFactoryMock = \Mockery::mock(RequestFactoryInterface::class);
        $this->streamFactoryMock = \Mockery::mock(StreamFactoryInterface::class);

        $this->resource = new SparkPost(
            $this->clientMock,
            $this->requestFactoryMock,
            $this->streamFactoryMock,
            ['key' => 'SPARKPOST_API_KEY']
        );
    }

    public function tearDown(): void
    {
        \Mockery::close();
    }

    public function testRequestSync()
    {
        $this->clientMock->shouldReceive('sendRequest')->andReturn($this->responseMock);
        $requestMock = \Mockery::mock('Psr\\Http\\Message\\RequestInterface');
        $this->requestFactoryMock->shouldReceive('createRequest')->andReturn($requestMock);
        $requestMock->shouldReceive('withHeader')->andReturnSelf();
        $streamMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');
        $this->streamFactoryMock->shouldReceive('createStream')->andReturn($streamMock);
        $requestMock->shouldReceive('withBody')->andReturn($requestMock);

        $this->assertInstanceOf('SparkPost\SparkPostResponse', $this->resource->request('POST', 'transmissions', $this->postTransmissionPayload));
    }

    public function testDebugOptionWhenFalse()
    {
        $this->resource->setOptions(['debug' => false]);
        $this->clientMock->shouldReceive('sendRequest')->andReturn($this->responseMock);
        $requestMock = \Mockery::mock('Psr\\Http\\Message\\RequestInterface');
        $this->requestFactoryMock->shouldReceive('createRequest')->andReturn($requestMock);
        $requestMock->shouldReceive('withHeader')->andReturnSelf();
        $streamMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');
        $this->streamFactoryMock->shouldReceive('createStream')->andReturn($streamMock);
        $requestMock->shouldReceive('withBody')->andReturn($requestMock);

        $response = $this->resource->request('POST', 'transmissions', $this->postTransmissionPayload);

        $this->assertEquals($response->getRequest(), null);
    }

    public function testDebugOptionWhenTrue()
    {
        // setup
        $this->resource->setOptions(['debug' => true]);
        $requestMock = \Mockery::mock('Psr\\Http\\Message\\RequestInterface');
        $this->requestFactoryMock->shouldReceive('createRequest')->andReturn($requestMock);
        $requestMock->shouldReceive('withHeader')->andReturnSelf();
        $streamMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');
        $this->streamFactoryMock->shouldReceive('createStream')->andReturn($streamMock);
        $requestMock->shouldReceive('withBody')->andReturn($requestMock);

        // successful
        $this->clientMock->shouldReceive('sendRequest')->once()->andReturn($this->responseMock);
        $response = $this->resource->request('POST', 'transmissions', $this->postTransmissionPayload);
        $this->assertEquals(json_decode($response->getRequest()['body'], true), $this->postTransmissionPayload);

        // unsuccessful
        $this->clientMock->shouldReceive('sendRequest')->once()->andThrow($this->exceptionMock);

        try {
            $response = $this->resource->request('POST', 'transmissions', $this->postTransmissionPayload);
        } catch (\Exception $e) {
            $this->assertEquals(json_decode($e->getRequest()['body'], true), $this->postTransmissionPayload);
        }
    }

    public function testSuccessfulSyncRequest()
    {
        $requestMock = \Mockery::mock('Psr\Http\Message\RequestInterface');
        $this->requestFactoryMock->shouldReceive('createRequest')->andReturn($requestMock);
        $requestMock->shouldReceive('withHeader')->andReturnSelf();
        $streamMock = \Mockery::mock('Psr\Http\Message\StreamInterface');
        $this->streamFactoryMock->shouldReceive('createStream')->andReturn($streamMock);
        $requestMock->shouldReceive('withBody')->andReturn($requestMock);

        $this->clientMock->shouldReceive('sendRequest')->
            once()->
            andReturn($this->responseMock);

        $response = $this->resource->syncRequest('POST', 'transmissions', $this->postTransmissionPayload);

        $this->assertEquals($this->responseBody, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUnsuccessfulSyncRequest()
    {
        $requestMock = \Mockery::mock('Psr\Http\Message\RequestInterface');
        $this->requestFactoryMock->shouldReceive('createRequest')->andReturn($requestMock);
        $requestMock->shouldReceive('withHeader')->andReturnSelf();
        $streamMock = \Mockery::mock('Psr\Http\Message\StreamInterface');
        $this->streamFactoryMock->shouldReceive('createStream')->andReturn($streamMock);
        $requestMock->shouldReceive('withBody')->andReturn($requestMock);

        $this->clientMock->shouldReceive('sendRequest')->
            once()->
            andThrow($this->exceptionMock);

        try {
            $this->resource->syncRequest('POST', 'transmissions', $this->postTransmissionPayload);
        } catch (\Exception $e) {
            $this->assertEquals($this->exceptionBody, $e->getBody());
            $this->assertEquals(500, $e->getCode());
        }
    }

    public function testSuccessfulSyncRequestWithRetries()
    {
        $requestMock = \Mockery::mock('Psr\Http\Message\RequestInterface');
        $this->requestFactoryMock->shouldReceive('createRequest')->andReturn($requestMock);
        $requestMock->shouldReceive('withHeader')->andReturnSelf();
        $streamMock = \Mockery::mock('Psr\Http\Message\StreamInterface');
        $this->streamFactoryMock->shouldReceive('createStream')->andReturn($streamMock);
        $requestMock->shouldReceive('withBody')->andReturn($requestMock);

        $this->clientMock->shouldReceive('sendRequest')->
            andReturn($this->badResponseMock, $this->badResponseMock, $this->responseMock);

        $this->resource->setOptions(['retries' => 2]);
        $response = $this->resource->syncRequest('POST', 'transmissions', $this->postTransmissionPayload);

        $this->assertEquals($this->responseBody, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUnsuccessfulSyncRequestWithRetries()
    {
        $requestMock = \Mockery::mock('Psr\Http\Message\RequestInterface');
        $this->requestFactoryMock->shouldReceive('createRequest')->andReturn($requestMock);
        $requestMock->shouldReceive('withHeader')->andReturnSelf();
        $streamMock = \Mockery::mock('Psr\Http\Message\StreamInterface');
        $this->streamFactoryMock->shouldReceive('createStream')->andReturn($streamMock);
        $requestMock->shouldReceive('withBody')->andReturn($requestMock);

        $this->clientMock->shouldReceive('sendRequest')->
            once()->
            andThrow($this->exceptionMock);

        $this->resource->setOptions(['retries' => 2]);
        try {
            $this->resource->syncRequest('POST', 'transmissions', $this->postTransmissionPayload);
        } catch (\Exception $e) {
            $this->assertEquals($this->exceptionBody, $e->getBody());
            $this->assertEquals(500, $e->getCode());
        }
    }

    public function testGetHttpHeaders()
    {
        $headers = $this->resource->getHttpHeaders([
            'Custom-Header' => 'testing',
        ]);

        $version = NSA::getProperty($this->resource, 'version');

        $this->assertEquals('SPARKPOST_API_KEY', $headers['Authorization']);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('testing', $headers['Custom-Header']);
        $this->assertEquals('php-sparkpost/'.$version, $headers['User-Agent']);
    }

    public function testGetUrl()
    {
        $url = 'https://api.sparkpost.com:443/api/v1/transmissions?key=value 1,value 2,value 3';
        $testUrl = $this->resource->getUrl('transmissions', ['key' => ['value 1', 'value 2', 'value 3']]);
        $this->assertEquals($url, $testUrl);
    }

    public function testSetHttpClient()
    {
        $mock = \Mockery::mock(ClientInterface::class);
        $this->resource->setHttpClient($mock);
        $this->assertEquals($mock, NSA::getProperty($this->resource, 'httpClient'));
    }

    public function testSetOptionsStringKey()
    {
        $this->resource->setOptions('SPARKPOST_API_KEY');
        $options = NSA::getProperty($this->resource, 'options');
        $this->assertEquals('SPARKPOST_API_KEY', $options['key']);
    }

    public function testSetBadOptions()
    {
        $this->expectException(\Exception::class);

        NSA::setProperty($this->resource, 'options', []);
        $this->resource->setOptions(['not' => 'SPARKPOST_API_KEY']);
    }

    public function testSetRequestFactory()
    {
        $requestFactory = \Mockery::mock(RequestFactoryInterface::class);
        $this->resource->setRequestFactory($requestFactory);

        $this->assertEquals($requestFactory, NSA::getProperty($this->resource, 'requestFactory'));
    }

    public function testSetStreamFactory()
    {
        $streamFactory = \Mockery::mock(StreamFactoryInterface::class);
        $this->resource->setStreamFactory($streamFactory);

        $this->assertEquals($streamFactory, NSA::getProperty($this->resource, 'streamFactory'));
    }
}
