<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Runner;

use Aes3xs\Tasker\AbstractRecipe;
use Aes3xs\Tasker\Exception\ActionNotAvailableException;
use Aes3xs\Tasker\Exception\SkipActionException;
use Aes3xs\Tasker\Reporter\Reporter;
use Aes3xs\Tasker\Resolver\ResourceResolver;
use Psr\Log\LoggerInterface;

/**
 * Action runner.
 */
class Runner
{
    /**
     * @var AbstractRecipe
     */
    protected $recipe;

    /**
     * @var ResourceResolver
     */
    protected $resourceResolver;

    /**
     * @var Reporter
     */
    protected $reporter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $actions;

    /**
     * Constructor.
     *
     * @param AbstractRecipe $recipe
     * @param ResourceResolver $resourceResolver
     * @param Reporter $reporter
     * @param LoggerInterface $logger
     */
    public function __construct(
        AbstractRecipe $recipe,
        ResourceResolver $resourceResolver,
        Reporter $reporter,
        LoggerInterface $logger
    ) {
        $this->recipe = $recipe;
        $this->resourceResolver = $resourceResolver;
        $this->reporter = $reporter;
        $this->logger = $logger;
    }

    /**
     * Run actions.
     *
     * @param array $actions
     * @param null $actionGroupName
     */
    public function runActions(array $actions, $actionGroupName = null)
    {
        if (null === $actionGroupName) {
            $actionGroupName = implode(', ', $actions);
            $actionGroupName = strlen($actionGroupName) > 50 ? substr($actionGroupName,0,50) . "..." : $actionGroupName;
        }
        $this->reporter->reportActionGroup($actionGroupName);
        foreach ($actions as $actionName) {
            $this->reporter->reportAction($actionName);
        }
        foreach ($actions as $actionName) {
            $this->runAction($actionName);
        }
    }

    /**
     * Run single action.
     *
     * @param $actionName
     * @return mixed|null
     * @throws \Exception
     */
    public function runAction($actionName)
    {
        $output = null;

        if (!in_array($actionName, $this->getAvailableActions())) {
            throw new ActionNotAvailableException($actionName);
        }

        $this->reporter->reportAction($actionName);
        try {
            $this->reporter->reportActionRunning($actionName);
            $this->logger->info('➤ ' . $actionName);
            $output = $this->resourceResolver->resolveCallback([$this->recipe, $actionName]);
            $this->reporter->reportActionSucceed($actionName, $output);
            $this->logger->info('✔ ' . $actionName);
            if ($output) {
                $this->logger->info('• ' . $actionName . ': ' . (string) $output);
            }
        } catch (SkipActionException $e) {
            $this->reporter->reportActionSkipped($actionName);
        } catch (\Exception $e) {
            $this->reporter->reportActionError($actionName, $e);
            $this->logger->error('✘ ' . $actionName, ['exception' => $e]);
            throw $e;
        }

        return $output;
    }

    /**
     * Get list of available actions.
     *
     * @return array
     */
    public function getAvailableActions()
    {
        if (null === $this->actions) {
            $this->actions = $this->extractAvailableActions();
        }

        return $this->actions;
    }

    protected function extractAvailableActions()
    {
        $actions = [];

        $parentClass = new \ReflectionClass($this->recipe);
        $rootClass = new \ReflectionClass(AbstractRecipe::class);

        /** @var \ReflectionClass[] $parents */
        $parents = [$parentClass];
        while ($parentClass = $parentClass->getParentClass()) {
            $parents[] = $parentClass;
        }
        $parents = array_reverse($parents);

        foreach ($parents as $parentClass) {
            $reflectionMethods = $parentClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($reflectionMethods as $method) {
                $methodName = $method->getName();

                $isMagicMethod = fnmatch("__*", $methodName);
                $isGetMethod = fnmatch("get[A-Z]*", $methodName);

                if ($method->isStatic() || $isMagicMethod || $isGetMethod || $rootClass->hasMethod($methodName)) {
                    continue;
                }

                $actions[$methodName] = $methodName;
            }
        }

        return array_values($actions);
    }
}
