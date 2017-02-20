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

/**
 * Interface to build list.
 */
interface BuildListInterface
{
    /**
     * Return all builds in array.
     *
     * @return BuildInterface[]
     */
    public function all();

    /**
     * Add build to a list.
     *
     * @param BuildInterface $build
     */
    public function add(BuildInterface $build);
}
