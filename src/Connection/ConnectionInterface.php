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

/**
 * Interface to connection definition.
 */
interface ConnectionInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return ServerInterface
     */
    public function getServer();

    /**
     * @return UserInterface
     */
    public function getUser();
}
