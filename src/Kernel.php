<?php

namespace Aes3xs\Yodler;

use Aes3xs\Yodler\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class Kernel
{
    protected $configFile;
    protected $container;

    /**
     * Kernel constructor.
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

        $configPath = dirname($loader->getLocator()->locate($configFile));
        $containerBuilder->setParameter('config_path', $configPath);
        $containerBuilder->compile();

        return $containerBuilder;
    }
}
