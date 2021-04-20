<?php


namespace App\DataFixtures\Providers;

class UserProvider
{
    /**
     * Contains the user with administrator privileges
     *
     * @var array
     */
    private const ADMIN_USER = [
        'username'  => 'admin',
        'email'     => 'admin@todo-co.fr',
        'password'  => 'L4hA5tcRS4yBcJLp',
        'roles'     => ['ROLE_ADMIN'],
    ];

    /**
     * Contains an user without privileges for test purposes
     *
     * @var array
     */
    private const TEST_USER = [
        'username'  => 'user',
        'email'     => 'user@example.com',
        'password'  => 'dNSc4L4Pb3hmfP9G',
        'roles'     => ['ROLE_USER'],
    ];

    /**
     * @return array
     */
    public static function getAdminUser(): array
    {
        return static::ADMIN_USER;
    }

    /**
     * @return array
     */
    public static function getTestUser(): array
    {
        return static::TEST_USER;
    }
}
