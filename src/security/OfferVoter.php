<?php

namespace App\Security;

use App\Entity\Offer;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OfferVoter extends Voter
{
  public const TRANSITION = 'CAN_TRANSITION';

  protected function supports(string $attribute, $subject): bool
  {
    return $attribute === self::TRANSITION && $subject instanceof Offer;
  }

  protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
  {
    $user = $token->getUser();
    if (!$user instanceof User) {
      return false;
    }

    if(in_array('ROLE_ADMIN', $user->getRoles(), true)) {
      return true;
    }

    return false;
  }
}