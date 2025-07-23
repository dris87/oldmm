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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\SpiritModelBundle\Model\SpiritModelInterface;

/**
 * Class FirmPackageService.
 *
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class FirmPackageService implements SpiritModelInterface
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
     * @var FirmPackageServiceEnum
     * @ORM\Column(type="firm_package_service_enum")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $serviceCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $extraServiceCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $packageId;

    /**
     * @var FirmPackage
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Package\FirmPackage",
     *     inversedBy="services",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $package;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $originalId;

    /**
     * @var FirmPackageService
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Package\FirmPackageService",
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
     * @var FirmPackageService
     *
     * @ORM\OneToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Package\FirmPackageService",
     * )
     * @ORM\JoinColumn(
     *     nullable=true,
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
     * Get id.
     *
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
     * Get name.
     *
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
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $serviceCount
     *
     * @return $this
     */
    public function setServiceCount($serviceCount)
    {
        $this->serviceCount = $serviceCount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getServiceCount()
    {
        return $this->serviceCount;
    }

    /**
     * @param $extraServiceCount
     *
     * @return $this
     */
    public function setExtraServiceCount($extraServiceCount)
    {
        $this->extraServiceCount = $extraServiceCount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraServiceCount()
    {
        return $this->extraServiceCount;
    }

    /**
     * @param $packageId
     *
     * @return $this
     */
    public function setPackageId($packageId)
    {
        $this->packageId = $packageId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackageId()
    {
        return $this->packageId;
    }

    /**
     * @param FirmPackage $package
     *
     * @return $this
     */
    public function setPackage(FirmPackage $package)
    {
        $this->package = $package;
        $this->packageId = $package->getId();

        return $this;
    }

    /**
     * @return FirmPackage
     */
    public function getPackage()
    {
        return $this->package;
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
     * @return FirmPackageService|mixed|null
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
     * @return FirmPackageService|SpiritModelInterface|null
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
