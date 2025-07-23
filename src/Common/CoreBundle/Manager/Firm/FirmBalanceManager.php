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

namespace Common\CoreBundle\Manager\Firm;

use Common\CoreBundle\Entity\Firm\Balance\FirmBalance;
use Common\CoreBundle\Entity\Firm\Balance\FirmBalanceItem;
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Entity\Firm\Order\FirmOrder;
use Common\CoreBundle\Entity\Firm\Order\FirmOrderItem;
use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Manager\Firm\BalanceManager\ServiceActivatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class FirmBalanceManager.
 */
class FirmBalanceManager
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ArrayCollection|FirmBalance[]
     */
    private $balances = null;

    /**
     * @var array|ServiceActivatorInterface[]
     */
    private $activators = [];

    /**
     * FirmCartManager constructor.
     *
     * @param TokenStorageInterface  $tokenStorage
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * @return bool
     */
    public function hasBalance()
    {
        $user = $this->getUser();

        return is_object($user) && ($user instanceof FirmColleague);
    }

    /**
     * @return ArrayCollection|FirmBalance[]
     */
    public function getBalances()
    {
        if (null === $this->balances) {
            $firm = $this->getFirm();
            $balances = $this->entityManager
                ->createQueryBuilder()
                ->select(['o', 'i'])
                ->from('CommonCoreBundle:Firm\Balance\FirmBalance', 'o')
                ->leftJoin('o.items', 'i')
                ->andWhere('o.firm = :firm')
                    ->setParameter('firm', $firm)
                ->getQuery()
                ->getResult()
            ;

            $this->balances = $this->hydrateBalances($balances);
        }

        return $this->balances;
    }

    /**
     * @return FirmBalance|null
     */
    public function getLatestBalanceItem()
    {
        $selectedBalance = null;
        $expiredAt = null;

        foreach ($this->getBalances() as $balance) {
            if (null === $selectedBalance || $balance->getExpiredAt() >= $expiredAt) {
                $selectedBalance = $balance;
                $expiredAt = $selectedBalance->getExpiredAt();
            }
        }

        return $selectedBalance;
    }

    /**
     * @return FirmBalance|null
     */
    public function getFirstBalanceItem()
    {
        $selectedBalance = null;
        $expiredAt = null;

        foreach ($this->getBalances() as $balance) {
            if (null === $selectedBalance || $balance->getExpiredAt() <= $expiredAt) {
                $selectedBalance = $balance;
                $expiredAt = $selectedBalance->getExpiredAt();
            }
        }

        return $selectedBalance;
    }

    /**
     * @param string $service
     *
     * @return int
     */
    public function getServiceCount($service)
    {
        $serviceEnum = FirmPackageServiceEnum::createByReadable($service);

        return $this->getServiceCountByEnum($serviceEnum);
    }

    /**
     * @param FirmPackageServiceEnum $serviceEnum
     *
     * @return int
     */
    public function getServiceCountByEnum(FirmPackageServiceEnum $serviceEnum)
    {
        $result = 0;

        foreach ($this->getBalances() as $balance) {
            $result += $balance->getServiceCredit($serviceEnum);
        }

        return $result;
    }

    /**
     * @param string $service
     *
     * @return bool
     */
    public function hasService($service)
    {
        return $this->getServiceCount($service) > 0;
    }

    /**
     * @param FirmPackageServiceEnum $serviceEnum
     *
     * @return bool
     */
    public function hasServiceByEnum(FirmPackageServiceEnum $serviceEnum)
    {
        return $this->getServiceCountByEnum($serviceEnum) > 0;
    }

    /**
     * @param ServiceActivatorInterface $activator
     *
     * @return $this
     */
    public function addServiceActivator(ServiceActivatorInterface $activator)
    {
        $this->activators[] = $activator;

        return $this;
    }

    /**
     * @param FirmOrder $order
     *
     * @return $this
     */
    public function activateOrder(FirmOrder $order)
    {
        $items = $order->getItems();

        $items = $this->sortOrderItems($items);

        foreach ($items as $item) {
            $this->applyActivatorsOnOrderItem($item);
        }

        $this->entityManager->flush();

        return $this;
    }

    /**
     * TODO: ezt itt kitisztázni....
     *
     * @param FirmPackage $package
     * @param Offer       $offer
     *
     * @return bool
     */
    public function disableOfferService(FirmPackage $package, Offer $offer)
    {
        foreach ($package->getServices() as $packageService) {
            $hasReference = $packageService->getType()->hasReference();
            if (!$hasReference) {
                throw new \RuntimeException('Service item have to be a reference object');
            }

            switch ($packageService->getType()) {
                case FirmPackageServiceEnum::create(FirmPackageServiceEnum::OFFER_EXALTATION):
                    $offer->setOfferExaltationUntil(null);
                    break;
                case FirmPackageServiceEnum::create(FirmPackageServiceEnum::ADVANCE_FILTER):
                    $offer->setAdvanceFilterUntil(null);
                    break;
            }
        }

        $this->entityManager->persist($offer);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @return \Common\CoreBundle\Entity\Firm\Firm
     */
    public function getFirm()
    {
        if (!$this->hasBalance()) {
            throw new \RuntimeException('Logged in user is not a firm colleague');
        }

        if (empty($this->getUser()->getFirmId())) {
            throw new \RuntimeException('Firm colleague has no firm');
        }

        return $this->getUser()->getFirm();
    }

    /**
     * @param FirmPackageServiceEnum $packageServiceType
     * @param int                    $amount
     */
    public function resolveServiceCount(FirmPackageServiceEnum $packageServiceType, int $amount = 1)
    {
        $balanceItem = $this->getFirstBalanceItem();
        $balanceItems = $balanceItem->getItems();
        /** @var FirmBalanceItem $item */
        foreach ($balanceItems as $item) {
            if ($item->getType() == $packageServiceType) {
                $item->decrementCredit($amount);
                if ($item->getCredit() < 1) {
                    $balanceItem->getItems()->removeElement($item);
                    if ($balanceItem->getItems()->count() > 0) {
                        $this->entityManager->persist($balanceItem);
                    } else {
                        $this->entityManager->remove($balanceItem);
                    }
                    $this->entityManager->remove($item);
                } else {
                    $this->entityManager->persist($item);
                }
                $this->entityManager->flush();

                return;
            }
        }
    }

    /**
     * @param FirmOrderItem $orderItem
     */
    private function applyActivatorsOnOrderItem(FirmOrderItem $orderItem)
    {
        $package = $orderItem->getPackage();
        $em = $this->entityManager;

        foreach ($package->getServices() as $service) {
            foreach ($this->activators as $activator) {
                if ($activator->supportService($service)) {
                    $orderItemService = $orderItem->getOrderItemServiceByServiceEnum($service->getType());
                    $referenceId = $orderItemService
                        ? $orderItemService->getReferenceId()
                        : null
                    ;
                    $reference = $referenceId
                        ? $em->find('CommonCoreBundle:Offer\Offer', $referenceId)
                        : null
                    ;

                    $activator->activateService($orderItem, $service, $reference);

                    break;
                }
            }
        }
    }

    /**
     * @return FirmColleague
     */
    private function getUser()
    {
        $token = $this->tokenStorage->getToken();

        return $token->getUser();
    }

    /**
     * @param array|FirmBalance[] $balances
     *
     * @return ArrayCollection|FirmBalance[]
     */
    private function hydrateBalances(array $balances)
    {
        $result = new ArrayCollection();
        $now = new \DateTime();
        $haveToFlush = [];
        $em = $this->entityManager;

        foreach ($balances as $balance) {
            if ($balance->getExpiredAt() <= $now) {
                $em->remove($balance);
                $haveToFlush[] = $balance;
            } else {
                $result->add($balance);
            }
        }

        if (!empty($haveToFlush)) {
            $em->flush($haveToFlush);
        }

        return $result;
    }

    /**
     * @param Collection|FirmOrderItem[] $orderItems
     *
     * @return Collection|FirmOrderItem[]
     */
    private function sortOrderItems(Collection $orderItems)
    {
        $result = $orderItems->filter(function ($orderItem) {
            /* @var FirmOrderItem $orderItem */
            return !$orderItem->getPackage()->getIsExtra();
        });

        foreach ($orderItems as $orderItem) {
            if (null === $orderItem->getPackage()->getIsExtra()) {
                $result->add($orderItem);
            }
        }

        return $result;
    }
}
