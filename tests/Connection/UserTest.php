<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Connection;

use Aes3xs\Yodler\Connection\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $user = new User('login', 'password', 'key', 'passphrase', true);

        $this->assertEquals('login', $user->getLogin());
        $this->assertEquals('password', $user->getPassword());
        $this->assertEquals('key', $user->getKey());
        $this->assertEquals('passphrase', $user->getPassphrase());
        $this->assertEquals(true, $user->getForwarding());
    }
}
