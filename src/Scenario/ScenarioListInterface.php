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

interface ScenarioListInterface
{
    /**
     * Return all scenarios in key-indexed array.
     *
     * @return ScenarioInterface[]
     */
    public function all();

    /**
     * Add scenario to a list.
     *
     * @param ScenarioInterface $scenario
     *
     * @throws ScenarioAlreadyExistsException
     */
    public function add(ScenarioInterface $scenario);

    /**
     * Get scenario from a list by name.
     *
     * @param $name
     *
     * @return ScenarioInterface
     *
     * @throws ScenarioNotFoundException
     */
    public function get($name);
}
