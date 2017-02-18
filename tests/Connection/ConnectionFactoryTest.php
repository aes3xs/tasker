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
use Aes3xs\Yodler\Connection\ConnectionFactory;
use Aes3xs\Yodler\Connection\Server;
use Aes3xs\Yodler\Connection\User;
use Aes3xs\Yodler\Exception\FileReadException;
use Aes3xs\Yodler\Variable\VariableFactory;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
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

        $variableFactory = new VariableFactory();
        $connectionFactory = new ConnectionFactory($variableFactory);

        $connectionList = $connectionFactory->createListFromConfiguration($configuration);
        $this->assertCount(1, $connectionList->all());

        /** @var Connection $connection */
        $connection = $connectionList->get('test');
        $server = $connection->getServer();
        $user = $connection->getUser();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('test', $connection->getName());
        $this->assertCount(1, $connection->getVariables()->all());
        $this->assertEquals('value', $connection->getVariables()->get('test')->getValue());

        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals('host', $server->getHost());
        $this->assertEquals(1122, $server->getPort());

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('login', $user->getLogin());
        $this->assertEquals('password', $user->getPassword());
        $this->assertEquals('key', $user->getKey());
        $this->assertEquals('passphrase', $user->getPassphrase());
        $this->assertEquals(true, $user->getForwarding());

    }

    public function testConfigurationDefaults()
    {
        $configuration = [
            'test' => [],
        ];

        $variableFactory = new VariableFactory();
        $connectionFactory = new ConnectionFactory($variableFactory);

        $connectionList = $connectionFactory->createListFromConfiguration($configuration);

        /** @var Connection $connection */
        $connection = $connectionList->get('test');
        $server = $connection->getServer();
        $user = $connection->getUser();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('test', $connection->getName());
        $this->assertEquals([], $connection->getVariables()->all());

        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals(null, $server->getHost());
        $this->assertEquals(null, $server->getPort());

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(null, $user->getLogin());
        $this->assertEquals(null, $user->getPassword());
        $this->assertEquals(null, $user->getKey());
        $this->assertEquals(null, $user->getPassphrase());
        $this->assertEquals(false, $user->getForwarding());
    }

    public function testEmptyConfiguration()
    {
        $configuration = [
            'test' => null,
        ];

        $variableFactory = new VariableFactory();
        $connectionFactory = new ConnectionFactory($variableFactory);
        $connectionList = $connectionFactory->createListFromConfiguration($configuration);
        $connection = $connectionList->get('test');

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals('test', $connection->getName());
    }

    public function testMultiple()
    {
        $configuration = [
            'connection1' => [],
            'connection2' => [],
            'connection3' => [],
        ];

        $variableFactory = new VariableFactory();
        $connectionFactory = new ConnectionFactory($variableFactory);

        $connectionList = $connectionFactory->createListFromConfiguration($configuration);
        $this->assertCount(3, $connectionList->all());

        $connection1 = $connectionList->get('connection1');
        $connection2 = $connectionList->get('connection2');
        $connection3 = $connectionList->get('connection3');

        $this->assertInstanceOf(Connection::class, $connection1);
        $this->assertEquals('connection1', $connection1->getName());
        $this->assertInstanceOf(Connection::class, $connection2);
        $this->assertEquals('connection2', $connection2->getName());
        $this->assertInstanceOf(Connection::class, $connection3);
        $this->assertEquals('connection3', $connection3->getName());
    }

    public function testLoadKey()
    {
        $configuration = [
            'test' => [
                'key' => __DIR__ . '/../Fixtures/key/id_rsa'
            ],
        ];

        $variableFactory = new VariableFactory();
        $connectionFactory = new ConnectionFactory($variableFactory);
        $connectionList = $connectionFactory->createListFromConfiguration($configuration);
        $connection = $connectionList->get('test');

        $expected = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
Private Key Contents
-----END RSA PRIVATE KEY-----
EOF;

        $this->assertEquals($expected, $connection->getUser()->getKey());
    }

    public function testBadConfiguration()
    {
        $this->expectException(\Exception::class);

        $configuration = [
            'test' => [
                'wrong_parameter' => 'value',
            ],
        ];

        $variableFactory = new VariableFactory();
        $connectionFactory = new ConnectionFactory($variableFactory);

        $connectionFactory->createListFromConfiguration($configuration);
    }

    public function testCreateStubConnection()
    {
        $variableFactory = new VariableFactory();
        $connectionFactory = new ConnectionFactory($variableFactory);

        /** @var Connection $connection */
        $connection = $connectionFactory->createStubConnection();
        $server = $connection->getServer();
        $user = $connection->getUser();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals(null, $connection->getName());
        $this->assertEquals([], $connection->getVariables()->all());

        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals(null, $server->getHost());
        $this->assertEquals(null, $server->getPort());

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(null, $user->getLogin());
        $this->assertEquals(null, $user->getPassword());
        $this->assertEquals(null, $user->getKey());
        $this->assertEquals(null, $user->getPassphrase());
        $this->assertEquals(false, $user->getForwarding());
    }
}
