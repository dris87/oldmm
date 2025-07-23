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

namespace Common\CoreBundle\Entity\Development\Documentation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;

/**
 * Class DocumentationItem.
 *
 * @ORM\Entity
 */
class DocumentationItem implements InitializeModelInterface
{
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $description;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $groupId;

    /**
     * @var DocumentationGroup
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Development\Documentation\DocumentationGroup",
     *     inversedBy="items"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $group;

    /**
     * @var ArrayCollection|DocumentationItemCode[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Development\Documentation\DocumentationItemCode",
     *     mappedBy="item",
     *     cascade={"persist"}
     * )
     */
    private $codes;

    /**
     * @var ArrayCollection|DocumentationItemAlert[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Development\Documentation\DocumentationItemAlert",
     *     mappedBy="item",
     *     cascade={"persist"}
     * )
     */
    private $alerts;

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
     * EmployeeCv constructor.
     */
    public function __construct()
    {
        $this->codes = new ArrayCollection();
        $this->alerts = new ArrayCollection();
        $this->initializeModel();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return null !== $this->getTitle() ? $this->getTitle() : 'Ismeretlen';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): ? string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ? string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * @return DocumentationGroup|null
     */
    public function getGroup(): ? DocumentationGroup
    {
        return $this->group;
    }

    /**
     * @param DocumentationGroup $group
     */
    public function setGroup(DocumentationGroup $group): void
    {
        $this->setGroupId($group->getId());
        $this->group = $group;
    }

    /**
     * @param DocumentationItemCode $code
     *
     * @return $this
     */
    public function addCode(DocumentationItemCode $code)
    {
        $this->codes[] = $code;

        return $this;
    }

    /**
     * @param DocumentationItemCode $code
     *
     * @return bool
     */
    public function removeCode(DocumentationItemCode $code)
    {
        return $this->codes->removeElement($code);
    }

    /**
     * @return ArrayCollection|DocumentationItem[]
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * @return int
     */
    public function getCodesCount()
    {
        return $this->codes->count();
    }

    /**
     * @param DocumentationItemAlert $alert
     *
     * @return $this
     */
    public function addAlert(DocumentationItemAlert $alert)
    {
        $this->alerts[] = $alert;

        return $this;
    }

    /**
     * @param DocumentationItemAlert $alert
     *
     * @return bool
     */
    public function removeAlert(DocumentationItemAlert $alert)
    {
        return $this->alerts->removeElement($alert);
    }

    /**
     * @return ArrayCollection|DocumentationItemAlert[]
     */
    public function getAlerts()
    {
        return $this->alerts;
    }

    /**
     * @return int
     */
    public function getAlertsCount()
    {
        return $this->alerts->count();
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function initializeModel()
    {
        $this->codes = new ValueSetterCollection($this->codes, [
            'item' => $this,
        ]);
        $this->alerts = new ValueSetterCollection($this->alerts, [
            'item' => $this,
        ]);
    }
}
