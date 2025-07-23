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

use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Firm\Balance\FirmBalance;
use Common\CoreBundle\Entity\Firm\Cart\FirmCartItem;
use Common\CoreBundle\Entity\Firm\Order\FirmOrder;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Firm\FirmStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;
use Spirit\SpiritModelBundle\Model\SpiritModelInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Class Firm.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\Firm\FirmRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 * @Vich\Uploadable
 */
class Firm implements InitializeModelInterface, SpiritModelInterface
{
    /**
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
    private $originalId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastSpiritId;

    /**
     * @var ArrayCollection|Offer[]
     * @ORM\OneToMany(targetEntity="Common\CoreBundle\Entity\Offer\Offer", mappedBy="firm")
     */
    private $offers;

    /**
     * @var FirmColleague
     *
     * @ORM\OneToOne(targetEntity="Common\CoreBundle\Entity\Firm\FirmColleague", mappedBy="firm")
     * @ORM\OrderBy({"lastLoginTime" = "DESC"})
     */
    private $firmColleague;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=8,
     *     max=20,
     *     minMessage="Your first name must be at least {{ limit }} characters long",
     *     maxMessage="Your first name cannot be longer than {{ limit }} characters"
     * )
     */
    private $taxNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=3,
     *     max=30,
     *     minMessage="Your name must be at least {{ limit }} characters long",
     *     maxMessage="Your name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(
     *     min=5,
     *     max=225,
     *     minMessage="Your long name must be at least {{ limit }} characters long",
     * )
     */
    private $nameLong;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=3,
     *     max=225,
     *     minMessage="Your representative name must be at least {{ limit }} characters long",
     *     maxMessage="Your representative name cannot be longer than {{ limit }} characters"
     * )
     */
    private $representative;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $postalLocationId;

    /**
     * @var DicLocation
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicLocation")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     */
    private $postalLocation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="Your street must be at least {{ limit }} characters long",
     *     maxMessage="Your street cannot be longer than {{ limit }} characters"
     * )
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $streetNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $floor;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $doorNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $postalStreet;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $postalStreetNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $postalFloor;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $postalDoorNumber;

    /**
     * @var DicLocation
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicLocation")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true,
     * )
     */
    private $sitesLocation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $sitesStreet;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $sitesStreetNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $videoUrl;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $webPageUrl;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $socialMedia;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numberOfEmployees;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", length=4, nullable=true)
     */
    private $foundedYear;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $introduction;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $visio;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $whyDescription;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/*",  })
     */
    private $logo;

    /**
     * @Vich\UploadableField(mapping="firm_logo", fileNameProperty="logoName")
     *
     * @var File
     */
    private $logoFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $logoName;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/*",  })
     */
    private $coverImage;

    /**
     * @Vich\UploadableField(mapping="firm_cover_image", fileNameProperty="coverImageName")
     *
     * @var File
     */
    private $coverImageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $coverImageName;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/*",  })
     */
    private $whyUsImage;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $viewedNumber = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="firm_status_enum", options={"default" = 1})
     */
    private $status;

    /**
     * @var Collection|FirmCartItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Cart\FirmCartItem",
     *     mappedBy="firm",
     * )
     * @ORM\JoinColumn(
     *     onDelete="CASCADE"
     * )
     */
    private $cartItems;

    /**
     * @var Collection|FirmOrder[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Order\FirmOrder",
     *     mappedBy="firm",
     * )
     * @ORM\JoinColumn(
     *     onDelete="CASCADE"
     * )
     */
    private $orders;

    /**
     * @var Collection|FirmBalance[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Balance\FirmBalance",
     *     mappedBy="firm"
     * )
     * @ORM\JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $balances;

    /**
     * @var ArrayCollection|FirmCv[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\FirmCv",
     *     mappedBy="firm"
     * )
     */
    private $cvs;

    /**
     * @var Firm
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Firm\Firm")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    private $original;

    /**
     * @var Firm
     *
     * @ORM\OneToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Firm"
     * )
     * @ORM\JoinColumn(
     *     nullable=true
     * )
     */
    private $lastSpirit;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct()
    {
        $this->status = FirmStatusEnum::create(FirmStatusEnum::ACTIVE);

        $this->offers = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->balances = new ArrayCollection();

        $this->initializeModel();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return !empty($this->name) ? $this->name : '';
    }

    public function initializeModel()
    {
        $this->offers = new ValueSetterCollection($this->offers, [
            'firm' => $this,
        ]);
        $this->cartItems = new ValueSetterCollection($this->cartItems, [
            'firm' => $this,
        ]);
        $this->orders = new ValueSetterCollection($this->orders, [
            'firm' => $this,
        ]);
        $this->balances = new ValueSetterCollection($this->balances, [
            'firm' => $this,
        ]);
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
     * Set originalId.
     *
     * @param int|null $originalId
     *
     * @return Firm
     */
    public function setOriginalId($originalId = null)
    {
        $this->originalId = $originalId;

        return $this;
    }

    /**
     * Get originalId.
     *
     * @return int|null
     */
    public function getOriginalId()
    {
        return $this->originalId;
    }

    /**
     * Set lastSpiritId.
     *
     * @param int|null $lastSpiritId
     *
     * @return Firm
     */
    public function setLastSpiritId($lastSpiritId = null)
    {
        $this->lastSpiritId = $lastSpiritId;

        return $this;
    }

    /**
     * Get lastSpiritId.
     *
     * @return int|null
     */
    public function getLastSpiritId()
    {
        return $this->lastSpiritId;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|null $image
     *
     * @throws \Exception
     */
    public function setLogoFile(?File $image = null): void
    {
        $this->logoFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return File|null
     */
    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    /**
     * @param string|null $logoName
     */
    public function setLogoName(?string $logoName): void
    {
        $this->logoName = $logoName;
    }

    /**
     * @return string|null
     */
    public function getLogoName(): ?string
    {
        return $this->logoName;
    }

    /**
     * @return string|null
     */
    public function getRealLogoName(): ?string
    {
        return empty($this->logoName) ? 'logo-default.png' : $this->logoName;
    }

    /**
     * @param File|null $image
     *
     * @throws \Exception
     */
    public function setCoverImageFile(?File $image = null): void
    {
        $this->coverImageFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return File|null
     */
    public function getCoverImageFile(): ?File
    {
        return $this->coverImageFile;
    }

    /**
     * @param string|null $coverImageName
     */
    public function setCoverImageName(?string $coverImageName): void
    {
        $this->coverImageName = $coverImageName;
    }

    public function getCoverImageName(): ?string
    {
        return $this->coverImageName;
    }

    /**
     * Set taxNumber.
     *
     * @param string $taxNumber
     *
     * @return Firm
     */
    public function setTaxNumber($taxNumber)
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    /**
     * Get taxNumber.
     *
     * @return string
     */
    public function getTaxNumber()
    {
        return $this->taxNumber;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Firm
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get nameWithContact.
     *
     * @return string
     */
    public function getNameWithContact()
    {
        return $this->name." - ".$this->firmColleague;
    }

    /**
     * Set nameLong.
     *
     * @param string|null $nameLong
     *
     * @return Firm
     */
    public function setNameLong($nameLong = null)
    {
        $this->nameLong = $nameLong;

        return $this;
    }

    /**
     * Get nameLong.
     *
     * @return string|null
     */
    public function getNameLong()
    {
        return $this->nameLong;
    }

    /**
     * Set representative.
     *
     * @param string $representative
     *
     * @return Firm
     */
    public function setRepresentative($representative)
    {
        $this->representative = $representative;

        return $this;
    }

    /**
     * Get representative.
     *
     * @return string
     */
    public function getRepresentative()
    {
        return $this->representative;
    }

    /**
     * Set locationId.
     *
     * @param int $locationId
     *
     * @return Firm
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
     * Set postalLocationId.
     *
     * @param int $postalLocationId
     *
     * @return Firm
     */
    public function setPostalLocationId($postalLocationId)
    {
        $this->postalLocationId = $postalLocationId;

        return $this;
    }

    /**
     * Get postalLocationId.
     *
     * @return int
     */
    public function getPostalLocationId()
    {
        return $this->postalLocationId;
    }

    /**
     * Set street.
     *
     * @param string|null $street
     *
     * @return Firm
     */
    public function setStreet($street = null)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street.
     *
     * @return string|null
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set streetNumber.
     *
     * @param string|null $streetNumber
     *
     * @return Firm
     */
    public function setStreetNumber($streetNumber = null)
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    /**
     * Get streetNumber.
     *
     * @return string|null
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * Set floor.
     *
     * @param string|null $floor
     *
     * @return Firm
     */
    public function setFloor($floor = null)
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * Get floor.
     *
     * @return string|null
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * Set doorNumber.
     *
     * @param string|null $doorNumber
     *
     * @return Firm
     */
    public function setDoorNumber($doorNumber = null)
    {
        $this->doorNumber = $doorNumber;

        return $this;
    }

    /**
     * Get doorNumber.
     *
     * @return string|null
     */
    public function getDoorNumber()
    {
        return $this->doorNumber;
    }

    /**
     * @return DicLocation
     */
    public function getSitesLocation()
    {
        return $this->sitesLocation;
    }

    /**
     * @param DicLocation $sitesLocation
     */
    public function setSitesLocation(DicLocation $sitesLocation): void
    {
        $this->sitesLocation = $sitesLocation;
    }

    /**
     * @return string
     */
    public function getSitesStreet()
    {
        return $this->sitesStreet;
    }

    /**
     * @param string $sitesStreet
     */
    public function setSitesStreet(string $sitesStreet): void
    {
        $this->sitesStreet = $sitesStreet;
    }

    /**
     * @return string
     */
    public function getSitesStreetNumber()
    {
        return $this->sitesStreetNumber;
    }

    /**
     * @param string $sitesStreetNumber
     */
    public function setSitesStreetNumber(string $sitesStreetNumber): void
    {
        $this->sitesStreetNumber = $sitesStreetNumber;
    }

    /**
     * Set postalStreet.
     *
     * @param string|null $postalStreet
     *
     * @return Firm
     */
    public function setPostalStreet($postalStreet = null)
    {
        $this->postalStreet = $postalStreet;

        return $this;
    }

    /**
     * Get postalStreet.
     *
     * @return string|null
     */
    public function getPostalStreet()
    {
        return $this->postalStreet;
    }

    /**
     * Set postalStreetNumber.
     *
     * @param string|null $postalStreetNumber
     *
     * @return Firm
     */
    public function setPostalStreetNumber($postalStreetNumber = null)
    {
        $this->postalStreetNumber = $postalStreetNumber;

        return $this;
    }

    /**
     * Get postalStreetNumber.
     *
     * @return string|null
     */
    public function getPostalStreetNumber()
    {
        return $this->postalStreetNumber;
    }

    /**
     * Set postalFloor.
     *
     * @param string|null $postalFloor
     *
     * @return Firm
     */
    public function setPostalFloor($postalFloor = null)
    {
        $this->postalFloor = $postalFloor;

        return $this;
    }

    /**
     * Get postalFloor.
     *
     * @return string|null
     */
    public function getPostalFloor()
    {
        return $this->postalFloor;
    }

    /**
     * Set postalDoorNumber.
     *
     * @param string|null $postalDoorNumber
     *
     * @return Firm
     */
    public function setPostalDoorNumber($postalDoorNumber = null)
    {
        $this->postalDoorNumber = $postalDoorNumber;

        return $this;
    }

    /**
     * Get postalDoorNumber.
     *
     * @return string|null
     */
    public function getPostalDoorNumber()
    {
        return $this->postalDoorNumber;
    }

    /**
     * Set videoUrl.
     *
     * @param string|null $videoUrl
     *
     * @return Firm
     */
    public function setVideoUrl($videoUrl = null)
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    /**
     * Get videoUrl.
     *
     * @return string|null
     */
    public function getVideoUrl()
    {
        return $this->videoUrl;
    }

    /**
     * Set webPageUrl.
     *
     * @param string|null $webPageUrl
     *
     * @return Firm
     */
    public function setWebPageUrl($webPageUrl = null)
    {
        $this->webPageUrl = $webPageUrl;

        return $this;
    }

    /**
     * Get webPageUrl.
     *
     * @return string|null
     */
    public function getWebPageUrl()
    {
        return $this->webPageUrl;
    }

    /**
     * Set socialMedia.
     *
     * @param string|null $socialMedia
     *
     * @return Firm
     */
    public function setSocialMedia($socialMedia = null)
    {
        $this->socialMedia = $socialMedia;

        return $this;
    }

    /**
     * Get socialMedia.
     *
     * @return string|null
     */
    public function getSocialMedia()
    {
        return $this->socialMedia;
    }

    /**
     * Set numberOfEmployees.
     *
     * @param int|null $numberOfEmployees
     *
     * @return Firm
     */
    public function setNumberOfEmployees($numberOfEmployees = null)
    {
        $this->numberOfEmployees = $numberOfEmployees;

        return $this;
    }

    /**
     * Get numberOfEmployees.
     *
     * @return int|null
     */
    public function getNumberOfEmployees()
    {
        return $this->numberOfEmployees;
    }

    /**
     * Set foundedYear.
     *
     * @param int|null $foundedYear
     *
     * @return Firm
     */
    public function setFoundedYear($foundedYear = null)
    {
        $this->foundedYear = $foundedYear;

        return $this;
    }

    /**
     * Get foundedYear.
     *
     * @return int|null
     */
    public function getFoundedYear()
    {
        return $this->foundedYear;
    }

    /**
     * Set introduction.
     *
     * @param string|null $introduction
     *
     * @return Firm
     */
    public function setIntroduction($introduction = null)
    {
        $this->introduction = $introduction;

        return $this;
    }

    /**
     * Get introduction.
     *
     * @return string|null
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Set visio.
     *
     * @param string|null $visio
     *
     * @return Firm
     */
    public function setVisio($visio = null)
    {
        $this->visio = $visio;

        return $this;
    }

    /**
     * Get visio.
     *
     * @return string|null
     */
    public function getVisio()
    {
        return $this->visio;
    }

    /**
     * Set whyDescription.
     *
     * @param string|null $whyDescription
     *
     * @return Firm
     */
    public function setWhyDescription($whyDescription = null)
    {
        $this->whyDescription = $whyDescription;

        return $this;
    }

    /**
     * Get whyDescription.
     *
     * @return string|null
     */
    public function getWhyDescription()
    {
        return $this->whyDescription;
    }

    /**
     * Set logo.
     *
     * @param string|null $logo
     *
     * @return Firm
     */
    public function setLogo($logo = null)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo.
     *
     * @return string|null
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set coverImage.
     *
     * @param string|null $coverImage
     *
     * @return Firm
     */
    public function setCoverImage($coverImage = null)
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    /**
     * Get coverImage.
     *
     * @return string|null
     */
    public function getCoverImage()
    {
        return $this->coverImage;
    }

    /**
     * Set whyUsImage.
     *
     * @param string|null $whyUsImage
     *
     * @return Firm
     */
    public function setWhyUsImage($whyUsImage = null)
    {
        $this->whyUsImage = $whyUsImage;

        return $this;
    }

    /**
     * Get whyUsImage.
     *
     * @return string|null
     */
    public function getWhyUsImage()
    {
        return $this->whyUsImage;
    }

    /**
     * Set viewedNumber.
     *
     * @param int|null $viewedNumber
     *
     * @return Firm
     */
    public function setViewedNumber($viewedNumber = null)
    {
        $this->viewedNumber = $viewedNumber;

        return $this;
    }

    /**
     * Get viewedNumber.
     *
     * @return int|null
     */
    public function getViewedNumber()
    {
        return $this->viewedNumber;
    }

    /**
     * Set status.
     *
     * @param FirmStatusEnum $status
     *
     * @return Firm
     */
    public function setStatus(FirmStatusEnum $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return FirmStatusEnum
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return FirmColleague|mixed
     */
    public function getContactInfo()
    {
        return !empty($this->getFirmColleague()) ? $this->getFirmColleague()->getContactInfo() : null;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Firm
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
     * @return Firm
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
     * Set deletedAt.
     *
     * @param \DateTime|null $deletedAt
     *
     * @return Firm
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt.
     *
     * @return \DateTime|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add offer.
     *
     * @param Offer $offer
     *
     * @return Firm
     */
    public function addOffer(Offer $offer)
    {
        $this->offers[] = $offer;

        return $this;
    }

    /**
     * Remove offer.
     *
     * @param Offer $offer
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeOffer(Offer $offer)
    {
        return $this->offers->removeElement($offer);
    }

    /**
     * Get offers.
     *
     * @return Collection
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @param FirmCv $firmCv
     *
     * @return $this
     */
    public function addCv(FirmCv $firmCv)
    {
        $this->cvs[] = $firmCv;

        return $this;
    }

    /**
     * @param FirmCv $firmCv
     *
     * @return bool
     */
    public function removeCv(FirmCv $firmCv)
    {
        return $this->cvs->removeElement($firmCv);
    }

    /**
     * @return ArrayCollection|FirmCv[]
     */
    public function getCvs()
    {
        return $this->cvs;
    }

    /**
     * @return int
     */
    public function getCvsCount()
    {
        return $this->cvs->count();
    }

    /**
     * @param EmployeeCv $employeeCv
     *
     * @return bool
     */
    public function isCvUnlocked(EmployeeCv $employeeCv)
    {
        /** @var FirmCv $cv */
        foreach ($this->getCvs() as $cv) {
            if ($cv->getEmployeeCv() === $employeeCv) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return FirmColleague
     */
    public function getFirmColleague()
    {
        return $this->firmColleague;
    }

    /**
     * @param FirmColleague $firmColleague
     *
     * @return $this
     */
    public function setFirmColleague(FirmColleague $firmColleague)
    {
        $this->firmColleague = $firmColleague;

        return $this;
    }

    /**
     * Set location.
     *
     * @param DicLocation $location
     *
     * @return Firm
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
     * @return DicLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set postalLocation.
     *
     * @param DicLocation $postalLocation
     *
     * @return Firm
     */
    public function setPostalLocation(DicLocation $postalLocation)
    {
        $this->postalLocation = $postalLocation;
        $this->postalLocationId = $postalLocation->getId();

        return $this;
    }

    /**
     * Get postalLocation.
     *
     * @return DicLocation
     */
    public function getPostalLocation()
    {
        return $this->postalLocation;
    }

    /**
     * Add cartItem.
     *
     * @param FirmCartItem $cartItem
     *
     * @return Firm
     */
    public function addCartItem(FirmCartItem $cartItem)
    {
        $this->cartItems[] = $cartItem;

        return $this;
    }

    /**
     * Remove cartItem.
     *
     * @param FirmCartItem $cartItem
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeCartItem(FirmCartItem $cartItem)
    {
        return $this->cartItems->removeElement($cartItem);
    }

    /**
     * Get cartItems.
     *
     * @return Collection
     */
    public function getCartItems()
    {
        return $this->cartItems;
    }

    /**
     * @return Collection|FirmOrder[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Add order.
     *
     * @param FirmOrder $order
     *
     * @return Firm
     */
    public function addOrders(FirmOrder $order): self
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order.
     *
     * @param FirmOrder $order
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeOrders(FirmOrder $order): bool
    {
        return $this->orders->removeElement($order);
    }

    /**
     * Add balance.
     *
     * @param FirmBalance $balance
     *
     * @return Firm
     */
    public function addBalance(FirmBalance $balance)
    {
        $this->balances[] = $balance;

        return $this;
    }

    /**
     * Remove balance.
     *
     * @param FirmBalance $balance
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeBalance(FirmBalance $balance)
    {
        return $this->balances->removeElement($balance);
    }

    /**
     * Get balances.
     *
     * @return Collection
     */
    public function getBalances()
    {
        return $this->balances;
    }

    /**
     * Set original.
     *
     * @param Firm|null $original
     *
     * @return Firm
     */
    public function setOriginal($original)
    {
        $this->original = $original;
        $this->original = $original
            ? $original->getId()
            : null
        ;

        return $this;
    }

    /**
     * @return Firm|mixed|null
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @param SpiritModelInterface|null $lastSpirit
     *
     * @return $this|SpiritModelInterface
     */
    public function setLastSpirit($lastSpirit)
    {
        $this->lastSpirit = $lastSpirit;
        $this->lastSpiritId = $lastSpirit
            ? $lastSpirit->getId()
            : null
        ;

        return $this;
    }

    /**
     * @return Firm|SpiritModelInterface|null
     */
    public function getLastSpirit()
    {
        return $this->lastSpirit;
    }

    /**
     * @return string|null
     */
    public function getLastActivity()
    {
        $lastUser = $this->getFirmColleague();
        if ($lastUser && $lastUser->getLastLoginTime()) {
            return $lastUser->getLastLoginTime()->format('Y.m.d');
        }

        return null;
    }

    /**
     * Get full address of hq.
     *
     * Examples:
     * Budapest VII. kerület, Rákóczi út 28. II. em.
     * Budapest VII. ker., Rákóczi út 28/A II. em. 10/B
     * Budapest VII., Rákóczi út 28/A II. 10/B 1072
     * Szekszárd, Kadarka u. 4–6. 2700
     *
     * @return mixed
     */
    public function getAddress()
    {
        $address = $this->location->getZip()->getValue().' '.$this->location->getCity()->getValue();
        $address .= ', '.$this->getStreet().' '.$this->getStreetNumber();
        if (!empty($this->floor)) {
            $address .= ' '.$this->floor.'. em';
        }
        if (!empty($this->doorNumber)) {
            $address .= ' '.$this->doorNumber;
        }

        return $address;
    }

    /**
     * Get short address of hq.
     *
     * Examples:
     * Budapest VII. kerület, Rákóczi út 28. II. em.
     * Budapest VII. ker., Rákóczi út 28/A II. em. 10/B
     * Budapest VII., Rákóczi út 28/A II. 10/B 1072
     * Szekszárd, Kadarka u. 4–6. 2700
     *
     * @return mixed
     */
    public function getShortAddress()
    {
        $address = $this->location->getZip()->getValue().' '.$this->location->getCity()->getValue();

        return $address;
    }
}
