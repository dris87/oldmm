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

namespace Common\CoreBundle\Command\Order;

use Common\CoreBundle\Enumeration\Firm\Order\FirmOrderStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A console command that activates the last order.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:activate-last-order
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:activate-last-order -vv
 *
 * Class ActivateLastOrderCommand
 */
class ActivateLastOrderCommand extends Command
{
    /**
     * To make your command lazily loaded, configure the $defaultName static property,
     * so it will be instantiated only when the command is actually called.
     *
     * @var string
     */
    protected static $defaultName = 'app:activate-last-order';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ActivateLastOrderCommand constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->entityManager = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Activate last order')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $order = $this->entityManager
            ->createQueryBuilder()
            ->select(['o'])
            ->from('CommonCoreBundle:Firm\FirmOrder', 'o')
            ->andWhere('o.status = :status')
                ->setParameter('status', FirmOrderStatusEnum::create(FirmOrderStatusEnum::INIT))
            ->addOrderBy('o.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult()
        ;

        if ($order) {
            try {
                $output->writeln('Order #'.$order->getId().' found.');
                $order->setStatus(FirmOrderStatusEnum::create(FirmOrderStatusEnum::PAID));
                $this->entityManager->flush();
                $output->writeln('Order #'.$order->getId().' activated.');
            } catch (\Exception $ex) {
                $output->writeln($ex->getTraceAsString());
                throw $ex;
            }
        } else {
            $output->writeln('There is no activatable order.');
        }
    }
}
