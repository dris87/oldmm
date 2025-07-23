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

use Common\CoreBundle\Entity\Dictionary\DicPosition;
use Common\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FirmColleague.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\User\UserRepository")
 */
class FirmColleague extends User
{
    /**
     * Default roles for colleagues.
     */
    const ROLES = ['ROLE_COLLEAGUE'];

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $firmId;

    /**
     * @var Firm
     *
     * @ORM\OneToOne(
     *     targetEntity="Common\CoreBundle\Entity\Firm\Firm",
     *     inversedBy="firmColleague",
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="SET NULL",
     * )
     */
    private $firm;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $positionId;

    /**
     * @var DicPosition
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Dictionary\DicPosition")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     * )
     * @Assert\NotBlank
     */
    private $position;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * @param null $firmId
     *
     * @return $this
     */
    public function setFirmId($firmId = null)
    {
        $this->firmId = $firmId;

        return $this;
    }

    /**
     * @return int
     */
    public function getFirmId()
    {
        return $this->firmId;
    }

    /**
     * @param Firm $firm
     *
     * @return $this
     */
    public function setFirm(Firm $firm)
    {
        $this->firm = $firm;
        $this->firmId = $firm->getId();

        return $this;
    }

    /**
     * @return Firm
     */
    public function getFirm()
    {
        return $this->firm;
    }

    /**
     * @param null $positionId
     *
     * @return $this
     */
    public function setPositionId($positionId = null)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPositionId()
    {
        return $this->positionId;
    }

    /**
     * @param DicPosition $position
     *
     * @return $this
     */
    public function setPosition(DicPosition $position)
    {
        $this->position = $position;
        $this->positionId = $position->getId();

        return $this;
    }

    /**
     * @return DicPosition
     */
    public function getPosition()
    {
        return $this->position;
    }
}
