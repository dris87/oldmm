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

namespace Common\CoreBundle\Entity\Firm\Order;

use Common\CoreBundle\Entity\Firm\Package\FirmPackageService;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\SpiritModelBundle\Model\SpiritContainerModelInterface;

/**
 * Class FirmOrderItemService.
 *
 * @ORM\Entity
 */
class FirmOrderItemService implements SpiritContainerModelInterface
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
    private $orderItemId;

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
     * )
     */
    private $service;

    /**
     * @var FirmOrderItem
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Order\FirmOrderItem",
     *     inversedBy="orderItemServices",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     */
    private $orderItem;

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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param $orderItemId
     *
     * @return $this
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
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
     * @param FirmOrderItem $orderItem
     *
     * @return $this
     */
    public function setOrderItem(FirmOrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
        $this->orderItemId = $orderItem->getId();

        return $this;
    }

    /**
     * @return FirmOrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * Set referenceId.
     *
     * @param int $referenceId
     *
     * @return FirmOrderItemService
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * Get referenceId.
     *
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
