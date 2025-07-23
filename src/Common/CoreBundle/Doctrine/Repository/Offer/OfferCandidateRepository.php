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

namespace Common\CoreBundle\Doctrine\Repository\Offer;

use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Offer\OfferCandidate;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class OfferCandidateRepository.
 */
class OfferCandidateRepository extends EntityRepository
{
    /**ó
     * @param Offer $offer
     * @param Employee $employee
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isDirectOfferCandidate(Offer $offer, Employee $employee)
    {
        return $this->createQueryBuilder('oc')
            ->leftJoin('oc.employeeCv', 'ocecv')
            ->leftJoin('ocecv.employee', 'oce')
            ->where('oc.offer = :offer')
            ->andWhere('oc.direct = 1')
            ->andWhere('oce = :employee')
            ->setParameter('offer', $offer)
            ->setParameter('employee', $employee)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns the paginator version
     * employee offer candidate rows.
     *
     * @param Employee $employee
     * @param int      $page
     * @param int      $numItems
     *
     * @return Pagerfanta
     */
    public function findEmployeeAppliedOffersPaginated(Employee $employee, int $page = 1, int $numItems = OfferCandidate::NUM_ITEMS): Pagerfanta
    {
        $query = $this->getEmployeeAppliedOffersQuery($employee);

        return $this->createPaginator($query, $page, $numItems);
    }

    /**
     * Return query of employee offer candidate rows.
     *
     * @param Employee $employee
     *
     * @return \Doctrine\ORM\Query
     */
    public function getEmployeeAppliedOffersQuery(Employee $employee)
    {
        return $this->createQueryBuilder('oc')
            ->leftJoin('oc.employeeCv', 'ocecv')
            ->andWhere('oc.direct = true')
            ->andWhere('ocecv.employee = :employee')
            ->orderBy('oc.createdAt', 'DESC')
            ->setParameter('employee', $employee)
            ->getQuery();
    }

    /**
     * @param Query $query
     * @param int   $page
     * @param int   $numItems
     *
     * @return Pagerfanta
     */
    public function createPaginator(Query $query, int $page, int $numItems): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage($numItems);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
