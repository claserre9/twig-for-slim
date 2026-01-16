<?php

namespace Claserre9\TwigForSlim\Tests;

use Claserre9\TwigForSlim\TwigExtension;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteParserInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Uri;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtensionTest extends TestCase
{
    protected $routeParser;
    protected $uri;

    protected function setUp(): void
    {
        $this->routeParser = $this->createMock(RouteParserInterface::class);
        $this->uri = new Uri('http://example.com/base');
    }

    public function testGetFunctions()
    {
        $extension = new TwigExtension($this->routeParser);
        $functions = $extension->getFunctions();

        $this->assertIsArray($functions);
        $this->assertCount(6, $functions); // url_for, full_url_for, path, url, relative_path, base_path
    }

    public function testAddCustomFunction()
    {
        $extension = new TwigExtension($this->routeParser);
        $customFunction = new TwigFunction('custom', function () {
            return 'foo';
        });
        $extension->addFunction($customFunction);
        
        $functions = $extension->getFunctions();
        $this->assertCount(7, $functions);
        $this->assertSame($customFunction, end($functions));
    }

    public function testAddCustomFilter()
    {
        $extension = new TwigExtension($this->routeParser);
        $customFilter = new TwigFilter('custom_filter', function ($val) {
            return $val;
        });
        $extension->addFilter($customFilter);
        
        $filters = $extension->getFilters();
        $this->assertCount(1, $filters);
        $this->assertSame($customFilter, $filters[0]);
    }

    public function testUrlFor()
    {
        $this->routeParser->expects($this->once())
            ->method('urlFor')
            ->with('test-route', ['id' => 1], ['q' => 'search'])
            ->willReturn('/test-route/1?q=search');

        $extension = new TwigExtension($this->routeParser);
        $result = $extension->urlFor('test-route', ['id' => 1], ['q' => 'search']);

        $this->assertEquals('/test-route/1?q=search', $result);
    }

    public function testFullUrlFor()
    {
        $this->routeParser->expects($this->once())
            ->method('fullUrlFor')
            ->willReturn('http://example.com/base/test-route');

        $extension = new TwigExtension($this->routeParser, $this->uri);
        $result = $extension->fullUrlFor('test-route');

        $this->assertEquals('http://example.com/base/test-route', $result);
    }

    public function testRelativePath()
    {
        $extension = new TwigExtension($this->routeParser, new Uri('http://example.com/a/b/c/'));
        
        // From /a/b/c/ to /a/b/d -> ../d
        $this->assertEquals('../d', $extension->relativePath('/a/b/d'));
        
        // From /a/b/c/ to /a/x -> ../../x
        $this->assertEquals('../../x', $extension->relativePath('/a/x'));

        // From /a/b/c (file) to /a/b/d -> d
        $extension2 = new TwigExtension($this->routeParser, new Uri('http://example.com/a/b/c'));
        $this->assertEquals('d', $extension2->relativePath('/a/b/d'));
        
        // From /a/b/c (file) to /a/x -> ../x
        $this->assertEquals('../x', $extension2->relativePath('/a/x'));
    }

    public function testBasePath()
    {
        $extension = new TwigExtension($this->routeParser, null, '/my-base-path');
        $this->assertEquals('/my-base-path', $extension->basePath());
        
        $extension->setBasePath('/new-base-path');
        $this->assertEquals('/new-base-path', $extension->basePath());
    }
}
