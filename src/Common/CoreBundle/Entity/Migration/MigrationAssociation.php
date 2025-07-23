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

namespace Common\CoreBundle\Entity\Offer;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * This entity is used to create associations
 * between data from source array and our entity field
 *
 * Class MigrationAssociation
 */
class MigrationAssociation
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
     */
    private $sourceFieldName;

    private $sourceFieldParent;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $associatedFieldName;

    private $associatedFieldType;

    private $associatedFieldAlgorithm;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceFieldName(): string
    {
        return $this->sourceFieldName;
    }

    /**
     * @param string $sourceFieldName
     */
    public function setSourceFieldName(string $sourceFieldName): void
    {
        $this->sourceFieldName = $sourceFieldName;
    }

    /**
     * @return string
     */
    public function getAssociatedFieldName(): string
    {
        return $this->associatedFieldName;
    }

    /**
     * @param string $associatedFieldName
     */
    public function setAssociatedFieldName(string $associatedFieldName): void
    {
        $this->associatedFieldName = $associatedFieldName;
    }
}
