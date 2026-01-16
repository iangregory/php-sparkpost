<?php

namespace SparkPost\Test;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SparkPost\SparkPost;

class TransmissionTest extends TestCase
{
    private $clientMock;
    private $requestFactoryMock;
    private $streamFactoryMock;
    /** @var SparkPost */
    private $resource;

    private $postTransmissionPayload = [
        'content' => [
            'from' => ['name' => 'Sparkpost Team', 'email' => 'postmaster@sendmailfor.me'],
            'subject' => 'First Mailing From PHP',
            'text' => 'Congratulations, {{name}}!! You just sent your very first mailing!',
        ],
        'substitution_data' => ['name' => 'Avi'],
        'recipients' => [
            [
                'address' => [
                    'name' => 'Vincent',
                    'email' => 'vincent.song@sparkpost.com',
                ],
            ],
            ['address' => 'test@example.com'],
        ],
        'cc' => [
            [
                'address' => [
                    'email' => 'avi.goldman@sparkpost.com',
                ],
            ],
        ],
        'bcc' => [
            ['address' => 'Emely Giraldo <emely.giraldo@sparkpost.com>'],
        ],
    ];

    private $getTransmissionPayload = [
        'campaign_id' => 'thanksgiving',
    ];

    /**
     * (non-PHPdoc).
     *
     * @before
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp(): void
    {
        // setup mocks for PSR-18 client and PSR-17 factories
        $this->clientMock = \Mockery::mock(ClientInterface::class);
        $this->requestFactoryMock = \Mockery::mock(RequestFactoryInterface::class);
        $this->streamFactoryMock = \Mockery::mock(StreamFactoryInterface::class);

        // Setup default expectations for factories (will be called by every request)
        $requestMock = \Mockery::mock('Psr\\Http\\Message\\RequestInterface');
        $this->requestFactoryMock->shouldReceive('createRequest')->andReturn($requestMock)->byDefault();
        $requestMock->shouldReceive('withHeader')->andReturnSelf()->byDefault();
        $streamMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');
        $this->streamFactoryMock->shouldReceive('createStream')->andReturn($streamMock)->byDefault();
        $requestMock->shouldReceive('withBody')->andReturn($requestMock)->byDefault();

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

    public function testInvalidEmailFormat()
    {
        $this->expectException(\Exception::class);

        $this->postTransmissionPayload['recipients'][] = [
            'address' => 'invalid email format',
        ];

        $response = $this->resource->transmissions->post($this->postTransmissionPayload);
    }

    public function testGet()
    {
        $responseMock = \Mockery::mock('Psr\Http\Message\ResponseInterface');
        $responseBodyMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');

        $responseBody = ['results' => 'yay'];

        $this->clientMock->shouldReceive('sendRequest')->
            once()->

            andReturn($responseMock);

        $responseMock->shouldReceive('getStatusCode')->andReturn(200);
        $responseMock->shouldReceive('getBody')->andReturn($responseBodyMock);
        $responseBodyMock->shouldReceive('__toString')->andReturn(json_encode($responseBody));

        $response = $this->resource->transmissions->get($this->getTransmissionPayload);

        $this->assertEquals($responseBody, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPut()
    {
        $responseMock = \Mockery::mock('Psr\Http\Message\ResponseInterface');
        $responseBodyMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');

        $responseBody = ['results' => 'yay'];

        $this->clientMock->shouldReceive('sendRequest')->
            once()->

            andReturn($responseMock);

        $responseMock->shouldReceive('getStatusCode')->andReturn(200);
        $responseMock->shouldReceive('getBody')->andReturn($responseBodyMock);
        $responseBodyMock->shouldReceive('__toString')->andReturn(json_encode($responseBody));

        $response = $this->resource->transmissions->put($this->getTransmissionPayload);

        $this->assertEquals($responseBody, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPost()
    {
        $responseMock = \Mockery::mock('Psr\Http\Message\ResponseInterface');
        $responseBodyMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');

        $responseBody = ['results' => 'yay'];

        $this->clientMock->shouldReceive('sendRequest')->
            once()->

            andReturn($responseMock);

        $responseMock->shouldReceive('getStatusCode')->andReturn(200);
        $responseMock->shouldReceive('getBody')->andReturn($responseBodyMock);
        $responseBodyMock->shouldReceive('__toString')->andReturn(json_encode($responseBody));

        $response = $this->resource->transmissions->post($this->postTransmissionPayload);

        $this->assertEquals($responseBody, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostWithRecipientList()
    {
        $postTransmissionPayload = $this->postTransmissionPayload;
        $postTransmissionPayload['recipients'] = ['list_id' => 'SOME_LIST_ID'];

        $responseMock = \Mockery::mock('Psr\Http\Message\ResponseInterface');
        $responseBodyMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');

        $responseBody = ['results' => 'yay'];

        $this->clientMock->shouldReceive('sendRequest')->
            once()->

            andReturn($responseMock);

        $responseMock->shouldReceive('getStatusCode')->andReturn(200);
        $responseMock->shouldReceive('getBody')->andReturn($responseBodyMock);
        $responseBodyMock->shouldReceive('__toString')->andReturn(json_encode($responseBody));

        $response = $this->resource->transmissions->post();

        $this->assertEquals($responseBody, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDelete()
    {
        $responseMock = \Mockery::mock('Psr\Http\Message\ResponseInterface');
        $responseBodyMock = \Mockery::mock('Psr\\Http\\Message\\StreamInterface');

        $responseBody = ['results' => 'yay'];

        $this->clientMock->shouldReceive('sendRequest')->
            once()->

            andReturn($responseMock);

        $responseMock->shouldReceive('getStatusCode')->andReturn(200);
        $responseMock->shouldReceive('getBody')->andReturn($responseBodyMock);
        $responseBodyMock->shouldReceive('__toString')->andReturn(json_encode($responseBody));

        $response = $this->resource->transmissions->delete($this->getTransmissionPayload);

        $this->assertEquals($responseBody, $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFormatPayload()
    {
        $correctFormattedPayload = json_decode('{"content":{"from":{"name":"Sparkpost Team","email":"postmaster@sendmailfor.me"},"subject":"First Mailing From PHP","text":"Congratulations, {{name}}!! You just sent your very first mailing!","headers":{"CC":"avi.goldman@sparkpost.com"}},"substitution_data":{"name":"Avi"},"recipients":[{"address":{"name":"Vincent","email":"vincent.song@sparkpost.com"}},{"address":{"email":"test@example.com"}},{"address":{"email":"emely.giraldo@sparkpost.com","header_to":"\"Vincent\" <vincent.song@sparkpost.com>"}},{"address":{"email":"avi.goldman@sparkpost.com","header_to":"\"Vincent\" <vincent.song@sparkpost.com>"}}]}', true);

        $formattedPayload = $this->resource->transmissions->formatPayload($this->postTransmissionPayload);
        $this->assertEquals($correctFormattedPayload, $formattedPayload);
    }
}
