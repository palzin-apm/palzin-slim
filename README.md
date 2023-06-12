# Real-Time monitoring package using Palzin Monitor

[![Latest Stable Version](http://poser.pugx.org/palzin-apm/palzin-slim/v?style=for-the-badge)](https://packagist.org/packages/palzin-apm/palzin-slim) [![Total Downloads](http://poser.pugx.org/palzin-apm/palzin-slim/downloads?style=for-the-badge)](https://packagist.org/packages/palzin-apm/palzin-slim) [![License](http://poser.pugx.org/palzin-apm/palzin-slim/license?style=for-the-badge)](https://packagist.org/packages/palzin-apm/palzin-slim)

Palzin Monitor offers a comprehensive real-time monitoring platform that allows you to monitor and analyze the performance of your applications. With Palzin Monitor Slim Package, you can effortlessly capture and track all requests without making any code modifications. This feature provides valuable insights into the impact of your methods, database statements, and external requests on the overall user experience.

This package is specifically designed for applications built on the Slim framework.

## Requirements

- PHP >= 7.2.0
- Slim >= 4.x

## Installation

To install the latest version, use the following composer command:

```
composer require palzin-apm/palzin-slim
```

### Register On Container

You need to register the Palzin Monitor (APM) instance inside the application container to make the monitoring agent available within your application. Here's an example of how to register the Palzin Monitor instance:

```php
$container->set('palzin', function () {
    $configuration = new \Palzin\Slim\Configuration('PALZIN_APM_INGESTION_KEY');
    $configuration->setUrl('YOUR URL');
    return new Palzin($configuration);
});
```

Consider using [environment variables](https://github.com/vlucas/phpdotenv) to store your project's INGESTION KEY. If you are using a Slim 4 skeleton, you can add a new container definition in the `app/dependencies.php` file.

```php
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Other service definitions...

        'palzin' => function (ContainerInterface $container) {
            $configuration = new \Palzin\Slim\Configuration('PALZIN_APM_INGESTION_KEY');
            $configuration->setUrl('YOUR URL');
            return new Palzin\Palzin($configuration);
        }
    ]);
}
```

You can obtain the `PALZIN_APM_INGESTION_KEY` and `PALZIN_APM_URL` by creating a new project in your [Palzin](https://palzin.app) account.

## Middleware

To attach the middleware, you can either attach it globally or to specific routes. Here are examples of both:

Attach globally:

```php
$app->add(\Palzin\Slim\WebRequestMonitoring::class);
```

Attach to specific routes:

```php
$app->get('/', function () {
    // Your code here...
})->add(\Palzin\Slim\WebRequestMonitoring::class);
```

## Test

To verify that everything is working correctly, create a test route and open it in the browser [http://localhost:8080](http://localhost:8080). Here's an example of a test route:

```php
$app->get('/test', function () {
    throw new \Exception('My First Exception.');
})->add(\Palzin\Slim\WebRequestMonitoring::class);
```

## Add Segment

You can add segments to the

transaction's timeline from route functions. Here's an example of how to add a segment:

```php
$app->get('/', function (Request $request, Response $response) {
    $this->get('palzin')->addSegment(function () {
        // Your code here...
        sleep(1);
    }, 'sleep');

    return $response;
});
```

If your routes are organized using controllers, you need to inject the container into the controller constructor. Here's an example:

```php
namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestController
{
    protected $container;

    /**
     * Inject the container to retrieve the palzin instance later.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response)
    {
        $this->container->get('palzin')->addSegment(function () {
            // Your code here...
            sleep(1);
        }, 'sleep');

        $response->getBody()->write('Test route.');

        return $response;
    }
}
```

## Official Documentation

For more detailed information, please refer to the [official documentation](https://palzin.app/guides/slim-introduction).

## License

This package is licensed under the [MIT](LICENSE) license.
