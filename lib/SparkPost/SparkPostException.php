<?php

namespace SparkPost;

use Psr\Http\Client\ClientExceptionInterface;

class SparkPostException extends \Exception
{
    /**
     * Variable to hold json decoded body from http response.
     */
    private $body;

    /**
     * Array with the request values sent.
     */
    private $request;

    /**
     * Sets up the custom exception and copies over original exception values.
     *
     * @param \Exception|ClientExceptionInterface $exception - the exception to be wrapped
     * @param mixed                               $request   - the request values sent
     */
    public function __construct(\Exception $exception, $request = null)
    {
        $this->request = $request;

        $message = $exception->getMessage();
        $code = $exception->getCode();

        // PSR-18 ClientExceptionInterface doesn't define getResponse(),
        // but many implementations (like Guzzle, Symfony) add it for non-network errors.
        // Try to extract response if available for better error messages.
        try {
            $response = $exception->getResponse();
            if ($response) {
                $body = $response->getBody();
                $message = $body->__toString();
                $this->body = json_decode($message, true);
                $code = $response->getStatusCode();
            }
        } catch (\Throwable $e) {
            // If getting response fails (method doesn't exist or other error),
            // just use the original exception message
        }

        try {
            $previous = $exception->getPrevious();
        } catch (\Throwable $e) {
            $previous = null;
        }

        parent::__construct($message, $code, $previous);
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
        return $this->body;
    }
}
