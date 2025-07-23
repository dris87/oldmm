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

namespace Common\CoreBundle\Manager\Migration\Managers;

use Common\CoreBundle\Entity\Migration\Migration;
use Common\CoreBundle\Manager\Migration\Interfaces\EntityMigrationManagerInterface;
use Common\CoreBundle\Manager\Migration\Interfaces\MigrationAlgorithmInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class MigrationManager.
 */
abstract class MigrationManager implements EntityMigrationManagerInterface
{
    /**
     * @var array
     */
    public $data;

    /**
     * @var MigrationAlgorithmInterface
     */
    protected $algorithm;

    /**
     * @var Migration
     */
    private $entity;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * MigrationManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @param Migration $entity
     */
    public function setEntity(Migration $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return Migration
     */
    public function getEntity(): Migration
    {
        return $this->entity;
    }

    /**
     * @return bool
     */
    abstract public function execute(): bool;

    /**
     * @return bool
     */
    abstract public function analyzeData(): bool;

    abstract protected function fillEntity();

    /**
     * @throws \Exception
     *
     * @return MigrationAlgorithmInterface
     */
    protected function getAlgorithmInstance()
    {
        if (empty($this->data)) {
            throw new \Exception('Manager must need an array of data to process!');
        }

        $algorithmClassFQName = '\\'.str_replace('\\Managers\\', '\\Algorithms\\', __NAMESPACE__.'\\'.$this->entity->getAlgorithm()->getReadable().'MigrationAlgorithm');
        $this->algorithm = new $algorithmClassFQName($this->data);

        return $this->algorithm;
    }
}
