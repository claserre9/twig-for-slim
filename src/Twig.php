<?php

namespace Claserre9\TwigForSlim;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class Twig
{
    /**
     * @var Environment
     */
    protected Environment $environment;

    /**
     * @var LoaderInterface
     */
    protected LoaderInterface $loader;

    /**
     * @var \Twig\RuntimeLoader\RuntimeLoaderInterface[]
     */
    protected array $runtimeLoaders = [];

    /**
     * Twig constructor.
     *
     * @param string|string[] $path
     * @param array<string, mixed> $settings
     */
    public function __construct($path, array $settings = [])
    {
        $this->loader = new FilesystemLoader($path);
        $this->environment = new Environment($this->loader, $settings);
    }

    /**
     * Create a new Twig instance
     *
     * @param string|string[] $path
     * @param array<string, mixed> $settings
     * @return self
     */
    public static function create($path, array $settings = []): self
    {
        return new self($path, $settings);
    }

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    /**
     * Add a runtime loader
     *
     * @param \Twig\RuntimeLoader\RuntimeLoaderInterface $runtimeLoader
     * @return void
     */
    public function addRuntimeLoader(\Twig\RuntimeLoader\RuntimeLoaderInterface $runtimeLoader): void
    {
        $this->runtimeLoaders[] = $runtimeLoader;
        $this->environment->addRuntimeLoader($runtimeLoader);
    }

    /**
     * Render a template
     *
     * @param string $template
     * @param array $data
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $template, array $data = []): string
    {
        return $this->environment->render($template, $data);
    }
}
