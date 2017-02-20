<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Deploy;

use Aes3xs\Yodler\Exception\DeployAlreadyExistsException;
use Aes3xs\Yodler\Exception\DeployNotFoundException;

/**
 * Deploy list implementation.
 */
class DeployList implements DeployListInterface
{
    /**
     * @var DeployInterface[]
     */
    protected $deploys = [];

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->deploys;
    }

    /**
     * {@inheritdoc}
     */
    public function add(DeployInterface $deploy)
    {
        if (isset($this->deploys[$deploy->getName()])) {
            throw new DeployAlreadyExistsException($deploy->getName());
        }

        $this->deploys[$deploy->getName()] = $deploy;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!isset($this->deploys[$name])) {
            throw new DeployNotFoundException($name);
        }

        return $this->deploys[$name];
    }
}
