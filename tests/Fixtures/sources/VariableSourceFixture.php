<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Fixture for VariableFactoryTest.
 */
class VariableSourceFixture
{
    public function test()
    {
        return 'value';
    }

    public function doTest2()
    {
        return 'value2';
    }

    public function runTest3()
    {
        return 'value3';
    }

    public function getTest4()
    {
        return 'value4';
    }

    public function setTest5()
    {
        return 'value5';
    }

    public function evaluated()
    {
        return 'valueEvaluated';
    }

    public function notEvaluated($argument)
    {
        return 'valueNotEvaluated';
    }
}
