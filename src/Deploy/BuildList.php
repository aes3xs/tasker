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
 * Build list implementation.
 */
class BuildList implements BuildListInterface
{
    /**
     * @var BuildInterface[]
     */
    protected $builds = [];

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->builds;
    }

    /**
     * {@inheritdoc}
     */
    public function add(BuildInterface $build)
    {
        $this->builds[] = $build;
    }
}
