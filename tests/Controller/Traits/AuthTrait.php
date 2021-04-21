<?php


namespace App\Tests\Controller\Traits;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

trait AuthTrait
{
    protected function getUser(string $username): UserInterface
    {
        return self::$container->get(UserRepository::class)
            ->findOneBy(['username' => $username])
        ;
    }

    public function logIn(KernelBrowser $client, UserInterface $user): void
    {
        $session = self::$container->get('session');

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
