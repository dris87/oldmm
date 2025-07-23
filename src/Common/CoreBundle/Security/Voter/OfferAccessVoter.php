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

use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class OfferAccessVoter.
 */
class OfferAccessVoter extends Voter
{
    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!($subject instanceof Offer)) {
            return false;
        }

        if (!in_array($attribute, [
            'ROLE_OFFER_CREATE',
            'ROLE_OFFER_EDIT',
            'ROLE_OFFER_DELETE',
            'ROLE_OFFER_VIEW_CANDIDATES',
        ])) {
            return false;
        }

        return true;
    }

    /**
     * @param string         $attribute
     * @param Offer          $offer
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $offer, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof FirmColleague) {
            return false;
        }

        if ('ROLE_OFFER_CREATE' === $attribute) {
            return true;
        }

        if ('ROLE_OFFER_EDIT' === $attribute) {
            if (
                $offer->getStatus() != OfferStatusEnum::create(OfferStatusEnum::SAVED) &&
                $offer->getStatus() != OfferStatusEnum::create(OfferStatusEnum::DENIED)
            ) {
                return false;
            }
        }

        if ('ROLE_OFFER_DELETE' === $attribute) {
            if ($offer->getStatus() != OfferStatusEnum::create(OfferStatusEnum::SAVED)) {
                return false;
            }
        }

        if ('ROLE_OFFER_VIEW_CANDIDATES' === $attribute) {
            if (
                $offer->getStatus() != OfferStatusEnum::create(OfferStatusEnum::ACTIVE) &&
                $offer->getStatus() != OfferStatusEnum::create(OfferStatusEnum::INACTIVE)
            ) {
                return false;
            }
        }

        return $offer->getFirm() === $user->getFirm();
    }
}
