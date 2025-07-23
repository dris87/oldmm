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

namespace Common\CoreBundle\Doctrine\Repository\Firm;

use Common\CoreBundle\Entity\Firm\Firm;
use Doctrine\ORM\EntityRepository;

/**
 * Class FirmCvRepository.
 */
class FirmCvRepository extends EntityRepository
{
    /**
     * @param Firm $firm
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFirmCvsQuery(Firm $firm)
    {
        return $this->createQueryBuilder('fc')
            ->select(['fc,ecv,oc,e,el'])
            ->leftJoin('fc.employeeCv', 'ecv')
            ->leftJoin('fc.offerCandidate', 'oc')
            ->innerJoin('ecv.employee', 'e')
            ->innerJoin('e.location', 'el')
            ->andWhere('fc.firm = :firm')
            ->orderBy('fc.createdAt', 'DESC')
            ->setParameter('firm', $firm);
    }
}
