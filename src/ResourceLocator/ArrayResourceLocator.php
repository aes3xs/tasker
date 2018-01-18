<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\ResourceLocator;

use Aes3xs\Tasker\Exception\ResourceNotFoundException;

class ArrayResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var array
     */
    protected $array;

    /**
     * Constructor.
     *
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->array)) {
            return $this->array[$name];
        }

        throw new ResourceNotFoundException($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_key_exists($name, $this->array);
    }
}
