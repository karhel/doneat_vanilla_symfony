<?php

namespace App\Security\Voter;

use App\Entity\Meal;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class MealVoter extends Voter
{
    public const EDIT       = 'edit';
    public const BOOK       = 'book';
    public const VIEW       = 'view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::BOOK])
            && $subject instanceof \App\Entity\Meal;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Meal $subject **/
        /** @var User $user **/

        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return ($subject->getCreatedBy() === $user);

            case self::BOOK:
                return ($subject->getCreatedBy() !== $user && $subject->getBookingRequests()->count() <= 0);
        }

        return false;
    }
}
