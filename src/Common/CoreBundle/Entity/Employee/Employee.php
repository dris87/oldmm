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

namespace Common\CoreBundle\Entity\Employee;

use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Entity\Dictionary\DicNationality;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Class Employee.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\User\UserRepository")
 * @Vich\Uploadable
 */
class Employee extends User implements InitializeModelInterface
{
    const ROLES = ['ROLE_EMPLOYEE'];

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $locationId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     * @Assert\LessThan("today")
     * @Assert\NotBlank
     * @Assert\Date
     */
    private $birthDate;

    /**
     * @var DicLocation
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicLocation")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false, )
     * @Assert\NotBlank
     */
    private $location;

    /**
     * @var ArrayCollection|DicNationality[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Common\CoreBundle\Entity\Dictionary\DicNationality",
     *     cascade={"persist"},
     * )
     * @ORM\JoinTable(name="employee_dic_nationality_relation")
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.employee.cv.nationality.count",
     * )
     */
    private $nationality;

    /**
     * @var ArrayCollection|EmployeeCv[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Employee\Cv\EmployeeCv",
     *     mappedBy="employee",
     *     cascade={"persist", "remove"}
     * )
     */
    private $cv;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/*",  })
     */
    private $picture;

    /**
     * @Vich\UploadableField(mapping="employee_picture", fileNameProperty="pictureName")
     *
     * @var File
     */
    private $pictureFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $pictureName;

    /**
     * @var EmployeeCoverLetter
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Employee\EmployeeCoverLetter",
     *     mappedBy="employee",
     *     cascade={"persist", "remove"}
     * )
     */
    private $coverLetters;

    /**
     * Employee constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->nationality = new ArrayCollection();
        $this->cv = new ArrayCollection();
        $this->coverLetters = new ArrayCollection();

        $this->initializeModel();
    }

    public function __toString()
    {
        if ($this->id) {
            return $this->getFullName();
        }

        return '';
    }

    public function initializeModel()
    {
        $this->coverLetters = new ValueSetterCollection($this->coverLetters, [
            'employee' => $this,
        ]);
        $this->cv = new ValueSetterCollection($this->cv, [
            'employee' => $this,
        ]);
    }

    /**
     * Set locationId.
     *
     * @param int $locationId
     *
     * @return Employee
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;

        return $this;
    }

    /**
     * Get locationId.
     *
     * @return int
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Set birthDate.
     *
     * @param \DateTime $birthDate
     *
     * @return Employee
     */
    public function setBirthDate(?\DateTime $birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate.
     *
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set location.
     *
     * @param DicLocation $location
     *
     * @return Employee
     */
    public function setLocation(DicLocation $location)
    {
        $this->location = $location;
        $this->locationId = $location->getId();

        return $this;
    }

    /**
     * Get location.
     *
     * @return \Common\CoreBundle\Entity\Dictionary\DicLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Add nationality.
     *
     * @param \Common\CoreBundle\Entity\Dictionary\DicNationality $nationality
     *
     * @return Employee
     */
    public function addNationality(DicNationality $nationality)
    {
        $this->nationality[] = $nationality;

        return $this;
    }

    /**
     * Remove nationality.
     *
     * @param DicNationality $nationality
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeNationality(DicNationality $nationality)
    {
        return $this->nationality->removeElement($nationality);
    }

    /**
     * Get nationality.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param EmployeeCv $cv
     *
     * @return $this
     */
    public function addCv(EmployeeCv $cv)
    {
        $this->cv[] = $cv;

        return $this;
    }

    /**
     * @param EmployeeCv $cv
     *
     * @return bool
     */
    public function removeCv(EmployeeCv $cv)
    {
        return $this->cv->removeElement($cv);
    }

    /**
     * @return ArrayCollection|EmployeeCv[]
     */
    public function getCv()
    {
        return $this->cv;
    }

    /**
     * @return int
     */
    public function getCvCount()
    {
        return $this->cv->count();
    }

    /**
     * @param null $picture
     *
     * @return $this
     */
    public function setPicture($picture = null)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param File|null $image
     *
     * @throws \Exception
     */
    public function setPictureFile(?File $image = null): void
    {
        $this->pictureFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return File|null
     */
    public function getPictureFile(): ?File
    {
        return $this->pictureFile;
    }

    /**
     * @param string|null $pictureName
     */
    public function setPictureName(?string $pictureName): void
    {
        $this->pictureName = $pictureName;
    }

    /**
     * @return string|null
     */
    public function getPictureName(): ?string
    {
        return $this->pictureName;
    }

    /**
     * Add coverLetter.
     *
     * @param EmployeeCoverLetter $coverLetter
     *
     * @return Employee
     */
    public function addCoverLetter(EmployeeCoverLetter $coverLetter)
    {
        $this->coverLetters[] = $coverLetter;

        return $this;
    }

    /**
     * Remove coverLetter.
     *
     * @param EmployeeCoverLetter $coverLetter
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeCoverLetter(EmployeeCoverLetter $coverLetter)
    {
        return $this->coverLetters->removeElement($coverLetter);
    }

    /**
     * Get coverLetters.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCoverLetters()
    {
        return $this->coverLetters;
    }
}
