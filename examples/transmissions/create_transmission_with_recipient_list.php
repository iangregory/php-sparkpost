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

// The ID of a list in your SparkPost account
$my_list = 'mylist1';

try {
    $response = $sparky->transmissions->post([
        'content' => [
            'from' => [
                'name' => 'SparkPost Team',
                'email' => "from@$sending_domain",
            ],
            'subject' => 'Mailing With Recipient List From PHP',
            'html' => '<html><body><h1>Congratulations, {{name}}!</h1><p>You just sent an email to everyone on your recipient list!</p></body></html>',
            'text' => 'Congratulations, {{name}}! You just sent an email to everyone on your recipient list!',
        ],
        'substitution_data' => ['name' => 'YOUR_FIRST_NAME'],
        'recipients' => ['list_id' => $my_list],
    ]);
    echo $response->getStatusCode() . "\n";
    print_r($response->getBody()) . "\n";
} catch (\Exception $e) {
    echo $e->getCode() . "\n";
    echo $e->getMessage() . "\n";
}
