<?php


namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixture extends AbstractFixture implements DependentFixtureInterface
{
    private function getDummyTask(): Task
    {
        return (new Task())
            ->setTitle($this->faker->words(3, true))
            ->setContent($this->faker->paragraph(1))
        ;
    }

    protected function loadData(ObjectManager $manager): void
    {
        // Creation of many tasks without user linked to it
        $this->createMany(5, 'unlinked_task', function () {
            return $this->getDummyTask();
        });

        /**
         * Creation of several tasks affiliated to the users
         * @var User[] $users
         */
        $users = [
            $this->getReference('test_user'),
            $this->getReference('admin_user'),
            ...$this->getReferences('main_user')
        ];

        foreach ($users as $user) {
            $this->createMany(3, sprintf('%s_task', $user->getUsername()), function () use ($user) {
                return $this->getDummyTask()
                    ->setUser($user);
            });
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
