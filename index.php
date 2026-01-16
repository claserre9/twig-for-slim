<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Claserre9\TwigForSlim\Twig;
use Claserre9\TwigForSlim\middlewares\TwigMiddleware;

require __DIR__ . '/vendor/autoload.php';

// Twig configuration - using the library's Twig wrapper
$twig = Twig::create(__DIR__ . '/templates');

/**
 * Instantiate App
 */
$app = AppFactory::create();

/**
 * The routing middleware should be added earlier than the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled by the middleware
 */
$app->addRoutingMiddleware();

// Add Twig Middleware
$app->add(TwigMiddleware::create($app, $twig));

/**
 * Add Error Middleware
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello");
    return $response;
})->setName('home');

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    /** @var Twig $twig */
    $twig = $request->getAttribute('twig');
    
    $payload = $twig->render('hello.twig', [
        'title' => 'Slim Twig Example',
        'name' => $args['name']
    ]);
    
    $response->getBody()->write($payload);
    return $response;
})->setName('hello');

$app->get('/test-urls', function (Request $request, Response $response) {
    /** @var Twig $twig */
    $twig = $request->getAttribute('twig');

    $payload = $twig->render('test_url.twig');

    $response->getBody()->write($payload);
    return $response;
})->setName('test_urls');

// Run app
$app->run();