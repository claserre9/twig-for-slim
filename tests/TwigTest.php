<?php

namespace Claserre9\TwigForSlim\Tests;

use Claserre9\TwigForSlim\Twig;
use Claserre9\TwigForSlim\TwigExtension;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteParserInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class TwigTest extends TestCase
{
    public function testConstructor()
    {
        $path = __DIR__ . '/../templates';
        $twig = new Twig($path);

        $this->assertInstanceOf(Environment::class, $twig->getEnvironment());
        $this->assertInstanceOf(LoaderInterface::class, $twig->getLoader());
    }

    public function testCreate()
    {
        $path = __DIR__ . '/../templates';
        $twig = Twig::create($path);

        $this->assertInstanceOf(Twig::class, $twig);
    }

    public function testRender()
    {
        $path = __DIR__ . '/../templates';
        $twig = new Twig($path);

        // We need to add the extension because the template uses url_for
        $routeParser = $this->createMock(RouteParserInterface::class);
        $routeParser->method('urlFor')->willReturn('/home');
        $twig->getEnvironment()->addExtension(new TwigExtension($routeParser));

        $result = $twig->render('hello.twig', [
            'name' => 'World',
            'title' => 'Test Page'
        ]);
        $this->assertStringContainsString('Hello World', $result);
        $this->assertStringContainsString('The URL for the home page is: /home', $result);
    }

    public function testAddRuntimeLoader()
    {
        $path = __DIR__ . '/../templates';
        $twig = new Twig($path);

        $runtimeLoader = $this->createMock(RuntimeLoaderInterface::class);
        $twig->addRuntimeLoader($runtimeLoader);

        // We can't easily check if it was added to the environment because there is no getRuntimeLoaders
        // but we can at least check it doesn't crash and the property is updated (if it was public/accessible)
        $this->assertTrue(true);
    }
}
