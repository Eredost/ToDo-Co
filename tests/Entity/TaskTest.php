<?php


namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use App\Tests\Entity\Traits\AssertTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskTest extends KernelTestCase
{
    use AssertTrait;

    protected function getEntity(): Task
    {
        return (new Task())
            ->setTitle('Une tâche très importante')
            ->setContent('Lorem dolor sit amet constrectur')
            ->setCreatedAt(new \DateTime())
            ->setUser(new User())
        ;
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    public function testReturnTypeId(): void
    {
        self::assertNull($this->getEntity()->getId());
    }

    public function testReturnTypeCreatedAt(): void
    {
        self::assertInstanceOf(\DateTimeInterface::class, $this->getEntity()->getCreatedAt());
    }

    public function testInvalidBlankTitle(): void
    {
        $this->assertHasErrors($this->getEntity()->setTitle(''), 1);
    }

    public function testInvalidLengthTitle(): void
    {
        $this->assertHasErrors($this->getEntity()->setTitle(str_repeat('*', 51)), 1);
    }

    public function testReturnTypeTitle(): void
    {
        self::assertIsString($this->getEntity()->getTitle());
    }

    public function testInvalidBlankContent(): void
    {
        $this->assertHasErrors($this->getEntity()->setContent(''), 1);
    }

    public function testInvalidLengthContent(): void
    {
        $this->assertHasErrors($this->getEntity()->setContent(str_repeat('*', 501)), 1);
    }

    public function testReturnTypeContent(): void
    {
        self::assertIsString($this->getEntity()->getContent());
    }

    public function testToggleIsDone(): void
    {
        $task = $this->getEntity();
        $task->toggle(!$task->isDone());

        self::assertTrue($task->isDone());
    }

    public function testReturnTypeIsDone(): void
    {
        self::assertIsBool($this->getEntity()->isDone());
    }

    public function testReturnTypeUser(): void
    {
        self::assertInstanceOf(UserInterface::class, $this->getEntity()->getUser());
    }
}
