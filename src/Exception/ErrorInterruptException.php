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
 * This exception is thrown when deploy should be interrupted because of error occured in parallel build.
 */
class ErrorInterruptException extends \RuntimeException implements ExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Execution interrupted because of error in another process');
    }
}
