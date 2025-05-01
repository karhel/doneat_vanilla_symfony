<?php

namespace App\Security\Voter;

use App\Entity\Meal;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class MealVoter extends Voter
{
    public const EDIT = 'edit';
    public const VIEW = 'view';
    public const BOOK = 'book';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::BOOK])
            && $subject instanceof \App\Entity\Meal;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                /** @var Meal $subject **/
                /** @var User $user **/
                return ($subject->getCreatedBy() === $user);

            case self::VIEW:
                return true;

            case self::BOOK:
                /** @var Meal $subject **/
                /** @var User $user **/
                return ($subject->getCreatedBy() !== $user);
        }

        return false;
    }
}
