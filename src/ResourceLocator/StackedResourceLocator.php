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

class StackedResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var ResourceLocatorInterface[]
     */
    protected $locators;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->locators = [];
    }

    public function addLocator(ResourceLocatorInterface $locator)
    {
        $this->locators[] = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        foreach ($this->locators as $locator) {
            if ($locator->has($name)) {
                return $locator->get($name);
            }
        }

        throw new ResourceNotFoundException($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        foreach ($this->locators as $locator) {
            if ($locator->has($name)) {
                return true;
            }
        }

        return false;
    }
}
