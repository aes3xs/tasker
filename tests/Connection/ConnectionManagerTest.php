<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\DependencyInjection;

use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Connection\ConnectionManager;
use Aes3xs\Yodler\Variable\VariableList;

class ConnectionManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function assertConnection($connection, $host, $port, $login, $password, $key, $passphrase, $forwarding)
    {
        /** @var Connection $connection */

        $this->assertInstanceOf(Connection::class, $connection);

        $this->assertEquals($host, $connection->getHost());
        $this->assertEquals($port, $connection->getPort());
        $this->assertEquals($login, $connection->getLogin());
        $this->assertEquals($password, $connection->getPassword());
        $this->assertEquals($key, $connection->getKey());
        $this->assertEquals($passphrase, $connection->getPassphrase());
        $this->assertEquals($forwarding, $connection->isForwarding());
    }

    protected function assertVariables($expect, $variables)
    {
        $this->assertInstanceOf(VariableList::class, $variables);

        $this->assertEquals($expect, $variables->all());
    }

    public function testCreateListFromConfiguration()
    {
        $configuration = [
            'test' => [
                'host'       => 'host',
                'port'       => 1122,
                'login'      => 'login',
                'password'   => 'password',
                'key'        => 'key',
                'passphrase' => 'passphrase',
                'forwarding' => true,
                'variables'  => [
                    'test' => 'value',
                ],
            ],
        ];

        $manager = new ConnectionManager($configuration);

        $connection = $manager->get('test');

        $this->assertConnection($connection, 'host', 1122, 'login', 'password', 'key', 'passphrase', true);
        $this->assertVariables(['test' => 'value'], $connection->getVariables());
    }

    public function testConfigurationDefaults()
    {
        $configuration = [
            'test' => [],
        ];

        $manager = new ConnectionManager($configuration);

        $connection = $manager->get('test');

        $this->assertConnection($connection, null, null, null, null, null, null, false);
        $this->assertNull($connection->getVariables());
    }

    public function testEmptyConfiguration()
    {
        $configuration = [
            'test' => null,
        ];

        $manager = new ConnectionManager($configuration);

        $connection = $manager->get('test');

        $this->assertConnection($connection, null, null, null, null, null, null, false);
        $this->assertNull($connection->getVariables());
    }

    public function testMultiple()
    {
        $configuration = [
            'connection1' => [],
            'connection2' => [],
            'connection3' => [],
        ];

        $manager = new ConnectionManager($configuration);

        $connection1 = $manager->get('connection1');
        $this->assertConnection($connection1, null, null, null, null, null, null, false);
        $this->assertNull($connection1->getVariables());

        $connection2 = $manager->get('connection2');
        $this->assertConnection($connection2, null, null, null, null, null, null, false);
        $this->assertNull($connection2->getVariables());

        $connection3 = $manager->get('connection3');
        $this->assertConnection($connection3, null, null, null, null, null, null, false);
        $this->assertNull($connection3->getVariables());
    }

    public function testLoadKey()
    {
        $configuration = [
            'test' => [
                'key' => __DIR__ . '/../Fixtures/key/id_rsa'
            ],
        ];

        $manager = new ConnectionManager($configuration);

        $connection = $manager->get('test');

        $expected = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
Private Key Contents
-----END RSA PRIVATE KEY-----
EOF;

        $this->assertEquals($expected, $connection->getKey());
    }

    public function testBadConfiguration()
    {
        $this->expectException(\Exception::class);

        $configuration = [
            'test' => [
                'wrong_parameter' => 'value',
            ],
        ];

        new ConnectionManager($configuration);
    }
}
