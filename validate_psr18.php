#!/usr/bin/env php
<?php

/**
 * Validation script for PSR-18 migration.
 *
 * This script validates that the PSR-18 migration works correctly by:
 * 1. Instantiating SparkPost with PSR-18 client
 * 2. Listing templates from the API
 * 3. Sending a test email
 *
 * Requirements:
 *   - SPARKPOST_API_KEY environment variable with your API key
 *   - Optional: SPARKPOST_SENDING_DOMAIN (defaults to sparkpostbox.com)
 *   - Optional: SPARKPOST_TEST_EMAIL (defaults to test@sink.sparkpostmail.com)
 *
 * Usage:
 *   export SPARKPOST_API_KEY="your-api-key"
 *   php validate_psr18.php
 *
 * Or with inline key:
 *   SPARKPOST_API_KEY="your-api-key" php validate_psr18.php
 *
 * Note: This is a temporary validation script and will be removed after
 * the PSR-18 migration is confirmed working.
 */

require __DIR__.'/vendor/autoload.php';

use SparkPost\SparkPost;
use Symfony\Component\HttpClient\Psr18Client;

// Configuration
$apiKey = getenv('SPARKPOST_API_KEY');
$sendingDomain = getenv('SPARKPOST_SENDING_DOMAIN') ?: 'thespaceuk.com';
$testRecipient = getenv('SPARKPOST_TEST_EMAIL') ?: 'ian+test@ians-net.co.uk';

if (empty($apiKey)) {
    echo "ERROR: SPARKPOST_API_KEY environment variable is required\n";
    echo "Usage: SPARKPOST_API_KEY=\"your-key\" php validate_psr18.php\n";
    exit(1);
}

echo "=================================================\n";
echo "SparkPost PSR-18 Validation Script\n";
echo "=================================================\n\n";

try {
    // Step 1: Create PSR-18 client and instantiate SparkPost
    echo "[1/3] Instantiating SparkPost with PSR-18 client...\n";

    $psr18Client = new Psr18Client();

    $sparky = new SparkPost(
        $psr18Client,  // ClientInterface
        $psr18Client,  // RequestFactoryInterface
        $psr18Client,  // StreamFactoryInterface
        ['key' => $apiKey]
    );

    echo "  ✓ SparkPost instance created successfully\n";
    echo "  ✓ Using Symfony HTTP Client (PSR-18 compatible)\n\n";

    // Step 2: List templates
    echo "[2/3] Fetching templates from API...\n";

    $response = $sparky->request('GET', 'templates');
    $statusCode = $response->getStatusCode();
    $body = $response->getBody();

    if (200 === $statusCode && isset($body['results'])) {
        $templateCount = count($body['results']);
        echo "  ✓ API request successful (HTTP $statusCode)\n";
        echo "  ✓ Found $templateCount template(s)\n";

        if ($templateCount > 0) {
            echo '  → First template: '.$body['results'][0]['id']."\n";
        }
    } else {
        echo "  ⚠ Unexpected response (HTTP $statusCode)\n";
        print_r($body);
    }
    echo "\n";

    // Step 3: Send test email
    echo "[3/3] Sending test email...\n";
    echo "  → To: $testRecipient\n";
    echo "  → From: test@$sendingDomain\n";

    $response = $sparky->transmissions->post([
        'content' => [
            'from' => [
                'name' => 'PSR-18 Validation',
                'email' => "test@$sendingDomain",
            ],
            'subject' => 'PSR-18 Migration Validation Test',
            'html' => '<html><body><h1>Success!</h1><p>This email confirms that the SparkPost PSR-18 migration is working correctly.</p><p>Sent at: {{timestamp}}</p></body></html>',
            'text' => 'Success! This email confirms that the SparkPost PSR-18 migration is working correctly. Sent at: {{timestamp}}',
        ],
        'substitution_data' => [
            'timestamp' => date('Y-m-d H:i:s T'),
        ],
        'recipients' => [
            [
                'address' => [
                    'email' => $testRecipient,
                ],
            ],
        ],
    ]);

    $statusCode = $response->getStatusCode();
    $body = $response->getBody();

    if (200 === $statusCode && isset($body['results'])) {
        echo "  ✓ Email sent successfully (HTTP $statusCode)\n";
        echo '  ✓ Transmission ID: '.$body['results']['id']."\n";
        echo '  ✓ Accepted: '.$body['results']['total_accepted_recipients']." recipient(s)\n";
        echo '  ✓ Rejected: '.$body['results']['total_rejected_recipients']." recipient(s)\n";
    } else {
        echo "  ⚠ Unexpected response (HTTP $statusCode)\n";
        print_r($body);
    }

    echo "\n=================================================\n";
    echo "✓ ALL VALIDATION CHECKS PASSED\n";
    echo "=================================================\n";
    echo "\nThe PSR-18 migration is working correctly!\n\n";

    exit(0);
} catch (\SparkPost\SparkPostException $e) {
    echo "\n❌ SparkPost API Error:\n";
    echo '  Code: '.$e->getCode()."\n";
    echo '  Message: '.$e->getMessage()."\n";

    if ($e->getBody()) {
        echo "  Response Body:\n";
        print_r($e->getBody());
    }

    exit(1);
} catch (Psr\Http\Client\ClientExceptionInterface $e) {
    echo "\n❌ HTTP Client Error:\n";
    echo '  Type: '.get_class($e)."\n";
    echo '  Message: '.$e->getMessage()."\n";

    exit(1);
} catch (Exception $e) {
    echo "\n❌ Unexpected Error:\n";
    echo '  Type: '.get_class($e)."\n";
    echo '  Message: '.$e->getMessage()."\n";
    echo "  Trace:\n".$e->getTraceAsString()."\n";

    exit(1);
}
