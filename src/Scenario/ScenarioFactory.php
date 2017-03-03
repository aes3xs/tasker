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

use Aes3xs\Yodler\Annotation\Condition;
use Aes3xs\Yodler\Annotation\Failback;
use Aes3xs\Yodler\Exception\ClassMismatchException;
use Aes3xs\Yodler\Exception\ClassNotFoundException;
use Aes3xs\Yodler\Recipe\RecipeInterface;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Scenario factory implementation.
 */
class ScenarioFactory implements ScenarioFactoryInterface
{
    /**
     * @var VariableFactoryInterface
     */
    protected $variableFactory;

    /**
     * Constructor.
     *
     * @param VariableFactoryInterface $variableFactory
     */
    public function __construct(VariableFactoryInterface $variableFactory)
    {
        $this->variableFactory = $variableFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createListFromConfiguration($scenarioConfiguration)
    {
        $scenarios = new ScenarioList();

        $annotationReader = new AnnotationReader();

        foreach ($scenarioConfiguration as $name => $class) {

            $methods = null;
            if (strpos($class, '::') !== false) {
                list($class, $method) = explode('::', $class);
                $methods[] = $method;
            }

            if (!class_exists($class)) {
                throw new ClassNotFoundException($class);
            }

            if (!is_a($class, RecipeInterface::class, true)) {
                throw new ClassMismatchException(RecipeInterface::class, $class);
            }

            $variables = $this->variableFactory->createList();
            $actions = new ActionList();
            $failbackActions = new ActionList();

            $source = new $class();
            $reflectionClass = new \ReflectionClass($class);

            if (null === $methods) {
                $methods = [];
                $parentClass = $reflectionClass;
                do {
                    $collectedMethods = [];
                    $reflectionMethods = $parentClass->getMethods(\ReflectionMethod::IS_PUBLIC);
                    foreach ($reflectionMethods as $method) {
                        if ($method->isConstructor()) {
                            continue;
                        }
                        $collectedMethods[$method->getName()] = $method->getName();
                    }
                    $methods = $collectedMethods + $methods;
                } while ($parentClass = $parentClass->getParentClass());
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
                    $failbackActions->add($action);
                } else {
                    $actions->add($action);
                }
            }

            $reflectionProperties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($reflectionProperties as $property) {
                $variables->add($property->getName(), $property->getValue($source));
            }

            $scenario = new Scenario($name, $actions, $failbackActions, $variables);

            $scenarios->add($scenario);
        }

        return $scenarios;
    }
}
