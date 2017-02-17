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
 * Interface to variable list feature for services.
 *
 * If service implements this interface it becomes provider for global variable heap.
 * Currently works for Connection, Scenario and Deploy.
 */
interface VariableSuppliedInterface
{
    /**
     * @return VariableListInterface
     */
    public function getVariables();
}
