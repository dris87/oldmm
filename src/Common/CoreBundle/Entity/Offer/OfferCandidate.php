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

namespace Common\CoreBundle\Entity\Offer;

use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Enumeration\Offer\OfferCandidateStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class OfferCandidate.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\Offer\OfferCandidateRepository")
 */
class OfferCandidate
{
    /**
     * @var int
     */
    const NUM_ITEMS = 10;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $offerId;

    /**
     * @var Offer
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Offer\Offer",
     *     inversedBy="candidates"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true,
     *     onDelete="CASCADE",
     * )
     */
    private $offer;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $employeeCvId;

    /**
     * @var EmployeeCv
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Employee\Cv\EmployeeCv",
     *     inversedBy="candidates",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $employeeCv;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $direct = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $paired = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $moved = false;

    /**
     * @var OfferCandidateStatusEnum
     *
     * @ORM\Column(type="offer_candidate_status_enum", options={"default" = 0})
     */
    private $status;

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

    public function __toString()
    {
        return $this->offer->getTitle().' - '.$this->getEmployee()->getFullName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $offerId
     *
     * @return $this
     */
    public function setOfferId($offerId)
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * @param Offer $offer
     *
     * @return $this
     */
    public function setOffer(Offer $offer)
    {
        $this->offer = $offer;
        $this->offerId = $offer->getId();

        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param $employeeCvId
     *
     * @return $this
     */
    public function setEmployeeCvId($employeeCvId)
    {
        $this->employeeCvId = $employeeCvId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmployeeCvId()
    {
        return $this->employeeCvId;
    }

    /**
     * @param EmployeeCv $employeeCv
     *
     * @return $this
     */
    public function setEmployeeCv(EmployeeCv $employeeCv)
    {
        $this->employeeCv = $employeeCv;
        $this->employeeCvId = $employeeCv->getId();

        return $this;
    }

    /**
     * @return EmployeeCv
     */
    public function getEmployeeCv()
    {
        return $this->employeeCv;
    }

    /**
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->getEmployeeCv()->getEmployeeId();
    }

    /**
     * @return Employee
     */
    public function getEmployee()
    {
        return $this->getEmployeeCv()->getEmployee();
    }

    /**
     * @return bool
     */
    public function isDirect(): bool
    {
        return $this->direct;
    }

    /**
     * @param bool $direct
     */
    public function setDirect(bool $direct): void
    {
        $this->direct = $direct;
    }

    /**
     * @return bool
     */
    public function isPaired(): bool
    {
        return $this->paired;
    }

    /**
     * @param bool $paired
     */
    public function setPaired(bool $paired): void
    {
        $this->paired = $paired;
    }

    /**
     * @return bool
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * @param bool $moved
     */
    public function setMoved(bool $moved): void
    {
        $this->moved = $moved;
    }

    /**
     * @return OfferCandidateStatusEnum
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param OfferCandidateStatusEnum $status
     */
    public function setStatus(OfferCandidateStatusEnum $status)
    {
        $this->status = $status;
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
