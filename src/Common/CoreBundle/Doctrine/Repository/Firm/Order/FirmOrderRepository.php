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

namespace Common\CoreBundle\Doctrine\Repository\Firm\Order;

use Common\CoreBundle\Entity\Firm\Firm;
use Doctrine\ORM\EntityRepository;

/**
 * Class FirmOrderRepository.
 */
class FirmOrderRepository extends EntityRepository
{
    /**
     * @param Firm $firm
     *
     * @return mixed
     */
    public function findWithPackagesByFirm(Firm $firm)
    {
        return $this
            ->createQueryBuilder('o')
            ->select(['o', 'oi', 'p', 's'])
            ->innerJoin('o.items', 'oi')
            ->leftJoin('oi.package', 'p')
            ->leftJoin('p.services', 's')
            ->addOrderBy('o.id', 'DESC')
            ->andWhere('o.firm = :firm')
                ->setParameter('firm', $firm)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findWithLivePackageByFirm(Firm $firm)
    {
        return $this
            ->createQueryBuilder('o')
            ->select(['o', 'oi', 'p', 's'])
            ->innerJoin('o.items', 'oi')
            ->leftJoin('oi.package', 'p')
            ->leftJoin('p.services', 's')
            ->addOrderBy('o.id', 'DESC')
            ->andWhere('o.firm = :firm')
                ->setParameter('firm', $firm)
            ->andWhere('o.status = :status')
                ->setParameter('status', 1)
            ->getQuery()
            ->getResult()
        ;
    }
}
