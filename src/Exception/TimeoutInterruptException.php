<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Exception;

/**
 * This exception is thrown when deploy process waiting too long to continue working.
 */
class TimeoutInterruptException extends \RuntimeException implements ExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Deploy interrupted by timeout while waiting other builds');
    }
}
