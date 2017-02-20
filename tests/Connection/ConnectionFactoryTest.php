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
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateListFromConfiguration()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with(['test' => 'value'])->willReturn($variablesMock);

        $connectionFactory = new ConnectionFactory($variableFactoryMock);

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

        $connectionList = $connectionFactory->createListFromConfiguration($configuration);
        $this->assertCount(1, $connectionList->all());

        /** @var Connection $connection */
        $connection = $connectionList->get('test');
        $expectedServer = new Server('host', 1122);
        $expectedUser = new User('login', 'password', 'key', 'passphrase', true);
        $expectedConnection = new Connection('test', $expectedServer, $expectedUser, $variablesMock);

        $this->assertEquals($expectedConnection, $connection);
    }

    public function testConfigurationDefaults()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with([])->willReturn($variablesMock);

        $connectionFactory = new ConnectionFactory($variableFactoryMock);

        $configuration = [
            'test' => [],
        ];

        $connectionList = $connectionFactory->createListFromConfiguration($configuration);

        /** @var Connection $connection */
        $connection = $connectionList->get('test');
        $expectedServer = new Server(null, null);
        $expectedUser = new User(null, null, null, null, false);
        $expectedConnection = new Connection('test', $expectedServer, $expectedUser, $variablesMock);

        $this->assertEquals($expectedConnection, $connection);
    }

    public function testEmptyConfiguration()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with([])->willReturn($variablesMock);

        $configuration = [
            'test' => null,
        ];

        $connectionFactory = new ConnectionFactory($variableFactoryMock);
        $connectionList = $connectionFactory->createListFromConfiguration($configuration);

        /** @var Connection $connection */
        $connection = $connectionList->get('test');
        $expectedServer = new Server(null, null);
        $expectedUser = new User(null, null, null, null, false);
        $expectedConnection = new Connection('test', $expectedServer, $expectedUser, $variablesMock);

        $this->assertEquals($expectedConnection, $connection);
    }

    public function testMultiple()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with([])->willReturn($variablesMock);

        $configuration = [
            'connection1' => [],
            'connection2' => [],
            'connection3' => [],
        ];

        $connectionFactory = new ConnectionFactory($variableFactoryMock);

        $connectionList = $connectionFactory->createListFromConfiguration($configuration);
        $this->assertCount(3, $connectionList->all());

        $server = new Server(null, null);
        $user = new User(null, null, null, null, false);
        $expectedConnection1 = new Connection('connection1', $server, $user, $variablesMock);
        $expectedConnection2 = new Connection('connection2', $server, $user, $variablesMock);
        $expectedConnection3 = new Connection('connection3', $server, $user, $variablesMock);

        $this->assertEquals($expectedConnection1, $connectionList->get('connection1'));
        $this->assertEquals($expectedConnection2, $connectionList->get('connection2'));
        $this->assertEquals($expectedConnection3, $connectionList->get('connection3'));
    }

    public function testLoadKey()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with([])->willReturn($variablesMock);

        $configuration = [
            'test' => [
                'key' => __DIR__ . '/../Fixtures/key/id_rsa'
            ],
        ];

        $connectionFactory = new ConnectionFactory($variableFactoryMock);
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

        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);

        $configuration = [
            'test' => [
                'wrong_parameter' => 'value',
            ],
        ];

        $connectionFactory = new ConnectionFactory($variableFactoryMock);

        $connectionFactory->createListFromConfiguration($configuration);
    }

    public function testCreateStubConnection()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with([])->willReturn($variablesMock);

        $connectionFactory = new ConnectionFactory($variableFactoryMock);

        /** @var Connection $connection */
        $connection = $connectionFactory->createStubConnection();

        $expectedServer = new Server(null, null);
        $expectedUser = new User(null, null, null, null, false);
        $expectedConnection = new Connection(null, $expectedServer, $expectedUser, $variablesMock);

        $this->assertEquals($expectedConnection, $connection);
    }
}
