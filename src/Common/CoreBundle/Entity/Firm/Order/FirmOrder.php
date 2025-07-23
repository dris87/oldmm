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

use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Enumeration\Firm\Order\FirmOrderStatusEnum;
use Common\CoreBundle\Enumeration\Firm\Payment\FirmPaymentMethodEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;
use Spirit\SpiritModelBundle\Model\SpiritContainerModelInterface;

/**
 * Class FirmOrder.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\Firm\Order\FirmOrderRepository")
 */
class FirmOrder implements InitializeModelInterface, SpiritContainerModelInterface
{
    /**
     * Default VAT rate in percent!
     * TODO: Move this to database.
     */
    const VAT_VALUE = 27;

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
     *     inversedBy="orders",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     */
    private $firm;

    /**
     * @var FirmOrderStatusEnum
     *
     * @ORM\Column(type="firm_order_status_enum", options={"default" = 0})
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $priceNet;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $priceGross;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $invoiceSerial;

    /**
     * @var FirmPaymentMethodEnum
     *
     * @ORM\Column(type="firm_payment_method_enum")
     */
    private $paymentMethod;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @var Collection|FirmOrderItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Order\FirmOrderItem",
     *     mappedBy="order",
     * )
     */
    private $items;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * FirmOrder constructor.
     */
    public function __construct()
    {
        $this->status = FirmOrderStatusEnum::create(FirmOrderStatusEnum::INIT);
        $this->items = new ArrayCollection();

        $this->initializeModel();
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
     * @param FirmOrderStatusEnum $status
     *
     * @return $this
     */
    public function setStatus(FirmOrderStatusEnum $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return FirmOrderStatusEnum|static
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $priceNet
     *
     * @return $this
     */
    public function setPriceNet($priceNet)
    {
        $this->priceNet = $priceNet;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriceNet()
    {
        return $this->priceNet;
    }

    /**
     * @param $priceGross
     *
     * @return $this
     */
    public function setPriceGross($priceGross)
    {
        $this->priceGross = $priceGross;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriceGross()
    {
        return $this->priceGross;
    }

    /**
     * @param null $invoiceSerial
     *
     * @return $this
     */
    public function setInvoiceSerial($invoiceSerial = null)
    {
        $this->invoiceSerial = $invoiceSerial;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceSerial()
    {
        return $this->invoiceSerial;
    }

    /**
     * @param FirmPaymentMethodEnum $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod(FirmPaymentMethodEnum $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return FirmPaymentMethodEnum
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param \DateTime|null $activatedAt
     *
     * @return $this
     */
    public function setActivatedAt(\DateTime $activatedAt = null)
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * @param FirmOrderItem $item
     *
     * @return $this
     */
    public function addItem(FirmOrderItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param FirmOrderItem $item
     *
     * @return bool
     */
    public function removeItem(FirmOrderItem $item)
    {
        return $this->items->removeElement($item);
    }

    /**
     * @return ArrayCollection|Collection|FirmOrderItem[]
     */
    public function getItems()
    {
        return $this->items;
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
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function initializeModel()
    {
        $this->items = new ValueSetterCollection($this->items, [
            'order' => $this,
        ]);
    }

    /**
     * @return string
     */
    public function getSerial()
    {
        return '1'.str_pad(''.$this->id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @return string
     */
    public function getInvoicePdfPath()
    {
        return '/pdf/invoice/'
            .implode('/', str_split(substr(sha1($this->invoiceSerial), 0, 16)))
            .'/'.$this->invoiceSerial.'.pdf'
        ;
    }
}
