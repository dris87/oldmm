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

namespace Common\CoreBundle\Doctrine\EventSubscriber;

use Common\CoreBundle\Entity\Firm\Order\FirmOrder;
use Common\CoreBundle\Enumeration\Firm\Order\FirmOrderStatusEnum;
use Common\CoreBundle\Manager\Firm\FirmBalanceManager;
use Common\CoreBundle\Manager\Szamlazzhu\CreateInvoice;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

/**
 * Class FirmOrderSubscriber.
 */
class FirmOrderSubscriber implements EventSubscriber
{
    /**
     * @var CreateInvoice
     */
    private $invoioceCreator;

    /**
     * @var FirmBalanceManager
     */
    private $balanceManager;

    /**
     * @var FirmOrder[]
     */
    private $invoicableOrders = [];

    /**
     * @var FirmOrder[]
     */
    private $activatableOrders = [];

    /**
     * FirmOrderSubscriber constructor.
     *
     * @param CreateInvoice      $invoiceCreator
     * @param FirmBalanceManager $balanceManager
     */
    public function __construct(CreateInvoice $invoiceCreator, FirmBalanceManager $balanceManager)
    {
        $this->invoioceCreator = $invoiceCreator;
        $this->balanceManager = $balanceManager;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'postPersist',
            'postUpdate',
        ];
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->postUpdateAndPersist($eventArgs);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->postUpdateAndPersist($eventArgs);
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $uow = $eventArgs->getEntityManager()->getUnitOfWork();

        $this->invoicableOrders = [];
        $this->activatableOrders = [];

        $this->collectInvoicableOrders($uow, $uow->getScheduledEntityInsertions());
        $this->collectInvoicableOrders($uow, $uow->getScheduledEntityUpdates());

        $this->collectActivatableOrders($uow, $uow->getScheduledEntityUpdates());
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function postUpdateAndPersist(LifecycleEventArgs $eventArgs)
    {
        $order = $eventArgs->getEntity();

        if (!($order instanceof FirmOrder)) {
            return;
        }

        $isInvoicable = in_array($order, $this->invoicableOrders, true);
        $isActivatable = in_array($order, $this->activatableOrders, true);
        $haveToFlush = false;

        if ($isInvoicable) {
            $this->invoioceCreator->createInvoiceOf($order);
            $haveToFlush = true;
        }

        if ($isActivatable) {
            $this->balanceManager->activateOrder($order);
            $haveToFlush = true;
        }

        if ($haveToFlush) {
            $eventArgs->getEntityManager()->flush($order);
        }
    }

    /**
     * @param UnitOfWork $uow
     * @param array      $collection
     */
    private function collectInvoicableOrders(UnitOfWork $uow, array $collection)
    {
        foreach ($collection as $order) {
            if (!($order instanceof FirmOrder)) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($order);

            if (!isset($changeSet['status'])) {
                continue;
            }

            $statusChange = $changeSet['status'];

            if (
                null === $statusChange[0] ||
                in_array($statusChange[1]->getValue(), [FirmOrderStatusEnum::INIT, FirmOrderStatusEnum::PAID]) &&
                !$statusChange[1]->equals($statusChange[0])
            ) {
                $this->invoicableOrders[] = $order;
            }
        }
    }

    /**
     * @param UnitOfWork $uow
     * @param array      $collection
     */
    private function collectActivatableOrders(UnitOfWork $uow, array $collection)
    {
        $paidStatus = FirmOrderStatusEnum::create(FirmOrderStatusEnum::PAID);

        foreach ($collection as $order) {
            if (!($order instanceof FirmOrder)) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($order);

            if (!isset($changeSet['status'])) {
                continue;
            }

            $statusChange = $changeSet['status'];

            if (
                null !== $statusChange[0] &&
                $statusChange[1]->equals($paidStatus) &&
                !$statusChange[1]->equals($statusChange[0])
            ) {
                $this->activatableOrders[] = $order;
            }
        }
    }
}
