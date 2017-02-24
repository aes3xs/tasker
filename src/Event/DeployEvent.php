<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Event;

use Aes3xs\Yodler\Deployer\DeployContextInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event invoked before deploy.
 */
class DeployEvent extends Event
{
    const NAME = 'deploy';

    /**
     * @var DeployContextInterface
     */
    protected $deployContext;

    /**
     * Constructor.
     *
     * @param DeployContextInterface $deployContext
     */
    public function __construct(DeployContextInterface $deployContext)
    {
        $this->deployContext = $deployContext;
    }

    /**
     * @return DeployContextInterface
     */
    public function getDeployContext()
    {
        return $this->deployContext;
    }
}
