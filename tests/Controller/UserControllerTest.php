<?php


namespace App\Tests\Controller;

use App\Tests\Controller\Traits\AuthTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use AuthTrait;

    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @dataProvider provideUrls
     */
    public function testPagesAccessAsAnonymous($url): void
    {
        $this->client->request('GET', $url);
        self::assertResponseRedirects('/login');
    }

    /**
     * @dataProvider provideUrls
     */
    public function testPagesAccessAsUser($url): void
    {
        $this->logIn($this->client, $this->getUser('user'));
        $this->client->request('GET', $url);
        self::assertTrue($this->client->getResponse()->isForbidden());
    }

    /**
     * @dataProvider provideUrls
     */
    public function testPagesAccessAsAdmin($url): void
    {
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', $url);
        self::assertResponseIsSuccessful();
    }

    public function testEditPageAccessAsAnonymous(): void
    {
        $userId = $this->getUser('user')->getId();
        $this->client->request('GET', sprintf('/users/%d/edit', $userId));
        self::assertResponseRedirects('/login');
    }

    public function testEditPageAccessAsUser(): void
    {
        $user = $this->getUser('user');
        $this->logIn($this->client, $user);
        $this->client->request('GET', sprintf('/users/%d/edit', $user->getId())) ;
        self::assertTrue($this->client->getResponse()->isForbidden());
    }

    public function testEditPageAccessNotFound(): void
    {
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', '/users/0/edit');
        self::assertTrue($this->client->getResponse()->isNotFound());
    }

    public function testCreationWithInvalidInputValues(): void
    {
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', []);

        self::assertPageTitleContains('Ajouter un utilisateur');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testCreationWithInvalidCSRFToken(): void
    {
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', [
            'user[username]' => 'a',
            'user[password][first]' => 'Pas3word',
            'user[password][second]' => 'Pas3word',
            'user[email]' => 'email@exxample.com',
            'user[_token]' => '',
        ]);

        self::assertPageTitleContains('Ajouter un utilisateur');
        self::assertSelectorTextContains('div.alert.alert-danger', 'The CSRF token is invalid. Please try to resubmit the form.');
    }

    public function testCreationWithAlreadyUsedUsername(): void
    {
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', [
            'user[username]' => 'admin',
            'user[password][first]' => 'Pas3word',
            'user[password][second]' => 'Pas3word',
            'user[email]' => 'email@exxample.com',
        ]);

        self::assertPageTitleContains('Ajouter un utilisateur');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testCreationWithAlreadyUsedEmail(): void
    {
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', [
            'user[username]' => 'a',
            'user[password][first]' => 'Pas3word',
            'user[password][second]' => 'Pas3word',
            'user[email]' => 'admin@todo-co.fr',
        ]);

        self::assertPageTitleContains('Ajouter un utilisateur');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testCreation(): void
    {
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', [
            'user[username]' => 'a',
            'user[password][first]' => 'Pas3word',
            'user[password][second]' => 'Pas3word',
            'user[email]' => 'email@exxample.com',
        ]);

        self::assertResponseRedirects('/users');
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('div.alert.alert-success');

        $newUser = $this->getUser('a');
        self::assertNotNull($newUser);
    }

    public function testEditWithInvalidInputValues(): void
    {
        $user = $this->getUser('user');
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', sprintf('/users/%d/edit', $user->getId()));
        $this->client->submitForm('Modifier', [
            'user[username]' => '',
            'user[email]' => '',
        ]);

        self::assertPageTitleContains('Modifier un utilisateur');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testEditWithInvalidCSRFToken(): void
    {
        $user = $this->getUser('user');
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', sprintf('/users/%d/edit', $user->getId()));
        $this->client->submitForm('Modifier', [
            'user[password][first]' => 'Pas3word',
            'user[password][second]' => 'Pas3word',
            'user[_token]' => '',
        ]);

        self::assertPageTitleContains('Modifier un utilisateur');
        self::assertSelectorTextContains('div.alert.alert-danger', 'The CSRF token is invalid. Please try to resubmit the form.');
    }

    public function testEditWithAlreadyUsedUsername(): void
    {
        $user = $this->getUser('user');
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', sprintf('/users/%d/edit', $user->getId()));
        $this->client->submitForm('Modifier', [
            'user[username]' => 'admin',
            'user[password][first]' => 'Pas3word',
            'user[password][second]' => 'Pas3word',
        ]);

        self::assertPageTitleContains('Modifier un utilisateur');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testEditWithAlreadyUsedEmail(): void
    {
        $user = $this->getUser('user');
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', sprintf('/users/%d/edit', $user->getId()));
        $this->client->submitForm('Modifier', [
            'user[password][first]' => 'Pas3word',
            'user[password][second]' => 'Pas3word',
            'user[email]' => 'admin@todo-co.fr',
        ]);

        self::assertPageTitleContains('Modifier un utilisateur');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testEdit(): void
    {
        $user = $this->getUser('user');
        $this->logIn($this->client, $this->getUser('admin'));
        $this->client->request('GET', sprintf('/users/%d/edit', $user->getId()));
        $this->client->submitForm('Modifier', [
            'user[username]' => 'a',
            'user[password][first]' => 'Pas3word',
            'user[password][second]' => 'Pas3word',
        ]);

        self::assertResponseRedirects('/users');
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('div.alert.alert-success');

        $newUser = $this->getUser('a');
        self::assertNotNull($newUser);
    }

    public function provideUrls(): array
    {
        return [
            ['/users'],
            ['/users/create'],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
    }
}
