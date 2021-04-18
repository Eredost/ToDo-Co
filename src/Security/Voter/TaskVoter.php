<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['TASK_MANAGE'])
            && $subject instanceof \App\Entity\Task;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'TASK_MANAGE':
                return $this->canManage($user, $subject);
        }

        return false;
    }

    public function canManage(UserInterface $user, Task $subject): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return $user === $subject->getUser();
    }
}
