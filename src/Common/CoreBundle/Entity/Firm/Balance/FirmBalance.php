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

namespace Common\CoreBundle\Entity\Firm\Balance;

use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;

/**
 * Class FirmBalance.
 *
 * @ORM\Entity
 */
class FirmBalance implements InitializeModelInterface
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
     *     inversedBy="balances"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $firm;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $sign;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $expiredAt;

    /**
     * @var Collection|FirmBalanceItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Balance\FirmBalanceItem",
     *     mappedBy="balance",
     * )
     */
    private $items;

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
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();

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
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null $sign
     *
     * @return $this
     */
    public function setSign($sign = null)
    {
        $this->sign = $sign;

        return $this;
    }

    /**
     * @return string
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @param \DateTime $expiredAt
     *
     * @return $this
     */
    public function setExpiredAt(\DateTime $expiredAt)
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    /**
     * @param FirmBalanceItem $item
     *
     * @return $this
     */
    public function addItem(FirmBalanceItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param FirmBalanceItem $item
     *
     * @return bool
     */
    public function removeItem(FirmBalanceItem $item)
    {
        return $this->items->removeElement($item);
    }

    /**
     * Get items.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param FirmPackageServiceEnum $type
     *
     * @return FirmBalanceItem|mixed|null
     */
    public function getItemByType(FirmPackageServiceEnum $type)
    {
        foreach ($this->items as $item) {
            if ($item->getType()->equals($type)) {
                return $item;
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

    public function initializeModel()
    {
        $this->items = new ValueSetterCollection($this->items, [
            'balance' => $this,
        ]);
    }

    /**
     * @param FirmPackageServiceEnum $serviceEnum
     *
     * @return int
     */
    public function getServiceCredit(FirmPackageServiceEnum $serviceEnum)
    {
        $result = 0;

        foreach ($this->items as $item) {
            if ($item->getType()->equals($serviceEnum)) {
                $result += $item->getCredit();
            }
        }

        return $result;
    }
}
