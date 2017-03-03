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

/**
 * Interface to variable factory.
 */
interface VariableFactoryInterface
{
    /**
     * Create list from associative array.
     *
     * @param array $values
     *
     * @return VariableListInterface
     */
    public function createList(array $values = []);
}
