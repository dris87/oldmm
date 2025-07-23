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

namespace Common\CoreBundle\Entity\Dictionary;

use Common\CoreBundle\Enumeration\Dictionary\DictionaryStatusEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\Dictionary\DictionaryRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="dictionary_type", type="smallint")
 * @ORM\DiscriminatorMap({
 *     0 = "Common\CoreBundle\Entity\Dictionary\DicLifeStyle",
 *     1 = "Common\CoreBundle\Entity\Dictionary\DicJobForm",
 *     2 = "Common\CoreBundle\Entity\Dictionary\DicSupport",
 *     3 = "Common\CoreBundle\Entity\Dictionary\DicExperience",
 *     4 = "Common\CoreBundle\Entity\Dictionary\DicEducation",
 *     5 = "Common\CoreBundle\Entity\Dictionary\DicCategory",
 *     6 = "Common\CoreBundle\Entity\Dictionary\DicSoftwareExperience",
 *     7 = "Common\CoreBundle\Entity\Dictionary\DicLanguage",
 *     8 = "Common\CoreBundle\Entity\Dictionary\DicCounty",
 *     9 = "Common\CoreBundle\Entity\Dictionary\DicCity",
 *     10 = "Common\CoreBundle\Entity\Dictionary\DicZip",
 *     11 = "Common\CoreBundle\Entity\Dictionary\DicItExperience",
 *     12 = "Common\CoreBundle\Entity\Dictionary\DicMarketStatus",
 *     13 = "Common\CoreBundle\Entity\Dictionary\DicDrivingLicense",
 *     14 = "Common\CoreBundle\Entity\Dictionary\DicShift",
 *     15 = "Common\CoreBundle\Entity\Dictionary\DicCompanyHelp",
 *     16 = "Common\CoreBundle\Entity\Dictionary\DicCompanyHelp",
 *     17 = "Common\CoreBundle\Entity\Dictionary\DicAdvantage",
 *     18 = "Common\CoreBundle\Entity\Dictionary\DicAdvantage",
 *     19 = "Common\CoreBundle\Entity\Dictionary\DicExpectation",
 *     20 = "Common\CoreBundle\Entity\Dictionary\DicDetail",
 *     21 = "Common\CoreBundle\Entity\Dictionary\DicDocumentType",
 *     22 = "Common\CoreBundle\Entity\Dictionary\DicPersonalStrength",
 *     23 = "Common\CoreBundle\Entity\Dictionary\DicNationality",
 *     24 = "Common\CoreBundle\Entity\Dictionary\DicPosition",
 *     25 = "Common\CoreBundle\Entity\Dictionary\DicSchool",
 *     26 = "Common\CoreBundle\Entity\Dictionary\DicIndustry",
 *     27 = "Common\CoreBundle\Entity\Dictionary\DicTask",
 * })
 *
 * Class Dictionary
 */
abstract class Dictionary implements InitializeModelInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $parentId;

    /**
     * @var int
     *
     * @ORM\Column(type="dictionary_status_enum", options={"default" = 1})
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000)
     * @Assert\NotBlank(groups={"submit", "save"})
     */
    protected $value;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=6)
     */
    protected $locale = 'hu_HU';

    /**
     * @var Dictionary
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Dictionary\Dictionary",
     *     mappedBy="parent"
     * )
     */
    protected $children;

    /**
     * @var DicExperience
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\Dictionary", inversedBy="children")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true,
     *     onDelete="CASCADE",
     * )
     */
    protected $parent;

    /**
     * @var Collection|DicLocation[]
     *
     * @ORM\OneToMany(targetEntity="Common\CoreBundle\Entity\Dictionary\DicLocation", mappedBy="zip")
     */
    protected $zipLocations;

    /**
     * @var Collection|DicLocation[]
     *
     * @ORM\OneToMany(targetEntity="Common\CoreBundle\Entity\Dictionary\DicLocation", mappedBy="county")
     */
    protected $countyLocations;

    /**
     * @var Collection|DicLocation[]
     *
     * @ORM\OneToMany(targetEntity="Common\CoreBundle\Entity\Dictionary\DicLocation", mappedBy="city")
     */
    protected $cityLocations;

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
     * Dictionary constructor.
     */
    public function __construct()
    {
        $this->status = DictionaryStatusEnum::create(DictionaryStatusEnum::ACTIVE);
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();

        $this->initializeModel();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $parentId
     *
     * @return $this
     */
    public function setParentId($parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param DictionaryStatusEnum $status
     *
     * @return $this
     */
    public function setStatus(DictionaryStatusEnum $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \Biplane\EnumBundle\Enumeration\EnumInterface|int|static
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
     * @param Dictionary $child
     *
     * @return $this
     */
    public function addChild(self $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @param Dictionary $child
     *
     * @return bool
     */
    public function removeChild(self $child)
    {
        return $this->children->removeElement($child);
    }

    /**
     * @return Dictionary|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Dictionary|null $parent
     *
     * @return $this
     */
    public function setParent(self $parent = null)
    {
        $this->parent = $parent;
        $this->parentId = $parent
            ? $parent->getId()
            : null
        ;

        return $this;
    }

    /**
     * @return DicExperience
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param DicLocation $zipLocation
     *
     * @return $this
     */
    public function addZipLocation(DicLocation $zipLocation)
    {
        $this->zipLocations[] = $zipLocation;

        return $this;
    }

    /**
     * @param DicLocation $zipLocation
     *
     * @return bool
     */
    public function removeZipLocation(DicLocation $zipLocation)
    {
        return $this->zipLocations->removeElement($zipLocation);
    }

    /**
     * @return Collection|DicLocation[]
     */
    public function getZipLocations()
    {
        return $this->zipLocations;
    }

    /**
     * @param DicLocation $countyLocation
     *
     * @return $this
     */
    public function addCountyLocation(DicLocation $countyLocation)
    {
        $this->countyLocations[] = $countyLocation;

        return $this;
    }

    /**
     * @param DicLocation $countyLocation
     *
     * @return bool
     */
    public function removeCountyLocation(DicLocation $countyLocation)
    {
        return $this->countyLocations->removeElement($countyLocation);
    }

    /**
     * @return Collection|DicLocation[]
     */
    public function getCountyLocations()
    {
        return $this->countyLocations;
    }

    /**
     * @param DicLocation $cityLocation
     *
     * @return $this
     */
    public function addCityLocation(DicLocation $cityLocation)
    {
        $this->cityLocations[] = $cityLocation;

        return $this;
    }

    /**
     * @param DicLocation $cityLocation
     *
     * @return bool
     */
    public function removeCityLocation(DicLocation $cityLocation)
    {
        return $this->cityLocations->removeElement($cityLocation);
    }

    /**
     * @return Collection|DicLocation[]
     */
    public function getCityLocations()
    {
        return $this->cityLocations;
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

    public function initializeModel()
    {
        $this->children = new ValueSetterCollection($this->children, [
            'parent' => $this,
        ]);
    }
}
