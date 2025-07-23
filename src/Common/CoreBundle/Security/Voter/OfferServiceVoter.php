<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Common\CoreBundle\Security\Voter;

use Common\CoreBundle\Entity\Offer\Offer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Description of OfferServiceVoter.
 *
 * @author sipee
 */
class OfferServiceVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!($subject instanceof Offer)) {
            return false;
        }

        if (!in_array($attribute, [
            'ROLE_OFFER_EXALTATION',
            'ROLE_ADVANCE_FILTER',
        ])) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $offer, TokenInterface $token)
    {
        /* @var Offer $offer */
        $now = new \DateTime();

        if ('ROLE_OFFER_EXALTATION' === $attribute) {
            return $offer->getOfferExaltationUntil() && $now <= $offer->getOfferExaltationUntil();
        }

        if ('ROLE_ADVANCE_FILTER' === $attribute) {
            return $offer->getAdvanceFilterUntil() && $now <= $offer->getAdvanceFilterUntil();
        }

        return false;
    }
}
