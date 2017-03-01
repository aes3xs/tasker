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
 * Action list implementation.
 */
class ActionList implements ActionListInterface
{
    /**
     * @var ActionInterface[]
     */
    protected $actions = [];

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ActionInterface $action)
    {
        $this->actions[] = $action;
    }
}