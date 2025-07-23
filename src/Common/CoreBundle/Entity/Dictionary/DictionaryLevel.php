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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="dictionary_level_type", type="smallint")
 * @ORM\DiscriminatorMap({
 *     0 = "Common\CoreBundle\Entity\Dictionary\DicExperienceLevel",
 *     1 = "Common\CoreBundle\Entity\Dictionary\DicEducationLevel",
 *     2 = "Common\CoreBundle\Entity\Dictionary\DicSoftwareExperienceLevel",
 *     3 = "Common\CoreBundle\Entity\Dictionary\DicLanguageLevel",
 *     4 = "Common\CoreBundle\Entity\Dictionary\DicItExperienceLevel",
 * })
 *
 * Class DictionaryLevel
 */
abstract class DictionaryLevel
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
     * @ORM\Column(type="dictionary_status_enum", options={"default" = 1})
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=6)
     */
    protected $locale;

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
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
