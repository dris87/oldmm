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

namespace Common\CoreBundle\Manager\Migration;

use Common\CoreBundle\Entity\Migration\Migration;
use Common\CoreBundle\Enumeration\Migration\MigrationStatusEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationTypeEnum;
use Common\CoreBundle\Manager\Migration\Interfaces\MigrationAlgorithmInterface;
use Common\CoreBundle\Manager\Migration\Managers\MigrationManager;
use Common\CoreBundle\Manager\Util\CurlManager;
use Common\CoreBundle\Manager\Util\XmlManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * This class will be responsible for calling the appropriate
 * migration manager for the requested entity.
 *
 * Class EntityMigrationManager
 */
class EntityMigrationManager
{
    /**
     * @var Migration
     */
    private $migration;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var XmlManager
     */
    private $xmlManager;

    /**
     * @var CurlManager
     */
    private $curlManager;

    /**
     * EntityMigrationManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param XmlManager             $xmlManager
     * @param CurlManager            $curlManager
     */
    public function __construct(EntityManagerInterface $em, XmlManager $xmlManager, CurlManager $curlManager)
    {
        $this->entityManager = $em;
        $this->xmlManager = $xmlManager;
        $this->curlManager = $curlManager;
    }

    /**
     * @return Migration
     */
    public function getMigration(): Migration
    {
        return $this->migration;
    }

    /**
     * @param Migration $migration
     */
    public function setMigration(Migration $migration): void
    {
        $this->migration = $migration;
    }

    /**
     * @param array|null $migrations
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function runScheduledMigrations(?array $migrations)
    {
        if (null === $migrations) {
            $migrations = $this->getScheduledMigrations();
        }
        if (empty($migrations)) {
            return false;
        }

        /** @var Migration $migration */
        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }

        return true;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function runMigration()
    {
        if (empty($this->migration)) {
            throw new \Exception('No Migration entity was set!');
        }

        /** @var MigrationManager $migrationManager */
        $migrationManager = $this->getManager();
        $migrationManager->setEntity($this->migration);

        $cache = new FilesystemCache('migrations');

        $cacheName = 'migration.entity.source.'.$this->migration->getId();

        if (!$cache->has($cacheName)) {
            $sourceArray = $this->getMigrationSourceArray();
            $cache->set($cacheName, $sourceArray);
            $migrationManager->setData($sourceArray);
        } else {
            $migrationManager->setData($cache->get($cacheName));
        }
        //$this->setEntityRunning($migration);

        if ($migrationManager->analyzeData()) {
            try {
                $execution = $migrationManager->execute();
            } catch (\Exception $exception) {
                exit('vége exceptionnel!');
            }
        }

        $this->migration->setLastExecuted(new \DateTime());
        if (!$execution) {
            $this->setEntityFailed($this->migration);

            return false;
        }

        $this->setEntityWaiting($this->migration);

        return true;
    }

    /**
     * Get all migration entities, which should run.
     *
     * @return Migration[]
     */
    public function getScheduledMigrations()
    {
        $migrations = $this->entityManager
            ->createQueryBuilder()
            ->select(['o'])
            ->from('CommonCoreBundle:Migration\Migration', 'o')
            ->andWhere('o.status = :status')
            ->setParameter('status', MigrationStatusEnum::create(MigrationStatusEnum::WAITING))
            ->getQuery()
            ->getResult();

        return $migrations;
    }

    /**
     * @param Migration $entity
     */
    public function setEntityRunning(Migration $entity)
    {
        $this->setEntityStatus($entity, MigrationStatusEnum::create(MigrationStatusEnum::RUNNING));
    }

    /**
     * @param Migration $entity
     */
    public function setEntityWaiting(Migration $entity)
    {
        $this->setEntityStatus($entity, MigrationStatusEnum::create(MigrationStatusEnum::WAITING));
    }

    /**
     * @param Migration $entity
     */
    public function setEntityFailed(Migration $entity)
    {
        $this->setEntityStatus($entity, MigrationStatusEnum::create(MigrationStatusEnum::FAILED));
    }

    /**
     * @throws \Exception
     *
     * @return array|\SimpleXMLElement
     */
    private function getMigrationSourceArray()
    {
        switch ($this->migration->getType()) {
            case MigrationTypeEnum::create(MigrationTypeEnum::XML):
                $xmlString = $this->curlManager->getStringFromUrl($this->migration->getUrl());
                // Todo: here we need to make sure we got back data!
                return $this->xmlManager->getXMLFromString($xmlString);
                break;
            case MigrationTypeEnum::create(MigrationTypeEnum::JSON):
                throw new \Exception('JSON format was not implemented yet!');
                break;
        }

        throw new \Exception('Unknown format!');
    }

    /**
     * @return bool
     */
    private function getManager()
    {
        $className = explode('\\', get_class($this->migration));
        $className = end($className);
        $managerClassFQName = '\\'.__NAMESPACE__.'\\Managers\\'.$className.'Manager';
        /* @var MigrationAlgorithmInterface $algorithmClass */
        return new $managerClassFQName($this->entityManager);
    }

    /**
     * @param Migration           $entity
     * @param MigrationStatusEnum $status
     */
    private function setEntityStatus(Migration $entity, MigrationStatusEnum $status)
    {
        $entity->setStatus($status);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
