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

use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\MappedSuperclassBase;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DeletedUser.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="user_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "user" = "Common\CoreBundle\Entity\User\DeletedUser",
 *     "employee" = "Common\CoreBundle\Entity\Employee\DeletedEmployee",
 *     "firm_colleague" = "Common\CoreBundle\Entity\Firm\DeletedFirmColleague",
 * })
 */
class DeletedUser extends MappedSuperclassBase
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="Your first name must be at least {{ limit }} characters long",
     *     maxMessage="Your first name cannot be longer than {{ limit }} characters"
     * )
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="Your first name must be at least {{ limit }} characters long",
     *     maxMessage="Your first name cannot be longer than {{ limit }} characters"
     * )
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank
     * @Assert\Email(
     *     message="The email '{{ value }}' is not a valid email.",
     *     checkMX=true
     * )
     */
    protected $email;

    /**
     * @var DeletedUserReason
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\User\DeletedUserReason"
     * )
     * @Assert\NotBlank(groups={"Deletion"}, message="Kérjük, válasszon legalább egyet a felsoroltak közül!")
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000)
     * @Assert\NotBlank(groups={"Deletion"}, message="Kérjük, írjon pár gondolatot, mivel tudnánk növelni a felhasználói élményt!")
     */
    private $reasonDescription;

    /**
     * DeletedUser constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * @return string
     */
    public function getFullNameReverse()
    {
        return $this->lastName.' '.$this->firstName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return DeletedUserReason
     */
    public function getReason(): ? DeletedUserReason
    {
        return $this->reason;
    }

    /**
     * @param DeletedUserReason $reason
     */
    public function setReason(DeletedUserReason $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @param $reasonDescription
     *
     * @return $this
     */
    public function setReasonDescription($reasonDescription)
    {
        $this->reasonDescription = $reasonDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonDescription()
    {
        return $this->reasonDescription;
    }
}
