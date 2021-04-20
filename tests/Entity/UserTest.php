<?php


namespace App\Tests\Entity;

use App\Entity\User;
use App\Tests\Entity\Traits\AssertTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    use AssertTrait;

    protected function getEntity(): User
    {
        return (new User())
            ->setEmail('john.doe@example.com')
            ->setUsername('John Doe')
            ->setRoles(['ROLE_USER'])
            ->setPassword('Pas3word')
        ;
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    public function testInvalidBlankEmail(): void
    {
        $this->assertHasErrors($this->getEntity()->setEmail(''), 1);
    }

    public function testInvalidValuesEmail(): void
    {
        $user = $this->getEntity();

        $this->assertHasErrors($user->setEmail('john.doe@example'), 1);
        $this->assertHasErrors($user->setEmail('@example.org'), 1);
        $this->assertHasErrors($user->setEmail('john.example.org'), 1);
    }

    public function testInvalidUniqueEmail(): void
    {
        $this->assertHasErrors($this->getEntity()->setEmail('admin@todo-co.fr'), 1);
    }

    public function testInvalidBlankUsername(): void
    {
        $this->assertHasErrors($this->getEntity()->setUsername(''), 1);
    }

    public function testInvalidLengthUsername(): void
    {
        $this->assertHasErrors($this->getEntity()->setUsername(str_repeat('*', 26)), 1);
    }

    public function testInvalidUniqueUsername(): void
    {
        $this->assertHasErrors($this->getEntity()->setUsername('admin'), 1);
    }

    public function testInvalidBlankRoles(): void
    {
        $this->assertHasErrors($this->getEntity()->setRoles(['']), 1);
    }

    public function testInvalidValueRoles(): void
    {
        $this->assertHasErrors($this->getEntity()->setRoles(['ROLE_USE', 'ROLE_MODERATOR']), 2);
    }

    public function testInvalidBlankPassword(): void
    {
        $this->assertHasErrors($this->getEntity()->setPassword(''), 1);
    }

    public function testInvalidLengthPassword(): void
    {
        $user = $this->getEntity();

        $this->assertHasErrors($user->setPassword('123'), 1);
        $this->assertHasErrors($user->setPassword(str_repeat('*', 41)), 1);
    }

    public function testInvalidValuesPassword(): void
    {
        $user = $this->getEntity();

        $this->assertHasErrors($user->setPassword('password'), 1);
        $this->assertHasErrors($user->setPassword('Password'), 1);
        $this->assertHasErrors($user->setPassword('pas3word'), 1);
        $this->assertHasErrors($user->setPassword('P3363388'), 1);
    }
}
