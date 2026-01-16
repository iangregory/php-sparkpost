<?php

namespace SparkPost;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class SparkPost
{
    /**
     * @var string Library version, used for setting User-Agent
     */
    private $version = '2.3.0';

    /**
     * @var ClientInterface used to make requests
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var array Options for requests
     */
    private $options;

    /**
     * Default options for requests that can be overridden with the setOptions function.
     */
    private static $defaultOptions = [
        'host' => 'api.sparkpost.com',
        'protocol' => 'https',
        'port' => 443,
        'key' => '',
        'version' => 'v1',
        'debug' => false,
        'retries' => 0,
    ];

    /**
     * @var Transmission Instance of Transmission class
     */
    public $transmissions;

    /**
     * Sets up the SparkPost instance.
     *
     * @param ClientInterface         $httpClient     - A PSR-18 HTTP client
     * @param RequestFactoryInterface $requestFactory - A PSR-17 request factory
     * @param StreamFactoryInterface  $streamFactory  - A PSR-17 stream factory
     * @param array                   $options        - An array to overide default options or a string to be used as an API key
     */
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        array $options,
    ) {
        $this->setOptions($options);
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->setupEndpoints();
    }

    /**
     * Sends request to SparkPost API.
     *
     * @param string $method
     * @param string $uri
     * @param array  $payload - either used as the request body or url query params
     * @param array  $headers
     *
     * @return SparkPostResponse
     */
    public function request($method = 'GET', $uri = '', $payload = [], $headers = [])
    {
        return $this->syncRequest($method, $uri, $payload, $headers);
    }

    /**
     * Sends sync request to SparkPost API.
     *
     * @param string $method
     * @param string $uri
     * @param array  $payload
     * @param array  $headers
     *
     * @return SparkPostResponse
     *
     * @throws SparkPostException
     */
    public function syncRequest($method = 'GET', $uri = '', $payload = [], $headers = [])
    {
        $requestValues = $this->buildRequestValues($method, $uri, $payload, $headers);
        $request = \call_user_func_array([$this, 'buildRequestInstance'], $requestValues);

        $retries = $this->options['retries'];
        try {
            if ($retries > 0) {
                $resp = $this->syncReqWithRetry($request, $retries);
            } else {
                $resp = $this->httpClient->sendRequest($request);
            }

            return new SparkPostResponse($resp, $this->ifDebug($requestValues));
        } catch (\Exception $exception) {
            throw new SparkPostException($exception, $this->ifDebug($requestValues));
        }
    }

    private function syncReqWithRetry($request, $retries)
    {
        $resp = $this->httpClient->sendRequest($request);
        $status = $resp->getStatusCode();
        if ($status >= 500 && $status <= 599 && $retries > 0) {
            return $this->syncReqWithRetry($request, $retries - 1);
        }

        return $resp;
    }

    /**
     * Builds request values from given params.
     *
     * @param string $method
     * @param string $uri
     * @param array  $payload
     * @param array  $headers
     *
     * @return array $requestValues
     */
    public function buildRequestValues($method, $uri, $payload, $headers)
    {
        $method = trim(strtoupper($method));

        if ('GET' === $method) {
            $params = $payload;
            $body = [];
        } else {
            $params = [];
            $body = $payload;
        }

        $url = $this->getUrl($uri, $params);
        $headers = $this->getHttpHeaders($headers);

        // old form-feed workaround now removed
        $body = json_encode($body);

        return [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    /**
     * Build RequestInterface from given params.
     *
     * @return RequestInterface
     */
    public function buildRequestInstance($method, $url, $headers, $body)
    {
        $request = $this->requestFactory->createRequest($method, $url);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (!empty($body)) {
            $stream = $this->streamFactory->createStream($body);
            $request = $request->withBody($stream);
        }

        return $request;
    }

    /**
     * Build RequestInterface from given params.
     *
     * @return RequestInterface
     */
    public function buildRequest($method, $uri, $payload, $headers)
    {
        $requestValues = $this->buildRequestValues($method, $uri, $payload, $headers);

        return \call_user_func_array([$this, 'buildRequestInstance'], $requestValues);
    }

    /**
     * Returns an array for the request headers.
     *
     * @param array $headers - any custom headers for the request
     *
     * @return array $headers - headers for the request
     */
    public function getHttpHeaders($headers = [])
    {
        $constantHeaders = [
            'Authorization' => $this->options['key'],
            'Content-Type' => 'application/json',
            'User-Agent' => "php-sparkpost/{$this->version}",
        ];

        foreach ($constantHeaders as $key => $value) {
            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Builds the request url from the options and given params.
     *
     * @param string $path   - the path in the url to hit
     * @param array  $params - query parameters to be encoded into the url
     *
     * @return string $url - the url to send the desired request to
     */
    public function getUrl($path, $params = [])
    {
        $options = $this->options;

        $paramsArray = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            array_push($paramsArray, "$key=$value");
        }

        $paramsString = implode('&', $paramsArray);

        return $options['protocol'].'://'.$options['host'].($options['port'] ? ':'.$options['port'] : '').'/api/'.$options['version'].'/'.$path.($paramsString ? '?'.$paramsString : '');
    }

    /**
     * Sets $httpClient to be used for request.
     *
     * @param ClientInterface $httpClient - the PSR-18 client to be used for request
     *
     * @return SparkPost
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Sets the options from the param and defaults for the SparkPost object.
     *
     * @param array $options - either an string API key or an array of options
     *
     * @return SparkPost
     */
    public function setOptions($options)
    {
        // if the options map is a string we should assume that its an api key
        if (\is_string($options)) {
            $options = ['key' => $options];
        }

        // Validate API key because its required
        if (!isset($this->options['key']) && (!isset($options['key']) || !preg_match('/\S/', $options['key']))) {
            throw new \Exception('You must provide an API key');
        }

        $this->options ??= self::$defaultOptions;

        // set options, overriding defaults
        foreach ($options as $option => $value) {
            if (key_exists($option, $this->options)) {
                $this->options[$option] = $value;
            }
        }

        return $this;
    }

    /**
     * Returns the given value if debugging, null otherwise.
     */
    private function ifDebug($param)
    {
        return $this->options['debug'] ? $param : null;
    }

    /**
     * Sets up any endpoints to custom classes e.g. $this->transmissions.
     */
    private function setupEndpoints()
    {
        $this->transmissions = new Transmission($this);
    }

    /**
     * @return SparkPost
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    /**
     * @return SparkPost
     */
    public function setStreamFactory(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }
}
