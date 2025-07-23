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

namespace Common\CoreBundle\Doctrine\Repository\Firm\Package;

//use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Doctrine\ORM\EntityRepository;

/**
 * Class FirmOrderRepository.
 */
class FirmPackageRepository extends EntityRepository
{
    /**
     * @param Firm $firm
     *
     * @return mixed
     */
    public function findPackage($id)
    {
        return $this
            ->createQueryBuilder('fp')
            ->andWhere('fp.id= :id')
                ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }
}
