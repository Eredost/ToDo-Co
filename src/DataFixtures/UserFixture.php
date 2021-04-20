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
        $credentials = UserProvider::getAdminUser();
        $admin = (new User())
            ->setUsername($credentials['username'])
            ->setEmail($credentials['email'])
            ->setRoles($credentials['roles'])
        ;
        $admin->setPassword($this->encoder->encodePassword($admin, $credentials['password']));
        $manager->persist($admin);

        // Creation of a simple user for test purposes
        $credentials = UserProvider::getTestUser();
        $user = (new User())
            ->setUsername($credentials['username'])
            ->setEmail($credentials['email'])
            ->setRoles($credentials['roles'])
        ;
        $user->setPassword($this->encoder->encodePassword($user, $credentials['password']));
        $manager->persist($user);

        // Creation of simple users who will be linked to tasks
        $this->createMany(4, 'main_user', function () {
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
