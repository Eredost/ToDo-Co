<?php


namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Tests\Controller\Traits\AuthTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    use AuthTrait;

    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function getTask(User $user): Task
    {
        return self::$container->get(TaskRepository::class)
            ->findOneBy(['user' => $user])
        ;
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
        self::assertResponseIsSuccessful();
    }

    public function testDeletionAsAnonymous(): void
    {
        $user = $this->getUser('user');
        $task = $this->getTask($user);

        $this->client->request('POST', sprintf('/tasks/%d/delete', $task->getId()));
        self::assertResponseRedirects('/login');
    }

    public function testDeletionNotFound(): void
    {
        $this->logIn($this->client, $this->getUser('user'));
        $this->client->request('POST', '/tasks/0/delete');
        self::assertTrue($this->client->getResponse()->isNotFound());
    }

    public function testDeletionWithInvalidCSRFToken(): void
    {
        $user = $this->getUser('user');
        $this->logIn($this->client, $user);
        $task = $this->getTask($user);

        $this->client->request('POST', sprintf('/tasks/%d/delete', $task->getId()));
        self::assertResponseRedirects('/tasks');

        $this->client->followRedirect();
        self::assertSelectorNotExists('div.alert.alert-success');
    }

    public function testDeletionWithoutBeingAuthor(): void
    {
        $admin = $this->getUser('admin');
        $task = $this->getTask($admin);
        $this->logIn($this->client, $this->getUser('user'));

        $csrfToken = self::$container->get('security.csrf.token_manager')->getToken('task_delete');
        $this->client->request('POST', sprintf('/tasks/%d/delete', $task->getId()), [
            '_csrf_token' => $csrfToken,
        ]);
        self::assertTrue($this->client->getResponse()->isForbidden());
    }

    public function testDeletionAsAuthor(): void
    {
        $user = $this->getUser('user');
        $task = $this->getTask($user);
        $this->logIn($this->client, $user);

        $csrfToken = self::$container->get('security.csrf.token_manager')->getToken('task_delete');
        $this->client->request('POST', sprintf('/tasks/%d/delete', $task->getId()), [
            '_csrf_token' => $csrfToken,
        ]);
        self::assertResponseRedirects('/tasks');

        $this->client->followRedirect();
        self::assertSelectorExists('div.alert.alert-success');
    }

    public function testDeletionWithoutBeingAuthorAsAdmin(): void
    {
        $user = $this->getUser('user');
        $task = $this->getTask($user);
        $this->logIn($this->client, $this->getUser('admin'));

        $csrfToken = self::$container->get('security.csrf.token_manager')->getToken('task_delete');
        $this->client->request('POST', sprintf('/tasks/%d/delete', $task->getId()), [
            '_csrf_token' => $csrfToken,
        ]);
        self::assertResponseRedirects('/tasks');

        $this->client->followRedirect();
        self::assertSelectorExists('div.alert.alert-success');
    }

    public function testToggleAsAnonymous(): void
    {
        $task = $this->getTask($this->getUser('user'));
        $this->client->request('POST', sprintf('/tasks/%d/toggle', $task->getId()));
        self::assertResponseRedirects('/login');
    }

    public function testToggleWithInvalidCSRFToken(): void
    {
        $user = $this->getUser('user');
        $task = $this->getTask($user);
        $this->logIn($this->client, $user);

        $this->client->request('POST', sprintf('/tasks/%d/toggle', $task->getId()));
        self::assertResponseRedirects('/tasks');

        $this->client->followRedirect();
        self::assertSelectorNotExists('div.alert.alert-success');
    }

    public function testToggle(): void
    {
        $user = $this->getUser('user');
        $task = $this->getTask($user);
        $this->logIn($this->client, $user);

        $csrfToken = self::$container->get('security.csrf.token_manager')->getToken('task_toggle');
        $this->client->request('POST', sprintf('/tasks/%d/toggle', $task->getId()), [
            '_csrf_token' => $csrfToken
        ]);
        self::assertResponseRedirects('/tasks');

        $this->client->followRedirect();
        self::assertSelectorExists('div.alert.alert-success');
    }

    public function testEditWithInvalidInputValues(): void
    {
        $user = $this->getUser('user');
        $task = $this->getTask($user);
        $this->logIn($this->client, $user);

        $this->client->request('GET', sprintf('/tasks/%d/edit', $task->getId()));
        $this->client->submitForm('Modifier', [
            'task[title]' => '',
            'task[content]' => '',
        ]);

        self::assertPageTitleContains('Modifier une tâche');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testEditWithInvalidCSRFToken(): void
    {
        $user = $this->getUser('user');
        $task = $this->getTask($user);
        $this->logIn($this->client, $user);

        $this->client->request('GET', sprintf('/tasks/%d/edit', $task->getId()));
        $this->client->submitForm('Modifier', [
            'task[_token]' => '',
        ]);

        self::assertPageTitleContains('Modifier une tâche');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testEdit(): void
    {
        $user = $this->getUser('user');
        $task = $this->getTask($user);
        $this->logIn($this->client, $user);

        $this->client->request('GET', sprintf('/tasks/%d/edit', $task->getId()));
        $this->client->submitForm('Modifier', [
            'task[title]' => 'Un nouveau titre',
        ]);
        self::assertResponseRedirects('/tasks');
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('div.alert.alert-success');
    }

    public function testCreationWithInvalidInputValues(): void
    {
        $this->logIn($this->client, $this->getUser('user'));
        $this->client->request('GET', '/tasks/create');
        $this->client->submitForm('Ajouter', [
            'task[title]' => '',
            'task[content]' => '',
        ]);

        self::assertPageTitleContains('Ajouter une tâche');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testCreationWithInvalidCSRFToken(): void
    {
        $this->logIn($this->client, $this->getUser('user'));
        $this->client->request('GET', '/tasks/create');
        $this->client->submitForm('Ajouter', [
            'task[title]' => 'Une nouvelle tâche',
            'task[content]' => 'Lorem dolor sit amet',
            'task[_token]' => '',
        ]);

        self::assertPageTitleContains('Ajouter une tâche');
        self::assertSelectorExists('span.glyphicon-exclamation-sign');
    }

    public function testCreation(): void
    {
        $this->logIn($this->client, $this->getUser('user'));
        $this->client->request('GET', '/tasks/create');
        $this->client->submitForm('Ajouter', [
            'task[title]' => 'Une nouvelle tâche',
            'task[content]' => 'Lorem dolor sit amet',
        ]);

        self::assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        self::assertSelectorExists('div.alert.alert-success');
    }

    public function provideUrls(): array
    {
        return [
            ['/tasks'],
            ['/tasks/done'],
            ['/tasks/create'],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
    }
}
