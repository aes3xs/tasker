<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler;

use Aes3xs\Yodler\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

/**
 * Kernel.
 */
class Kernel
{
    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     * @param $configFile
     */
    public function __construct($configFile)
    {
        $this->configFile = $configFile;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        if (!$this->container) {
            throw new RuntimeException('Kernel is not booted');
        }

        return $this->container;
    }

    /**
     * Initializes container.
     */
    public function boot()
    {
        $this->container = $this->buildContainer($this->configFile);
    }

    /**
     * @param $configFile
     * @return ContainerInterface
     */
    protected function buildContainer($configFile)
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator([__DIR__, getcwd()]));

        $loader->load('Resources/config/config.yml');
        $loader->load($configFile);

        $containerBuilder->addCompilerPass(new RegisterListenersPass());

        $configPath = dirname($loader->getLocator()->locate($configFile));
        $containerBuilder->setParameter('config_path', $configPath);
        $containerBuilder->compile();

        return $containerBuilder;
    }
}
