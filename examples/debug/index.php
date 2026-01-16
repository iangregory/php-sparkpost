<?php

namespace Examples\Templates;

require dirname(__FILE__) . '/../bootstrap.php';

use SparkPost\SparkPost;
use Symfony\Component\HttpClient\Psr18Client;

// Create PSR-18 client (Symfony HTTP Client implements all three required interfaces)
$psr18Client = new Psr18Client();

/*
 * configure options in example-options.json
 */
$sparky = new SparkPost(
    $psr18Client,  // ClientInterface
    $psr18Client,  // RequestFactoryInterface
    $psr18Client,  // StreamFactoryInterface
    [
        'key' => getenv('SPARKPOST_API_KEY'),
        // fetch API KEY from environment variable
        'debug' => true,
    ]
);

try {
    $response = $sparky->request('GET', 'templates');

    var_dump($response);

    echo "Request:\n";
    print_r($response->getRequest());

    echo "Response:\n";
    echo $response->getStatusCode() . "\n";
    print_r($response->getBody()) . "\n";
} catch (\Exception $e) {
    echo "Request:\n";
    print_r($e->getRequest());

    echo "Exception:\n";
    echo $e->getCode() . "\n";
    echo $e->getMessage() . "\n";
}
