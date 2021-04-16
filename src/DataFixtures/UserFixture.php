<?php


namespace App\DataFixtures;

use App\DataFixtures\Providers\UserProvider;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends AbstractFixture
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    protected function loadData(ObjectManager $manager): void
    {
        // Creation of a user with administrator privileges
        $admin = UserProvider::getAdminUser();
        $adminUser = (new User())
            ->setUsername($admin['username'])
            ->setEmail($admin['email'])
            ->setRoles($admin['roles'])
        ;
        $adminUser->setPassword($this->encoder->encodePassword($adminUser, $admin['password']));
        $manager->persist($adminUser);

        // Creation of simple users who will be linked to tasks
        $this->createMany(4, 'main_user', function ($count) {
            $user = (new User())
                ->setUsername($this->faker->userName)
                ->setEmail($this->faker->safeEmail)
                ->setRoles(['ROLE_USER'])
            ;
            $user->setPassword($this->encoder->encodePassword($user, 'Pas3word'));

            return $user;
        });

        $manager->flush();
    }
}
