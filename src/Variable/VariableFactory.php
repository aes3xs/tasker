<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Variable;

use Aes3xs\Yodler\Exception\ClassNotFoundException;
use Aes3xs\Yodler\Exception\FileNotFoundException;

/**
 * Variable factory implementation.
 */
class VariableFactory implements VariableFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createList(array $values = [])
    {
        return new VariableList($values);
    }

    /**
     * {@inheritdoc}
     */
    public function createListFromConfiguration($variableConfiguration)
    {
        $variables = $this->createList();

        foreach ($variableConfiguration as $file => $class) {

            if (!is_numeric($file)) {

                if (!file_exists($file)) {
                    throw new FileNotFoundException($file);
                }

                require_once $file;
            }

            if (!class_exists($class)) {
                throw new ClassNotFoundException($class);
            }

            $source = new $class();
            $reflectionClass = new \ReflectionClass($class);

            $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($reflectionMethods as $method) {
                $callback = $method->isStatic() ? $method->getClosure() : $method->getClosure($source)->bindTo($source, $source);
                $variables->add($method->getName(), $callback);
            }

            $reflectionProperties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($reflectionProperties as $property) {
                $variables->add($property->getName(), $property->getValue($source));
            }
        }

        return $variables;
    }
}
