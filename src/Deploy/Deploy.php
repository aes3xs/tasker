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
use Aes3xs\Yodler\Variable\VariableListInterface;
use Aes3xs\Yodler\Variable\VariableSuppliedInterface;

/**
 * Deploy implementation.
 */
class Deploy implements DeployInterface, VariableSuppliedInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var BuildListInterface
     */
    protected $builds;

    /**
     * @var ActionListInterface
     */
    protected $doneActions;

    /**
     * @var VariableListInterface
     */
    protected $variables;

    /**
     * Constructor.
     *
     * @param $name
     * @param BuildListInterface $builds
     * @param ActionListInterface $doneActions
     * @param VariableListInterface $variables
     */
    public function __construct(
        $name,
        BuildListInterface $builds,
        ActionListInterface $doneActions,
        VariableListInterface $variables
    ) {
        $this->name = $name;
        $this->builds = $builds;
        $this->doneActions = $doneActions;
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * {@inheritdoc}
     */
    public function getDoneActions()
    {
        return $this->doneActions;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
