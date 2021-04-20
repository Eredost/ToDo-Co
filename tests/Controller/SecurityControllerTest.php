<?php


namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\Controller\Traits\AuthTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityControllerTest extends WebTestCase
{
    use AuthTrait;

    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function getUser(string $username): UserInterface
    {
        return self::$container->get(UserRepository::class)
            ->findOneBy(['username' => $username])
        ;
    }

    public function testLogout(): void
    {
        $this->logIn($this->client, $this->getUser('user'));
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects();

        $this->client->followRedirect();
        self::assertResponseRedirects('/login');
    }

    public function testLogInWithInvalidCredentials(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Se connecter', [
            'username' => 'invalid',
            'password' => 'credentials',
        ]);
        $this->client->followRedirect();
        self::assertSelectorTextContains('div.alert.alert-danger', 'Identifiants invalides');
        self::assertPageTitleSame('Connexion - To Do List app');
    }

    public function testLogInWithInvalidCSRFToken():void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            'username'    => 'admin',
            'password'    => 'L4hA5tcRS4yBcJLp',
            '_csrf_token' => '',
        ]);
        $this->client->followRedirect();
        self::assertSelectorTextContains('div.alert.alert-danger', 'Invalid CSRF token.');
        self::assertPageTitleSame('Connexion - To Do List app');
    }

    public function testLogIn(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            'username'    => 'admin',
            'password'    => 'L4hA5tcRS4yBcJLp',
        ]);
        $this->client->followRedirect();
        self::assertPageTitleContains('Accueil');
    }

    public function testLogInAlreadyLogged(): void
    {
        $this->logIn($this->client, $this->getUser('user'));
        $this->client->request('GET', '/login');
        self::assertResponseRedirects();

        $this->client->followRedirect();
        self::assertPageTitleContains('Accueil');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
    }
}
