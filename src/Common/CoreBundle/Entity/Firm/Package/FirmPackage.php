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

namespace Common\CoreBundle\Entity\Firm\Package;

use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\SpiritModelBundle\Model\SpiritModelInterface;

/**
 * Class FirmPackage.
 *
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class FirmPackage implements SpiritModelInterface
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
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="blob", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $sign;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $timePiece;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1, options={"fixed" = true}, nullable=true)
     */
    private $timeUnit;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isExtra;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=6, options={"fixed" = true, "default" = "000000"})
     */
    private $color;

    /**
     * @var FirmPackageService
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Package\FirmPackageService",
     *     mappedBy="package",
     * )
     */
    private $services;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $originalId;

    /**
     * @var FirmPackage
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Package\FirmPackage"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true,
     * )
     */
    private $original;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastSpiritId;

    /**
     * @var FirmPackage
     *
     * @ORM\OneToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Package\FirmPackage"
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

    /**
     * FirmPackage constructor.
     */
    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param null $description
     *
     * @return $this
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * @param $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getPriceNet()
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getPriceGross()
    {
        return $this->price;
    }

    /**
     * @param null $timePiece
     *
     * @return $this
     */
    public function setTimePiece($timePiece = null)
    {
        $this->timePiece = $timePiece;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimePiece()
    {
        return $this->timePiece;
    }

    /**
     * @param null $timeUnit
     *
     * @return $this
     */
    public function setTimeUnit($timeUnit = null)
    {
        $this->timeUnit = $timeUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeUnit()
    {
        return $this->timeUnit;
    }

    /**
     * @param $isPublic
     *
     * @return $this
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param $isPublic
     *
     * @return $this
     */
    public function setIsExtra($isPublic)
    {
        $this->isExtra = $isPublic;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsExtra()
    {
        return $this->isExtra;
    }

    /**
     * @param $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param FirmPackageService $service
     *
     * @return $this
     */
    public function addService(FirmPackageService $service)
    {
        $this->services[] = $service;

        return $this;
    }

    /**
     * @param FirmPackageService $service
     *
     * @return bool
     */
    public function removeService(FirmPackageService $service)
    {
        return $this->services->removeElement($service);
    }

    /**
     * @return ArrayCollection|FirmPackageService
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param FirmPackageServiceEnum $enum
     *
     * @return FirmPackageService|mixed|null
     */
    public function getServiceByType(FirmPackageServiceEnum $enum)
    {
        foreach ($this->getServices() as $service) {
            if ($service->getType()->equals($enum)) {
                return $service;
            }
        }

        return null;
    }

    /**
     * @param FirmPackageServiceEnum $enum
     *
     * @return bool
     */
    public function hasServiceByType(FirmPackageServiceEnum $enum)
    {
        $service = $this->getServiceByType($enum);

        return (bool) $service;
    }

    /**
     * @return bool
     */
    public function getIsReferencePackage()
    {
        $services = $this->getServices();

        return 1 === $services->count() && $services->first()->getType()->hasReference();
    }

    /**
     * @param FirmPackageServiceEnum $enum
     *
     * @return int
     */
    public function getServiceCountByType(FirmPackageServiceEnum $enum)
    {
        $service = $this->getServiceByType($enum);

        return $service
            ? $service->getServiceCount()
            : 0
            ;
    }

    /**
     * @param $enumName
     *
     * @return int
     */
    public function getServiceCountByTypeName($enumName)
    {
        $enum = FirmPackageServiceEnum::createByReadable($enumName);

        return $this->getServiceCountByType($enum);
    }

    /**
     * @param int|null $originalId
     *
     * @return $this|SpiritModelInterface
     */
    public function setOriginalId($originalId)
    {
        $this->originalId = $originalId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOriginalId()
    {
        return $this->originalId;
    }

    /**
     * @param SpiritModelInterface|null $original
     *
     * @return $this|SpiritModelInterface
     */
    public function setOriginal($original)
    {
        $this->original = $original;
        $this->originalId = $original->getId()
            ? $original->getId()
            : null
        ;

        return $this;
    }

    /**
     * @return FirmPackage|mixed|null
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @param int|null $lastSpiritId
     *
     * @return $this|SpiritModelInterface
     */
    public function setLastSpiritId($lastSpiritId)
    {
        $this->lastSpiritId = $lastSpiritId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLastSpiritId()
    {
        return $this->lastSpiritId;
    }

    /**
     * @param SpiritModelInterface|null $lastSpirit
     *
     * @return $this|SpiritModelInterface
     */
    public function setLastSpirit($lastSpirit)
    {
        $this->lastSpirit = $lastSpirit;
        $this->lastSpiritId = $lastSpirit->getId()
            ? $lastSpirit->getId()
            : null
        ;

        return $this;
    }

    /**
     * @return FirmPackage|SpiritModelInterface|null
     */
    public function getLastSpirit()
    {
        return $this->lastSpirit;
    }

    /**
     * @param \DateTime $datetime
     *
     * @return $this|SpiritModelInterface
     */
    public function setCreatedAt(\DateTime $datetime)
    {
        $this->createdAt = $datetime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $datetime
     *
     * @return $this|SpiritModelInterface
     */
    public function setUpdatedAt(\DateTime $datetime)
    {
        $this->updatedAt = $datetime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}
