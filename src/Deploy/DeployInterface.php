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

use Aes3xs\Yodler\Action\ActionListInterface;

/**
 * Interface to deploy.
 */
interface DeployInterface
{
    /**
     * Return deploy name.
     *
     * @return string
     */
    public function getName();

    /**
     * Return after-deploy actions.
     *
     * @return ActionListInterface
     */
    public function getDoneActions();

    /**
     * Return deploy builds.
     *
     * @return BuildListInterface
     */
    public function getBuilds();
}
