<?php


namespace App\Tests\Entity;

use App\Entity\Task;
use App\Tests\Entity\Traits\AssertTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
{
    use AssertTrait;

    protected function getEntity(): Task
    {
        return (new Task())
            ->setTitle('Une tâche très importante')
            ->setContent('Lorem dolor sit amet constrectur')
            ->setCreatedAt(new \DateTime())
        ;
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    public function testInvalidBlankTitle(): void
    {
        $this->assertHasErrors($this->getEntity()->setTitle(''), 1);
    }

    public function testInvalidLengthTitle(): void
    {
        $this->assertHasErrors($this->getEntity()->setTitle(str_repeat('*', 51)), 1);
    }

    public function testInvalidBlankContent(): void
    {
        $this->assertHasErrors($this->getEntity()->setContent(''), 1);
    }

    public function testInvalidLengthContent(): void
    {
        $this->assertHasErrors($this->getEntity()->setContent(str_repeat('*', 501)), 1);
    }

    public function testToggleIsDone(): void
    {
        $task = $this->getEntity();
        $task->toggle(!$task->isDone());

        self::assertTrue($task->isDone());
    }
}
