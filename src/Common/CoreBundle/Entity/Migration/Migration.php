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

namespace Common\CoreBundle\Entity\Migration;

use Common\CoreBundle\Enumeration\Migration\MigrationAlgorithmEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationFrequencyEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationStatusEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationSyncTypeEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Spirit\ModelManagerBundle\Doctrine\Collection\ValueSetterCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="migration_type", type="smallint")
 * @ORM\DiscriminatorMap({
 *     0 = "Common\CoreBundle\Entity\Migration\Migration",
 *     1 = "Common\CoreBundle\Entity\Offer\OfferMigration",
 *     2 = "Common\CoreBundle\Entity\Firm\FirmMigration"
 * })
 */
class Migration
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
     *     minMessage="Migration name must be at least {{ limit }} characters long",
     *     maxMessage="Migration name cannot be longer than {{ limit }} characters"
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=3,
     *     max=500,
     *     minMessage="Migration url must be at least {{ limit }} characters long",
     *     maxMessage="Migration url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $url;

    /**
     * @var MigrationFrequencyEnum
     *
     * @ORM\Column(type="migration_frequency_enum", options={"default" = 0})
     */
    protected $frequency;

    /**
     * @var MigrationSyncTypeEnum
     *
     * @ORM\Column(type="migration_sync_type_enum", options={"default" = 1})
     */
    protected $syncType;

    /**
     * @var MigrationTypeEnum
     *
     * @ORM\Column(type="migration_type_enum", options={"default" = 1})
     */
    protected $type;

    /**
     * @var MigrationStatusEnum
     *
     * @ORM\Column(type="migration_status_enum", options={"default" = 0})
     */
    protected $status;

    /**
     * @var MigrationAlgorithmEnum
     *
     * @ORM\Column(type="migration_algorithm_enum", options={"default" = 0})
     */
    protected $algorithm;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastExecuted;

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
     * OfferMigration constructor.
     */
    public function __construct()
    {
        $this->status = MigrationStatusEnum::create(MigrationStatusEnum::DISABLED);
        $this->type = MigrationTypeEnum::create(MigrationTypeEnum::XML);
        $this->syncType = MigrationSyncTypeEnum::create(MigrationSyncTypeEnum::HALF);

        $this->initializeModel();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return null !== $this->name ? $this->name : '';
    }

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return MigrationFrequencyEnum
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param MigrationFrequencyEnum $frequency
     */
    public function setFrequency(MigrationFrequencyEnum $frequency): void
    {
        $this->frequency = $frequency;
    }

    /**
     * @return MigrationSyncTypeEnum
     */
    public function getSyncType()
    {
        return $this->syncType;
    }

    /**
     * @param MigrationSyncTypeEnum $syncType
     */
    public function setSyncType(MigrationSyncTypeEnum $syncType): void
    {
        $this->syncType = $syncType;
    }

    /**
     * @return MigrationTypeEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param MigrationTypeEnum $type
     */
    public function setType(MigrationTypeEnum $type): void
    {
        $this->type = $type;
    }

    /**
     * @return MigrationStatusEnum
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param MigrationStatusEnum $status
     */
    public function setStatus(MigrationStatusEnum $status): void
    {
        $this->status = $status;
    }

    /**
     * @return MigrationAlgorithmEnum
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @param MigrationAlgorithmEnum $algorithm
     */
    public function setAlgorithm(MigrationAlgorithmEnum $algorithm): void
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @return \DateTime
     */
    public function getLastExecuted(): ? \DateTime
    {
        return $this->lastExecuted;
    }

    /**
     * @param \DateTime $lastExecuted
     */
    public function setLastExecuted(\DateTime $lastExecuted): void
    {
        $this->lastExecuted = $lastExecuted;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Iinizalize the model collections here.
     */
    public function initializeModel()
    {
        /* Just an example
        $this->offers = new ValueSetterCollection($this->offers, [
            'firm' => $this,
        ]);
        */
    }
}
