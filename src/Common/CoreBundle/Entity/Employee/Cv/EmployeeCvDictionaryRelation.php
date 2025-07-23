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

use Common\CoreBundle\Entity\Dictionary\DicExperience;
use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Entity\Dictionary\DictionaryLevel;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class EmployeeCvDictionaryRelation.
 *
 * @ORM\Entity
 */
class EmployeeCvDictionaryRelation
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
     * @ORM\Column(type="integer", options={"default" = 0})
     */
    private $discriminator;

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
     *     inversedBy="dictionaryRelations",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $employeeCv;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $dictionaryId;

    /**
     * @var DicExperience
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\Dictionary")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $dictionary;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $levelId;

    /**
     * @var DicLocation
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DictionaryLevel")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true,
     * )
     */
    private $level;

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
     * @param $discriminator
     *
     * @return $this
     */
    public function setDiscriminator($discriminator)
    {
        $this->discriminator = $discriminator;

        return $this;
    }

    /**
     * @return int
     */
    public function getDiscriminator()
    {
        return $this->discriminator;
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
     * @param $dictionaryId
     *
     * @return $this
     */
    public function setDictionaryId($dictionaryId)
    {
        $this->dictionaryId = $dictionaryId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDictionaryId()
    {
        return $this->dictionaryId;
    }

    /**
     * @param Dictionary $dictionary
     *
     * @return $this
     */
    public function setDictionary(Dictionary $dictionary)
    {
        $this->dictionary = $dictionary;
        $this->dictionaryId = $dictionary->getId();

        return $this;
    }

    /**
     * @return DicExperience
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * @param null $levelId
     *
     * @return $this
     */
    public function setLevelId($levelId = null)
    {
        $this->levelId = $levelId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * @param DictionaryLevel|null $level
     *
     * @return $this
     */
    public function setLevel(DictionaryLevel $level = null)
    {
        $this->level = $level;
        $this->levelId = $level
            ? $level->getId()
            : null
        ;

        return $this;
    }

    /**
     * @return DicLocation|null
     */
    public function getLevel()
    {
        return $this->level;
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
