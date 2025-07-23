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

namespace Common\CoreBundle\Entity\Employee\Cv;

use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\DicEducationLevel;
use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Entity\Dictionary\DicSchool;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmployeeCvEducation.
 *
 * @ORM\Entity
 */
class EmployeeCvEducation
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
    private $employeeCvId;

    /**
     * @var EmployeeCv
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Employee\Cv\EmployeeCv",
     *     inversedBy="educations"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $employeeCv;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $categoryId;

    /**
     * @var DicCategory
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicCategory")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     * @Assert\NotBlank
     */
    private $category;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $schoolId;

    /**
     * @var DicSchool
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicSchool")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     * @Assert\NotBlank
     */
    private $school;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $educationLevelId;

    /**
     * @var DicEducationLevel
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicEducationLevel")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     * @Assert\NotBlank
     */
    private $educationLevel;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $locationId;

    /**
     * @var DicLocation
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicLocation")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     * @Assert\NotBlank
     */
    private $location;

    /**
     * @var int
     *
     * @ORM\Column(type="date")
     * @Assert\NotBlank
     * @Assert\Date
     */
    private $fromDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotBlank(groups={"notInProgress"})
     * @Assert\Date
     * @Assert\GreaterThan(propertyPath="fromDate", groups={"notInProgress"})
     */
    private $toDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $inProgress = false;

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
     * @param $categoryId
     *
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param DicCategory $category
     *
     * @return $this
     */
    public function setCategory(DicCategory $category)
    {
        $this->category = $category;
        $this->categoryId = $category->getId();

        return $this;
    }

    /**
     * @return DicCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param $schoolId
     *
     * @return $this
     */
    public function setSchoolId($schoolId)
    {
        $this->schoolId = $schoolId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSchoolId()
    {
        return $this->schoolId;
    }

    /**
     * @param DicSchool $school
     *
     * @return $this
     */
    public function setSchool(DicSchool $school)
    {
        $this->school = $school;
        $this->schoolId = $school->getId();

        return $this;
    }

    /**
     * @return DicSchool
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * @param $educationLevelId
     *
     * @return $this
     */
    public function setEducationLevelId($educationLevelId)
    {
        $this->educationLevelId = $educationLevelId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEducationLevelId()
    {
        return $this->educationLevelId;
    }

    /**
     * @param DicEducationLevel $educationLevel
     *
     * @return $this
     */
    public function setEducationLevel(DicEducationLevel $educationLevel)
    {
        $this->educationLevel = $educationLevel;
        $this->educationLevelId = $educationLevel->getId();

        return $this;
    }

    /**
     * @return DicEducationLevel
     */
    public function getEducationLevel()
    {
        return $this->educationLevel;
    }

    /**
     * @param $locationId
     *
     * @return $this
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param DicLocation $location
     *
     * @return $this
     */
    public function setLocation(DicLocation $location)
    {
        $this->location = $location;
        $this->locationId = $location->getId();

        return $this;
    }

    /**
     * @return DicLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param \DateTime|null $fromDate
     *
     * @return $this
     */
    public function setFromDate(?\DateTime $fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getFromDate()
    {
        if ($this->fromDate > $this->toDate && !empty($this->toDate)) {
            $toDate = $this->toDate;
            $this->toDate = $this->fromDate;
            $this->fromDate = $toDate;
        }

        return $this->fromDate;
    }

    /**
     * @param \DateTime|null $toDate
     *
     * @return $this
     */
    public function setToDate(?\DateTime $toDate)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getToDate()
    {
        if ($this->fromDate > $this->toDate && !empty($this->toDate)) {
            $toDate = $this->toDate;
            $this->toDate = $this->fromDate;
            $this->fromDate = $toDate;
        }

        return $this->toDate;
    }

    /**
     * @param $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param $inProgress
     *
     * @return $this
     */
    public function setInProgress($inProgress)
    {
        $this->inProgress = $inProgress;

        return $this;
    }

    /**
     * @return bool
     */
    public function getInProgress()
    {
        $inProgress = false;
        if (null === $this->getToDate()) {
            $inProgress = true;
        }
        $this->inProgress = $inProgress;

        return $this->inProgress;
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
