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

namespace Common\CoreBundle\Entity\Firm\Cart;

use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Common\CoreBundle\Entity\Firm\Package\FirmPackageService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class FirmCartItem.
 *
 * @ORM\Entity
 */
class FirmCartItem
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $firmId;

    /**
     * @var Firm
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Firm",
     *     inversedBy="cartItems",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $firm;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $packageId;

    /**
     * @var FirmPackage
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Firm\Package\FirmPackage")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $package;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @var Collection|FirmCartItemService[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Cart\FirmCartItemService",
     *     mappedBy="cartItem"
     * )
     */
    private $cartItemServices;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cartItemServices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $firmId
     *
     * @return $this
     */
    public function setFirmId($firmId)
    {
        $this->firmId = $firmId;

        return $this;
    }

    /**
     * @return int
     */
    public function getFirmId()
    {
        return $this->firmId;
    }

    /**
     * @param Firm $firm
     *
     * @return $this
     */
    public function setFirm(Firm $firm)
    {
        $this->firm = $firm;
        $this->firmId = $firm->getId();

        return $this;
    }

    /**
     * @return Firm
     */
    public function getFirm()
    {
        return $this->firm;
    }

    /**
     * @param $packageId
     *
     * @return $this
     */
    public function setPackageId($packageId)
    {
        $this->packageId = $packageId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPackageId()
    {
        return $this->packageId;
    }

    /**
     * Set package.
     *
     * @param FirmPackage $package
     *
     * @return FirmCartItem
     */
    public function setPackage(FirmPackage $package)
    {
        $this->package = $package;
        $this->packageId = $package->getId();

        return $this;
    }

    /**
     * Get package.
     *
     * @return FirmPackage
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param FirmCartItemService $cartItemService
     *
     * @return $this
     */
    public function addCartItemService(FirmCartItemService $cartItemService)
    {
        $this->cartItemServices[] = $cartItemService;

        return $this;
    }

    /**
     * @param FirmCartItemService $cartItemService
     *
     * @return bool
     */
    public function removeCartItemService(FirmCartItemService $cartItemService)
    {
        return $this->cartItemServices->removeElement($cartItemService);
    }

    /**
     * @return Collection|\Doctrine\Common\Collections\ArrayCollection|FirmCartItemService[]
     */
    public function getCartItemServices()
    {
        return $this->cartItemServices;
    }

    /**
     * @param FirmPackageService $service
     *
     * @return FirmCartItemService|mixed|null
     */
    public function getCartItemServiceByPackageService(FirmPackageService $service)
    {
        foreach ($this->getCartItemServices() as $cartItemService) {
            if ($cartItemService->getService()->getId() === $service->getId()) {
                return $cartItemService;
            }
        }

        return null;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
