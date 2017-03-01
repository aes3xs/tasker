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
 * Interface to scenario factory.
 */
interface ScenarioFactoryInterface
{
    /**
     * Create list from configuration parsed from YAML.
     *
     * @param $scenarioConfiguration
     *
     * @return ScenarioListInterface
     */
    public function createListFromConfiguration($scenarioConfiguration);
}
