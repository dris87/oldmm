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

namespace Common\CoreBundle\Presentation;

use Common\CoreBundle\Entity\Firm\Cart\FirmCartItem;
use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class CartItem.
 */
class CartItem
{
    /**
     * @var FirmCartItem
     */
    private $cartItem;

    /**
     * @var ArrayCollection|CartItemService[]
     */
    private $services;

    /**
     * CartItem constructor.
     *
     * @param FirmCartItem $cartItem
     */
    public function __construct(FirmCartItem $cartItem)
    {
        $this->cartItem = $cartItem;
        $this->services = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->cartItem->getId();
    }

    /**
     * @return FirmCartItem
     */
    public function getCartItem()
    {
        return $this->cartItem;
    }

    /**
     * @return FirmPackage
     */
    public function getPackage(): FirmPackage
    {
        return $this->cartItem->getPackage();
    }

    /**
     * @param FirmPackage $package
     *
     * @return CartItem
     */
    public function setPackage(FirmPackage $package): self
    {
        $this->cartItem->setPackage($package);

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->cartItem->getQuantity();
    }

    /**
     * @param int $quantity
     *
     * @return CartItem
     */
    public function setQuantity(int $quantity): self
    {
        $this->cartItem->setQuantity($quantity);

        return $this;
    }

    /**
     * @param int $quantity
     *
     * @return CartItem
     */
    public function addQuantity(int $quantity): self
    {
        $fullQuantity = $this->getQuantity();

        return $this->setQuantity($fullQuantity + $quantity);
    }

    /**
     * @param int $quantity
     *
     * @return CartItem
     */
    public function subQuantity(int $quantity): self
    {
        $fullQuantity = $this->getQuantity();

        if ($quantity > $fullQuantity) {
            $quantity = $fullQuantity;
        }

        return $this->setQuantity($fullQuantity - $quantity);
    }

    /**
     * @return ArrayCollection|CartItemService[]
     */
    public function getServices(): ArrayCollection
    {
        return $this->services;
    }

    /**
     * @param FirmPackage $package
     * @param null        $reference
     *
     * @return bool
     */
    public function isPackageMatched(FirmPackage $package, $reference = null)
    {
        if ($package->getId() !== $this->getPackage()->getId()) {
            return false;
        }

        if (null === $reference) {
            return true;
        }

        $referenceId = $reference->getId();
        foreach ($this->services as $item) {
            $type = $item->getType();

            if (!$type->hasReference() && null !== $item->getReference()) {
                return false;
            }

            if ($type->hasReference() && $item->getReference()->getId() !== $referenceId) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isStandardPackage()
    {
        if (1 !== $this->getServices()->count()) {
            return true;
        }

        /* @var CartItemService $cartService */
        $cartService = $this->getServices()->first();

        return !($cartService->getType()->hasReference());
    }

    /**
     * @param FirmPackageServiceEnum|null $service
     * @param null                        $reference
     *
     * @return bool
     */
    public function isReferencePackage(FirmPackageServiceEnum $service = null, $reference = null)
    {
        if ($this->isStandardPackage()) {
            return false;
        }

        /* @var CartItemService $cartService */
        $cartService = $this->getServices()->first();

        if ((null !== $service) && ($cartService->getType()->getValue() !== $service->getValue())) {
            return false;
        }

        if ((null !== $reference) && ($cartService->getReference()->getId() !== $reference->getId())) {
            return false;
        }

        return true;
    }
}
