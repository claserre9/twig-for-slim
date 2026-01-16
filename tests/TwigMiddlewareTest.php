<?php

namespace Claserre9\TwigForSlim\Tests;

use Claserre9\TwigForSlim\middlewares\TwigMiddleware;
use Claserre9\TwigForSlim\Twig;
use Claserre9\TwigForSlim\TwigExtension;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Twig\Environment;

class TwigMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $twig = $this->createMock(Twig::class);
        $environment = $this->createMock(Environment::class);
        $twig->method('getEnvironment')->willReturn($environment);

        // We use a mock that includes getBasePath because RouteParserInterface doesn't have it
        // but the concrete implementation in Slim usually does.
        $routeParser = $this->getMockBuilder(RouteParserInterface::class)
            ->onlyMethods(['relativeUrlFor', 'urlFor', 'fullUrlFor'])
            ->addMethods(['getBasePath'])
            ->getMock();
        $routeParser->method('getBasePath')->willReturn('/base');

        $middleware = new TwigMiddleware($twig, $routeParser);

        $request = $this->createMock(ServerRequestInterface::class);
        $uri = $this->createMock(UriInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('twig', $twig)
            ->willReturn($request);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler->method('handle')->willReturn($response);

        $environment->expects($this->once())
            ->method('hasExtension')
            ->willReturn(false);
        $environment->expects($this->once())
            ->method('addExtension');

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testCreateFromApp()
    {
        $app = $this->createMock(App::class);
        $twig = $this->createMock(Twig::class);
        $routeCollector = $this->createMock(RouteCollectorInterface::class);
        $routeParser = $this->getMockBuilder(RouteParserInterface::class)
            ->onlyMethods(['relativeUrlFor', 'urlFor', 'fullUrlFor'])
            ->addMethods(['getBasePath'])
            ->getMock();

        $app->method('getRouteCollector')->willReturn($routeCollector);
        $routeCollector->method('getRouteParser')->willReturn($routeParser);

        $middleware = TwigMiddleware::create($app, $twig);

        $this->assertInstanceOf(TwigMiddleware::class, $middleware);
    }

    public function testCreateFromAppWithContainer()
    {
        $app = $this->createMock(App::class);
        $container = $this->createMock(ContainerInterface::class);
        $twig = $this->createMock(Twig::class);
        $routeCollector = $this->createMock(RouteCollectorInterface::class);
        $routeParser = $this->getMockBuilder(RouteParserInterface::class)
            ->onlyMethods(['relativeUrlFor', 'urlFor', 'fullUrlFor'])
            ->addMethods(['getBasePath'])
            ->getMock();

        $app->method('getContainer')->willReturn($container);
        $app->method('getRouteCollector')->willReturn($routeCollector);
        $routeCollector->method('getRouteParser')->willReturn($routeParser);

        $container->method('has')->with('twig_instance')->willReturn(true);
        $container->method('get')->with('twig_instance')->willReturn($twig);

        $middleware = TwigMiddleware::create($app, 'twig_instance', 'twig_key');

        $this->assertInstanceOf(TwigMiddleware::class, $middleware);

        // Use reflection to check if containerKey is set correctly
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('containerKey');
        $property->setAccessible(true);
        $this->assertEquals('twig_key', $property->getValue($middleware));
    }
}
