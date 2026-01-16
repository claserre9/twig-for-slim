# Twig for Slim

A Twig template integration for the Slim 4 microframework. This library provides a simple wrapper for Twig, a Slim middleware for easy integration, and a Twig extension for Slim-specific functions (like generating URLs from routes).

### Overview

This project allows you to quickly set up Twig as the template engine for your Slim 4 application. It includes:
- A `Twig` wrapper class for managing the Twig environment.
- `TwigMiddleware` to inject the Twig instance into Slim requests.
- `TwigExtension` providing useful functions like `url_for` inside your Twig templates.

### Requirements

- **PHP:** ^7.4 || ^8.0
- **Slim Framework:** ^4.0
- **Twig:** ^3.0
- **Guzzle HTTP PSR-7:** ^2.0

### Installation

Install the package via Composer:

```bash
composer require claserre9/twig-for-slim
```

### Basic Usage

Below is a basic example of how to integrate Twig into your Slim 4 application.

#### 1. Setup in `index.php`

```php
<?php
use Slim\Factory\AppFactory;
use Claserre9\TwigForSlim\Twig;
use Claserre9\TwigForSlim\middlewares\TwigMiddleware;

require __DIR__ . '/vendor/autoload.php';

// Initialize Twig with the path to your templates
$twig = Twig::create(__DIR__ . '/templates');

$app = AppFactory::create();

// Add Slim's Routing Middleware (required for url_for and other route helpers)
$app->addRoutingMiddleware();

// Add Twig Middleware to the app
$app->add(TwigMiddleware::create($app, $twig));

// Define a route
$app->get('/hello/{name}', function ($request, $response, $args) {
    // Access Twig from the request attribute
    $twig = $request->getAttribute('twig');
    
    $payload = $twig->render('hello.twig', [
        'name' => $args['name']
    ]);
    
    $response->getBody()->write($payload);
    return $response;
})->setName('hello');

$app->run();
```

#### 2. Create a Template (`templates/hello.twig`)

```twig
<!DOCTYPE html>
<html>
<head>
    <title>Hello Page</title>
</head>
<body>
    <h1>Hello, {{ name }}!</h1>
    <p>Go to <a href="{{ url_for('hello', {'name': 'world'}) }}">World</a></p>
</body>
</html>
```

### Twig Extension Functions

The following functions are available within your Twig templates thanks to `TwigExtension`:

- `url_for(routeName, data, queryParams)`: Generates a URL for a named route.
- `full_url_for(routeName, data, queryParams)`: Generates a fully qualified URL for a named route.
- `path(routeName, data, relative)`: Alias for generating route paths.
- `url(routeName, data, schemeRelative)`: Generates a URL, optionally scheme-relative.
- `relative_path(path)`: Calculates a relative path based on the current URI.

### Project Structure

```text
twig-for-slim/
├── src/
│   ├── Twig.php               # Twig Environment wrapper
│   ├── TwigExtension.php      # Custom Twig functions for Slim
│   └── middlewares/
│       └── TwigMiddleware.php # Slim Middleware for Twig integration
├── templates/                 # (Optional) Your Twig templates
├── index.php                  # Example entry point
├── composer.json              # Project dependencies and autoloading
└── README.md                  # Project documentation
```

### Scripts

Currently, there are no custom scripts defined in `composer.json`.

### Environment Variables

No specific environment variables are required by this library. Configuration is handled through the `Twig::create()` method.

### Tests

- TODO: Add unit tests for `TwigMiddleware` and `TwigExtension`.

### License

This project is licensed under the MIT License. See the `composer.json` file for details.
