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

use Aes3xs\Yodler\Annotation\After;
use Aes3xs\Yodler\Annotation\Before;
use Aes3xs\Yodler\Annotation\Condition;
use Aes3xs\Yodler\Annotation\Failback;
use Aes3xs\Yodler\Exception\ClassNotFoundException;
use Aes3xs\Yodler\Exception\ScenarioNotFoundException;
use Aes3xs\Yodler\Variable\VariableList;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Scenario manager implementation.
 */
class ScenarioManager
{
    /**
     * @var Scenario[]
     */
    protected $scenarios = [];

    /**
     * Constructor.
     *
     * @param $configuration
     */
    public function __construct($configuration)
    {
        $this->scenarios = $this->createListFromConfiguration($configuration);
    }

    /**
     * Get scenario from a list by name.
     *
     * @param $name
     *
     * @return Scenario
     *
     * @throws ScenarioNotFoundException
     */
    public function get($name)
    {
        if (!isset($this->scenarios[$name])) {
            throw new ScenarioNotFoundException($name);
        }

        return $this->scenarios[$name];
    }

    /**
     * Return all scenarios in key-indexed array.
     *
     * @return Scenario[]
     */
    public function all()
    {
        return $this->scenarios;
    }

    /**
     * Create list from configuration parsed from YAML.
     *
     * @param $configuration
     *
     * @return Scenario[]
     */
    protected function createListFromConfiguration($configuration)
    {
        $scenarios = [];

        $annotationReader = new AnnotationReader();

        foreach ($configuration as $name => $class) {

            $scenario = new Scenario($name);
            $scenarios[$name] = $scenario;

            $methods = null;
            if (strpos($class, '::') !== false) {
                list($class, $method) = explode('::', $class);
                $methods[] = $method;
            }

            if (!class_exists($class)) {
                throw new ClassNotFoundException($class);
            }

            $reflectionClass = new \ReflectionClass($class);

            $constructor = $reflectionClass->getConstructor();
            if ($constructor && $constructor->getNumberOfParameters() > 0) {
                throw new \RuntimeException('Recipe must have constuctor with no arguments: ' . $reflectionClass->getName());
            }

            $source = $reflectionClass->newInstance();

            if (null === $methods) {
                $methods = [];
                $parentClass = $reflectionClass;

                /** @var \ReflectionClass[] $parents */
                $parents = [$reflectionClass];
                while ($parentClass = $parentClass->getParentClass()) {
                    $parents[] = $parentClass;
                }
                $parents = array_reverse($parents);

                foreach ($parents as $parentClass) {

                    $collectedMethods = [];
                    $reflectionMethods = $parentClass->getMethods(\ReflectionMethod::IS_PUBLIC);
                    foreach ($reflectionMethods as $method) {
                        if ($method->isConstructor()) {
                            continue;
                        }
                        $collectedMethods[$method->getName()] = $method->getName();
                    }
                    $methods += $collectedMethods;

                    foreach ($reflectionMethods as $method) {
                        /** @var After $afterAnnotation */
                        $afterAnnotation = $annotationReader->getMethodAnnotation($method, After::class);
                        $after = $afterAnnotation ? $afterAnnotation->value : null;

                        /** @var Before $afterAnnotation */
                        $beforeAnnotation = $annotationReader->getMethodAnnotation($method, Before::class);
                        $before = $beforeAnnotation ? $beforeAnnotation->value : null;

                        if (!$after && !$before) {
                            continue;
                        }
                        if ($after && $before) {
                            throw new \RuntimeException('After and Before annotations cannot be used together: ' . $parentClass->getName() . '::' . $method->getName());
                        }

                        unset($methods[$method->getName()]);

                        $actionName = $after ?: $before;
                        if (!isset($methods[$actionName])) {
                            throw new \RuntimeException('Action doesn\'t exist: ' . $actionName);
                        }
                        $index = array_search($actionName, array_keys($methods)) + ($after ? 1 : 0);

                        $methods = array_merge(
                            array_slice($methods, 0, $index, true),
                            [$method->getName() => $method->getName()],
                            array_slice($methods, $index, null, true)
                        );
                    }
                }

                $methods = array_values($methods);
            }

            foreach ($methods as $methodName) {

                $method = $reflectionClass->getMethod($methodName);

                $callback = $method->isStatic() ? $method->getClosure() : $method->getClosure($source)->bindTo($source, $source);

                /** @var Condition $conditionAnnotation */
                $conditionAnnotation = $annotationReader->getMethodAnnotation($method, Condition::class);
                $condition = $conditionAnnotation ? $conditionAnnotation->value : null;

                $isFailback = !!$annotationReader->getMethodAnnotation($method, Failback::class);

                $action = new Action($method->getName(), $callback, $condition);

                if ($isFailback) {
                    $scenario->addFailback($action);
                } else {
                    $scenario->addAction($action);
                }
            }

            $reflectionProperties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            $variables = new VariableList();
            foreach ($reflectionProperties as $property) {
                $variables->add($property->getName(), $property->getValue($source));
            }

            $scenario->setVariables($variables);
        }

        return $scenarios;
    }
}
