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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 */
class DicLocation
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
     * @ORM\Column(type="integer")
     */
    private $zipId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $countyId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $cityId;

    /**
     * @var DicZip
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Dictionary\DicZip",
     *     inversedBy="zipLocations"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     */
    private $zip;

    /**
     * @var DicCounty
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Dictionary\DicCounty",
     *     inversedBy="countyLocations",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     */
    private $county;

    /**
     * @var DicCity
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Dictionary\DicCity",
     *     inversedBy="cityLocations",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     */
    private $city;

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

    public function __toString()
    {
        return $this->getCity() ? $this->getCity()->getValue() : '';
    }

    /**
     * @return string
     */
    public function getFullLocation()
    {
        return $this->getZip()->getValue().' '.$this->getCity()->getValue().'('.$this->getCounty()->getValue().')';
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
     * Set zipId.
     *
     * @param int $zipId
     *
     * @return DicLocation
     */
    public function setZipId($zipId)
    {
        $this->zipId = $zipId;

        return $this;
    }

    /**
     * Get zipId.
     *
     * @return int
     */
    public function getZipId()
    {
        return $this->zipId;
    }

    /**
     * Set countyId.
     *
     * @param int $countyId
     *
     * @return DicLocation
     */
    public function setCountyId($countyId)
    {
        $this->countyId = $countyId;

        return $this;
    }

    /**
     * Get countyId.
     *
     * @return int
     */
    public function getCountyId()
    {
        return $this->countyId;
    }

    /**
     * Set cityId.
     *
     * @param int $cityId
     *
     * @return DicLocation
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId.
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return DicLocation
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
     * @return DicLocation
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
     * Set zip.
     *
     * @param \Common\CoreBundle\Entity\Dictionary\DicZip $zip
     *
     * @return DicLocation
     */
    public function setZip(\Common\CoreBundle\Entity\Dictionary\DicZip $zip)
    {
        $this->zip = $zip;
        $this->zipId = $zip->getId();

        return $this;
    }

    /**
     * Get zip.
     *
     * @return \Common\CoreBundle\Entity\Dictionary\DicZip
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set county.
     *
     * @param \Common\CoreBundle\Entity\Dictionary\DicCounty $county
     *
     * @return DicLocation
     */
    public function setCounty(\Common\CoreBundle\Entity\Dictionary\DicCounty $county)
    {
        $this->county = $county;
        $this->countyId = $county->getId();

        return $this;
    }

    /**
     * Get county.
     *
     * @return \Common\CoreBundle\Entity\Dictionary\DicCounty
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set city.
     *
     * @param \Common\CoreBundle\Entity\Dictionary\DicCity $city
     *
     * @return DicLocation
     */
    public function setCity(\Common\CoreBundle\Entity\Dictionary\DicCity $city)
    {
        $this->city = $city;
        $this->cityId = $city->getId();

        return $this;
    }

    /**
     * Get city.
     *
     * @return \Common\CoreBundle\Entity\Dictionary\DicCity
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get city name.
     *
     * @return string
     */
    public function getCityName()
    {
        return $this->city->getValue();
    }
}
