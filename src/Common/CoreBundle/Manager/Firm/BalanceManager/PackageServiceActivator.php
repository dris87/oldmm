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

namespace Common\CoreBundle\Manager\Firm\BalanceManager;

use Common\CoreBundle\Entity\Firm\Balance\FirmBalance;
use Common\CoreBundle\Entity\Firm\Balance\FirmBalanceItem;
use Common\CoreBundle\Entity\Firm\Order\FirmOrderItem;
use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Common\CoreBundle\Entity\Firm\Package\FirmPackageService;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Manager\Firm\FirmBalanceManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PackageServiceActivator.
 */
class PackageServiceActivator implements ServiceActivatorInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FirmBalanceManager
     */
    private $balanceManager;

    /**
     * @var FirmBalance[]
     */
    private $balances = [];

    /**
     * GeneralPackageServiceActivator constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param FirmBalanceManager     $balanceManager
     */
    public function __construct(EntityManagerInterface $entityManager, FirmBalanceManager $balanceManager)
    {
        $this->entityManager = $entityManager;
        $this->balanceManager = $balanceManager;
    }

    /**
     * @param FirmPackageService $service
     *
     * @return bool
     */
    public function supportService(FirmPackageService $service)
    {
        if (!in_array($service->getType()->getValue(), [
            FirmPackageServiceEnum::CV_COUNT,
            FirmPackageServiceEnum::MATCH,
            FirmPackageServiceEnum::DATABASE,
        ])) {
            return false;
        }

        return true;
    }

    /**
     * @param FirmOrderItem      $orderItem
     * @param FirmPackageService $service
     * @param null               $reference
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function activateService(
        FirmOrderItem $orderItem,
        FirmPackageService $service,
        $reference = null
    ) {
        $balance = $this->getBalanceByOrderItem($orderItem);

        if (
            !$service->getPackage()->getIsExtra() ||
            null === ($balanceItem = $balance->getItemByType($service->getType()))
        ) {
            $balanceItem = new FirmBalanceItem();
            $balanceItem
                ->setType($service->getType())
                ->setCredit(0)
                ->setAvailableExtraCredit(0)
            ;
            $balance->addItem($balanceItem);
            $balanceItem->setType($service->getType());

            $this->entityManager->persist($balanceItem);
        }

        $balanceItem
            ->setCredit(
                $balanceItem->getCredit() +
                $orderItem->getCount() * $service->getServiceCount()
            )
            ->setAvailableExtraCredit(
                $balanceItem->getCredit() +
                $orderItem->getCount($service->getExtraServiceCount())
            )
        ;
    }

    /**
     * @param FirmOrderItem $orderItem
     *
     * @throws \Exception
     *
     * @return FirmBalance|null
     */
    protected function getBalanceByOrderItem(FirmOrderItem $orderItem)
    {
        $package = $orderItem->getPackage();
        if ($package->getIsExtra()) {
            return $this->balanceManager->getLatestBalanceItem();
        }

        $hash = spl_object_hash($orderItem);
        if (isset($this->balances[$hash])) {
            return $this->balances[$hash];
        }

        $balance = new FirmBalance();
        $balance
            ->setName($package->getName())
            ->setSign($package->getSign())
            ->setFirm($orderItem->getOrder()->getFirm())
            ->setExpiredAt($this->calculateExpiredAtByPackage($package))
        ;
        $this->entityManager->persist($balance);

        $this->balances[$hash] = $balance;

        return $balance;
    }

    /**
     * @param FirmPackage $package
     *
     * @throws \Exception
     *
     * @return \DateTime
     */
    private function calculateExpiredAtByPackage(FirmPackage $package)
    {
        $now = new \DateTime();

        $u = $package->getTimeUnit();
        $p = $package->getTimePiece();

        $interval = new \DateInterval('P'.$p.$u);

        return $now->add($interval);
    }
}
