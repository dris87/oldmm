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

use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class FirmBalanceItem.
 *
 * @ORM\Entity
 */
class FirmBalanceItem
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
    private $balanceId;

    /**
     * @var FirmPackageServiceEnum
     *
     * @ORM\Column(type="firm_package_service_enum")
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $credit;

    /**
     * @var
     */
    private $availableExtraCredit;

    /**
     * @var FirmBalance
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Balance\FirmBalance",
     *     inversedBy="items",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $balance;

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
     * @param $balanceId
     *
     * @return $this
     */
    public function setBalanceId($balanceId)
    {
        $this->balanceId = $balanceId;

        return $this;
    }

    /**
     * @return int
     */
    public function getBalanceId()
    {
        return $this->balanceId;
    }

    /**
     * @param FirmPackageServiceEnum $type
     *
     * @return $this
     */
    public function setType(FirmPackageServiceEnum $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return FirmPackageServiceEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $credit
     *
     * @return $this
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * @return int
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param int $amount
     *
     * @return $this
     */
    public function decrementCredit(int $amount = 1)
    {
        $this->setCredit($this->getCredit() - abs($amount));

        return $this;
    }

    /**
     * @param $availableExtraCredit
     *
     * @return $this
     */
    public function setAvailableExtraCredit($availableExtraCredit)
    {
        $this->availableExtraCredit = $availableExtraCredit;

        return $this;
    }

    /**
     * @return int
     */
    public function getAvailableExtraCredit()
    {
        return $this->availableExtraCredit;
    }

    /**
     * @param FirmBalance $balance
     *
     * @return $this
     */
    public function setBalance(FirmBalance $balance)
    {
        $this->balance = $balance;
        $this->balanceId = $balance->getId();

        return $this;
    }

    /**
     * @return FirmBalance
     */
    public function getBalance()
    {
        return $this->balance;
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
