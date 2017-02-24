<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Console;

use Aes3xs\Yodler\Deploy\DeployInterface;
use Aes3xs\Yodler\Deployer\DeployerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deploy command exucutes configured deploy.
 */
class DeployCommand extends Command
{
    /**
     * @var DeployInterface
     */
    protected $deploy;

    /**
     * @var DeployerInterface
     */
    protected $deployer;

    /**
     * Constructor.
     *
     * @param DeployInterface $deploy
     * @param DeployerInterface $deployer
     */
    public function __construct(DeployInterface $deploy, DeployerInterface $deployer)
    {
        $this->deploy = $deploy;
        $this->deployer = $deployer;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName($this->deploy->getName());

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->deployer->deploy($this->deploy);
    }
}
