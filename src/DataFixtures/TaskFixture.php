<?php


namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        $users = $this->getReferences('main_user');

        // Creation of many tasks without user linked to it
        $this->createMany(5, 'unlinked_task', function () {
            return (new Task())
                ->setTitle($this->faker->title)
                ->setContent($this->faker->paragraph(1))
            ;
        });

        /**
         * Creation of several tasks affiliated to the user
         * @var User[] $users
         */
        foreach ($users as $user) {
            $this->createMany(3, sprintf('%s_task', $user->getUsername()), function () use ($user) {
                return (new Task())
                    ->setTitle($this->faker->title)
                    ->setContent($this->faker->paragraph(1))
                    ->setUser($user)
                ;
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
