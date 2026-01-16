<?php

namespace Examples\Templates;

require dirname(__FILE__) . '/../bootstrap.php';

use SparkPost\SparkPost;
use Symfony\Component\HttpClient\Psr18Client;

// Create PSR-18 client (Symfony HTTP Client implements all three required interfaces)
$psr18Client = new Psr18Client();

// In these examples, fetch API key from environment variable
$sparky = new SparkPost(
    $psr18Client,  // ClientInterface
    $psr18Client,  // RequestFactoryInterface
    $psr18Client,  // StreamFactoryInterface
    ['key' => getenv('SPARKPOST_API_KEY')]
);

try {
    $response = $sparky->request('GET', 'templates');
    echo $response->getStatusCode() . "\n";
    print_r($response->getBody()) . "\n";
} catch (\Exception $e) {
    echo $e->getCode() . "\n";
    echo $e->getMessage() . "\n";
}
