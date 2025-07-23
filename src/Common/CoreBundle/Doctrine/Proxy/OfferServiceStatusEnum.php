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

namespace Common\CoreBundle\Doctrine\Proxy;

use Biplane\EnumBundle\Enumeration\EnumInterface;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Enumeration\Offer\OfferServiceStatusEnum as BaseOfferServiceStatusEnum;
use Common\CoreBundle\Manager\Firm\FirmCartManager;

/**
 * Class OfferServiceStatusEnum.
 */
class OfferServiceStatusEnum extends BaseOfferServiceStatusEnum
{
    /**
     * @var FirmPackageServiceEnum
     */
    public $service;
    /**
     * @var Offer
     */
    private $offer;

    /**
     * @var FirmCartManager
     */
    private $cartManager;

    /**
     * @var BaseOfferServiceStatusEnum
     */
    private $enum;

    /**
     * OfferServiceStatusEnum constructor.
     *
     * @param Offer                  $offer
     * @param FirmCartManager        $cartManager
     * @param FirmPackageServiceEnum $service
     */
    public function __construct(Offer $offer, FirmCartManager $cartManager, FirmPackageServiceEnum $service)
    {
        $this->offer = $offer;
        $this->cartManager = $cartManager;
        $this->service = $service;
    }

    /**
     * Gets the raw value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->getEnum()->getValue();
    }

    /**
     * Gets the human representation of the value.
     *
     * @return string
     */
    public function getReadable()
    {
        return $this->getEnum()->getReadable();
    }

    /**
     * Determines whether enums are equals.
     *
     * @param EnumInterface $enum An enum object to compare with this instance
     *
     * @return bool True if $enum is an enum with the same type and value as this instance; otherwise, false
     */
    public function equals(EnumInterface $enum)
    {
        return $this->getEnum()->equals($enum);
    }

    /**
     * @return BaseOfferServiceStatusEnum
     */
    private function getEnum()
    {
        if (null !== $this->enum) {
            return $this->enum;
        }

        $offer = $this->offer;
        $service = $this->service;
        $until = $offer->getOfferServiceUntil($service);

        $now = new \DateTime();
        if (null !== $until && $until > $now) {
            $this->enum = BaseOfferServiceStatusEnum::create(BaseOfferServiceStatusEnum::ACTIVE);

            return $this->enum;
        }

        $cartItem = $this->cartManager->getByReference($service, $offer);

        $this->enum = BaseOfferServiceStatusEnum::create(
            (null !== $cartItem)
                ? BaseOfferServiceStatusEnum::IN_CART
                : BaseOfferServiceStatusEnum::INACTIVE
        );

        return $this->enum;
    }
}
