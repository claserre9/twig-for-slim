<?php

namespace Claserre9\TwigForSlim\middlewares;

use Claserre9\TwigForSlim\Twig;
use Claserre9\TwigForSlim\TwigExtension;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;

class TwigMiddleware implements MiddlewareInterface
{
    /**
     * @var Twig
     */
    protected Twig $twig;

    /**
     * @var RouteParserInterface|null
     */
    protected ?RouteParserInterface $routeParser;

    /**
     * @var string
     */
    protected string $containerKey;

    /**
     * TwigMiddleware constructor.
     *
     * @param Twig                      $twig
     * @param RouteParserInterface|null $routeParser
     * @param string                    $containerKey
     */
    public function __construct(Twig $twig, ?RouteParserInterface $routeParser = null, string $containerKey = 'twig')
    {
        $this->twig = $twig;
        $this->routeParser = $routeParser;
        $this->containerKey = $containerKey;
    }

    /**
     * @param App|Twig $appOrTwig
     * @param Twig|string $twigOrContainerKey
     * @param string $containerKey
     * @return self
     */
    public static function create($appOrTwig, $twigOrContainerKey = 'twig', string $containerKey = 'twig'): self
    {
        if ($appOrTwig instanceof App) {
            $twig = $twigOrContainerKey;
            if (!$twig instanceof Twig) {
                $container = $appOrTwig->getContainer();
                if ($container === null) {
                    throw new \RuntimeException('App does not have a container');
                }
                if (!$container->has((string)$twigOrContainerKey)) {
                    throw new \RuntimeException(sprintf(
                        'Twig instance not found in container with key "%s"',
                        (string)$twigOrContainerKey
                    ));
                }
                $twig = $container->get((string)$twigOrContainerKey);
                if (!$twig instanceof Twig) {
                    throw new \RuntimeException(sprintf(
                        'Service "%s" in container is not an instance of %s',
                        (string)$twigOrContainerKey,
                        Twig::class
                    ));
                }
            }
            return new self($twig, $appOrTwig->getRouteCollector()->getRouteParser(), $containerKey);
        }

        if ($appOrTwig instanceof Twig) {
            return new self($appOrTwig, null, (string)$twigOrContainerKey);
        }

        throw new \InvalidArgumentException('Invalid arguments for TwigMiddleware::create()');
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeParser = $this->routeParser;
        if ($routeParser === null) {
            try {
                $routeContext = RouteContext::fromRequest($request);
                $routeParser = $routeContext->getRouteParser();
            } catch (\RuntimeException $e) {
                // Routing has not happened yet
            }
        }

        if ($routeParser !== null) {
            $basePath = method_exists($routeParser, 'getBasePath') ? $routeParser->getBasePath() : '';
            if (!$this->twig->getEnvironment()->hasExtension(TwigExtension::class)) {
                $extension = new TwigExtension($routeParser, $request->getUri(), $basePath);
                $this->twig->getEnvironment()->addExtension($extension);
            } else {
                /** @var TwigExtension $extension */
                $extension = $this->twig->getEnvironment()->getExtension(TwigExtension::class);
                $extension->setUri($request->getUri());
                $extension->setBasePath($basePath);
            }
        }

        $request = $request->withAttribute($this->containerKey, $this->twig);

        return $handler->handle($request);
    }
}
