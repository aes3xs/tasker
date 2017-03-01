<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Connection;

use Aes3xs\Yodler\Exception\FileReadException;

/**
 * Interface to connection factory.
 */
interface ConnectionFactoryInterface
{
    /**
     * Return empty list.
     *
     * @return ConnectionListInterface
     */
    public function createList();

    /**
     * Create list from configuration parsed from YAML.
     *
     * @param $connectionConfiguration
     *
     * @return ConnectionListInterface
     *
     * @throws FileReadException
     */
    public function createListFromConfiguration($connectionConfiguration);
}
