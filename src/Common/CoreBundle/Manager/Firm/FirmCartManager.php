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

use Common\CoreBundle\Entity\Firm\Cart\FirmCartItem;
use Common\CoreBundle\Entity\Firm\Cart\FirmCartItemService;
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Entity\Firm\Order\FirmOrder;
use Common\CoreBundle\Entity\Firm\Order\FirmOrderItem;
use Common\CoreBundle\Entity\Firm\Order\FirmOrderItemService;
use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Firm\Order\FirmOrderStatusEnum;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Enumeration\Firm\Payment\FirmPaymentMethodEnum;
use Common\CoreBundle\Presentation\CartItem;
use Common\CoreBundle\Presentation\CartItemService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class FirmCartManager.
 */
class FirmCartManager
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
     * @var ArrayCollection|CartItem[]
     */
    private $items = null;

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
    public function hasCart()
    {
        $token = $this->tokenStorage->getToken();

        $user = $token->getUser();

        return is_object($user) && ($user instanceof FirmColleague);
    }

    /**
     * @return \Common\CoreBundle\Entity\Firm\Firm
     */
    public function getFirm()
    {
        if (!$this->hasCart()) {
            throw new \RuntimeException('Logged in user is not a firm colleague');
        }

        if (empty($this->getUser()->getFirmId())) {
            throw new \RuntimeException('Firm colleague has no firm');
        }

        return $this->getUser()->getFirm();
    }

    /**
     * @return FirmOrder|null
     */
    public function createOrder()
    {
        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return null;
        }

        $em = $this->entityManager;

        $order = new FirmOrder();
        $order
            ->setStatus(FirmOrderStatusEnum::create(FirmOrderStatusEnum::INIT))
            ->setFirm($this->getFirm())
            ->setPriceNet($this->getPriceNet())
            ->setPriceGross($this->getPriceGross())
            ->setPaymentMethod(FirmPaymentMethodEnum::create(FirmPaymentMethodEnum::BANK_TRANSFER))
        ;

        $em->persist($order);

        foreach ($cartItems as $cartItem) {
            $this->createOrderItem($order, $cartItem);
        }

        $em->flush();

        return $order;
    }

    public function purge()
    {
        $em = $this->entityManager;

        foreach ($this->getCartItems() as $cartItem) {
            $this->remove($cartItem, false);
        }

        $em->flush();
    }

    /**
     * @return ArrayCollection|CartItem[]
     */
    public function getCartItems()
    {
        if (null === $this->items) {
            $firm = $this->getFirm();

            $entities = $this->entityManager->createQueryBuilder()
                ->select(['ci', 'p', 's', 'cis'])
                ->from('CommonCoreBundle:Firm\Cart\FirmCartItem', 'ci')
                ->innerJoin('ci.package', 'p')
                ->innerJoin('p.services', 's')
                ->leftJoin(
                    'ci.cartItemServices',
                    'cis'
                )
                ->andWhere('ci.firm = :firm')
                    ->setParameter('firm', $firm)
                ->andWhere('cis.id IS NULL or cis.serviceId = s.id')
                ->getQuery()
                ->getResult()
            ;

            $this->loadReferences($entities);

            $this->items = $this->hydrateCartItems($entities);
        }

        return $this->items;
    }

    /**
     * @return ArrayCollection
     */
    public function getStandardCartItems()
    {
        $result = new ArrayCollection();

        foreach ($this->getCartItems() as $cartItem) {
            if ($cartItem->isStandardPackage()) {
                $result->add($cartItem);
            }
        }

        return $result;
    }

    /**
     * @param FirmPackageServiceEnum|null $service
     *
     * @return ArrayCollection
     */
    public function getReferenceCartItems(FirmPackageServiceEnum $service = null)
    {
        $result = new ArrayCollection();

        foreach ($this->getCartItems() as $cartItem) {
            if ($cartItem->isReferencePackage($service)) {
                $result->add($cartItem);
            }
        }

        return $result;
    }

    /**
     * @param FirmPackage $package
     * @param int         $quantity
     * @param object|null $reference
     *
     * @return $this
     */
    public function add(FirmPackage $package, $quantity, $reference = null)
    {
        $this->addPackage($package, $quantity, $reference, false);

        return $this;
    }

    /**
     * @param FirmPackage $package
     * @param int         $quantity
     * @param object|null $reference
     *
     * @return $this
     */
    public function sub(FirmPackage $package, $quantity, $reference = null)
    {
        $this->addPackage($package, (-1) * $quantity, $reference, false);

        return $this;
    }

    /**
     * @param FirmPackage $package
     * @param int         $quantity
     * @param object|null $reference
     *
     * @return $this
     */
    public function set(FirmPackage $package, $quantity, $reference = null)
    {
        $this->addPackage($package, $quantity, $reference, true);

        return $this;
    }

    /**
     * @param CartItem $cartItem
     * @param bool     $flush
     */
    public function remove(CartItem $cartItem, $flush = true)
    {
        $id = $cartItem->getId();

        foreach ($this->getCartItems() as $ci) {
            if ($ci->getId() === $id) {
                $this->getCartItems()->removeElement($ci);

                break;
            }
        }

        $this->entityManager->remove($cartItem->getCartItem());

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param FirmPackage $package
     * @param object|null $reference
     *
     * @return CartItem|null
     */
    public function get(FirmPackage $package, $reference = null)
    {
        foreach ($this->getCartItems() as $cartItem) {
            if ($cartItem->isPackageMatched($package, $reference)) {
                return $cartItem;
            }
        }

        return null;
    }

    /**
     * @param FirmPackageServiceEnum $service
     * @param $reference
     *
     * @return CartItem|mixed|null
     */
    public function getByReference(FirmPackageServiceEnum $service, $reference)
    {
        foreach ($this->getCartItems() as $cartItem) {
            if ($cartItem->isReferencePackage($service, $reference)) {
                return $cartItem;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function getPriceNet()
    {
        $sum = 0;
        foreach ($this->getCartItems() as $item) {
            $sum += $item->getPackage()->getPrice() * $item->getQuantity();
        }

        return $sum;
    }

    /**
     * @return int
     */
    public function getPriceGross()
    {
        return round($this->getPriceNet() * (100 + FirmOrder::VAT_VALUE) / 100);
    }

    /**
     * @return int
     */
    public function getPriceVat()
    {
        return $this->getPriceGross() - $this->getPriceNet();
    }

    /**
     * @param FirmOrder $order
     * @param CartItem  $cartItem
     *
     * @return FirmOrderItem
     */
    private function createOrderItem(FirmOrder $order, CartItem $cartItem)
    {
        $orderItem = new FirmOrderItem();
        $orderItem
            ->setPackage($cartItem->getPackage())
            ->setCount($cartItem->getQuantity())
        ;
        $order->addItem($orderItem);

        $em = $this->entityManager;
        $em->persist($orderItem);

        foreach ($cartItem->getServices() as $cartItemService) {
            if (null === $cartItemService->getReference()) {
                continue;
            }

            $orderItemService = new FirmOrderItemService();
            $orderItemService
                ->setService($cartItemService->getService())
                ->setReferenceId($cartItemService->getReference()->getId())
            ;
            $orderItem->addOrderItemService($orderItemService);

            $em->persist($orderItemService);
        }

        return $orderItem;
    }

    /**
     * @param FirmPackage $package
     * @param $quantity
     * @param $reference
     * @param $override
     *
     * @return $this
     */
    private function addPackage(FirmPackage $package, $quantity, $reference, $override)
    {
        $cartItem = $this->get($package, $reference);

        if ($cartItem) {
            if ($override) {
                $cartItem->setQuantity($quantity);
            } else {
                $cartItem->addQuantity($quantity);
            }
        } else {
            $cartItem = $this->create($package, $quantity, $reference);
            $this->getCartItems()->add($cartItem);
        }

        if (0 >= $cartItem->getQuantity()) {
            $this->remove($cartItem);

            return $this;
        }

        $this->entityManager->flush();

        return $this;
    }

    /**
     * @param FirmPackage $package
     * @param int         $quantity
     * @param object      $reference
     *
     * @return CartItem
     */
    private function create(FirmPackage $package, $quantity, $reference = null)
    {
        $firmCartItem = new FirmCartItem();
        $firmCartItem
            ->setFirm($this->getFirm())
            ->setPackage($package)
            ->setQuantity($quantity)
        ;
        $this->entityManager->persist($firmCartItem);

        $cartItem = new CartItem($firmCartItem);
        $services = $cartItem->getServices();

        foreach ($package->getServices() as $packageService) {
            $hasReference = $packageService->getType()->hasReference();
            if ($hasReference && null === $reference) {
                throw new \RuntimeException('Created cart item have to be a reference object');
            }

            $cartItemService = new CartItemService($packageService);

            if ($hasReference) {
                $firmCartItemService = new FirmCartItemService();
                $firmCartItemService
                    ->setCartItem($firmCartItem)
                    ->setService($packageService)
                    ->setReferenceId($reference->getId())
                ;
                $this->entityManager->persist($firmCartItemService);

                $cartItemService->setReference($reference);
            }

            $services->add($cartItemService);
        }

        return $cartItem;
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
     * @param array $firmCartItems
     *
     * @return ArrayCollection
     */
    private function hydrateCartItems(array $firmCartItems)
    {
        $result = new ArrayCollection();

        foreach ($firmCartItems as $firmCartItem) {
            $item = new CartItem($firmCartItem);
            $this->addServicesToItem(
                $item,
                $firmCartItem
            );
            $result->add($item);
        }

        return $result;
    }

    /**
     * @param CartItem     $cartItem
     * @param FirmCartItem $firmCartItem
     */
    private function addServicesToItem(CartItem $cartItem, FirmCartItem $firmCartItem)
    {
        $services = $cartItem->getServices();

        foreach ($cartItem->getPackage()->getServices() as $packageService) {
            $cartSericeItem = new CartItemService($packageService);

            $firmCartItemService = $firmCartItem->getCartItemServiceByPackageService($packageService);

            if ($firmCartItemService) {
                $reference = $this->entityManager->find(Offer::class, $firmCartItemService->getReferenceId());

                if (empty($reference)) {
                    continue;
                }

                $cartSericeItem->setReference($reference);
            }

            $services->add($cartSericeItem);
        }
    }

    /**
     * @param array|FirmCartItem[] $firmCartItems
     */
    private function loadReferences(array &$firmCartItems)
    {
        $ids = [];

        foreach ($firmCartItems as $firmCartItem) {
            foreach ($firmCartItem->getCartItemServices() as $firmCartItemService) {
                $ids[] = $firmCartItemService->getReferenceId();
            }
        }

        $ids = array_unique($ids);

        if (empty($ids)) {
            return;
        }

        $this
            ->entityManager
            ->createQueryBuilder()
            ->select(['o'])
            ->from('CommonCoreBundle:Offer\Offer', 'o', 'o.id')
            ->andWhere('o.id IN (:ids)')
                ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }
}
