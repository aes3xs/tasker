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
 * This exception is thrown when deploy with the provided name doesn't exist in the list.
 */
class DeployNotFoundException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $deployName;

    /**
     * Constructor.
     *
     * @param string $deployName
     */
    public function __construct($deployName)
    {
        parent::__construct(sprintf('Deploy not found %s', $deployName));

        $this->deployName = $deployName;
    }

    /**
     * @return string
     */
    public function getDeployName()
    {
        return $this->deployName;
    }
}
