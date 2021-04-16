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
     * @return array
     */
    public static function getAdminUser(): array
    {
        return static::ADMIN_USER;
    }
}
