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

use Cocur\Slugify\Slugify;
use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\DicCity;
use Common\CoreBundle\Entity\Dictionary\DicCompanyHelp;
use Common\CoreBundle\Entity\Dictionary\DicCounty;
use Common\CoreBundle\Entity\Dictionary\DicDrivingLicense;
use Common\CoreBundle\Entity\Dictionary\DicItExperience;
use Common\CoreBundle\Entity\Dictionary\DicJobForm;
use Common\CoreBundle\Entity\Dictionary\DicLanguage;
use Common\CoreBundle\Entity\Dictionary\DicLifeStyle;
use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Entity\Dictionary\DicMarketStatus;
use Common\CoreBundle\Entity\Dictionary\DicPersonalStrength;
use Common\CoreBundle\Entity\Dictionary\DicShift;
use Common\CoreBundle\Entity\Dictionary\DicSoftwareExperience;
use Common\CoreBundle\Entity\Dictionary\DicSupport;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Offer\OfferCandidate;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStatusEnum;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStyleEnum;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToMoveEnum;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToTravelEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\FilteredCollection;
use Spirit\ModelManagerBundle\Doctrine\Collection\ToManyCollection;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmployeeCv.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\Employee\Cv\EmployeeCvRepository")
 */
class EmployeeCv implements InitializeModelInterface
{
    const NUM_ITEMS = 9;

    /**
     * @var EmployeeCvWillToTravelEnum
     *
     * @ORM\Column(type="employee_cv_will_to_travel_enum",  options={"default" = 1})
     */
    protected $willToTravel;

    /**
     * @var EmployeeCvWillToMoveEnum
     *
     * @ORM\Column(
     *     type="employee_cv_will_to_move_enum",
     *     length=2,
     *     nullable=true
     * )
     */
    protected $willToMove;

    /**
     * @var EmployeeCvStyleEnum
     *
     * @ORM\Column(type="employee_cv_style_enum", options={"default" = 0})
     */
    protected $style;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $name = 'Önéletrajzom';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $employeeId;

    /**
     * @var Employee
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Employee\Employee",
     *     inversedBy="cv",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $employee;

    /**
     * @var ArrayCollection|EmployeeCvEducation[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Employee\Cv\EmployeeCvEducation",
     *     mappedBy="employeeCv",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"fromDate" = "DESC"})
     */
    private $educations;

    /**
     * @var ArrayCollection|EmployeeCvExperience[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Employee\Cv\EmployeeCvExperience",
     *     mappedBy="employeeCv",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"fromDate" = "DESC"})
     */
    private $experiences;

    /**
     * @var ArrayCollection|EmployeeCvDocument[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Employee\Cv\EmployeeCvDocument",
     *     mappedBy="employeeCv",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"name" = "DESC"})
     */
    private $documents;

    /**
     * @var ArrayCollection|DicLocation[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.employee.cv.will_to_move_location.count",
     *     groups={"willToMoveLocation"}
     * )
     */
    private $willToMoveLocations;

    /**
     * @var ArrayCollection|DicLocation[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.employee.cv.will_to_travel_location.count",
     *     groups={"willToTravelLocation"}
     * )
     */
    private $willToTravelLocations;

    /**
     * @var ArrayCollection|Dictionary[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Employee\Cv\EmployeeCvDictionaryRelation",
     *     mappedBy="employeeCv",
     *     orphanRemoval=true,
     *     cascade={"persist"},
     * )
     */
    private $dictionaryRelations;

    /**
     * @var ArrayCollection|OfferCandidate[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Offer\OfferCandidate",
     *     mappedBy="employeeCv"
     * )
     */
    private $candidates;

    /**
     * @var ArrayCollection|DicCompanyHelp[]
     */
    private $companyHelps;

    /**
     * @var ArrayCollection|DicDrivingLicense[]
     */
    private $drivingLicenses;

    /**
     * @var ArrayCollection|EmployeeCvDictionaryRelation[]
     */
    private $itExperiences;

    /**
     * @var ArrayCollection|EmployeeCvDictionaryRelation[]
     */
    private $softwareExperiences;

    /**
     * @var ArrayCollection|DicJobForm[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.employee.cv.jobform.count",
     * )
     */
    private $jobForms;

    /**
     * @var ArrayCollection|EmployeeCvDictionaryRelation[]
     */
    private $languages;

    /**
     * @var ArrayCollection|DicLifeStyle[]
     */
    private $lifeStyles;

    /**
     * @var ArrayCollection|DicMarketStatus[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.employee.cv.market_status.count",
     * )
     */
    private $marketStatuses;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank(groups={"willToTravelDistance"})
     * @Assert\Range(
     *     min=10,
     *     minMessage="validation.employee.cv.will_to_travel_distance.length",
     *     groups={"willToTravelDistance"}
     * )
     */
    private $willToTravelDistance;

    /**
     * @var ArrayCollection|DicPersonalStrength[]
     */
    private $personalStrengths;

    /**
     * @var ArrayCollection|DicCategory[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.employee.cv.category.count",
     * )
     */
    private $searchCategories;

    /**
     * @var ArrayCollection|DicShift[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.employee.cv.shifts.count",
     * )
     */
    private $shifts;

    /**
     * @var ArrayCollection|DicSupport[]
     */
    private $supports;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $cafeteria = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $salaryFrom;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(propertyPath="salaryFrom", groups={"extra"})
     */
    private $salaryTo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $hobby;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $jobComment;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $extraComment;

    /**
     * @var EmployeeCvStatusEnum
     *
     * @ORM\Column(type="employee_cv_status_enum", options={"default" = 10})
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=6)
     */
    private $locale = 'hu_HU';

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
     * EmployeeCv constructor.
     */
    public function __construct()
    {
        $this->status = EmployeeCvStatusEnum::create(EmployeeCvStatusEnum::ACTIVE);
        $this->style = EmployeeCvStyleEnum::create(EmployeeCvStyleEnum::UJALLAS_ORANGE);
        $this->educations = new ArrayCollection();
        $this->experiences = new ArrayCollection();
        $this->candidates = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->dictionaryRelations = new ArrayCollection();
        $this->willToTravel = EmployeeCvWillToTravelEnum::create(EmployeeCvWillToTravelEnum::BY_DISTANCE);

        $this->initializeModel();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getWithOwnerName();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param $employeeId
     *
     * @return $this
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * @param Employee $employee
     *
     * @return $this
     */
    public function setEmployee(Employee $employee)
    {
        $this->employee = $employee;
        $this->employeeId = $employee->getId();

        return $this;
    }

    /**
     * @return Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * @param EmployeeCvEducation $education
     *
     * @return $this
     */
    public function addEducation(EmployeeCvEducation $education)
    {
        $this->educations[] = $education;

        return $this;
    }

    /**
     * @param EmployeeCvEducation $education
     *
     * @return bool
     */
    public function removeEducation(EmployeeCvEducation $education)
    {
        return $this->educations->removeElement($education);
    }

    /**
     * @return ArrayCollection|EmployeeCvEducation[]
     */
    public function getEducations()
    {
        return $this->educations;
    }

    /**
     * @return int
     */
    public function getEducationsCount()
    {
        return $this->educations->count();
    }

    /**
     * @return array
     */
    public function getEducationsArray()
    {
        $array = [];

        foreach ($this->educations->getIterator() as $education) {
            /* @var EmployeeCvEducation $education */

            $array[$education->getId()] = [
                'id' => $education->getId(),
                'school' => [0 => $education->getSchool()->getId(), '_labels' => [0 => $education->getSchool()->getValue()]],
                'educationLevel' => [0 => $education->getEducationLevel()->getId(), '_labels' => [0 => $education->getEducationLevel()->getValue()]],
                'location' => [0 => $education->getLocation()->getId(), '_labels' => [0 => $education->getLocation()->getCity()->getValue()]],
                'category' => [0 => $education->getCategory()->getId(), '_labels' => [0 => $education->getCategory()->getValue()]],
                'fromDate' => $education->getFromDate()->format('Y-m'),
                'toDate' => (null !== $education->getToDate()) ? $education->getToDate()->format('Y-m') : '',
                'comment' => $education->getComment(),
                'inProgress' => $education->getInProgress(),
            ];
        }

        return $array;
    }

    /**
     * @param EmployeeCvExperience $experience
     *
     * @return $this
     */
    public function addExperience(EmployeeCvExperience $experience)
    {
        $this->experiences[] = $experience;

        return $this;
    }

    /**
     * @param EmployeeCvExperience $experience
     *
     * @return bool
     */
    public function removeExperience(EmployeeCvExperience $experience)
    {
        return $this->experiences->removeElement($experience);
    }

    /**
     * @return ArrayCollection|EmployeeCvExperience[]
     */
    public function getExperiences()
    {
        return $this->experiences;
    }

    /**
     * @return int
     */
    public function getExperienceCount()
    {
        return $this->experiences->count();
    }

    /**
     * @return array
     */
    public function getExperiencesArray()
    {
        $array = [];

        foreach ($this->experiences->getIterator() as $experience) {
            /* @var EmployeeCvExperience $experience */

            $array[$experience->getId()] = [
                'id' => $experience->getId(),
                'location' => [0 => $experience->getLocation()->getId(), '_labels' => [0 => $experience->getLocation()->getCity()->getValue()]],
                'experience' => [0 => $experience->getExperience()->getId(), '_labels' => [0 => $experience->getExperience()->getValue()]],
                'fromDate' => $experience->getFromDate()->format('Y-m'),
                'toDate' => (null !== $experience->getToDate()) ? $experience->getToDate()->format('Y-m') : '',
                'companyName' => $experience->getCompanyName(),
                'comment' => $experience->getComment(),
                'inProgress' => $experience->getInProgress(),
            ];
        }

        return $array;
    }

    /**
     * @param EmployeeCvDocument $document
     *
     * @return $this
     */
    public function addDocument(EmployeeCvDocument $document)
    {
        $this->documents[] = $document;

        return $this;
    }

    /**
     * @param EmployeeCvDocument $document
     *
     * @return bool
     */
    public function removeDocument(EmployeeCvDocument $document)
    {
        return $this->documents->removeElement($document);
    }

    /**
     * @return ArrayCollection|EmployeeCvDocument[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Dictionary $willToMoveLocation
     *
     * @return $this
     */
    public function addWillToMoveLocation(Dictionary $willToMoveLocation)
    {
        $this->willToMoveLocations[] = $willToMoveLocation;

        return $this;
    }

    /**
     * @param Dictionary $willToMoveLocation
     *
     * @return bool
     */
    public function removeWillToMoveLocation(Dictionary $willToMoveLocation)
    {
        return $this->willToMoveLocations->removeElement($willToMoveLocation);
    }

    public function clearWillToMoveLocations()
    {
        return $this->willToMoveLocations->clear();
    }


    /**
     * @return ArrayCollection|DicLocation[]
     */
    public function getWillToMoveLocations()
    {
        return $this->willToMoveLocations;
    }

    /**
     * @param Dictionary $willToTravelLocation
     *
     * @return $this
     */
    public function addWillToTravelLocation(Dictionary $willToTravelLocation)
    {
        $this->willToTravelLocations[] = $willToTravelLocation;

        return $this;
    }

    /**
     * @param Dictionary $willToTravelLocation
     *
     * @return bool
     */
    public function removeWillToTravelLocation(Dictionary $willToTravelLocation)
    {
        return $this->willToTravelLocations->removeElement($willToTravelLocation);
    }

    public function clearWillToTravelLocation()
    {
        return $this->willToTravelLocations->clear();
    }

    /**
     * @return ArrayCollection|DicLocation[]
     */
    public function getWillToTravelLocations()
    {
        return $this->willToTravelLocations;
    }

    /**
     * @param EmployeeCvWillToTravelEnum $willToTravel
     *
     * @return $this
     */
    public function setWillToTravel(EmployeeCvWillToTravelEnum $willToTravel)
    {
        $this->willToTravel = $willToTravel;

        return $this;
    }

    /**
     * @return EmployeeCvWillToTravelEnum|static
     */
    public function getWillToTravel()
    {
        return $this->willToTravel;
    }

    /**
     * @param EmployeeCvWillToMoveEnum|null $willToMove
     *
     * @return $this
     */
    public function setWillToMove(?EmployeeCvWillToMoveEnum $willToMove)
    {
        $this->willToMove = $willToMove;

        return $this;
    }

    /**
     * @return EmployeeCvWillToMoveEnum
     */
    public function getWillToMove()
    {
        return $this->willToMove;
    }

    /**
     * @param EmployeeCvDictionaryRelation $dictionaryRelation
     *
     * @return $this
     */
    public function addDictionaryRelation(EmployeeCvDictionaryRelation $dictionaryRelation)
    {
        $this->dictionaryRelations[] = $dictionaryRelation;

        return $this;
    }

    /**
     * @param EmployeeCvDictionaryRelation $dictionaryRelation
     *
     * @return bool
     */
    public function removeDictionaryRelation(EmployeeCvDictionaryRelation $dictionaryRelation)
    {
        return $this->dictionaryRelations->removeElement($dictionaryRelation);
    }

    /**
     * Get dictionaryRelations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDictionaryRelations()
    {
        return $this->dictionaryRelations;
    }

    /**
     * @param OfferCandidate $candidate
     *
     * @return $this
     */
    public function addCandidate(OfferCandidate $candidate)
    {
        $this->candidates[] = $candidate;

        return $this;
    }

    /**
     * @param OfferCandidate $candidate
     *
     * @return bool
     */
    public function removeCandidate(OfferCandidate $candidate)
    {
        return $this->candidates->removeElement($candidate);
    }

    /**
     * @return ArrayCollection|OfferCandidate[]
     */
    public function getCandidates()
    {
        return $this->candidates;
    }

    /**
     * @param Offer $offer
     *
     * @return mixed|OfferCandidate|null
     */
    public function getCandidateByOffer(Offer $offer)
    {
        foreach ($this->candidates as $candidate) {
            if ($candidate->getOfferId() === $offer->getId()) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param DicCompanyHelp $companyHelp
     *
     * @return $this
     */
    public function addCompanyHelp(DicCompanyHelp $companyHelp)
    {
        $this->companyHelps[] = $companyHelp;

        return $this;
    }

    /**
     * @param DicCompanyHelp $companyHelp
     *
     * @return bool
     */
    public function removeCompanyHelp(DicCompanyHelp $companyHelp)
    {
        return $this->companyHelps->removeElement($companyHelp);
    }

    /**
     * @return ArrayCollection|DicCompanyHelp[]
     */
    public function getCompanyHelps()
    {
        return $this->companyHelps;
    }

    /**
     * @param DicDrivingLicense $drivingLicense
     *
     * @return $this
     */
    public function addDrivingLicense(DicDrivingLicense $drivingLicense)
    {
        $this->drivingLicenses[] = $drivingLicense;

        return $this;
    }

    /**
     * @param DicDrivingLicense $drivingLicense
     *
     * @return bool
     */
    public function removeDrivingLicense(DicDrivingLicense $drivingLicense)
    {
        return $this->drivingLicenses->removeElement($drivingLicense);
    }

    /**
     * @return ArrayCollection|DicDrivingLicense[]
     */
    public function getDrivingLicenses()
    {
        return $this->drivingLicenses;
    }

    /**
     * @param DicItExperience $itExperience
     *
     * @return $this
     */
    public function addItExperience(DicItExperience $itExperience)
    {
        $this->itExperiences[] = $itExperience;

        return $this;
    }

    /**
     * @param DicItExperience $itExperience
     *
     * @return bool
     */
    public function removeItExperience(DicItExperience $itExperience)
    {
        return $this->itExperiences->removeElement($itExperience);
    }

    /**
     * @return ArrayCollection|EmployeeCvDictionaryRelation[]
     */
    public function getItExperiences()
    {
        return $this->itExperiences;
    }

    /**
     * @param DicSoftwareExperience $softwareExperience
     *
     * @return $this
     */
    public function addSoftwareExperience(DicSoftwareExperience $softwareExperience)
    {
        $this->softwareExperiences[] = $softwareExperience;

        return $this;
    }

    /**
     * @param DicSoftwareExperience $softwareExperience
     *
     * @return bool
     */
    public function removeSoftwareExperience(DicSoftwareExperience $softwareExperience)
    {
        return $this->softwareExperiences->removeElement($softwareExperience);
    }

    /**
     * @return ArrayCollection|EmployeeCvDictionaryRelation[]
     */
    public function getSoftwareExperiences()
    {
        return $this->softwareExperiences;
    }

    /**
     * @param DicJobForm $jobForm
     *
     * @return $this
     */
    public function addJobForm(DicJobForm $jobForm)
    {
        $this->jobForms[] = $jobForm;

        return $this;
    }

    /**
     * @param DicJobForm $jobForm
     *
     * @return bool
     */
    public function removeJobForm(DicJobForm $jobForm)
    {
        return $this->jobForms->removeElement($jobForm);
    }

    /**
     * @return ArrayCollection|DicJobForm[]
     */
    public function getJobForms()
    {
        return $this->jobForms;
    }

    /**
     * @return ArrayCollection|EmployeeCvDictionaryRelation[]
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param DicLifeStyle $lifeStyle
     *
     * @return $this
     */
    public function addLifeStyle(DicLifeStyle $lifeStyle)
    {
        $this->lifeStyles[] = $lifeStyle;

        return $this;
    }

    /**
     * @param DicLifeStyle $lifeStyle
     *
     * @return bool
     */
    public function removeLifeStyle(DicLifeStyle $lifeStyle)
    {
        return $this->lifeStyles->removeElement($lifeStyle);
    }

    /**
     * @return ArrayCollection|DicLifeStyle[]
     */
    public function getLifeStyles()
    {
        return $this->lifeStyles;
    }

    /**
     * @param DicMarketStatus $marketStatus
     *
     * @return $this
     */
    public function addMarketStatus(DicMarketStatus $marketStatus)
    {
        $this->marketStatuses[] = $marketStatus;

        return $this;
    }

    /**
     * @param DicMarketStatus $marketStatus
     *
     * @return bool
     */
    public function removeMarketStatus(DicMarketStatus $marketStatus)
    {
        return $this->marketStatuses->removeElement($marketStatus);
    }

    /**
     * @return ArrayCollection|DicMarketStatus[]
     */
    public function getMarketStatuses()
    {
        return $this->marketStatuses;
    }

    /**
     * Set willToTravelDistance.
     *
     * @param int|null $willToTravelDistance
     *
     * @return EmployeeCv
     */
    public function setWillToTravelDistance($willToTravelDistance = null)
    {
        $this->willToTravelDistance = $willToTravelDistance;

        return $this;
    }

    /**
     * Get willToTravelDistance.
     *
     * @return int|null
     */
    public function getWillToTravelDistance()
    {
        return $this->willToTravelDistance;
    }

    /**
     * @param DicPersonalStrength $personalStrength
     *
     * @return $this
     */
    public function addPersonalStrength(DicPersonalStrength $personalStrength)
    {
        $this->personalStrengths[] = $personalStrength;

        return $this;
    }

    /**
     * @param DicPersonalStrength $personalStrength
     *
     * @return bool
     */
    public function removePersonalStrength(DicPersonalStrength $personalStrength)
    {
        return $this->personalStrengths->removeElement($personalStrength);
    }

    /**
     * @return ArrayCollection|DicPersonalStrength[]
     */
    public function getPersonalStrengths()
    {
        return $this->personalStrengths;
    }

    /**
     * @return ArrayCollection|DicCategory[]
     */
    public function getSearchCategories()
    {
        return $this->searchCategories;
    }

    /**
     * @param DicCategory $searchCategory
     *
     * @return $this
     */
    public function addSearchCategory(DicCategory $searchCategory)
    {
        $this->searchCategories[] = $searchCategory;

        return $this;
    }

    /**
     * @param DicCategory $searchCategory
     *
     * @return bool
     */
    public function removeSearchCategory(DicCategory $searchCategory)
    {
        return $this->searchCategories->removeElement($searchCategory);
    }

    /**
     * @param DicShift $shift
     *
     * @return $this
     */
    public function addShift(DicShift $shift)
    {
        $this->shifts[] = $shift;

        return $this;
    }

    /**
     * @param DicShift $shift
     *
     * @return bool
     */
    public function removeShift(DicShift $shift)
    {
        return $this->shifts->removeElement($shift);
    }

    /**
     * @return ArrayCollection|DicShift[]
     */
    public function getShifts()
    {
        return $this->shifts;
    }

    /**
     * @return ArrayCollection|DicSupport[]
     */
    public function getSupports()
    {
        return $this->supports;
    }

    /**
     * @param DicSupport $support
     *
     * @return $this
     */
    public function addSupport(DicSupport $support)
    {
        $this->supports[] = $support;

        return $this;
    }

    /**
     * @param DicSupport $support
     *
     * @return bool
     */
    public function removeSupport(DicSupport $support)
    {
        return $this->supports->removeElement($support);
    }

    /**
     * @param $cafeteria
     *
     * @return $this
     */
    public function setCafeteria($cafeteria)
    {
        $this->cafeteria = $cafeteria;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCafeteria()
    {
        return $this->cafeteria;
    }

    /**
     * @param null $salaryFrom
     *
     * @return $this
     */
    public function setSalaryFrom($salaryFrom = null)
    {
        $this->salaryFrom = $salaryFrom;

        return $this;
    }

    /**
     * @return int
     */
    public function getSalaryFrom()
    {
        return $this->salaryFrom;
    }

    /**
     * @param null $salaryTo
     *
     * @return $this
     */
    public function setSalaryTo($salaryTo = null)
    {
        $this->salaryTo = $salaryTo;

        return $this;
    }

    /**
     * @return int
     */
    public function getSalaryTo()
    {
        return $this->salaryTo;
    }

    /**
     * @param null $hobby
     *
     * @return $this
     */
    public function setHobby($hobby = null)
    {
        $this->hobby = $hobby;

        return $this;
    }

    /**
     * @return string
     */
    public function getHobby()
    {
        return $this->hobby;
    }

    /**
     * @param null $jobComment
     *
     * @return $this
     */
    public function setJobComment($jobComment = null)
    {
        $this->jobComment = $jobComment;

        return $this;
    }

    /**
     * @return string
     */
    public function getJobComment()
    {
        return $this->jobComment;
    }

    /**
     * @param string|null $extraComment
     *
     * @return $this
     */
    public function setExtraComment(?string $extraComment)
    {
        $this->extraComment = $extraComment;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraComment()
    {
        return $this->extraComment;
    }

    /**
     * @return EmployeeCvStyleEnum
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param EmployeeCvStyleEnum $style
     */
    public function setStyle(EmployeeCvStyleEnum $style): void
    {
        $this->style = $style;
    }

    /**
     * @param EmployeeCvStatusEnum $status
     *
     * @return $this
     */
    public function setStatus(EmployeeCvStatusEnum $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return EmployeeCvStatusEnum|static
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
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

    /**
     * @return string
     */
    public function getWithOwnerName()
    {
        return (string) $this->employee->getFullName().' - '.$this->id.'('.$this->name.')';
    }

    public function initializeModel()
    {
        $this->companyHelps = $this->retrieveSelectionCollection([DicCompanyHelp::class], 0);
        $this->drivingLicenses = $this->retrieveSelectionCollection([DicDrivingLicense::class], 0);
        $this->itExperiences = $this->retrieveDetailedCollection([DicItExperience::class], 0);
        $this->jobForms = $this->retrieveSelectionCollection([DicJobForm::class], 0);
        $this->languages = $this->retrieveDetailedCollection([DicLanguage::class], 0);
        $this->lifeStyles = $this->retrieveSelectionCollection([DicLifeStyle::class], 0);
        $this->marketStatuses = $this->retrieveSelectionCollection([DicMarketStatus::class], 0);
        $this->personalStrengths = $this->retrieveSelectionCollection([DicPersonalStrength::class], 0);
        $this->searchCategories = $this->retrieveSelectionCollection([DicCategory::class], 0);
        $this->shifts = $this->retrieveSelectionCollection([DicShift::class], 0);
        $this->softwareExperiences = $this->retrieveDetailedCollection([DicSoftwareExperience::class], 0);
        $this->supports = $this->retrieveSelectionCollection([DicSupport::class], 0);
        $this->willToMoveLocations = $this->retrieveSelectionCollection([
            DicCounty::class,
            DicCity::class,
        ], 0);
        $this->willToTravelLocations = $this->retrieveSelectionCollection([
            DicCounty::class,
            DicCity::class,
        ], 1);

        $this->educations = new ValueSetterCollection($this->educations, [
            'employeeCv' => $this,
        ]);
        $this->experiences = new ValueSetterCollection($this->experiences, [
            'employeeCv' => $this,
        ]);
        $this->documents = new ValueSetterCollection($this->documents, [
            'employeeCv' => $this,
        ]);
        $this->candidates = new ValueSetterCollection($this->candidates, [
            'employeeCv' => $this,
        ]);
    }

    /**
     * @param array $classes
     * @param $discriminator
     *
     * @return FilteredCollection|ToManyCollection|ValueSetterCollection
     */
    private function retrieveSelectionCollection(array $classes, $discriminator)
    {
        $collection = $this->retrieveDetailedCollection($classes, $discriminator);

        $collection = new ToManyCollection(
            $collection,
            $this,
            EmployeeCvDictionaryRelation::class,
            'employeeCv',
            'dictionary'
        );

        return $collection;
    }

    /**
     * @param array $classes
     * @param $discriminator
     *
     * @return FilteredCollection|ValueSetterCollection
     */
    private function retrieveDetailedCollection(array $classes, $discriminator)
    {
        $collection = new FilteredCollection($this->dictionaryRelations, function ($entity) use ($classes, $discriminator) {
            /* @var EmployeeCvDictionaryRelation $entity */
            if ($entity->getDiscriminator() !== $discriminator) {
                return false;
            }

            foreach ($classes as $class) {
                if (is_a($entity->getDictionary(), $class)) {
                    return true;
                }
            }

            return false;
        });

        $collection = new ValueSetterCollection($collection, [
            'employeeCv' => $this,
            'discriminator' => $discriminator,
        ]);

        return $collection;
    }

    /**
     * @return string
     */
    public function getGeneratedFileName($is_light = true){

        $now = new \DateTime();
        $file_name_start = (!$is_light) ? $this->getEmployee()->getFullName() : '';

        $file_name_end = '-'.$this->getEmployee()->getLocation()->getCityName(). '-' . $now->format('Y-m-d h:i:s'). '-mumi-hu';
        $slug = $file_name_start.$file_name_end;

        $slugify = new Slugify();
        $slugify->activateRuleSet('hungarian');
        return $slugify->slugify($slug);
    }
}
