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
 * Interface to user definition.
 */
interface UserInterface
{
    /**
     * @return string
     */
    public function getLogin();

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @return string
     */
    public function getKey();

    /**
     * @return string
     */
    public function getPassphrase();

    /**
     * @return bool
     */
    public function getForwarding();
}
