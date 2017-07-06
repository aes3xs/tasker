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
use Symfony\Component\Console\Command\Command;

/**
 * Scenario implementation.
 *
 * Scenario contains action definitions to run.
 */
class Scenario
{
    /**
     * @var Action[]
     */
    protected $actions = [];

    /**
     * @var Action[]
     */
    protected $failbacks = [];

    /**
     * @var callable
     */
    protected $initializer;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    public function setInitializer(callable $callback)
    {
        $reflectionFunction = new \ReflectionFunction($callback);

        $tooManyArguments = $reflectionFunction->getNumberOfParameters() > 1;
        if ($tooManyArguments) {
            throw new \RuntimeException(sprintf('Constructor argument list must be empty or contain one \Symfony\Component\Console\Command\Command argument, given %d arguments', $reflectionFunction->getNumberOfParameters()));
        }

        if ($reflectionFunction->getNumberOfParameters()) {

            $arg = $reflectionFunction->getParameters()[0];

            $invalidArgument = $arg->getClass() && !is_a($arg->getClass()->getName(), Command::class, true);
            if ($invalidArgument) {
                throw new \RuntimeException('Constructor argument list must be empty or contain one \Symfony\Component\Console\Command\Command argument');
            }
        }

        $this->initializer = $callback;

        return $this;
    }

    public function invokeInitializer(Command $command)
    {
        if ($this->initializer) {
            call_user_func($this->initializer, $command);
        }
    }

    /**
     * Return list of actions.
     *
     * @return Action[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Add action.
     *
     * @param Action $action
     *
     * @return $this
     */
    public function addAction(Action $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Return list of actions to execute on error.
     *
     * @return Action[]
     */
    public function getFailbacks()
    {
        return $this->failbacks;
    }

    /**
     * Add failback action.
     *
     * @param Action $action
     *
     * @return $this
     */
    public function addFailback(Action $action)
    {
        $this->failbacks[] = $action;

        return $this;
    }
}
