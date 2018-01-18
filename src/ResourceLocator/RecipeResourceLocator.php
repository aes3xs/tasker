<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\ResourceLocator;

use Aes3xs\Tasker\AbstractRecipe;
use Aes3xs\Tasker\Exception\ResourceNotFoundException;
use Doctrine\Common\Inflector\Inflector;

class RecipeResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var AbstractRecipe
     */
    protected $recipe;

    /**
     * Constructor.
     *
     * @param AbstractRecipe $recipe
     */
    public function __construct(AbstractRecipe $recipe)
    {
        $this->recipe = $recipe;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($method = $this->findMethod($name)) {
            $methodReflection = new \ReflectionMethod(get_class($this->recipe), $method);
            return $methodReflection->getClosure($this->recipe)->bindTo($this->recipe, $this->recipe);
        }

        if ($property = $this->findProperty($name)) {
            $propertyReflection = new \ReflectionProperty(get_class($this->recipe), $property);
            return $propertyReflection->getValue($this->recipe);
        }

        throw new ResourceNotFoundException($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return null !== $this->findMethod($name) || null !== $this->findProperty($name);
    }

    protected function findProperty($name)
    {
        $classReflection = new \ReflectionClass($this->recipe);

        foreach ($classReflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $propertyReflection) {
            if ($propertyReflection->isStatic()) {
                continue;
            }
            if ((class_exists($name) || interface_exists($name)) && is_object($propertyReflection->getValue()) && is_a($propertyReflection->getValue(), $name)) {
                return $propertyReflection->getName();
            }
            if ($propertyReflection->getName() === $name) {
                return $name;
            }
        }

        return null;
    }

    protected function findMethod($name)
    {
        $classReflection = new \ReflectionClass($this->recipe);

        foreach ($classReflection->getProperties(\ReflectionMethod::IS_PUBLIC) as $methodReflection) {
            if ($methodReflection->isStatic()) {
                continue;
            }
            if ($methodReflection->getName() === "get" . Inflector::classify($name)) {
                return $methodReflection->getName();
            }
        }

        return null;
    }
}
