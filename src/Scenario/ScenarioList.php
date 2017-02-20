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

use Aes3xs\Yodler\Exception\ScenarioAlreadyExistsException;
use Aes3xs\Yodler\Exception\ScenarioNotFoundException;

/**
 * Default implementation for scenario list.
 */
class ScenarioList implements ScenarioListInterface
{
    /**
     * @var ScenarioInterface[]
     */
    protected $scenarios = [];

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ScenarioInterface $scenario)
    {
        if (isset($this->scenarios[$scenario->getName()])) {
            throw new ScenarioAlreadyExistsException($scenario->getName());
        }

        $this->scenarios[$scenario->getName()] = $scenario;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!isset($this->scenarios[$name])) {
            throw new ScenarioNotFoundException($name);
        }

        return $this->scenarios[$name];
    }
}
