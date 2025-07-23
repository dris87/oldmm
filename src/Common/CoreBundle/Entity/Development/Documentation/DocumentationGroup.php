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
use Spirit\ModelManagerBundle\Model\InitializeModelInterface;

/**
 * Class DocumentationGroup.
 *
 * @ORM\Entity
 */
class DocumentationGroup implements InitializeModelInterface
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
    private $topicId;

    /**
     * @var DocumentationTopic
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Development\Documentation\DocumentationTopic",
     *     inversedBy="groups"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $topic;

    /**
     * @var ArrayCollection|DocumentationItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Common\CoreBundle\Entity\Development\Documentation\DocumentationItem",
     *     mappedBy="group",
     *     cascade={"persist"}
     * )
     */
    private $items;

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
        $this->initializeModel();
    }

    /**
     * @return string
     */
    public function __toString(): string
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
    public function getTopicId(): int
    {
        return $this->topicId;
    }

    /**
     * @param int $topicId
     */
    public function setTopicId(int $topicId): void
    {
        $this->topicId = $topicId;
    }

    /**
     * @return DocumentationTopic|null
     */
    public function getTopic(): ? DocumentationTopic
    {
        return $this->topic;
    }

    /**
     * @param DocumentationTopic $topic
     */
    public function setTopic(DocumentationTopic $topic): void
    {
        $this->setTopicId($topic->getId());
        $this->topic = $topic;
    }

    /**
     * @param DocumentationItem $item
     *
     * @return $this
     */
    public function addItem(DocumentationItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param DocumentationItem $item
     *
     * @return bool
     */
    public function removeItem(DocumentationItem $item)
    {
        return $this->items->removeElement($item);
    }

    /**
     * @return ArrayCollection|DocumentationItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return $this->items->count();
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
    }
}
