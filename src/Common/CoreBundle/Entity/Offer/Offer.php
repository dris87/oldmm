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

use Cocur\Slugify\Slugify;
use Common\CoreBundle\Doctrine\Proxy\OfferServiceStatusEnum as OfferServiceStatusEnumProxy;
use Common\CoreBundle\Entity\Dictionary\DicAdvantage;
use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\DicCity;
use Common\CoreBundle\Entity\Dictionary\DicCompanyHelp;
use Common\CoreBundle\Entity\Dictionary\DicCounty;
use Common\CoreBundle\Entity\Dictionary\DicDetail;
use Common\CoreBundle\Entity\Dictionary\DicDrivingLicense;
use Common\CoreBundle\Entity\Dictionary\DicEducationLevel;
use Common\CoreBundle\Entity\Dictionary\DicExpectation;
use Common\CoreBundle\Entity\Dictionary\DicExperience;
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
use Common\CoreBundle\Entity\Dictionary\DicTask;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Enumeration\Offer\OfferCandidateStatusEnum;
use Common\CoreBundle\Enumeration\Offer\OfferServiceStatusEnum;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Common\CoreBundle\Presentation\DateTimeRange;
use Common\CoreBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\FilteredCollection;
use Spirit\ModelManagerBundle\Doctrine\Collection\ToManyCollection;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\Offer\OfferRepository")
 * @ORM\Table(name="offer")
 */
class Offer implements InitializeModelInterface
{
    /**
     * @var int
     */
    const NUM_ITEMS = 10;

    /**
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
     *     inversedBy="offers",
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
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"submit", "save"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\NotBlank(groups={"submit"})
     */
    private $lead;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $anonim;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $minimal_package;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $minimal_without_cv;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"submit", "save"})
     */
    private $minimal_email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"submit", "save"})
     */
    private $minimal_title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"submit", "save"})
     */
    private $minimal_city;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"submit", "save"})
     */
    private $minimal_url;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     * @Assert\DateTime(format="Y-m-d")
     * @Assert\NotBlank(groups={"submit", "save"})
     */
    private $applicableFromDate;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true, options={"default" = 0})
     * @Assert\NotBlank(groups={"submit"})
     * @Assert\Range(
     *     min="1",
     *     max="127",
     *     groups={"submit"}
     * )
     */
    private $numberOfEmployee;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     * @Assert\DateTime(format="Y-m-d")
     * @Assert\NotBlank(groups={"submit"})
     */
    private $expireDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $slug;

    /**
     * @var OfferStatusEnum
     *
     * @ORM\Column(type="offer_status_enum", options={"default" = 0})
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $locale;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $offerExaltationUntil;

    /**
     * @var |DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $advanceFilterUntil;

    /**
     * @var ArrayCollection|DicLocation[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.offer.manage.location.count",
     *     groups={"submit"}
     * )
     */
    private $locations;

    /**
     * @var ArrayCollection|Dictionary[]
    */
    private $workLocations;

    /**
     * @var ArrayCollection|OfferDictionaryRelation[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Offer\OfferDictionaryRelation",
     *     mappedBy="offer",
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
     *     mappedBy="offer"
     * )
     */
    private $candidates;

    /**
     * @var ArrayCollection|OfferDictionaryRelation[]
     * @Assert\Valid(groups={"submit", "save"})
     */
    private $tasks;

    /**
     * @var ArrayCollection|DicCompanyHelp[]
     */
    private $companyHelps;

    /**
     * @var ArrayCollection|DicDrivingLicense[]
     */
    private $drivingLicenses;

    /**
     * @var ArrayCollection|OfferDictionaryRelation[]
     * @Assert\Valid(groups={"submit"})
     */
    private $educations;

    /**
     * @var ArrayCollection|OfferDictionaryRelation[]
     * @Assert\Valid(groups={"submit"})
     */
    private $experiences;

    /**
     * @var ArrayCollection|OfferDictionaryRelation[]
     * @Assert\Valid(groups={"submit"})
     */
    private $itExperiences;

    /**
     * @var ArrayCollection|DicCategory[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.offer.manage.categories.count",
     *     groups={"submit"}
     * )
     */
    private $categories;

    /**
     * @var ArrayCollection|DicJobForm[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.offer.manage.job_form.count",
     *     groups={"submit"}
     * )
     */
    private $jobForms;

    /**
     * @var ArrayCollection|OfferDictionaryRelation[]
     * @Assert\Valid(groups={"submit"})
     */
    private $languages;

    /**
     * @var ArrayCollection|DicLifeStyle[]
     * @Assert\Valid(groups={"submit"})
     */
    private $lifeStyles;

    /**
     * @var ArrayCollection|DicMarketStatus[]
     * @Assert\Valid(groups={"submit"})
     */
    private $marketStatuses;

    /**
     * @var ArrayCollection|DicPersonalStrength[]
     * @Assert\Valid(groups={"submit"})
     */
    private $personalStrengths;

    /**
     * @var ArrayCollection|DicShift[]
     * @Assert\Count(
     *     min=1,
     *     minMessage="validation.offer.manage.shifts.count",
     *     groups={"submit"}
     * )
     */
    private $shifts;

    /**
     * @var ArrayCollection|OfferDictionaryRelation[]
     * @Assert\Valid(groups={"submit"})
     */
    private $softwareExperiences;

    /**
     * @var ArrayCollection|DicSupport[]
     */
    private $support;

    /**
     * @var ArrayCollection|DicExpectation[]
     * @Assert\Valid(groups={"submit"})
     */
    private $expectations;

    /**
     * @var ArrayCollection|DicAdvantage[]
     * @Assert\Valid(groups={"submit"})
     */
    private $advantages;

    /**
     * @var ArrayCollection|OfferDictionaryRelation[]
     * @Assert\Valid(groups={"submit"})
     */
    private $details;

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
     * @var OfferServiceStatusEnum
     * @ RM\Column(type="offer_service_status_enum", options={"default" = 0})
     */
    private $offerExaltationStatus;

    /**
     * @var OfferServiceStatusEnum
     * @ ORM\Column(type="offer_service_status_enum", options={"default" = 0})
     */
    private $advanceFilterStatus;

    /**
     * @var DicEducationLevel
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicEducationLevel")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true,
     * )
     * @Assert\NotBlank(groups={"submit"})
     */
    private $minEducation;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $sourceName;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $leadImg;


    /**
     * Offer constructor.
     */
    public function __construct()
    {
        $this->status = OfferStatusEnum::create(OfferStatusEnum::SAVED);
        $this->applicableFromDate = new \DateTime();
        $this->expireDate = new \DateTime('+1 month');
        $this->dictionaryRelations = new ArrayCollection();
        $this->candidates = new ArrayCollection();

        $this->initializeModel();
    }

    public function __toString()
    {
        if ($this->getId()) {
            return $this->getTitle() ? $this->getFirm()->getName().' - '.$this->getTitle() :
                $this->getFirm()->getName().' - '.$this->getApplicableFromDate()->format('Y-m-d');
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSourceName(): ? string
    {
        return $this->sourceName;
    }

    /**
     * @param string $sourceName
     */
    public function setSourceName(?string $sourceName): void
    {
        $this->sourceName = $sourceName;
    }

    public function initializeModel()
    {
        $this->tasks = $this->retrieveSelectionCollection([DicTask::class], 0);
        $this->companyHelps = $this->retrieveSelectionCollection([DicCompanyHelp::class], 0);
        $this->drivingLicenses = $this->retrieveSelectionCollection([DicDrivingLicense::class], 0);
        $this->experiences = $this->retrieveDetailedCollection([DicExperience::class], 0);
        $this->itExperiences = $this->retrieveDetailedCollection([DicItExperience::class], 0);
        $this->categories = $this->retrieveSelectionCollection([DicCategory::class], 0);
        $this->educations = $this->retrieveDetailedCollection([DicCategory::class], 1);
        $this->jobForms = $this->retrieveSelectionCollection([DicJobForm::class], 0);
        $this->languages = $this->retrieveDetailedCollection([DicLanguage::class], 0);
        $this->lifeStyles = $this->retrieveSelectionCollection([DicLifeStyle::class], 0);
        $this->marketStatuses = $this->retrieveSelectionCollection([DicMarketStatus::class], 0);
        $this->personalStrengths = $this->retrieveSelectionCollection([DicPersonalStrength::class], 0);
        $this->shifts = $this->retrieveSelectionCollection([DicShift::class], 0);
        $this->softwareExperiences = $this->retrieveDetailedCollection([DicSoftwareExperience::class], 0);
        $this->support = $this->retrieveSelectionCollection([DicSupport::class], 0);
        $this->expectations = $this->retrieveSelectionCollection([DicExpectation::class], 0);
        $this->advantages = $this->retrieveSelectionCollection([DicAdvantage::class], 0);
        $this->details = $this->retrieveSelectionCollection([DicDetail::class], 0);
        $this->locations = $this->retrieveSelectionCollection([
            DicCounty::class,
            DicCity::class,
        ], 0);


        $this->workLocations = $this->retrieveSelectionCollection([
            DicCounty::class,
            DicCity::class,
        ], 2);

        /*
        $this->dictionaryRelations = new ValueSetterCollection($this->dictionaryRelations, [
            'offer' => $this,
        ]);
         */
        $this->candidates = new ValueSetterCollection($this->candidates, [
            'offer' => $this,
        ]);
    }

    /**
     * @return ArrayCollection|OfferDictionaryRelation[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return ArrayCollection|DicCompanyHelp[]
     */
    public function getCompanyHelps()
    {
        return $this->companyHelps;
    }

    /**
     * @return ArrayCollection|DicDrivingLicense[]
     */
    public function getDrivingLicenses()
    {
        return $this->drivingLicenses;
    }

    /**
     * @return ArrayCollection|OfferDictionaryRelation[]
     */
    public function getEducations()
    {
        return $this->educations;
    }

    /**
     * @return ArrayCollection|OfferDictionaryRelation[]
     */
    public function getExperiences()
    {
        return $this->experiences;
    }

    /**
     * @return ArrayCollection|OfferDictionaryRelation[]
     */
    public function getItExperiences()
    {
        return $this->itExperiences;
    }

    /**
     * @return ArrayCollection|DicCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return ArrayCollection|DicJobForm[]
     */
    public function getJobForms()
    {
        return $this->jobForms;
    }

    /**
     * @return ArrayCollection|OfferDictionaryRelation[]
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return ArrayCollection|DicLifeStyle[]
     */
    public function getLifeStyles()
    {
        return $this->lifeStyles;
    }

    /**
     * @return ArrayCollection|DicMarketStatus[]
     */
    public function getMarketStatuses()
    {
        return $this->marketStatuses;
    }

    /**
     * @return ArrayCollection|DicPersonalStrength[]
     */
    public function getPersonalStrengths()
    {
        return $this->personalStrengths;
    }

    /**
     * @return ArrayCollection|DicShift[]
     */
    public function getShifts()
    {
        return $this->shifts;
    }

    /**
     * @return ArrayCollection|OfferDictionaryRelation[]
     */
    public function getSoftwareExperiences()
    {
        return $this->softwareExperiences;
    }

    /**
     * @return ArrayCollection|DicSupport[]
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * @return ArrayCollection|DicExpectation[]
     */
    public function getExpectations()
    {
        return $this->expectations;
    }

    /**
     * @return ArrayCollection|DicAdvantage[]
     */
    public function getAdvantages()
    {
        return $this->advantages;
    }

    /**
     * @return ArrayCollection|DicDetail[]
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @Assert\Collection(
     *     fields={
     *         "from" = {
     *             @Assert\NotBlank,
     *             @AppAssert\DateValue(
     *                 operation="gte",
     *                 date_value="now",
     *                 with_time=false,
     *                 message="error.offer.less-than-today",
     *                 groups={"submit"}
     *             )
     *         },
     *         "until"=@Assert\NotBlank,
     *     },
     *     allowExtraFields=true,
     * )
     * @AppAssert\DateRange(
     *     min_days="1",
     *     max_days="32",
     *     groups={"submit"}
     * )
     *
     * @return DateTimeRange
     */
    public function getApplicableDateRange()
    {
        $range = new DateTimeRange();

        $range
            ->setFrom($this->getApplicableFromDate())
            ->setUntil($this->getExpireDate())
        ;

        return $range;
    }

    /**
     * @param DateTimeRange|null $range
     */
    public function setApplicableDateRange(DateTimeRange $range = null)
    {

        $this->setApplicableFromDate($range->getFrom());
        $this->setExpireDate($range->getUntil());
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firmId.
     *
     * @param int $firmId
     *
     * @return Offer
     */
    public function setFirmId($firmId)
    {
        $this->firmId = $firmId;

        return $this;
    }

    /**
     * Get firmId.
     *
     * @return int
     */
    public function getFirmId()
    {
        return $this->firmId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return Offer
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set lead.
     *
     * @param string|null $lead
     *
     * @return Offer
     */
    public function setLead($lead = null)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * Get lead.
     *
     * @return string|null
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * Set anonim.
     *
     * @param bool|null $anonim
     *
     * @return Offer
     */
    public function setAnonim($anonim = null)
    {
        $this->anonim = $anonim;

        return $this;
    }

    /**
     * Get anonim.
     *
     * @return bool|null
     */
    public function getAnonim()
    {
        return $this->anonim;
    }

    /**
     * Set minimal_package.
     *
     * @param bool|null $minimal_package
     *
     * @return Offer
     */
    public function setMinimalPackage($minimal_package = null)
    {
        $this->minimal_package = $minimal_package;

        return $this;
    }

    /**
     * Get minimal_package.
     *
     * @return bool|null
     */
    public function getMinimalPackage()
    {
        return $this->minimal_package;
    }

    /**
     * Set minimal_without_cv.
     *
     * @param bool|null $minimal_without_cv
     *
     * @return Offer
     */
    public function setMinimalWithoutCv($minimal_without_cv = null)
    {
        $this->minimal_without_cv = $minimal_without_cv;

        return $this;
    }

    /**
     * Get minimal_without_cv.
     *
     * @return bool|null
     */
    public function getMinimalWithoutCv()
    {
        return $this->minimal_without_cv;
    }

    /**
     * Set minimal_email.
     *
     * @param string|null $minimal_email
     *
     * @return Offer
     */
    public function setMinimalEmail($minimal_email = null)
    {
        $this->minimal_email = $minimal_email;

        return $this;
    }

    /**
     * Get minimal_email.
     *
     * @return string|null
     */
    public function getMinimalEmail()
    {
        return $this->minimal_email;
    }

    /**
     * Set minimal_title.
     *
     * @param string|null $minimal_title
     *
     * @return Offer
     */
    public function setMinimalTitle($minimal_title = null)
    {
        $this->minimal_title = $minimal_title;

        return $this;
    }

    /**
     * Get minimal_title.
     *
     * @return string|null
     */
    public function getMinimalTitle()
    {
        return $this->minimal_title;
    }

    /**
     * Set minimal_city.
     *
     * @param string|null $minimal_city
     *
     * @return Offer
     */
    public function setMinimalCity($minimal_city = null)
    {
        $this->minimal_city = $minimal_city;

        return $this;
    }

    /**
     * Get minimal_city.
     *
     * @return string|null
     */
    public function getMinimalCity()
    {
        return $this->minimal_city;
    }


    /**
     * Set minimal_url.
     *
     * @param string|null $minimal_url
     *
     * @return Offer
     */
    public function setMinimalUrl($minimal_url = null)
    {
        $this->minimal_url = $minimal_url;

        return $this;
    }

    /**
     * Get minimal_url.
     *
     * @return string|null
     */
    public function getMinimalUrl()
    {
        return $this->minimal_url;
    }


    /**
     * @return string|null
     */
    public function getRealLogoName(): ?string
    {
        return (!$this->anonim) ? $this->getFirm()->getRealLogoName() : 'logo-default-anonim.png';
    }

    /**
     * Set applicableFromDate.
     *
     * @param \DateTime $applicableFromDate
     *
     * @return Offer
     */
    public function setApplicableFromDate(\DateTime $applicableFromDate = null)
    {
        $this->applicableFromDate = $applicableFromDate;

        return $this;
    }

    /**
     * Get applicableFromDate.
     *
     * @return \DateTime
     */
    public function getApplicableFromDate()
    {
        return $this->applicableFromDate;
    }

    /**
     * Set numberOfEmployee.
     *
     * @param int|null $numberOfEmployee
     *
     * @return Offer
     */
    public function setNumberOfEmployee($numberOfEmployee = null)
    {
        $this->numberOfEmployee = $numberOfEmployee;

        return $this;
    }

    /**
     * Get numberOfEmployee.
     *
     * @return int|null
     */
    public function getNumberOfEmployee()
    {
        return $this->numberOfEmployee;
    }

    /**
     * Set expireDate.
     *
     * @param \DateTime $expireDate
     *
     * @return Offer
     */
    public function setExpireDate(\DateTime $expireDate = null)
    {

        $this->expireDate = $expireDate;

        return $this;
    }

    /**
     * Get expireDate.
     *
     * @return \DateTime
     */
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
     * Set slug.
     *
     * @param string|null $slug
     *
     * @return Offer
     */
    public function setSlug($slug = null)
    {
        if (null === $slug) {
            $slug= $this->generateSlug();
        }

        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string|null
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function generateSlug(){
        $slug= $this->getTitle().'-'.$this->getCollectionValueSlug($this->getLocations());
        $slug.= (!$this->getAnonim() and !empty($this->getFirm())) ? '-' . $this->getFirm()->getName() : '';

        $slugify = new Slugify();
        $slugify->activateRuleSet('hungarian');
        return $slugify->slugify($slug);
    }

    /**
     * @param Collection $collection
     * @return string
     */
    public function getCollectionValueSlug(Collection $collection){
        $slug = '';

        /**
         * @var int $key
         * @var Dictionary $value
         */
        foreach ($collection as $key=>$value){

            $slug.= !empty($value) ? $value->getValue() . ( ($key !== $collection->count() ) ? '-' : '' ) : '';

        }

        return $slug;
    }

    /**
     * Set status.
     *
     * @param OfferStatusEnum $status
     *
     * @return Offer
     */
    public function setStatus(OfferStatusEnum $status)
    {
        $this->status = $this->getCorrectStatus( $status );

        return $this;
    }

    /**
     * Get status.
     *
     * @return OfferStatusEnum
     */
    public function getStatus()
    {
        return $this->getCorrectStatus( $this->status );
    }

    /**
     * Important For Insert/Update: In order for this to work properly,
     * we must call this after we have applicable
     * and expire date set. Otherwise this will not be able
     * to decide if we are waiting or expired!
     *
     * @param OfferStatusEnum $status
     * @return \Biplane\EnumBundle\Enumeration\EnumInterface|OfferStatusEnum
     */
    public function getCorrectStatus(OfferStatusEnum $status){

        if (
            $status != OfferStatusEnum::create(OfferStatusEnum::DENIED) &&
            $status != OfferStatusEnum::create(OfferStatusEnum::SAVED) &&
            $status != OfferStatusEnum::create(OfferStatusEnum::INACTIVE) &&
            $status != OfferStatusEnum::create(OfferStatusEnum::UNDER_CONSIDERATION) &&
            $status != OfferStatusEnum::create(OfferStatusEnum::MIGRATED) &&
            !empty($this->applicableFromDate) &&
            !empty($this->expireDate )
        ) {
            $today = new \DateTime();
            // we need to make sure the offer is in WAITING state if the start date is a future date
            if ($this->getApplicableFromDate() > $today) {
                return OfferStatusEnum::create(OfferStatusEnum::WAITING);
            }
            // we need to make sure the offer is in EXPIRED state if the expire date is less then today
            elseif ($this->getExpireDate() < $today) {
                return OfferStatusEnum::create(OfferStatusEnum::INACTIVE);
            }
        }

        return $status;
    }

    /**
     * Set locale.
     *
     * @param string|null $locale
     *
     * @return Offer
     */
    public function setLocale($locale = null)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Offer
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return Offer
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set firm.
     *
     * @param Firm $firm
     *
     * @return Offer
     */
    public function setFirm(Firm $firm)
    {
        $this->firm = $firm;
        $this->firmId = $firm->getId();

        return $this;
    }

    /**
     * Get firm.
     *
     * @return Firm
     */
    public function getFirm()
    {
        return $this->firm;
    }

    /**
     * Add dictionaryRelation.
     *
     * @param OfferDictionaryRelation $dictionaryRelation
     *
     * @return Offer
     */
    public function addDictionaryRelation(OfferDictionaryRelation $dictionaryRelation)
    {
        $this->dictionaryRelations[] = $dictionaryRelation;

        return $this;
    }

    /**
     * Remove dictionaryRelation.
     *
     * @param OfferDictionaryRelation $dictionaryRelation
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeDictionaryRelation(OfferDictionaryRelation $dictionaryRelation)
    {
        return $this->dictionaryRelations->removeElement($dictionaryRelation);
    }

    /**
     * @param DicCategory $category
     *
     * @return $this
     */
    public function addCategory(DicCategory $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * @param DicCategory $category
     *
     * @return bool
     */
    public function removeCategory(DicCategory $category)
    {
        return $this->categories->removeElement($category);
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
     * Get dictionaryRelations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDictionaryRelations()
    {
        return $this->dictionaryRelations;
    }

    /**
     * Add location.
     *
     * @param Dictionary $location
     *
     * @return Offer
     */
    public function addLocation(Dictionary $location)
    {
        $this->locations[] = $location;

        return $this;
    }

    /**
     * SET location.
     *
     * @param Dictionary $location
     *
     * @return $this
     */
    public function setLocation(Dictionary $location)
    {
        $this->locations = [$location];

        return $this;
    }

    /**
     * Remove location.
     *
     * @param Dictionary $location
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeLocation(Dictionary $location)
    {
        return $this->locations->removeElement($location);
    }

    /**
     * Get locations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Add candidate.
     *
     * @param OfferCandidate $candidate
     *
     * @return Offer
     */
    public function addCandidate(OfferCandidate $candidate)
    {
        $this->candidates[] = $candidate;

        return $this;
    }

    /**
     * Remove candidate.
     *
     * @param OfferCandidate $candidate
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeCandidate(OfferCandidate $candidate)
    {
        return $this->candidates->removeElement($candidate);
    }

    /**
     * Get candidates.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCandidates()
    {
        return $this->candidates;
    }

    /**
     * Set offerExaltationUntil.
     *
     * @param \DateTime|null $offerExaltationUntil
     *
     * @return Offer
     */
    public function setOfferExaltationUntil(\DateTime $offerExaltationUntil = null)
    {
        $this->offerExaltationUntil = $offerExaltationUntil;

        return $this;
    }

    /**
     * Get offerExaltationUntil.
     *
     * @return \DateTime|null
     */
    public function getOfferExaltationUntil()
    {
        return $this->offerExaltationUntil;
    }

    /**
     * Set advenceFilterUntil.
     *
     * @param \DateTime|null $advenceFilterUntil
     *
     * @return Offer
     */
    public function setAdvanceFilterUntil(\DateTime $advenceFilterUntil = null)
    {
        $this->advanceFilterUntil = $advenceFilterUntil;

        return $this;
    }

    /**
     * Get advenceFilterUntil.
     *
     * @return \DateTime|null
     */
    public function getAdvanceFilterUntil()
    {
        return $this->advanceFilterUntil;
    }

    /**
     * @return int
     */
    public function getCandidatesCount()
    {
        return $this->candidates->count();
    }

    /**
     * Method will return the number of candidates
     * in a given status.
     *
     * @param OfferCandidateStatusEnum $candidateStatusEnum
     *
     * @return int
     */
    public function getCandidateStatusCount(OfferCandidateStatusEnum $candidateStatusEnum)
    {
        $count = 0;

        foreach ($this->candidates as $candidate) {
            if ($candidateStatusEnum == $candidate->getStatus()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return int
     */
    public function getDirectCandidates()
    {
        $count = 0;

        foreach ($this->candidates as $candidate) {
            if ($candidate->isDirect()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return int
     */
    public function getPairedCandidates()
    {
        /*
         * @Todo Implement this one
         */
        return 0;
    }

    /**
     * @return int
     */
    public function getNewCandidates()
    {
        return $this->getCandidateStatusCount(OfferCandidateStatusEnum::create(OfferCandidateStatusEnum::NEW));
    }

    /**
     * @return OfferServiceStatusEnum
     */
    public function getOfferExaltationStatus()
    {
        return $this->offerExaltationStatus;
    }

    /**
     * @param OfferServiceStatusEnum $offerExaltationStatus
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setOfferExaltationStatus(OfferServiceStatusEnum $offerExaltationStatus)
    {
        $this->offerExaltationStatus = $offerExaltationStatus;

        if ($offerExaltationStatus instanceof OfferServiceStatusEnumProxy) {
            return $this;
        }

        $activeEnum = OfferServiceStatusEnum::create(OfferServiceStatusEnum::ACTIVE);
        if ($offerExaltationStatus->equals($activeEnum)) {
            $now = new \DateTime();
            $now->add(new \DateInterval('P1M'));
            $this->offerExaltationUntil = $now;
        } else {
            $this->offerExaltationUntil = null;
        }

        return $this;
    }

    /**
     * @return OfferServiceStatusEnum
     */
    public function getAdvanceFilterStatus()
    {
        return $this->advanceFilterStatus;
    }

    /**
     * @param OfferServiceStatusEnum $advanceFilterStatus
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setAdvanceFilterStatus(OfferServiceStatusEnum $advanceFilterStatus)
    {
        $this->advanceFilterStatus = $advanceFilterStatus;

        if ($advanceFilterStatus instanceof OfferServiceStatusEnumProxy) {
            return $this;
        }

        $activeEnum = OfferServiceStatusEnum::create(OfferServiceStatusEnum::ACTIVE);
        if ($advanceFilterStatus->equals($activeEnum)) {
            $now = new \DateTime();
            $now->add(new \DateInterval('P1M'));
            $this->advanceFilterUntil = $now;
        } else {
            $this->advanceFilterUntil = null;
        }

        return $this;
    }

    /**
     * @param FirmPackageServiceEnum $type
     *
     * @return OfferServiceStatusEnum
     */
    public function getOfferServiceStatus(FirmPackageServiceEnum $type)
    {
        $method = 'get'.implode(array_map('ucfirst', explode('_', $type->getReadable()))).'Status';

        return $this->$method();
    }

    /**
     * @param FirmPackageServiceEnum $type
     * @param OfferServiceStatusEnum $value
     *
     * @return $this
     */
    public function setOfferServiceStatus(FirmPackageServiceEnum $type, OfferServiceStatusEnum $value)
    {
        $method = 'set'.implode(array_map('ucfirst', explode('_', $type->getReadable()))).'Status';

        $this->$method($value);

        return $this;
    }

    /**
     * @param FirmPackageServiceEnum $type
     *
     * @return \DateTime|null
     */
    public function getOfferServiceUntil(FirmPackageServiceEnum $type)
    {
        $method = 'get'.implode(array_map('ucfirst', explode('_', $type->getReadable()))).'Until';

        return $this->$method();
    }

    /**
     * @param FirmPackageServiceEnum $type
     * @param \DateTime|null         $value
     *
     * @return $this
     */
    public function setOfferServiceUntil(FirmPackageServiceEnum $type, \DateTime $value = null)
    {
        $method = 'set'.implode(array_map('ucfirst', explode('_', $type->getReadable()))).'Until';

        $this->$method($value);

        return $this;
    }

    /**
     * @return DicEducationLevel
     */
    public function getMinEducation()
    {
        return $this->minEducation;
    }

    /**
     * @param DicEducationLevel $minEducation
     */
    public function setMinEducation(DicEducationLevel $minEducation): void
    {
        $this->minEducation = $minEducation;
    }

    /**
     * @return string
     */
    public function getLocationsString()
    {
        $locations = '';

        foreach ($this->locations as $key => $location) {
            $locations .= $location->getValue().($key > 0 ? ',' : '');
        }

        return $locations;
    }

    /**
     * @param mixed $id
     *
     * @return Offer
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param ArrayCollection|DicLocation[] $locations
     *
     * @return Offer
     */
    public function setLocations($locations)
    {
        $this->locations = $locations;

        return $this;
    }

    /**
     * @param ArrayCollection|OfferDictionaryRelation[] $dictionaryRelations
     *
     * @return Offer
     */
    public function setDictionaryRelations($dictionaryRelations)
    {
        $this->dictionaryRelations = $dictionaryRelations;

        return $this;
    }

    /**
     * @param ArrayCollection|OfferCandidate[] $candidates
     *
     * @return Offer
     */
    public function setCandidates($candidates)
    {
        $this->candidates = $candidates;

        return $this;
    }

    /**
     * @param ArrayCollection|OfferDictionaryRelation[] $tasks
     *
     * @return Offer
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * @param ArrayCollection|DicCompanyHelp[] $companyHelps
     *
     * @return Offer
     */
    public function setCompanyHelps($companyHelps)
    {
        $this->companyHelps = $companyHelps;

        return $this;
    }

    /**
     * @param ArrayCollection|DicDrivingLicense[] $drivingLicenses
     *
     * @return Offer
     */
    public function setDrivingLicenses($drivingLicenses)
    {
        $this->drivingLicenses = $drivingLicenses;

        return $this;
    }

    /**
     * @param ArrayCollection|OfferDictionaryRelation[] $educations
     *
     * @return Offer
     */
    public function setEducations($educations)
    {
        $this->educations = $educations;

        return $this;
    }

    /**
     * @param ArrayCollection|OfferDictionaryRelation[] $experiences
     *
     * @return Offer
     */
    public function setExperiences($experiences)
    {
        $this->experiences = $experiences;

        return $this;
    }

    /**
     * @param ArrayCollection|OfferDictionaryRelation[] $itExperiences
     *
     * @return Offer
     */
    public function setItExperiences($itExperiences)
    {
        $this->itExperiences = $itExperiences;

        return $this;
    }

    /**
     * @param ArrayCollection|DicCategory[] $categories
     *
     * @return Offer
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @param ArrayCollection|DicJobForm[] $jobForms
     *
     * @return Offer
     */
    public function setJobForms($jobForms)
    {
        $this->jobForms = $jobForms;

        return $this;
    }

    /**
     * @param ArrayCollection|OfferDictionaryRelation[] $languages
     *
     * @return Offer
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * @param ArrayCollection|DicLifeStyle[] $lifeStyles
     *
     * @return Offer
     */
    public function setLifeStyles($lifeStyles)
    {
        $this->lifeStyles = $lifeStyles;

        return $this;
    }

    /**
     * @param ArrayCollection|DicMarketStatus[] $marketStatuses
     *
     * @return Offer
     */
    public function setMarketStatuses($marketStatuses)
    {
        $this->marketStatuses = $marketStatuses;

        return $this;
    }

    /**
     * @param ArrayCollection|DicPersonalStrength[] $personalStrengths
     *
     * @return Offer
     */
    public function setPersonalStrengths($personalStrengths)
    {
        $this->personalStrengths = $personalStrengths;

        return $this;
    }

    /**
     * @param ArrayCollection|DicShift[] $shifts
     *
     * @return Offer
     */
    public function setShifts($shifts)
    {
        $this->shifts = $shifts;

        return $this;
    }

    /**
     * @param ArrayCollection|OfferDictionaryRelation[] $softwareExperiences
     *
     * @return Offer
     */
    public function setSoftwareExperiences($softwareExperiences)
    {
        $this->softwareExperiences = $softwareExperiences;

        return $this;
    }

    /**
     * @param ArrayCollection|DicSupport[] $support
     *
     * @return Offer
     */
    public function setSupport($support)
    {
        $this->support = $support;

        return $this;
    }

    /**
     * @param ArrayCollection|DicExpectation[] $expectations
     *
     * @return Offer
     */
    public function setExpectations($expectations)
    {
        $this->expectations = $expectations;

        return $this;
    }

    /**
     * @param ArrayCollection|DicAdvantage[] $advantages
     *
     * @return Offer
     */
    public function setAdvantages($advantages)
    {
        $this->advantages = $advantages;

        return $this;
    }

    /**
     * @param $details
     *
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
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
            OfferDictionaryRelation::class,
            'offer',
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
            /* @var OfferDictionaryRelation $entity */
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
            'offer' => $this,
            'discriminator' => $discriminator,
        ]);

        return $collection;
    }

    /**
     * Get workLocations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkLocations()
    {
        return $this->workLocations;
    }

    /**
     * Add workLocation.
     *
     * @param Dictionary $workLocation
     *
     * @return Offer
     */
    public function addWorkLocation(Dictionary $workLocation)
    {
        $this->workLocations[] = $workLocation;

        return $this;
    }

    /**
     * Remove workLocation.
     *
     * @param Dictionary $workLocation
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeWorkLocation(Dictionary $workLocation)
    {
        return $this->workLocations->removeElement($workLocation);
    }

    /**
     * @param ArrayCollection|Dictionary[] $workLocations
     *
     * @return Offer
     */
    public function setWorkLocations($workLocations)
    {
        $this->workLocations = $workLocations;

        return $this;
    }

    /**
     * Get leadImg.
     *
     * @return string|null
     */
    public function getLeadImg()
    {
        return $this->leadImg;
    }

    /**
     * Set leadImg.
     *
     * @param string|null $leadImg
     *
     * @return Offer
     */
    public function setLeadImg($leadImg = null)
    {
        $this->leadImg = $leadImg;

        return $this;
    }

}
