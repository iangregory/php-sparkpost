[Sign up](https://app.sparkpost.com/join?plan=free-0817?src=Social%20Media&sfdcid=70160000000pqBb&pc=GitHubSignUp&utm_source=github&utm_medium=social-media&utm_campaign=github&utm_content=sign-up) for a SparkPost account and visit our [Developer Hub](https://developers.sparkpost.com) for even more content.

# SparkPost PHP Library

[![Travis CI](https://travis-ci.org/SparkPost/php-sparkpost.svg?branch=master)](https://travis-ci.org/SparkPost/php-sparkpost)
[![Coverage Status](https://coveralls.io/repos/SparkPost/php-sparkpost/badge.svg?branch=master&service=github)](https://coveralls.io/github/SparkPost/php-sparkpost?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/sparkpost/sparkpost.svg?maxAge=3600)](https://packagist.org/packages/sparkpost/sparkpost)
[![Packagist](https://img.shields.io/packagist/v/sparkpost/sparkpost.svg?maxAge=3600)](https://packagist.org/packages/sparkpost/sparkpost)

A fork of the official PHP library for using [the SparkPost REST API](https://developers.sparkpost.com/api/).

Before using this library, you must have a valid API Key. To get an API Key, please log in to your SparkPost account and generate one in the Settings page.

## Installation

**This fork uses PSR-18 HTTP Client standard instead of HTTPlug. See [PSR-18 Migration](#psr-18-migration) for upgrade instructions.**

The recommended way to install the SparkPost PHP Library is through composer.

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

SparkPost requires a PSR-18 HTTP client implementation. We recommend Symfony HTTP Client:

```bash
composer require sparkpost/sparkpost
composer require symfony/http-client nyholm/psr7
```

Alternatively, you can use Guzzle 7 or any other PSR-18 compatible client:

```bash
composer require guzzlehttp/guzzle "^7.0"
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
use SparkPost\SparkPost;
```

**Note:** Without composer the costs outweigh the benefits of using the PHP client library. A simple function like the one in [issue #164](https://github.com/SparkPost/php-sparkpost/issues/164#issuecomment-289888237) wraps the SparkPost API and makes it easy to use the API without resolving the composer dependencies.

## Setting up a PSR-18 HTTP Client

SparkPost requires a PSR-18 compatible HTTP client. The library uses PSR-18 (HTTP Client), PSR-17 (HTTP Factories), and PSR-7 (HTTP Messages) standards.

### Quick Start with Symfony HTTP Client (Recommended)

```php
<?php
require 'vendor/autoload.php';

use SparkPost\SparkPost;
use Symfony\Component\HttpClient\Psr18Client;

// Symfony HTTP Client implements all three required interfaces
$httpClient = new Psr18Client();

$sparky = new SparkPost(
    $httpClient,  // ClientInterface
    $httpClient,  // RequestFactoryInterface
    $httpClient,  // StreamFactoryInterface
    ['key' => 'YOUR_API_KEY']
);
```

### Using Guzzle 7

```php
<?php
require 'vendor/autoload.php';

use SparkPost\SparkPost;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

$guzzle = new Client();
$factory = new HttpFactory();

$sparky = new SparkPost(
    $guzzle,   // ClientInterface
    $factory,  // RequestFactoryInterface
    $factory,  // StreamFactoryInterface
    ['key' => 'YOUR_API_KEY']
);
```

## Initialization
#### new SparkPost(httpClient, requestFactory, streamFactory, options)
* `httpClient`
    * Required: Yes
    * Type: `Psr\Http\Client\ClientInterface`
    * PSR-18 HTTP client implementation
* `requestFactory`
    * Required: Yes
    * Type: `Psr\Http\Message\RequestFactoryInterface`
    * PSR-17 request factory
* `streamFactory`
    * Required: Yes
    * Type: `Psr\Http\Message\StreamFactoryInterface`
    * PSR-17 stream factory
* `options`
    * Required: Yes
    * Type: `Array`
    * Configuration array with API key and other options
* `options.key`
    * Required: Yes
    * Type: `String`
    * A valid Sparkpost API key
* `options.host`
    * Required: No
    * Type: `String`
    * Default: `api.sparkpost.com`
* `options.protocol`
    * Required: No
    * Type: `String`
    * Default: `https`
* `options.port`
    * Required: No
    * Type: `Number`
    * Default: 443
* `options.version`
    * Required: No
    * Type: `String`
    * Default: `v1`
* `options.async`
    * Required: No
    * Type: `Boolean`
    * Default: `true`
    * `async` defines if the `request` function sends an asynchronous or synchronous request. If your client does not support async requests set this to `false`
* `options.retries`
    * Required: No
    * Type: `Number`
    * Default: `0`
    * `retries` controls how many API call attempts the client makes after receiving a 5xx response
* `options.debug`
    * Required: No
    * Type: `Boolean`
    * Default: `false`
    * If `debug` is true, then all `SparkPostResponse` and `SparkPostException` instances will return any array of the request values through the function `getRequest`

## Methods
### request(method, uri [, payload [, headers]])
* `method`
    * Required: Yes
    * Type: `String`
    * HTTP method for request
* `uri`
    * Required: Yes
    * Type: `String`
    * The URI to receive the request
* `payload`
    * Required: No
    * Type: `Array`
    * If the method is `GET` the values are encoded into the URL. Otherwise, if the method is `POST`, `PUT`, or `DELETE` the payload is used for the request body.
* `headers`
    * Required: No
    * Type: `Array`
    * Custom headers to be sent with the request.

### syncRequest(method, uri [, payload [, headers]])
Sends a synchronous request to the SparkPost API and returns a `SparkPostResponse`

### asyncRequest(method, uri [, payload [, headers]])
Sends an asynchronous request to the SparkPost API and returns a `SparkPostPromise`

### setHttpClient(httpClient)
* `httpClient`
    *  Required: Yes
    * HTTP client or adapter supported by HTTPlug

### setOptions(options)
* `options`
    *  Required: Yes
    *  Type: `Array`
    * See constructor

## Endpoints
### transmissions
* **post(payload)**
    * `payload` - see request options
    * `payload.cc`
        * Required: No
        * Type: `Array`
        * Recipients to receive a carbon copy of the transmission
    * `payload.bcc`
        * Required: No
        * Type: `Array`
        * Recipients to discreetly receive a carbon copy of the transmission

## Examples

### Send An Email Using The Transmissions Endpoint
```php
<?php
require 'vendor/autoload.php';

use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

$httpClient = new GuzzleAdapter(new Client());
// Good practice to not have API key literals in code - set an environment variable instead
// For simple example, use synchronous model
$sparky = new SparkPost($httpClient, ['key' => getenv('SPARKPOST_API_KEY'), 'async' => false]);

try {
    $response = $sparky->transmissions->post([
        'content' => [
            'from' => [
                'name' => 'SparkPost Team',
                'email' => 'from@sparkpostbox.com',
            ],
            'subject' => 'First Mailing From PHP',
            'html' => '<html><body><h1>Congratulations, {{name}}!</h1><p>You just sent your very first mailing!</p></body></html>',
            'text' => 'Congratulations, {{name}}!! You just sent your very first mailing!',
        ],
        'substitution_data' => ['name' => 'YOUR_FIRST_NAME'],
        'recipients' => [
            [
                'address' => [
                    'name' => 'YOUR_NAME',
                    'email' => 'YOUR_EMAIL',
                ],
            ],
        ],
        'cc' => [
            [
                'address' => [
                    'name' => 'ANOTHER_NAME',
                    'email' => 'ANOTHER_EMAIL',
                ],
            ],
        ],
        'bcc' => [
            [
                'address' => [
                    'name' => 'AND_ANOTHER_NAME',
                    'email' => 'AND_ANOTHER_EMAIL',
                ],
            ],
        ],
    ]);
    } catch (\Exception $error) {
        var_dump($error);
    }
print($response->getStatusCode());
$results = $response->getBody()['results'];
var_dump($results);
?>
```

More examples [here](./examples/):
### [Transmissions](./examples/transmissions/)
- Create with attachment
- Create with recipient list
- Create with cc and bcc
- Create with template
- Create
- Delete (scheduled transmission by campaign_id *only*)

### [Templates](./examples/templates/)
- Create
- Get
- Get (list) all
- Update
- Delete

### [Message Events](./examples/message-events/)
- get
- get (with retry logic)

### Send An API Call Using The Base Request Function

We provide a base request function to access any of our API resources.
```php
<?php
require 'vendor/autoload.php';

use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

$httpClient = new GuzzleAdapter(new Client());
$sparky = new SparkPost($httpClient, [
    'key' => getenv('SPARKPOST_API_KEY'),
    'async' => false]);

$webhookId = 'afd20f50-865a-11eb-ac38-6d7965d56459';
$response = $sparky->request('DELETE', 'webhooks/' . $webhookId);
print($response->getStatusCode());
?>
```

> Be sure to not have a leading `/` in your resource URI.

For complete list of resources, refer to [API documentation](https://developers.sparkpost.com/api/).

## Handling Responses
The API calls either return a `SparkPostPromise` or `SparkPostResponse` depending on if `async` is `true` or `false`

### Synchronous
```php
$sparky->setOptions(['async' => false]);
try {
    $response = ... // YOUR API CALL GOES HERE

    echo $response->getStatusCode()."\n";
    print_r($response->getBody())."\n";
}
catch (\Exception $e) {
    echo $e->getCode()."\n";
    echo $e->getMessage()."\n";
}
```


## Handling Exceptions

The library throws `SparkPostException` when there is a problem with the request or the server returns a status code of `400` or higher.

### SparkPostException
* **getCode()**
    * Returns the response status code of `400` or higher
* **getMessage()**
    * Returns the exception message
* **getBody()**
    * If there is a response body it returns it as an `Array`. Otherwise it returns `null`.
* **getRequest()**
    * Returns an array with the request values `method`, `url`, `headers`, `body` when `debug` is `true`


### Contributing
See [contributing](https://github.com/SparkPost/php-sparkpost/blob/master/CONTRIBUTING.md).

## PSR-18 Migration

**Version 3.0 migrated from HTTPlug to PSR-18 HTTP Client standard.**

### Breaking Changes

1. **Constructor signature changed**:
   ```php
   // Old (v2.x with HTTPlug)
   $sparky = new SparkPost($httpClient, ['key' => 'YOUR_API_KEY']);

   // New (v3.x with PSR-18)
   use Symfony\Component\HttpClient\Psr18Client;

   $psr18Client = new Psr18Client();
   $sparky = new SparkPost(
       $psr18Client,        // ClientInterface
       $psr18Client,        // RequestFactoryInterface
       $psr18Client,        // StreamFactoryInterface
       ['key' => 'YOUR_API_KEY']
   );
   ```

2. **Async support removed**: PSR-18 does not support asynchronous requests. All requests are now synchronous.
   - Removed `asyncRequest()` method
   - Removed `async` option from constructor
   - Removed `SparkPostPromise` class
   - The `request()` method now always returns `SparkPostResponse` instead of `SparkPostPromise`
   - Use `->wait()` is no longer needed/available

3. **HTTP client discovery removed**: HTTP client and factories must be explicitly provided. No auto-discovery.

4. **Dependencies changed**:
   - Removed: `php-http/httplug`, `php-http/message`, `php-http/discovery`
   - Added: `psr/http-client`, `psr/http-factory`, `psr/http-message`
   - Recommended: `symfony/http-client`, `nyholm/psr7`

### Migration Steps

1. Update composer dependencies:
   ```bash
   composer remove php-http/httplug php-http/message php-http/discovery
   composer require symfony/http-client nyholm/psr7
   ```

2. Update SparkPost instantiation:
   ```php
   // Replace HTTPlug adapter
   use Symfony\Component\HttpClient\Psr18Client;

   $psr18Client = new Psr18Client();
   $sparky = new SparkPost($psr18Client, $psr18Client, $psr18Client, ['key' => 'YOUR_API_KEY']);
   ```

3. Remove async code:
   ```php
   // Old async code
   $promise = $sparky->request('GET', 'templates');
   $response = $promise->wait();

   // New synchronous code
   $response = $sparky->request('GET', 'templates');
   ```

4. Remove promise callbacks:
   ```php
   // Old
   $promise->then(
       function($response) { /* ... */ },
       function($exception) { /* ... */ }
   );

   // New - use try/catch
   try {
       $response = $sparky->request('GET', 'templates');
       // handle success
   } catch (\SparkPost\SparkPostException $e) {
       // handle error
   }
   ```
