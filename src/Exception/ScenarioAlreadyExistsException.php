<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Exception;

/**
 * This exception is thrown when scenario with the same name already exists in the list.
 */
class ScenarioAlreadyExistsException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $scenarioName;

    /**
     * Constructor.
     *
     * @param string $scenarioName
     */
    public function __construct($scenarioName)
    {
        parent::__construct(sprintf('Scenario already exists %s', $scenarioName));

        $this->scenarioName = $scenarioName;
    }

    /**
     * @return string
     */
    public function getScenarioName()
    {
        return $this->scenarioName;
    }
}
