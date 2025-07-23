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

use Common\CoreBundle\Enumeration\Development\Documentation\DocumentationTopicTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 */
class DocumentationTopic
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $color;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $icon;
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var ArrayCollection|DocumentationGroup[]
     *
     * @ORM\OneToMany(targetEntity="Common\CoreBundle\Entity\Development\Documentation\DocumentationGroup", mappedBy="topic")
     */
    private $groups;

    /**
     * @var DocumentationTopicTypeEnum
     *
     * @ORM\Column(type="development_documentation_topic_type_enum", options={"default" = 0})
     */
    private $type;

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
     * DocumentationTopic constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (null !== $this->title) ? $this->title : 'Ismeretlen';
    }

    /**
     * @return int|null
     */
    public function getId(): ? int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
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
     * @return string|null
     */
    public function getColor(): ? string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ? string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @param \Common\CoreBundle\Entity\Development\Documentation\DocumentationGroup $group
     *
     * @return $this
     */
    public function addGroup(DocumentationGroup $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * @param DocumentationGroup $group
     *
     * @return bool
     */
    public function removeGroup(DocumentationGroup $group)
    {
        return $this->groups->removeElement($group);
    }

    /**
     * @return ArrayCollection|DocumentationGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return int
     */
    public function getGroupsCount()
    {
        return $this->groups->count();
    }

    /**
     * @return DocumentationTopicTypeEnum|null
     */
    public function getType(): ? DocumentationTopicTypeEnum
    {
        return $this->type;
    }

    /**
     * @param DocumentationTopicTypeEnum $type
     */
    public function setType(DocumentationTopicTypeEnum $type): void
    {
        $this->type = $type;
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
}
