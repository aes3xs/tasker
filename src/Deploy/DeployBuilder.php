<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Deploy;

use Aes3xs\Yodler\Annotation\After;
use Aes3xs\Yodler\Annotation\Always;
use Aes3xs\Yodler\Annotation\Before;
use Aes3xs\Yodler\Annotation\Condition;
use Aes3xs\Yodler\Annotation\Failback;
use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Exception\ClassNotFoundException;
use Aes3xs\Yodler\Exception\FileReadException;
use Aes3xs\Yodler\Scenario\Action;
use Aes3xs\Yodler\Scenario\Scenario;
use Doctrine\Common\Annotations\AnnotationReader;

class DeployBuilder
{
    public function build($name, array $data)
    {
        $scenario = $this->createScenario($data['scenario']);
        $connection = $this->createConnection($data['connection']);

        return new Deploy($name, $scenario, $connection);
    }

    /**
     * Create list from configuration parsed from YAML.
     *
     * @param array $data
     *
     * @return Connection
     *
     * @throws FileReadException
     */
    protected function createConnection(array $data)
    {
        $connection = new Connection();

        $connection
            ->setHost($data['host'])
            ->setPort($data['port'])
            ->setLogin($data['login'])
            ->setPassword($data['password'])
            ->setPassphrase($data['passphrase'])
            ->setForwarding($data['forwarding']);

        $key = $data['key'];
        if ($key && file_exists($key)) {
            $keyContent = file_get_contents($key);
            if ($keyContent === false) {
                throw new FileReadException($key);
            }
            $key = $keyContent;
        }

        $connection->setKey($key);

        return $connection;
    }

    /**
     * Create scenario from classname.
     *
     * @param $class
     *
     * @return Scenario
     */
    protected function createScenario($class)
    {
        $annotationReader = new AnnotationReader();

        $scenario = new Scenario();

        $methods = null;
        if (strpos($class, '::') !== false) {
            list($class, $method) = explode('::', $class);
            $methods[] = $method;
        }

        if (!class_exists($class)) {
            throw new ClassNotFoundException($class);
        }

        $reflectionClass = new \ReflectionClass($class);

        $source = $reflectionClass->newInstanceWithoutConstructor();

        if ($constructor = $reflectionClass->getConstructor()) {
            $scenario->setInitializer($constructor->getClosure($source));
        }

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
            $isAlways = !!$annotationReader->getMethodAnnotation($method, Always::class);

            $action = new Action($method->getName(), $callback, $condition);

            if (!$isFailback || $isAlways) {
                $scenario->addAction($action);
            }

            if ($isFailback || $isAlways) {
                $scenario->addFailback($action);
            }
        }

        return $scenario;
    }
}
