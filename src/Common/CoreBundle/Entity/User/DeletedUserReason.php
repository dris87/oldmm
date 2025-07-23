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

namespace Common\CoreBundle\Entity\User;

use Common\CoreBundle\Entity\MappedSuperclassBase;
use Common\CoreBundle\Enumeration\User\DeletedUserReasonStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="reason_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "user" = "Common\CoreBundle\Entity\User\DeletedUserReason",
 *     "employee" = "Common\CoreBundle\Entity\Employee\DeletedEmployeeReason",
 *     "firm_colleague" = "Common\CoreBundle\Entity\Firm\DeletedFirmColleagueReason",
 * })
 *
 * Class DeletedUserReason
 */
class DeletedUserReason extends MappedSuperclassBase
{
    /**
     * @var DeletedUserReasonStatusEnum
     *
     * @ORM\Column(type="deleted_user_reason_status_enum", options={"default" = 1})
     */
    protected $status;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $position;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000)
     * @Assert\NotBlank
     */
    private $description;

    public function __construct()
    {
        $this->status = DeletedUserReasonStatusEnum::create(DeletedUserReasonStatusEnum::ACTIVE);
    }

    public function __toString()
    {
        return (!empty($this->description)) ? $this->description : '';
    }

    /**
     * @param $description
     *
     * @return $this
     */
    public function setDescription($description)
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
     * @return DeletedUserReasonStatusEnum
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param DeletedUserReasonStatusEnum $status
     */
    public function setStatus(DeletedUserReasonStatusEnum $status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getPosition(): ? int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
