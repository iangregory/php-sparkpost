<?php

namespace SparkPost;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * SparkPostResponse wraps a PSR-7 ResponseInterface and provides convenience methods.
 */
class SparkPostResponse
{
    /**
     * ResponseInterface to be wrapped by SparkPostResponse.
     */
    private $response;

    /**
     * Array with the request values sent.
     */
    private $request;

    /**
     * set the response to be wrapped.
     */
    public function __construct(ResponseInterface $response, $request = null)
    {
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * Returns the request values sent.
     *
     * @return array $request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the body.
     *
     * @return array $body - the json decoded body from the http response
     */
    public function getBody()
    {
        $body = $this->response->getBody();
        $body_string = $body->__toString();

        $json = json_decode($body_string, true);

        return $json;
    }

    /**
     * pass these down to the response given in the constructor.
     */
    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion($version): self
    {
        return new self($this->response->withProtocolVersion($version), $this->request);
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader($name, $value): self
    {
        return new self($this->response->withHeader($name, $value), $this->request);
    }

    public function withAddedHeader($name, $value): self
    {
        return new self($this->response->withAddedHeader($name, $value), $this->request);
    }

    public function withoutHeader($name): self
    {
        return new self($this->response->withoutHeader($name), $this->request);
    }

    public function withBody(StreamInterface $body): self
    {
        return new self($this->response->withBody($body), $this->request);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        return new self($this->response->withStatus($code, $reasonPhrase), $this->request);
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }
}
