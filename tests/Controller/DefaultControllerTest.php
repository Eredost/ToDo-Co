<?php


namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\Controller\Traits\AuthTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultControllerTest extends WebTestCase
{
    use AuthTrait;

    private ?KernelBrowser $client = null;

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

    public function testLoginRedirect(): void
    {
        $this->client->request('GET', '/');
        self::assertResponseRedirects('/login');

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testHomeAccessLoggedAsUser(): void
    {
        $this->logIn($this->client, $this->getUser('user'));
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Accueil');

        // Checks that the section reserved for administrators is not displayed to users
        self::assertSelectorNotExists('a[href="/users"]');
        self::assertSelectorNotExists('a[href="/users/create"]');
    }

    public function testHomeAccessLoggedAsAdmin(): void
    {
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();

        // Checks if the section reserved for administrators is showing
        self::assertSelectorExists('a[href="/users"]');
        self::assertSelectorExists('a[href="/users/create"]');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
    }
}
