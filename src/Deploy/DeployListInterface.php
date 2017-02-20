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
 * Interface to deploy list.
 */
interface DeployListInterface
{
    /**
     * Return all deploys in key-indexed array.
     *
     * @return DeployInterface[]
     */
    public function all();

    /**
     * Add deploy to a list.
     *
     * @param DeployInterface $deploy
     *
     * @throws DeployAlreadyExistsException
     */
    public function add(DeployInterface $deploy);

    /**
     * Get deploy from a list by name.
     *
     * @param $name
     *
     * @return DeployInterface
     *
     * @throws DeployNotFoundException
     */
    public function get($name);
}
