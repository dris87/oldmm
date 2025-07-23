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

namespace All4One\AppBundle\Manager;

use Common\CoreBundle\Entity\Firm\Cart\FirmCartItem;
use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Common\CoreBundle\Manager\Firm\FirmCartManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PackageManager.
 */
class PackageManager
{
    /**
     * @var FirmCartManager
     */
    private $cartManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * PackageManager constructor.
     *
     * @param FirmCartManager        $cartManager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(FirmCartManager $cartManager, EntityManagerInterface $entityManager)
    {
        $this->cartManager = $cartManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public function getVisiblePackages()
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select(['o', 's'])
            ->from('CommonCoreBundle:Firm\Package\FirmPackage', 'o', 'o.id')
            ->innerJoin('o.services', 's')
            ->andWhere('o.isPublic = :ispublic')
            ->setParameter('ispublic', true)
            ->addOrderBy('o.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array
     */
    public function getReferencePackages()
    {
        /* @var FirmPackage[] $packages */
        $packages = $this->entityManager
            ->createQueryBuilder()
            ->select(['o', 's'])
            ->from('CommonCoreBundle:Firm\Package\FirmPackage', 'o', 'o.id')
            ->innerJoin('o.services', 's')
            ->andWhere('o.isPublic = :ispublic')
            ->setParameter('ispublic', false)
            ->addOrderBy('o.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $result = [];

        foreach ($packages as $package) {
            if ($package->getIsReferencePackage()) {
                $result[] = $package;
            }
        }

        return $result;
    }

    /**
     * @param array|FirmPackage[] $packages
     *
     * @return array
     */
    public function getCartInfoOfPackages($packages)
    {
        $cm = $this->cartManager;
        $result = [];

        foreach ($packages as $package) {
            $cartItem = $cm->get($package, null);
            if (!$cartItem) {
                $cartItem = new FirmCartItem();
                $cartItem
                    ->setPackage($package)
                    ->setQuantity(0)
                ;
            }

            $result[$package->getId()] = $cartItem;
        }

        return $result;
    }
}
