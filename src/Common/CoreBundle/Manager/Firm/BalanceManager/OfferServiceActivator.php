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

namespace Common\CoreBundle\Manager\Firm\BalanceManager;

use Common\CoreBundle\Entity\Firm\Order\FirmOrderItem;
use Common\CoreBundle\Entity\Firm\Package\FirmPackageService;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;

/**
 * Class OfferServiceActivator.
 */
class OfferServiceActivator implements ServiceActivatorInterface
{
    /**
     * @param FirmPackageService $service
     *
     * @return bool
     */
    public function supportService(FirmPackageService $service)
    {
        return in_array($service->getType()->getValue(), [
            FirmPackageServiceEnum::OFFER_EXALTATION,
            FirmPackageServiceEnum::ADVANCE_FILTER,
        ]);
    }

    /**
     * @param FirmOrderItem      $orderItem
     * @param FirmPackageService $service
     * @param null               $reference
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function activateService(
        FirmOrderItem $orderItem,
        FirmPackageService $service,
        $reference = null
    ) {
        /* @var Offer $reference */
        if (null === $reference) {
            return;
        }

        $until = (FirmPackageServiceEnum::OFFER_EXALTATION === $service->getType()->getValue())
            ? $reference->getOfferExaltationUntil()
            : $reference->getAdvanceFilterUntil()
        ;

        if (null === $until) {
            $until = new \DateTime();
        }

        $until->add(new \DateInterval('P30D'));

        if (FirmPackageServiceEnum::OFFER_EXALTATION === $service->getType()->getValue()) {
            $reference->setOfferExaltationUntil($until);
        } else {
            $reference->setAdvanceFilterUntil($until);
        }
    }
}
