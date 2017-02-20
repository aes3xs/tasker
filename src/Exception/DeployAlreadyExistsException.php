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
 * This exception is thrown when deploy with the same name already exists in the list.
 */
class DeployAlreadyExistsException extends \RuntimeException implements ExceptionInterface
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
        parent::__construct(sprintf('Deploy already exists %s', $deployName));

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
