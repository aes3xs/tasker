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
use Aes3xs\Yodler\Variable\VariableListInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\Node\Node;

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
        $value = $this->getRaw($name);
        return is_callable($value) ? $this->resolveCallback($value) : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveString($string)
    {
        $dependencies = $this->getTwigVariables($string);
        $data = [];
        foreach ($dependencies as $name) {
            $data[$name] = $this->get($name);
        }
        $template = $this->twig->createTemplate($string);
        return $template->render($data);
    }

    /**
     * Return twig variables by source.
     *
     * http://stackoverflow.com/a/40105067
     *
     * @param $source
     *
     * @return array
     */
    protected function getTwigVariables($source)
    {
        $tokens = $this->twig->tokenize(new \Twig_Source($source, ''));
        $parser = new \Twig_Parser($this->twig);
        $parsed = $parser->parse($tokens);
        $collected = [];
        $this->collectTwigNodes($parsed, $collected);
        return array_keys($collected);
    }

    /**
     * @param \Twig_Node[] $nodes
     * @param array $collected
     */
    protected function collectTwigNodes($nodes, array &$collected)
    {
        foreach ($nodes as $node) {
            $childNodes = $node->getIterator()->getArrayCopy();
            if (!empty($childNodes)) {
                $this->collectTwigNodes($childNodes, $collected); // recursion
            } elseif ($node instanceof \Twig_Node_Expression_Name) {
                $name = $node->getAttribute('name');
                $collected[$name] = $node; // ensure unique values
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolveExpression($expression)
    {
        $dependencies = $this->getExpressionVariables($expression);
        $data = [];
        foreach ($dependencies as $name) {
            $data[$name] = $this->get($name);
        }
        return $this->expressionLanguage->evaluate($expression, $data);
    }

    /**
     * @param $expression
     *
     * @return array
     */
    protected function getExpressionVariables($expression)
    {
        $nodes = $this->expressionLanguage->parse($expression, array_keys($this->all()))->getNodes()->nodes;
        $collected = [];
        $this->collectExpressionNameNodes($nodes, $collected);
        return array_keys($collected);
    }

    /**
     * @param array $nodes
     * @param array $collected
     */
    protected function collectExpressionNameNodes(array $nodes, array &$collected)
    {
        /** @var Node $node */
        foreach ($nodes as $node) {
            if ($node instanceof NameNode) {
                $name = $node->attributes['name'];
                $collected[$name] = $name; // ensure unique values
            }
            $this->collectExpressionNameNodes($node->nodes, $collected); // recursion
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolveCallback(callable $callback)
    {
        return $this->resolveCallbackWithCallstack($callback);
    }

    /**
     * Return all variables flatten to single list by name and priority.
     *
     * @return array
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
     * @param $name
     * @return mixed
     */
    public function getRaw($name)
    {
        foreach ($this->collection as $variables) {
            if ($variables->has($name)) {
                return $variables->get($name);
            }
        }

        throw new VariableNotFoundException($name);
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

            if (!$this->has($name)) {
                continue;
            }

            $value = $this->getRaw($name);

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
}
