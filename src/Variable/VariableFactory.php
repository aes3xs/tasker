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

use Aes3xs\Yodler\Exception\ClassMismatchException;
use Aes3xs\Yodler\Exception\ClassNotFoundException;
use Aes3xs\Yodler\Exception\FileNotFoundException;
use Aes3xs\Yodler\Recipe\RecipeInterface;

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
}
