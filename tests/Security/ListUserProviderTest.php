<?php

namespace Cascade\Security;

use Symfony\Component\Security\Core\User\User;

class ListUserProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ListUserProvider */
    private $sut;

    public function setUp()
    {
        $userList = [
            'foo' => [
                'password' => 'bar',
                'roles' => ['FOO_ROLE']
            ]
        ];

        $this->sut = new ListUserProvider($userList);
    }

    public function testLoadUserFromList()
    {
        $expected = new User('foo', 'bar', ['FOO_ROLE']);
        $this->assertEquals($expected, $this->sut->loadUserByUsername('foo'));
    }

    public function testThrowNotFoundIfUserIsNotInList()
    {
        $username = 'bob';

        $this->setExpectedException(
            'Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException',
            "Username $username does not exist"
        );

        $this->sut->loadUserByUsername($username);
    }

    public function testRefreshUser()
    {
        $user = new User('foo', 'bar', ['FOO_ROLE']);
        $expected = new User('foo', 'bar', ['FOO_ROLE']);
        $this->assertEquals($expected, $this->sut->refreshUser($user));
    }

    public function testThrowUnupportedUserExceptionIfPassedInvalidUserType()
    {
        $invalidUser = $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserInterface');
        $this->setExpectedException(
            'Symfony\\Component\\Security\\Core\\Exception\\UnsupportedUserException',
            sprintf('Instances of "%s" are not supported.', get_class($invalidUser))
        );

        $this->sut->refreshUser($invalidUser);
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->sut->supportsClass('Symfony\\Component\\Security\\Core\\User\\User'));
        $this->assertFalse($this->sut->supportsClass('Foo'));
    }
}
