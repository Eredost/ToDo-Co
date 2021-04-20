<?php


namespace App\Tests\Entity\Traits;

trait AssertTrait
{
    abstract protected function getEntity();

    /**
     * This method is used to test an object according to the validators which have
     * been attached to it and to display errors if it fails
     *
     * $this->assertHasErrors($user->setEmail('faux-email.fr'), 1);
     *
     * @param             $entity
     * @param int         $number
     * @param string|null $groups
     *
     * @return void
     */
    private function assertHasErrors($entity, int $number = 0, ?string $groups = null)
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($entity, null, $groups);
        $messages = [];

        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . '=>' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }
}
