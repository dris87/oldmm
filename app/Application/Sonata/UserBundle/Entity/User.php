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

namespace Application\Sonata\UserBundle\Entity;

use Doctrine\DBAL\Types\DateType;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;

/**
 * Class User.
 *
 * @ORM\Entity
 * @ORM\Table(name="back_office_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="full_name", type="string")
     */
    protected $fullName;

    /**
     * @var DateType
     *
     * @ORM\Column(name="birth_date", type="date")
     */
    protected $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="emergency_contact", type="string", nullable=true)
     */
    protected $emergencyContact;

    /**
     * @var string
     *
     * @ORM\Column(name="emergency_contact_phone", type="string", nullable=true)
     */
    protected $emergencyContactPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="post", type="string")
     */
    protected $post;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="superior_id", referencedColumnName="id", nullable=true)
     */
    protected $superior;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string")
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="office", type="string")
     */
    protected $office;

    // admin list field view helpers

    /**
     * @var array
     */
    protected $nameAndOffice;

    /**
     * @var array
     */
    protected $emailAndPhone;

    /**
     * @var array
     */
    protected $superiorAndPost;

    public function __toString()
    {
        return $this->fullName ?: '';
    }

    /**
     * Get id.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fullName.
     *
     * @param string $fullName
     *
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->fullName;
    }

    /**
     * Set birthDate.
     *
     * @param \DateTime $birthDate
     *
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate.
     *
     * @return DateType
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set emergencyContact.
     *
     * @param string|null $emergencyContact
     *
     * @return User
     */
    public function setEmergencyContact($emergencyContact = null)
    {
        $this->emergencyContact = $emergencyContact;

        return $this;
    }

    /**
     * Get emergencyContact.
     *
     * @return string|null
     */
    public function getEmergencyContact()
    {
        return $this->emergencyContact;
    }

    /**
     * Set emergencyContactPhone.
     *
     * @param string|null $emergencyContactPhone
     *
     * @return User
     */
    public function setEmergencyContactPhone($emergencyContactPhone = null)
    {
        $this->emergencyContactPhone = $emergencyContactPhone;

        return $this;
    }

    /**
     * Get emergencyContactPhone.
     *
     * @return string|null
     */
    public function getEmergencyContactPhone()
    {
        return $this->emergencyContactPhone;
    }

    /**
     * Set post.
     *
     * @param string $post
     *
     * @return User
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post.
     *
     * @return string
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set office.
     *
     * @param string $office
     *
     * @return User
     */
    public function setOffice($office)
    {
        $this->office = $office;

        return $this;
    }

    /**
     * Get office.
     *
     * @return string
     */
    public function getOffice()
    {
        return $this->office;
    }

    /**
     * Set superior.
     *
     * @param \Application\Sonata\UserBundle\Entity\User|null $superior
     *
     * @return User
     */
    public function setSuperior(\Application\Sonata\UserBundle\Entity\User $superior = null)
    {
        $this->superior = $superior;

        return $this;
    }

    /**
     * Get superior.
     *
     * @return \Application\Sonata\UserBundle\Entity\User|null
     */
    public function getSuperior()
    {
        return $this->superior;
    }

    // admin list field view helpers

    public function getNameAndOffice()
    {
        return [
            'fullName' => $this->fullName,
            'office' => $this->office,
        ];
    }

    public function getEmailAndPhone()
    {
        return [
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }

    public function getSuperiorAndPost()
    {
        return [
            'superior' => $this->superior ?: '-',
            'post' => $this->superior ? $this->superior->getPost() : '',
        ];
    }
}
