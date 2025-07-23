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

use Common\CoreBundle\Enumeration\Development\Documentation\DocumentationItemCodeTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class DocumentationItemCode.
 *
 * @ORM\Entity
 */
class DocumentationItemCode
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $itemId;

    /**
     * @var DocumentationItem
     *
     * @ORM\ManyToOne(
     *     targetEntity="Common\CoreBundle\Entity\Development\Documentation\DocumentationItem",
     *     inversedBy="codes"
     * )
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $item;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $snippet;

    /**
     * @var DocumentationItemCodeTypeEnum
     *
     * @ORM\Column(type="development_documentation_item_code_type_enum", options={"default" = 0})
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
     * DocumentationItem constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
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
     * @return int
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     */
    public function setItemId(int $itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return DocumentationItem|null
     */
    public function getItem(): ? DocumentationItem
    {
        return $this->item;
    }

    /**
     * @param DocumentationItem $item
     */
    public function setItem(DocumentationItem $item): void
    {
        //$this->setItemId($item->getId());
        $this->item = $item;
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
     * @return string
     */
    public function getSnippet(): ? string
    {
        return $this->snippet;
    }

    /**
     * @param string $snippet
     */
    public function setSnippet(string $snippet): void
    {
        $this->snippet = $snippet;
    }

    /**
     * @return DocumentationItemCodeTypeEnum
     */
    public function getType(): ? DocumentationItemCodeTypeEnum
    {
        return $this->type;
    }

    /**
     * @param DocumentationItemCodeTypeEnum $type
     */
    public function setType(DocumentationItemCodeTypeEnum $type): void
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
