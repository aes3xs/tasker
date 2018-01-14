<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\ResourceLocator;

use Aes3xs\Yodler\Exception\ResourceNotFoundException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($serviceId = $this->locateService($name)) {
            return $this->container->get($serviceId);
        }

        if ($this->container->hasParameter($name)) {
            $this->container->getParameter($name);
        }

        throw new ResourceNotFoundException($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return null !== $this->locateService($name) || $this->container->hasParameter($name);
    }

    protected function locateService($name)
    {
        if (class_exists($name) || interface_exists($name)) {
            foreach ($this->container->getDefinitions() as $id => $definition) {
                if (is_a($definition->getClass(), $name, true)) {
                    return $id;
                }
            }
        }

        if ($this->container->has($name)) {
            return $name;
        }

        return null;
    }
}
