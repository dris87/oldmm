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

use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Entity\Dictionary\DictionaryLevel;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OfferDictionaryRelation.
 *
 * @ORM\Entity
 */
class OfferDictionaryRelation
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
    private $offerId;

    /**
     * @var Offer
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Offer\Offer",
     *     inversedBy="dictionaryRelations",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $offer;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $dictionaryId;

    /**
     * @var Dictionary
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\Dictionary", cascade={"persist"})
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false
     * )
     * @Assert\Valid(groups={"submit", "save"})
     */
    private $dictionary;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $levelId;

    /**
     * @var DictionaryLevel
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DictionaryLevel", cascade={"persist"})
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
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

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
     * @param $offerId
     *
     * @return $this
     */
    public function setOfferId($offerId)
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * @param Offer $offer
     *
     * @return $this
     */
    public function setOffer(Offer $offer)
    {
        $this->offer = $offer;
        $this->offerId = $offer->getId();

        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer()
    {
        return $this->offer;
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
     * @return Dictionary
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getDictionary()->getValue();
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
     * @return DictionaryLevel
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
