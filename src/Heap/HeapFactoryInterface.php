<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Heap;

use Aes3xs\Yodler\Deployer\DeployContextInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface to heap factory.
 */
interface HeapFactoryInterface
{
    /**
     * Create and return deploy heap.
     *
     * @param DeployContextInterface $deployContext
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return HeapInterface
     */
    public function create(DeployContextInterface $deployContext, InputInterface $input, OutputInterface $output);
}
