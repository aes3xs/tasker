<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Resolver;

use Aes3xs\Tasker\Exception\ArgumentNotFoundException;
use Aes3xs\Tasker\Exception\ResourceCircularReferenceException;
use Aes3xs\Tasker\Exception\ResourceNotFoundException;
use Aes3xs\Tasker\Exception\RuntimeException;
use Aes3xs\Tasker\ResourceLocator\ResourceLocatorInterface;
use Doctrine\Common\Inflector\Inflector;

/**
 * Resource resolver.
 */
class ResourceResolver
{
    /**
     * @var ResourceLocatorInterface
     */
    protected $resourceLocator;

    /**
     * Constructor.
     *
     * @param ResourceLocatorInterface $resourceLocator
     */
    public function __construct(ResourceLocatorInterface $resourceLocator)
    {
        $this->resourceLocator = $resourceLocator;
    }

    public function resolveResource($name)
    {
        switch (true) {
            case $this->resourceLocator->has($name):
                $resource = $this->resourceLocator->get($name);
                break;
            case $this->resourceLocator->has(Inflector::camelize($name)):
                $resource = $this->resourceLocator->get(Inflector::camelize($name));
                break;
            case $this->resourceLocator->has(Inflector::tableize($name)):
                $resource = $this->resourceLocator->get(Inflector::tableize($name));
                break;
            default:
                throw new ResourceNotFoundException($name);
        }

        return is_callable($resource) ? $this->resolveCallback($resource) : $resource;
    }

    /**
     * Resolve string using twig syntax.
     *
     * http://stackoverflow.com/a/40105067
     *
     * @param $string
     *
     * @return string
     */
    public function resolveString($string)
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());

        $tokens = $twig->tokenize(new \Twig_Source($string, ''));
        $parser = new \Twig_Parser($twig);
        /** @var \Twig_Node[] $nodes */
        $nodes = $parser->parse($tokens);

        $collected = [];
        $this->collectTwigNodes($nodes, $collected);
        $dependencies = array_keys($collected);

        $data = [];
        foreach ($dependencies as $name) {
            $data[$name] = $this->resolveResource($name);
        }
        $template = $twig->createTemplate($string);
        return $template->render($data);
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
     * Resolve callback using arguments from the heap.
     *
     * Has circular reference detection.
     *
     * @param callable $callback
     *
     * @return mixed
     */
    public function resolveCallback(callable $callback)
    {
        return $this->resolveCallbackWithCallstack($callback);
    }

    protected function resolveCallbackWithCallstack(callable $callback, &$callstack = [])
    {
        if (is_array($callback)) {
            $class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            $reflection = new \ReflectionMethod($class, $callback[1]);
        } else {
            $reflection = new \ReflectionFunction($callback);
        }

        $arguments = [];
        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            $class = $parameter->getClass() ? $parameter->getClass()->getName() : null;

            switch (true) {
                case $class && $this->resourceLocator->has($class):
                    $value = $this->resourceLocator->get($class);
                    break;
                case $this->resourceLocator->has($name):
                    $value = $this->resourceLocator->get($name);
                    break;
                case $this->resourceLocator->has(Inflector::camelize($name)):
                    $value = $this->resourceLocator->get(Inflector::camelize($name));
                    break;
                case $this->resourceLocator->has(Inflector::tableize($name)):
                    $value = $this->resourceLocator->get(Inflector::tableize($name));
                    break;
                case $parameter->isDefaultValueAvailable():
                    $value = $parameter->getDefaultValue();
                    break;
                default:
                    throw new ArgumentNotFoundException($name);
            }

            if (is_callable($value)) {
                if (in_array($name, $callstack)) {
                    throw new ResourceCircularReferenceException($name, $callstack);
                }
                $callstack[] = $name;
                $value = $this->resolveCallbackWithCallstack($value, $callstack);
                $extractedName = array_pop($callstack);
                if ($extractedName !== $name) {
                    throw new RuntimeException(sprintf('Extracted name `%s` doesn\'t match called `%s`', $extractedName, $name));
                }
            }

            $arguments[] = $value;
        }

        return call_user_func_array($callback, $arguments);
    }
}
