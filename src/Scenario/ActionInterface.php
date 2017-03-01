<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Scenario;

/**
 * Interface to deploy actions.
 */
interface ActionInterface
{
    /**
     * Return name for action.
     *
     * @return string
     */
    public function getName();

    /**
     * Return condition.
     *
     * @return string|null
     */
    public function getCondition();

    /**
     * Return callback.
     *
     * @return callable
     */
    public function getCallback();
}
