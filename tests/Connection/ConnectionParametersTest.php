<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Commander;

use Aes3xs\Yodler\Connection\ConnectionParameters;

class ConnectionParametersTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPublicKeyContents()
    {
        $params = new ConnectionParameters();
        $params
            ->setPublicKey('Public Key Contents');

        $this->assertEquals('Public Key Contents', $params->getPublicKeyContents());
    }

    public function testGetPrivateKeyContents()
    {
        $params = new ConnectionParameters();
        $params
            ->setPrivateKey('Private Key Contents');

        $this->assertEquals('Private Key Contents', $params->getPrivateKeyContents());
    }

    public function testLoadPublicKeyContents()
    {
        $params = new ConnectionParameters();
        $params
            ->setPublicKey(__DIR__ . '/../Fixtures/key/id_rsa.pub');

        $this->assertEquals('Public Key Contents', $params->getPublicKeyContents());
    }

    public function testLoadPrivateKeyContents()
    {
        $params = new ConnectionParameters();
        $params
            ->setPrivateKey(__DIR__ . '/../Fixtures/key/id_rsa');

        $this->assertEquals('Private Key Contents', $params->getPrivateKeyContents());
    }
}
