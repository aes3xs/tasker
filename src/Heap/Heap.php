<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Heap;

use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Exception\VariableCircularReferenceException;
use Aes3xs\Yodler\Exception\VariableNotFoundException;
use Aes3xs\Yodler\Common\CallableHelper;
use Aes3xs\Yodler\Variable\VariableInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Heap implementation.
 */
class Heap implements HeapInterface
{
    /**
     * @var VariableListInterface[]
     */
    protected $collection = [];

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * Constructor.
     *
     * @param \Twig_Environment $twig
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(\Twig_Environment $twig, ExpressionLanguage $expressionLanguage)
    {
        $this->twig = $twig;
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * Add variable list to heap with first priority.
     *
     * @param VariableListInterface $variables
     */
    public function addVariables(VariableListInterface $variables)
    {
        array_unshift($this->collection, $variables);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        foreach ($this->collection as $variables) {
            if ($variables->has($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        foreach ($this->collection as $variables) {
            if ($variables->has($name)) {
                return $variables->get($name)->getValue();
            }
        }

        throw new VariableNotFoundException($name);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($name)
    {
        $value = $this->get($name);

        return is_callable($value) ? $this->resolveCallbackWithCallstack($value) : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveString($string)
    {
        $data = [];

        foreach ($this->all() as $variable) {
            if (!is_callable($variable->getValue())) {
                $data[$variable->getName()] = $variable->getValue();
            }
        }

        $template = $this->twig->createTemplate($string);

        $result = $template->render($data);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveExpression($expression)
    {
        $data = [];

        foreach ($this->all() as $variable) {
            if (!is_callable($variable->getValue())) {
                $data[$variable->getName()] = $variable->getValue();
            }
        }

        return $this->expressionLanguage->evaluate($expression, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies($name)
    {
        $value = $this->get($name);

        return is_callable($value) ? $this->getDependenciesWithCallstack($value) : [];
    }

    /**
     * Return all variables flatten to single list by name and priority.
     *
     * @return VariableInterface[]
     */
    protected function all()
    {
        $result = [];

        foreach ($this->collection as $variables) {
            $result += $variables->all();
        }

        return $result;
    }

    /**
     * Resolve callback using arguments from the heap.
     *
     * Has circular reference detection.
     *
     * @param callable $callback
     * @param array $callstack
     *
     * @return mixed
     *
     * @throws VariableCircularReferenceException
     */
    protected function resolveCallbackWithCallstack(callable $callback, &$callstack = [])
    {
        $arguments = CallableHelper::extractArguments($callback);

        $callArguments = [];

        foreach ($arguments as $name) {

            $value = $this->get($name);

            if (is_callable($value)) {

                if (in_array($name, $callstack)) {
                    throw new VariableCircularReferenceException($name, $callstack);
                }

                $callstack[] = $name;

                $value = $this->resolveCallbackWithCallstack($value, $callstack);

                $extractedName = array_pop($callstack);

                if ($extractedName !== $name) {
                    throw new RuntimeException(sprintf('Extracted name `%s` doesn\'t match called `%s`', $extractedName, $name));
                }
            }

            $callArguments[$name] = $value;
        }

        return CallableHelper::call($callback, $callArguments);
    }

    /**
     * Return callback dependencies using arguments from the heap.
     *
     * Has circular reference detection.
     *
     * @param callable $callback
     * @param array $callstack
     *
     * @return array
     *
     * @throws VariableCircularReferenceException
     */
    protected function getDependenciesWithCallstack(callable $callback, &$callstack = [])
    {
        $arguments = CallableHelper::extractArguments($callback);

        $dependencies = $arguments;

        foreach ($arguments as $name) {

            if (!$this->has($name)) {
                continue;
            }

            $value = $this->get($name);

            if (is_callable($value)) {

                if (in_array($name, $callstack)) {
                    throw new VariableCircularReferenceException($name, $callstack);
                }

                $callstack[] = $name;

                $dependencies = array_merge($dependencies, $this->getDependenciesWithCallstack($value, $callstack));

                $extractedName = array_pop($callstack);

                if ($extractedName !== $name) {
                    throw new RuntimeException(sprintf('Extracted name `%s` doesn\'t match called `%s`', $extractedName, $name));
                }
            }

            $callArguments[$name] = $value;
        }

        $dependencies = array_unique($dependencies);

        return $dependencies;
    }
}
