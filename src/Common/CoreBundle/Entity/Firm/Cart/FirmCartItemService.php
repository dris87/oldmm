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

use Common\CoreBundle\Entity\Firm\Package\FirmPackageService;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class FirmCartItemService.
 *
 * @ORM\Entity
 */
class FirmCartItemService
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
    private $cartItemId;

    /**
     * @var FirmCartItem
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Cart\FirmCartItem",
     *     inversedBy="cartItemServices",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $cartItem;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $serviceId;

    /**
     * @var FirmPackageService
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Firm\Package\FirmPackageService")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $service;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $referenceId;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $cartItemId
     *
     * @return $this
     */
    public function setCartItemId($cartItemId)
    {
        $this->cartItemId = $cartItemId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCartItemId()
    {
        return $this->cartItemId;
    }

    /**
     * @param FirmCartItem $cartItem
     *
     * @return $this
     */
    public function setCartItem(FirmCartItem $cartItem)
    {
        $this->cartItem = $cartItem;
        $this->cartItemId = $cartItem->getId();

        return $this;
    }

    /**
     * @return FirmCartItem
     */
    public function getCartItem()
    {
        return $this->cartItem;
    }

    /**
     * @param $serviceId
     *
     * @return $this
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param FirmPackageService $service
     *
     * @return $this
     */
    public function setService(FirmPackageService $service)
    {
        $this->service = $service;
        $this->serviceId = $service->getId();

        return $this;
    }

    /**
     * @return FirmPackageService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param $referenceId
     *
     * @return $this
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * @return int
     */
    public function getReferenceId()
    {
        return $this->referenceId;
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
