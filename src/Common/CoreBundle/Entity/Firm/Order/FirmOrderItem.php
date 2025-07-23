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

use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;
use Spirit\SpiritModelBundle\Model\SpiritContainerModelInterface;

/**
 * Class FirmOrderItem.
 *
 * @ORM\Entity
 */
class FirmOrderItem implements InitializeModelInterface, SpiritContainerModelInterface
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
    private $count;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $orderId;


    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $price_net;

    /**
     * @var FirmOrder
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Order\FirmOrder",
     *     inversedBy="items",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     */
    private $order;

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
     * )
     */
    private $package;

    /**
     * @var Collection|FirmOrderItemService[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Order\FirmOrderItemService",
     *     mappedBy="orderItem",
     * )
     */
    private $orderItemServices;

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
        $this->orderItemServices = new \Doctrine\Common\Collections\ArrayCollection();

        $this->initializeModel();
    }

    /**
     * @return int
     */
    public function getId():? int
    {
        return $this->id;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getSerial()
    {
        return '1'.str_pad(''.$this->id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @param $count
     *
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param $orderId
     *
     * @return $this
     */
    public function setPriceNet($price_net)
    {
        $this->price_net = $price_net;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriceNet()
    {
        return $this->price_net;
    }

    /**
     * @param FirmOrder $order
     *
     * @return $this
     */
    public function setOrder(FirmOrder $order)
    {
        $this->order = $order;
        $this->orderId = $order->getId();

        return $this;
    }

    /**
     * @return FirmOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set packageId.
     *
     * @param int $packageId
     *
     * @return FirmOrderItem
     */
    public function setPackageId($packageId)
    {
        $this->packageId = $packageId;

        return $this;
    }

    /**
     * Get packageId.
     *
     * @return int
     */
    public function getPackageId()
    {
        return $this->packageId;
    }

    /**
     * @param FirmPackage $package
     *
     * @return $this
     */
    public function setPackage(FirmPackage $package)
    {
        $this->package = $package;
        $this->packageId = $package->getId();

        return $this;
    }

    /**
     * @return FirmPackage
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return int
     */
    public function getPriceUnitNet()
    {
        return $this->package->getPrice();
    }

    /**
     * @return int
     */
    public function getPriceUnitGross()
    {
        return (int) (round($this->getPriceUnitNet() * (100 + FirmOrder::VAT_VALUE) / 100));
    }

    /**
     * @param FirmOrderItemService $orderItemService
     *
     * @return $this
     */
    public function addOrderItemService(FirmOrderItemService $orderItemService)
    {
        $this->orderItemServices[] = $orderItemService;

        return $this;
    }

    /**
     * @param FirmPackageServiceEnum $serviceEnum
     *
     * @return FirmOrderItemService|mixed|null
     */
    public function getOrderItemServiceByServiceEnum(FirmPackageServiceEnum $serviceEnum)
    {
        foreach ($this->getOrderItemServices() as $service) {
            if ($service->getService()->getType()->equals($serviceEnum)) {
                return $service;
            }
        }

        return null;
    }

    /**
     * @param $serviceName
     *
     * @return FirmOrderItemService|null
     */
    public function getOrderItemServiceByServiceName($serviceName)
    {
        $serviceEnum = FirmPackageServiceEnum::createByReadable($serviceName);

        return $this->getOrderItemServiceByServiceEnum($serviceEnum);
    }

    /**
     * @param FirmOrderItemService $orderItemService
     *
     * @return bool
     */
    public function removeOrderItemService(FirmOrderItemService $orderItemService)
    {
        return $this->orderItemServices->removeElement($orderItemService);
    }

    /**
     * @return Collection|\Doctrine\Common\Collections\ArrayCollection|FirmOrderItemService[]
     */
    public function getOrderItemServices()
    {
        return $this->orderItemServices;
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

    public function initializeModel()
    {
        $this->orderItemServices = new ValueSetterCollection($this->orderItemServices, [
            'orderItem' => $this,
        ]);
    }
}
