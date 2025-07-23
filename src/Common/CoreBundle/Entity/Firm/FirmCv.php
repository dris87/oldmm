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

namespace Common\CoreBundle\Entity\Firm;

use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\Offer\OfferCandidate;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class FirmCv.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\Firm\FirmCvRepository")
 */
class FirmCv
{
    /**
     * Number of items to list by default.
     */
    const NUM_ITEMS = 9;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var
     */
    private $firmId;

    /**
     * @var Firm
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Firm",
     *     inversedBy="cvs"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id"
     * )
     */
    private $firm;

    /**
     * @var OfferCandidate
     *
     * @ORM\OneToOne(targetEntity="Common\CoreBundle\Entity\Offer\OfferCandidate")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $offerCandidate;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $employeeCvId;

    /**
     * @var EmployeeCv
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Employee\Cv\EmployeeCv")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $employeeCv;

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
     * @return OfferCandidate
     */
    public function getOfferCandidate()
    {
        return $this->offerCandidate;
    }

    /**
     * @param OfferCandidate $offerCandidate
     */
    public function setOfferCandidate(OfferCandidate $offerCandidate): void
    {
        $this->offerCandidate = $offerCandidate;
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
     * Get employeeId.
     *
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->getEmployeeCv()->getEmployeeId();
    }

    /**
     * Get employee.
     *
     * @return \Common\CoreBundle\Entity\Employee\Employee
     */
    public function getEmployee()
    {
        return $this->getEmployeeCv()->getEmployee();
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
