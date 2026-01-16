<?php

namespace Examples\Transmissions;

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

// put your own sending domain and test recipient address here
$sending_domain = 'steve2-test.trymsys.net';
$your_email = 'bob@sink.sparkpostmail.com';

$template_id = 'PHP-example-template';

try {
    $response = $sparky->transmissions->post([
        'content' => ['template_id' => $template_id],
        'substitution_data' => ['name' => 'YOUR_FIRST_NAME'],
        'recipients' => [
            [
                'address' => [
                    'name' => 'YOUR_NAME',
                    'email' => $your_email,
                ],
            ],
        ],
    ]);
    echo $response->getStatusCode() . "\n";
    print_r($response->getBody()) . "\n";
} catch (\Exception $e) {
    echo $e->getCode() . "\n";
    echo $e->getMessage() . "\n";
}
