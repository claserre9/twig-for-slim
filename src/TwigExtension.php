<?php

namespace Claserre9\TwigForSlim;

use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteParserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * @var RouteParserInterface
     */
    protected RouteParserInterface $routeParser;

    /**
     * @var UriInterface|null
     */
    protected ?UriInterface $uri;

    /**
     * @var TwigFunction[]
     */
    protected array $functions = [];

    /**
     * @var TwigFilter[]
     */
    protected array $filters = [];

    /**
     * @var string
     */
    protected string $basePath = '';

    /**
     * TwigExtension constructor.
     *
     * @param RouteParserInterface $routeParser
     * @param UriInterface|null    $uri
     * @param string               $basePath
     */
    public function __construct(RouteParserInterface $routeParser, ?UriInterface $uri = null, string $basePath = '')
    {
        $this->routeParser = $routeParser;
        $this->uri = $uri;
        $this->basePath = $basePath;
    }

    /**
     * @param string $basePath
     * @return void
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    /**
     * @param UriInterface $uri
     * @return void
     */
    public function setUri(UriInterface $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return array_merge([
            new TwigFunction('url_for', [$this, 'urlFor']),
            new TwigFunction('full_url_for', [$this, 'fullUrlFor']),
            new TwigFunction('path', [$this, 'path']),
            new TwigFunction('url', [$this, 'url']),
            new TwigFunction('relative_path', [$this, 'relativePath']),
            new TwigFunction('base_path', [$this, 'basePath']),
        ], $this->functions);
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param TwigFunction $function
     * @return void
     */
    public function addFunction(TwigFunction $function): void
    {
        $this->functions[] = $function;
    }

    /**
     * @param TwigFilter $filter
     * @return void
     */
    public function addFilter(TwigFilter $filter): void
    {
        $this->filters[] = $filter;
    }

    /**
     * @param string $routeName
     * @param array  $data
     * @param array  $queryParams
     * @return string
     */
    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * @param string $routeName
     * @param array  $data
     * @param array  $queryParams
     * @return string
     */
    public function fullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        $uri = $this->uri ?: new \GuzzleHttp\Psr7\Uri('');
        return $this->routeParser->fullUrlFor($uri, $routeName, $data, $queryParams);
    }

    /**
     * @param string $routeName
     * @param array  $data
     * @param bool   $relative
     * @return string
     */
    public function path(string $routeName, array $data = [], bool $relative = false): string
    {
        if ($relative) {
            return $this->routeParser->relativeUrlFor($routeName, $data);
        }

        return $this->routeParser->urlFor($routeName, $data);
    }

    /**
     * @param string $routeName
     * @param array  $data
     * @param bool   $schemeRelative
     * @return string
     */
    public function url(string $routeName, array $data = [], bool $schemeRelative = false): string
    {
        $url = $this->fullUrlFor($routeName, $data);

        if ($schemeRelative) {
            return preg_replace('/^https?:/', '', $url);
        }

        return $url;
    }

    /**
     * @param string $path
     * @return string
     */
    public function relativePath(string $path): string
    {
        if ($this->uri === null || strpos($path, '/') !== 0) {
            return $path;
        }

        $basePath = $this->uri->getPath();
        if ($basePath === '' || $basePath === '/') {
            return ltrim($path, '/');
        }

        $source = explode('/', trim($basePath, '/'));
        $target = explode('/', trim($path, '/'));

        // If base path is a file, we should remove it from source
        if (substr($basePath, -1) !== '/' && !empty($source)) {
            array_pop($source);
        }

        while (count($source) > 0 && count($target) > 0 && $source[0] === $target[0]) {
            array_shift($source);
            array_shift($target);
        }

        $result = str_repeat('../', count($source)) . implode('/', $target);
        return $result ?: './';
    }

    /**
     * @return string
     */
    public function basePath(): string
    {
        return $this->basePath;
    }
}
