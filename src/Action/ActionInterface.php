<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Action;

use Aes3xs\Yodler\Heap\HeapInterface;

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
     * Check if action should be skipped.
     *
     * @param HeapInterface $heap
     *
     * @return bool
     */
    public function skip(HeapInterface $heap);

    /**
     * Execute action.
     *
     * @param HeapInterface $heap
     *
     * @return mixed
     */
    public function execute(HeapInterface $heap);

    /**
     * Return array of action dependencies.
     *
     * @return array
     */
    public function getDependencies();
}
