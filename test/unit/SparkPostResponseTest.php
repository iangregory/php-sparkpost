<?php

namespace SparkPost\Test;

use Mockery;
use PHPUnit\Framework\TestCase;
use SparkPost\SparkPostResponse;

class SparkPostResponseTest extends TestCase
{
    /** @var Mockery\MockInterface|\Psr\Http\Message\ResponseInterface */
    private $responseMock;
    /** @var string */
    private $returnValue;

    public function setUp(): void
    {
        $this->returnValue = 'some_value_to_return';
        $this->responseMock = \Mockery::mock('Psr\Http\Message\ResponseInterface');
    }

    public function testGetProtocolVersion()
    {
        $this->responseMock->shouldReceive('getProtocolVersion')->andReturn($this->returnValue);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $this->assertEquals($this->responseMock->getProtocolVersion(), $sparkpostResponse->getProtocolVersion());
    }

    public function testWithProtocolVersion()
    {
        $param = 'protocol version';

        $this->responseMock->shouldReceive('withProtocolVersion')->andReturn($this->responseMock);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $result = $sparkpostResponse->withProtocolVersion($param);
        $this->assertInstanceOf(SparkPostResponse::class, $result);
    }

    public function testGetHeaders()
    {
        $this->responseMock->shouldReceive('getHeaders')->andReturn([]);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $this->assertEquals($this->responseMock->getHeaders(), $sparkpostResponse->getHeaders());
    }

    public function testHasHeader()
    {
        $param = 'header';

        $this->responseMock->shouldReceive('hasHeader')->andReturn($this->returnValue);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $this->assertEquals($this->responseMock->hasHeader($param), $sparkpostResponse->hasHeader($param));
    }

    public function testGetHeader()
    {
        $param = 'header';

        $this->responseMock->shouldReceive('getHeader')->andReturn([]);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $this->assertEquals($this->responseMock->getHeader($param), $sparkpostResponse->getHeader($param));
    }

    public function testGetHeaderLine()
    {
        $param = 'header';

        $this->responseMock->shouldReceive('getHeaderLine')->andReturn($this->returnValue);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $this->assertEquals($this->responseMock->getHeaderLine($param), $sparkpostResponse->getHeaderLine($param));
    }

    public function testWithHeader()
    {
        $param = 'header';
        $param2 = 'value';

        $this->responseMock->shouldReceive('withHeader')->andReturn($this->responseMock);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $result = $sparkpostResponse->withHeader($param, $param2);
        $this->assertInstanceOf(SparkPostResponse::class, $result);
    }

    public function testWithAddedHeader()
    {
        $param = 'header';
        $param2 = 'value';

        $this->responseMock->shouldReceive('withAddedHeader')->andReturn($this->responseMock);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $result = $sparkpostResponse->withAddedHeader($param, $param2);
        $this->assertInstanceOf(SparkPostResponse::class, $result);
    }

    public function testWithoutHeader()
    {
        $param = 'header';

        $this->responseMock->shouldReceive('withoutHeader')->andReturn($this->responseMock);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $result = $sparkpostResponse->withoutHeader($param);
        $this->assertInstanceOf(SparkPostResponse::class, $result);
    }

    public function testGetRequest()
    {
        $request = ['some' => 'request'];
        $this->responseMock->shouldReceive('getRequest')->andReturn($request);
        $sparkpostResponse = new SparkPostResponse($this->responseMock, $request);
        $this->assertEquals($sparkpostResponse->getRequest(), $request);
    }

    public function testWithBody()
    {
        $param = \Mockery::mock('Psr\Http\Message\StreamInterface');

        $this->responseMock->shouldReceive('withBody')->andReturn($this->responseMock);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $result = $sparkpostResponse->withBody($param);
        $this->assertInstanceOf(SparkPostResponse::class, $result);
    }

    public function testGetStatusCode()
    {
        $this->responseMock->shouldReceive('getStatusCode')->andReturn(200);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $this->assertEquals($this->responseMock->getStatusCode(), $sparkpostResponse->getStatusCode());
    }

    public function testWithStatus()
    {
        $param = 200;

        $this->responseMock->shouldReceive('withStatus')->andReturn($this->responseMock);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $result = $sparkpostResponse->withStatus($param);
        $this->assertInstanceOf(SparkPostResponse::class, $result);
    }

    public function testGetReasonPhrase()
    {
        $this->responseMock->shouldReceive('getReasonPhrase')->andReturn($this->returnValue);
        $sparkpostResponse = new SparkPostResponse($this->responseMock);
        $this->assertEquals($this->responseMock->getReasonPhrase(), $sparkpostResponse->getReasonPhrase());
    }
}
