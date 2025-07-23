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
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Enumeration\User\UserGenderEnum;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements as RollerworksPasswordRequirements;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\User\UserRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="user_type", type="smallint")
 * @ORM\DiscriminatorMap({
 *     0 = "Common\CoreBundle\Entity\User\User",
 *     1 = "Common\CoreBundle\Entity\Employee\Employee",
 *     2 = "Common\CoreBundle\Entity\Firm\FirmColleague",
 * })
 * @UniqueEntity(
 *     "email",
 *     repositoryMethod="findBy",
 *     message="validation.registered_email"
 * )
 */
class User implements AdvancedUserInterface, \Serializable
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
     * @ORM\Column(type="string", length=6)
     */
    protected $locale = 'hu_HU';

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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @RollerworksPasswordRequirements(
     *     requireLetters=true,
     *     requireCaseDiff=true,
     *     requireNumbers=true,
     *     requireSpecialCharacter=false,
     *     tooShortMessage="validation.password_too_short",
     *     missingLettersMessage="validation.password_missing_letter",
     *     requireCaseDiffMessage="validation.password_require_case_diff",
     *     missingNumbersMessage="validation.password_missing_number",
     *     missingSpecialCharacterMessage="validation.password_missing_special_character",
     *     groups={"registration"}
     * )
     * @Assert\NotBlank(groups={"registration"})
     */
    protected $plainPassword;

    /**
     * @SecurityAssert\UserPassword(
     *     message="validation.password.current.wrong",
     *     groups={"change_password"}
     * )
     */
    protected $oldPassword;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @var UserGenderEnum
     *
     * @ORM\Column(type="user_gender_enum", options={"default" = 1}, nullable=true)
     */
    protected $gender;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number")
     * @Assert\NotBlank
     * @AssertPhoneNumber(defaultRegion="HU")
     */
    protected $phoneNumber;

    /**
     * @var UserStatusEnum
     *
     * @ORM\Column(type="user_status_enum", options={"default" = 0})
     */
    protected $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime(format="Y-m-d")
     */
    protected $lastLoginTime;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->status = UserStatusEnum::create(UserStatusEnum::NOT_VERIFIED);
        //$this->gender = GenderEnum::create(GenderEnum::MALE);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
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
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return mixed
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * @param mixed $oldPassword
     */
    public function setOldPassword($oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles = Employee::ROLES;
        }

        return array_unique($roles);
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return UserGenderEnum
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param UserGenderEnum $gender
     */
    public function setGender(UserGenderEnum $gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @return string
     */
    public function getPhoneNumberReadable()
    {
        return '+'.$this->phoneNumber->getCountryCode().' '.$this->phoneNumber->getNationalNumber();
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return UserStatusEnum
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param UserStatusEnum $status
     */
    public function setStatus(UserStatusEnum $status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getLastLoginTime()
    {
        return $this->lastLoginTime;
    }

    /**
     * @param \DateTime $lastLoginTime
     */
    public function setLastLoginTime(\DateTime $lastLoginTime)
    {
        $this->lastLoginTime = $lastLoginTime;
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
     * @return \DateTime
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
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     * See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
     * we're using bcrypt in security.yml to encode the password, so
     * the salt value is built-in and you don't have to generate one.
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            $this->status->getValue(),
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        list(
            $this->id,
            $this->email,
            $this->password,
            $status
        ) = unserialize($serialized, ['allowed_classes' => false]);

        $this->status = UserStatusEnum::create($status);
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return !(UserStatusEnum::create(UserStatusEnum::NOT_VERIFIED) == $this->getStatus());
    }

    /**
     * @return bool
     */
    public function isFirmColleague()
    {
        return $this instanceof FirmColleague;
    }

    /**
     * @return bool
     */
    public function isEmployee()
    {
        return $this instanceof Employee;
    }

    public function getLastLoginTimeForList()
    {
        return $this->lastLoginTime ? $this->lastLoginTime->format('Y.m.d') : '';
    }

    /**
     * @return array
     */
    public static function getStatusListReverse()
    {
        return array_flip(UserStatusEnum::getReadables());
    }

    /**
     * @return string
     */
    public function getStatusString(): string
    {
        return $this->status->getReadable();
    }

    /**
     * @return array
     */
    public function getContactInfo(): array
    {
        $phone = '+'.$this->phoneNumber->getCountryCode().' '.$this->phoneNumber->getNationalNumber();

        return ['email' => "{$this->getEmail()}", 'phoneNumber' => "{$phone}"];
    }
}
