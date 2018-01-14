<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\ResourceLocator;

use Aes3xs\Yodler\Exception\ResourceNotFoundException;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Component\Console\Input\InputInterface;

class InputResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * Constructor.
     *
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($this->input->hasArgument($name)) {
            return $this->input->getArgument($name);
        }

        if ($this->input->hasOption($name)) {
            return $this->input->getOption($name);
        }

        throw new ResourceNotFoundException($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->input->hasArgument($name) || $this->input->hasOption($name);
    }
}
