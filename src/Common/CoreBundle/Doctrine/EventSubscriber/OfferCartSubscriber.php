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

namespace Common\CoreBundle\Doctrine\EventSubscriber;

use Common\CoreBundle\Doctrine\Proxy\OfferServiceStatusEnum as OfferServiceStatusEnumProxy;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Manager\Firm\FirmCartManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class OfferCartSubscriber.
 */
class OfferCartSubscriber implements EventSubscriber
{
    /**
     * @var FirmCartManager
     */
    private $cartManager;

    /**
     * OfferCartSubscriber constructor.
     *
     * @param FirmCartManager $cartManager
     */
    public function __construct(FirmCartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $offer = $args->getEntity();

        if (!$offer instanceof Offer) {
            return;
        }

        /* @var Offer $offer */
        $offer->setOfferExaltationStatus(new OfferServiceStatusEnumProxy(
            $offer, $this->cartManager, FirmPackageServiceEnum::create(FirmPackageServiceEnum::OFFER_EXALTATION)
        ));

        $offer->setAdvanceFilterStatus(new OfferServiceStatusEnumProxy(
            $offer, $this->cartManager, FirmPackageServiceEnum::create(FirmPackageServiceEnum::ADVANCE_FILTER)
        ));
    }
}
