<?php

namespace Aes3xs\Yodler;

use Aes3xs\Yodler\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel
{
    protected $container;

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
        $this->container = $this->buildContainer();
    }

    /**
     * @return ContainerInterface
     */
    protected function buildContainer()
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->compile();

        return $containerBuilder;
    }
}
