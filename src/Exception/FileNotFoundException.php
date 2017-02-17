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
 * This exception is thrown when specified file cannot be found in filesystem.
 */
class FileNotFoundException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $filepath;

    /**
     * Constructor.
     * @param string $filepath
     */
    public function __construct($filepath)
    {
        parent::__construct(sprintf('File not found %s', $filepath));

        $this->filepath = $filepath;
    }

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }
}
