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

namespace Common\CoreBundle\Command\Migration;

use Common\CoreBundle\Entity\Migration\Migration;
use Common\CoreBundle\Manager\Migration\EntityMigrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A console command that activates the last order.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:run-migration
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:run-migration -vv
 *
 * Class RunMigrationCommand
 */
class RunMigrationCommand extends Command
{
    /**
     * To make your command lazily loaded, configure the $defaultName static property,
     * so it will be instantiated only when the command is actually called.
     *
     * @var string
     */
    protected static $defaultName = 'app:run-migration';

    /**
     * @var EntityMigrationManager
     */
    private $entityMigrationManager;

    /**
     * RunMigrationCommand constructor.
     *
     * @param EntityMigrationManager $entityMigrationManager
     */
    public function __construct(EntityMigrationManager $entityMigrationManager)
    {
        parent::__construct();

        $this->entityMigrationManager = $entityMigrationManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Run schaduled migrations!')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrations = $this->entityMigrationManager->getScheduledMigrations();

        if ($migrations) {
            try {
                /*
                 * @var Migration
                 */
                foreach ($migrations as $migration) {
                    $this->entityMigrationManager->setMigration($migration);
                    $executionResult = $this->entityMigrationManager->runMigration();
                    if ($executionResult) {
                        $output->writeln('Migration #'.$migration->getId().'-'.$migration->getName().' from '.$migration->getUrl().' executed successfully!');
                    } elseif (null === $executionResult) {
                        $output->writeln('WARNING: Migration #'.$migration->getId().'-'.$migration->getName().' from '.$migration->getUrl().' has no associated manager!');
                    } else {
                        $output->writeln('FAILED: Migration #'.$migration->getId().'-'.$migration->getName().' from '.$migration->getUrl().' failed to execute!');
                    }
                }
            } catch (\Exception $ex) {
                $output->writeln($ex->getTraceAsString());
                throw $ex;
            }
        } else {
            $output->writeln('There is no migration to run this time!');
        }
    }
}
