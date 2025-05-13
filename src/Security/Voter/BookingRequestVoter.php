<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class BookingRequestVoter extends Voter
{
    public const EDIT       = 'edit';
    public const VALIDATE   = 'validate';
    public const CLOSE      = 'close';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VALIDATE, self::CLOSE])
            && $subject instanceof \App\Entity\BookingRequest;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var BookingRequest $subject **/
        /** @var User $user **/

        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                return ($subject->getRequestedBy() === $user);

            case self::VALIDATE:
                // logic to determine if the user can VALIDATE
                // return true or false
                return ($subject->getMeal()->getCreatedBy() === $user);
            
            case self::CLOSE:
                // logic to determine if the user can CLOSE
                // return true or false
                return (!$subject->getClosedAt() && (
                    ($subject->getRequestedBy() === $user && !$subject->getClosedByEaterAt()) || 
                    ($subject->getMeal()->getCreatedBy() === $user && !$subject->getClosedByGiverAt())
                ));
        }

        return false;
    }
}
